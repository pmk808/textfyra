<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Student;
use App\Services\VonageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;  // Add this missing import

class MessageController extends Controller
{
    protected $vonageService;

    public function __construct(VonageService $vonageService)
    {
        $this->vonageService = $vonageService;
    }

    /**
     * Display message history.
     */
    public function index()
    {
        $messages = Message::with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('messages.index', compact('messages'));
    }

    /**
     * Display message creation form.
     */
    public function create()
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        $programs = Student::select('program')->distinct()->pluck('program');
        return view('messages.create', compact('programs'));
    }

    /**
     * Store and send a message.
     */
    public function store(Request $request)
    {
        Log::debug('Raw request data:', $request->all());

        $request->validate([
            'type' => 'required|in:individual,group',
            'recipient_individual' => 'required_if:type,individual',
            'recipient_group' => 'required_if:type,group',
            'message' => 'required|string|max:160'
        ]);

        try {
            // Determine recipient based on type
            $recipient = $request->type === 'individual'
                ? $request->recipient_individual
                : $request->recipient_group;

            Log::debug('Determined recipient:', ['type' => $request->type, 'recipient' => $recipient]);

            if ($request->type === 'individual') {
                Log::debug('Processing individual message');
                $student = Student::where('student_id', $recipient)->first();

                if (!$student) {
                    Log::warning('Student not found:', ['student_id' => $recipient]);
                    return redirect()->back()
                        ->with('error', 'Student not found')
                        ->withInput();
                }

                try {
                    Log::debug('Found student:', [
                        'student_id' => $student->student_id,
                        'phone' => $student->phone_number
                    ]);

                    $response = $this->vonageService->sendSMS($student->phone_number, $request->message);
                    Log::debug('Vonage service response:', $response);

                    Message::create([
                        'sender_id' => auth()->id(),
                        'recipient_type' => 'individual',
                        'recipient_value' => $student->student_id,
                        'message' => $request->message,
                        'status' => $response['success'] ? 'sent' : 'failed: ' . ($response['error'] ?? 'unknown error')
                    ]);

                    $status = $response['success'] ? 'success' : 'error';
                    $message = $response['message'];

                    return redirect()->back()
                        ->with($status, $message);
                } catch (\Exception $e) {
                    Log::error('Failed to process individual message:', [
                        'error' => $e->getMessage(),
                        'student_id' => $student->student_id
                    ]);

                    return redirect()->back()
                        ->with('error', 'Failed to send message: ' . $e->getMessage())
                        ->withInput();
                }
            } else {
                // Group message handling
                Log::debug('Processing group message:', ['program' => $request->recipient]);
                $students = Student::where('program', $request->recipient)->get();

                foreach ($students as $student) {
                    try {
                        $phoneNumber = $student->phone_number;
                        $response = $this->vonageService->sendSMS($phoneNumber, $request->message);

                        Message::create([
                            'sender_id' => auth()->id(),
                            'recipient_type' => 'group',
                            'recipient_value' => $request->recipient,
                            'message' => $request->message,
                            'status' => $response['success'] ? 'sent' : 'failed: ' . ($response['error'] ?? 'unknown error')
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send message: ' . $e->getMessage());
                        continue;
                    }
                }

                return redirect()->back()->with('success', 'Group messages processed');
            }
        } catch (\Exception $e) {
            Log::error('Exception in message store:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Failed to send message: ' . $e->getMessage())
                ->withInput();
        }
    }
}
