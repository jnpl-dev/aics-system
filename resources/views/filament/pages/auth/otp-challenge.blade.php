<x-filament-panels::page.simple>
    <style>
        .aics-otp-card {
            max-width: 640px;
            margin: 0 auto;
            padding: 2rem;
            border: 1px solid rgba(23, 99, 52, 0.24);
            border-radius: 14px;
            background: #ffffff;
            color: #176334;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
        }

        .dark .aics-otp-card {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 253, 255, 0.2);
            color: #fff;
            box-shadow: none;
        }

        .aics-otp-alert {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.875rem;
        }

        .aics-otp-alert--error {
            border: 1px solid #f4b4be;
            background: #fff1f4;
            color: #9b1c31;
        }

        .dark .aics-otp-alert--error {
            border-color: rgba(244, 114, 182, 0.35);
            background: rgba(127, 29, 29, 0.35);
            color: #fecdd3;
        }

        .aics-otp-form {
            margin-top: 1.4rem;
        }

        .aics-otp-row {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 0.8rem;
            margin-bottom: 0.65rem;
        }

        .aics-otp-label {
            font-size: 0.95rem;
            font-weight: 600;
            color: #176334;
        }

        .dark .aics-otp-label {
            color: #fffdff;
        }

        .aics-otp-star {
            color: #6C9C02;
        }

        .aics-otp-link {
            border: 0;
            background: transparent;
            color: #6C9C02;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
        }

        .aics-otp-link:disabled {
            cursor: not-allowed;
            opacity: 0.45;
            text-decoration: none;
        }

        .aics-otp-link:disabled:hover {
            color: #6C9C02;
            text-decoration: none;
        }

        .aics-otp-link:hover {
            color: #176334;
            text-decoration: underline;
        }

        .dark .aics-otp-link:hover {
            color: #a3e635;
        }

        .aics-otp-digit-group {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 0.55rem;
            margin-top: 0.35rem;
        }

        .aics-otp-digit {
            width: 100%;
            min-width: 0;
            border: 1px solid rgba(23, 99, 52, 0.25);
            border-radius: 10px;
            padding: 0.72rem 0;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 700;
            color: #176334;
            background: #fff;
        }

        .dark .aics-otp-digit {
            border-color: rgba(255, 253, 255, 0.22);
            color: #fffdff;
            background: rgba(255, 255, 255, 0.05);
        }

        .aics-otp-digit:focus {
            border-color: #6C9C02;
            outline: 3px solid rgba(108, 156, 2, 0.24);
            outline-offset: 0;
        }

        .aics-otp-submit {
            display: block;
            margin: 1.2rem auto 0;
            min-width: 150px;
            border: 0;
            border-radius: 9px;
            background: #176334;
            color: #fff;
            font-weight: 700;
            padding: 0.58rem 1.2rem;
            cursor: pointer;
        }

        .aics-otp-submit:hover {
            background: #6C9C02;
        }

        .aics-otp-submit:disabled {
            cursor: not-allowed;
            opacity: 0.85;
        }

        .aics-otp-submit-content {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .aics-otp-spinner {
            width: 0.9rem;
            height: 0.9rem;
            border-radius: 999px;
            border: 2px solid rgba(255, 255, 255, 0.35);
            border-top-color: #ffffff;
            animation: aics-otp-spin 0.7s linear infinite;
        }

        @keyframes aics-otp-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .aics-otp-reset {
            margin-top: 0.95rem;
            text-align: center;
        }

        .aics-otp-reset button {
            border: 0;
            background: transparent;
            color: #176334;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
        }

        .aics-otp-reset button:hover {
            color: #6C9C02;
            text-decoration: underline;
        }

        .dark .aics-otp-reset button {
            color: #fffdff;
        }

        .dark .aics-otp-reset button:hover {
            color: #a3e635;
        }
    </style>

    <div class="aics-otp-card" wire:init="sendInitialOtpIfNeeded">

        @if ($errors->any())
            <div class="aics-otp-alert aics-otp-alert--error">
                {{ $errors->first() }}
            </div>
        @endif

        <form wire:submit="verifyOtp" class="aics-otp-form">
            <div class="aics-otp-row">
                <label for="otp-digit-0" class="aics-otp-label">OTP Code<span class="aics-otp-star">*</span></label>

                <button
                    type="button"
                    wire:click="resendOtp"
                    class="aics-otp-link"
                    @disabled(! $this->otpSent)
                >
                    Resend OTP
                </button>
            </div>

            <div class="aics-otp-digit-group" data-otp-group>
                @for ($index = 0; $index < 6; $index++)
                    <input
                        id="otp-digit-{{ $index }}"
                        type="text"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="1"
                        wire:model.live="otpDigits.{{ $index }}"
                        class="aics-otp-digit"
                        data-otp-digit="{{ $index }}"
                        aria-label="OTP digit {{ $index + 1 }}"
                        @disabled(! $this->otpSent)
                    >
                @endfor
            </div>

            <button
                type="submit"
                class="aics-otp-submit"
                wire:loading.attr="disabled"
                wire:target="verifyOtp"
                @disabled(! $this->otpSent)
            >
                <span class="aics-otp-submit-content" wire:loading.remove wire:target="verifyOtp">
                    Verify OTP
                </span>
                <span class="aics-otp-submit-content" wire:loading.inline-flex wire:target="verifyOtp">
                    <span class="aics-otp-spinner" aria-hidden="true"></span>
                    Verifying...
                </span>
            </button>
        </form>

        <div class="aics-otp-reset">
            <button
                type="button"
                wire:click="useDifferentAccount"
            >
                Use a different account
            </button>
        </div>
    </div>

    <script>
        (() => {
            const digitInputs = Array.from(document.querySelectorAll('[data-otp-digit]'));

            if (!digitInputs.length) {
                return;
            }

            const sanitize = (value) => value.replace(/\D+/g, '');

            digitInputs.forEach((input, index) => {
                if (input.dataset.otpBound === '1') {
                    return;
                }

                input.dataset.otpBound = '1';

                input.addEventListener('input', (event) => {
                    const raw = sanitize(event.target.value);

                    if (raw.length > 1) {
                        raw.slice(0, 6).split('').forEach((digit, offset) => {
                            const target = digitInputs[index + offset];

                            if (target) {
                                target.value = digit;
                                target.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        });

                        const nextIndex = Math.min(index + raw.length, digitInputs.length - 1);
                        digitInputs[nextIndex]?.focus();

                        return;
                    }

                    event.target.value = raw;

                    if (raw !== '' && index < digitInputs.length - 1) {
                        digitInputs[index + 1].focus();
                    }
                });

                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Backspace' && input.value === '' && index > 0) {
                        digitInputs[index - 1].focus();
                    }

                    if (event.key === 'ArrowLeft' && index > 0) {
                        event.preventDefault();
                        digitInputs[index - 1].focus();
                    }

                    if (event.key === 'ArrowRight' && index < digitInputs.length - 1) {
                        event.preventDefault();
                        digitInputs[index + 1].focus();
                    }
                });

                input.addEventListener('paste', (event) => {
                    event.preventDefault();

                    const paste = sanitize(event.clipboardData?.getData('text') ?? '');

                    if (paste === '') {
                        return;
                    }

                    paste.slice(0, 6).split('').forEach((digit, offset) => {
                        const target = digitInputs[offset];

                        if (target) {
                            target.value = digit;
                            target.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });

                    const focusIndex = Math.min(paste.length, digitInputs.length) - 1;

                    if (focusIndex >= 0) {
                        digitInputs[focusIndex].focus();
                    }
                });
            });
        })();
    </script>
</x-filament-panels::page.simple>
