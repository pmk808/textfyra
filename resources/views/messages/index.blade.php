<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Message History') }}
            </h2>
            @if(auth()->user()->is_admin)
                <a href="{{ route('messages.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Send New Message
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($messages->isEmpty())
                        <p class="text-center text-gray-500">No messages found.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 border-b-2 border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 border-b-2 border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Sender
                                        </th>
                                        <th class="px-6 py-3 border-b-2 border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-6 py-3 border-b-2 border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Recipient
                                        </th>
                                        <th class="px-6 py-3 border-b-2 border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Message
                                        </th>
                                        <th class="px-6 py-3 border-b-2 border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @foreach($messages as $message)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                                {{ $message->created_at->format('M d, Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                                {{ $message->sender->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                                {{ ucfirst($message->recipient_type) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                                {{ $message->recipient_value }}
                                            </td>
                                            <td class="px-6 py-4 border-b border-gray-200">
                                                <div class="max-w-xs overflow-hidden text-ellipsis">
                                                    {{ $message->message }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                                {{ $message->status }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $messages->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>