<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Send Message') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('messages.store') }}" class="space-y-6">
                        @csrf

                        <!-- Message Type -->
                        <div>
                            <x-input-label for="type" :value="__('Send To')" />
                            <select id="type" name="type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required onchange="toggleRecipientInput()">
                                <option value="">Select Type</option>
                                <option value="individual">Individual Student</option>
                                <option value="group">Program Group</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Individual Recipient -->
                        <div id="individual-input" style="display: none;">
                            <x-input-label for="recipient-individual" :value="__('Student ID')" />
                            <x-text-input
                                id="recipient-individual"
                                name="recipient_individual"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Enter student ID"
                                :value="old('recipient_individual')" />
                            <x-input-error :messages="$errors->get('recipient_individual')" class="mt-2" />
                        </div>

                        <!-- Group Recipient -->
                        <div id="group-input" style="display: none;">
                            <x-input-label for="recipient-group" :value="__('Program')" />
                            <select
                                id="recipient-group"
                                name="recipient_group"
                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                <option value="{{ $program }}">{{ $program }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('recipient_group')" class="mt-2" />
                        </div>

                        <!-- Message -->
                        <div>
                            <x-input-label for="message" :value="__('Message')" />
                            <textarea id="message" name="message"
                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"
                                rows="4" maxlength="160" required
                                placeholder="Enter your message (max 160 characters)"></textarea>
                            <div class="text-sm text-gray-500 mt-1">
                                <span id="char-count">0</span>/160 characters
                            </div>
                            <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        </div>

                        <button type="button" onclick="debugFormValues()" class="text-sm text-gray-500">
                            Debug Form Values
                        </button>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Send Message') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Character count for message
        const messageInput = document.getElementById('message');
        const charCount = document.getElementById('char-count');

        messageInput.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;

            if (count > 160) {
                this.value = this.value.substring(0, 160);
                charCount.textContent = '160';
            }
        });

        function toggleRecipientInput() {
            const type = document.getElementById('type').value;
            const individualInput = document.getElementById('individual-input');
            const groupInput = document.getElementById('group-input');

            individualInput.style.display = type === 'individual' ? 'block' : 'none';
            groupInput.style.display = type === 'group' ? 'block' : 'none';
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();

            const type = document.getElementById('type').value;
            let recipientValue = '';

            if (type === 'individual') {
                recipientValue = document.getElementById('recipient-individual').value;
            } else if (type === 'group') {
                recipientValue = document.getElementById('recipient-group').value;
            }

            console.log('About to submit with recipient:', recipientValue);

            // Remove any existing hidden recipient input
            const existingHidden = document.getElementById('final-recipient');
            if (existingHidden) {
                existingHidden.remove();
            }

            // Create and add the hidden input
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'recipient';
            hiddenInput.id = 'final-recipient';
            hiddenInput.value = recipientValue;

            this.appendChild(hiddenInput);

            // Log final form data
            const formData = new FormData(this);
            console.log('Final form data:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            // Now submit the form
            this.submit();
        });

        function debugFormValues() {
            console.log('Type:', document.getElementById('type').value);
            console.log('Individual Recipient:', document.getElementById('recipient-individual').value);
            console.log('Group Recipient:', document.getElementById('recipient-group').value);
            console.log('Message:', document.getElementById('message').value);
        }
    </script>

    <script>
        function debugFormValues() {
            console.log('Type:', document.getElementById('type').value);
            console.log('Individual Recipient:', document.getElementById('recipient-individual').value);
            console.log('Group Recipient:', document.getElementById('recipient-group').value);
            console.log('Message:', document.getElementById('message').value);
        }
    </script>
</x-app-layout>