<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Student;
use App\Services\ItexmoService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected $smsService;

    public function __construct(ItexmoService $smsService)
    {
        $this->smsService = $smsService;
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
     * Store a newly created message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:individual,group',
            'recipient' => 'required|string',
            'message' => 'required|string|max:160'
        ]);

        $credits = $this->calculateSMSCredits($request->message);

        if ($request->type === 'individual') {
            $student = Student::where('student_id', $request->recipient)->first();
            if ($student) {
                try {
                    $phoneNumber = $this->formatPhoneNumber($student->phone_number);
                    $response = $this->smsService->sendSMS($phoneNumber, $request->message);
                    
                    Message::create([
                        'sender_id' => auth()->id(),
                        'recipient_type' => 'individual',
                        'recipient_value' => $student->student_id,
                        'message' => $request->message,
                        'status' => $response,
                        'credits_used' => $credits
                    ]);
                } catch (\InvalidArgumentException $e) {
                    return back()->with('error', $e->getMessage());
                }
            }
        } else {
            $students = Student::where('program', $request->recipient)->get();
            foreach ($students as $student) {
                try {
                    $phoneNumber = $this->formatPhoneNumber($student->phone_number);
                    $response = $this->smsService->sendSMS($phoneNumber, $request->message);
                    
                    Message::create([
                        'sender_id' => auth()->id(),
                        'recipient_type' => 'group',
                        'recipient_value' => $request->recipient,
                        'message' => $request->message,
                        'status' => $response,
                        'credits_used' => $credits
                    ]);
                } catch (\InvalidArgumentException $e) {
                    continue; 
                }
            }
        }

        return redirect()->back()->with('success', 'Message(s) sent successfully');
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
     * Format phone number to ensure it meets requirements.
     */
    private function formatPhoneNumber($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        
        if (substr($number, 0, 2) !== '09') {
            throw new \InvalidArgumentException('Phone number must start with 09');
        }
        
        if (strlen($number) !== 11) {
            throw new \InvalidArgumentException('Phone number must be 11 digits');
        }
        
        return $number;
    }

    /**
     * Calculate SMS credits needed based on message length.
     */
    private function calculateSMSCredits($message)
    {
        $length = strlen($message);
        
        if ($length <= 160) {
            return 1;
        }
        return ceil($length / 153); 
    }
}