<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WordPress Back Office - Sign In</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'pulse-glow': 'pulseGlow 2s infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': {
                                opacity: '0'
                            },
                            '100%': {
                                opacity: '1'
                            }
                        },
                        slideUp: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(20px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            }
                        },
                        pulseGlow: {
                            '0%, 100%': {
                                boxShadow: '0 0 20px rgba(59, 130, 246, 0.3)'
                            },
                            '50%': {
                                boxShadow: '0 0 30px rgba(59, 130, 246, 0.5)'
                            }
                        },
                        float: {
                            '0%, 100%': {
                                transform: 'translateY(0px)'
                            },
                            '50%': {
                                transform: 'translateY(-10px)'
                            }
                        }
                    },
                    backdropBlur: {
                        'xs': '2px',
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-wordpress {
            background: linear-gradient(135deg, #21759b 0%, #1e6ba8 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-wordpress:hover {
            background: linear-gradient(135deg, #1e6ba8 0%, #1a5f9e 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(33, 117, 155, 0.3);
        }

        .btn-wordpress:active {
            transform: translateY(0);
        }

        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
            animation-duration: 8s;
        }

        .shape:nth-child(2) {
            top: 60%;
            right: 20%;
            animation-delay: 2s;
            animation-duration: 10s;
        }

        .shape:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
            animation-duration: 12s;
        }

        .loading-spinner {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 2px solid #ffffff;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .error-message {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }
    </style>
</head>

<body class="h-full overflow-hidden">
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape w-32 h-32 bg-white rounded-full blur-sm"></div>
        <div class="shape w-24 h-24 bg-blue-200 rounded-full blur-sm"></div>
        <div class="shape w-40 h-40 bg-purple-200 rounded-full blur-sm"></div>
    </div>

    <!-- Main Container -->
    <div class="min-h-full flex items-center justify-center px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-md w-full space-y-8 animate-slide-up">

            <!-- Header Section -->
            <div class="text-center">
                <!-- Logo -->
                <div
                    class="mx-auto h-16 w-16 bg-white rounded-full flex items-center justify-center shadow-lg mb-6 animate-pulse-glow">
                    <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.219-5.175 1.219-5.175s-.31-.619-.31-1.538c0-1.441.83-2.515 1.838-2.515.859 0 1.279.649 1.279 1.424 0 .869-.538 2.175-.829 3.388-.229.958.489 1.739 1.438 1.739 1.752 0 2.924-2.239 2.924-4.874 0-2.009-1.312-3.519-3.707-3.519-2.709 0-4.388 2.003-4.388 4.246 0 .779.271 1.329.699 1.759.199.229.219.429.149.67-.05.229-.229.909-.259 1.039-.041.179-.169.229-.349.139-1.279-.589-1.838-2.149-1.838-3.909 0-2.848 1.958-6.157 5.818-6.157 3.109 0 5.157 2.239 5.157 4.654 0 3.199-1.759 5.718-4.357 5.718-.869 0-1.669-.459-1.948-1.018 0 0-.459 1.759-.559 2.149-.229.949-.619 1.888-.999 2.548 1.089.339 2.269.539 3.499.539 6.619 0 11.99-5.367 11.99-11.987C24.007 5.367 18.636.001 12.017.001z" />
                    </svg>
                </div>

                <h2 class="text-3xl font-bold text-white mb-2">
                    {{ __('auth.welcome_back') }}
                </h2>
                <p class="text-blue-100 text-sm">
                    {{ __('auth.subtitle') }}
                </p>
            </div>

            <!-- Login Form Card -->
            <div class="glass-effect rounded-2xl shadow-2xl p-8 space-y-6">

                @if (session('error'))
                    <div class="error-message bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700 font-medium">
                                    {{ session('error') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('success'))
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700 font-medium">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Login Options -->
                <div class="space-y-4">

                    <!-- Primary WordPress Login Button -->
                    <button onclick="initiateWordPressLogin()" id="wp-login-btn"
                        class="btn-wordpress w-full flex justify-center items-center py-4 px-6 border border-transparent text-base font-semibold rounded-xl text-white shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-300 focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed group">
                        <span id="btn-content" class="flex items-center">
                            <svg class="w-5 h-5 mr-3 group-hover:animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.219-5.175 1.219-5.175s-.31-.619-.31-1.538c0-1.441.83-2.515 1.838-2.515.859 0 1.279.649 1.279 1.424 0 .869-.538 2.175-.829 3.388-.229.958.489 1.739 1.438 1.739 1.752 0 2.924-2.239 2.924-4.874 0-2.009-1.312-3.519-3.707-3.519-2.709 0-4.388 2.003-4.388 4.246 0 .779.271 1.329.699 1.759.199.229.219.429.149.67-.05.229-.229.909-.259 1.039-.041.179-.169.229-.349.139-1.279-.589-1.838-2.149-1.838-3.909 0-2.848 1.958-6.157 5.818-6.157 3.109 0 5.157 2.239 5.157 4.654 0 3.199-1.759 5.718-4.357 5.718-.869 0-1.669-.459-1.948-1.018 0 0-.459 1.759-.559 2.149-.229.949-.619 1.888-.999 2.548 1.089.339 2.269.539 3.499.539 6.619 0 11.99-5.367 11.99-11.987C24.007 5.367 18.636.001 12.017.001z" />
                            </svg>
                            {{ __('auth.login_with_wp') }}
                        </span>
                        <span id="loading-spinner" class="loading-spinner hidden"></span>
                    </button>

                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">{{ __('auth.secure_oauth') }}</span>
                        </div>
                    </div>

                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium text-blue-800">
                                    {{ __('auth.messages.admin_access_required') }}
                                </h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>{{ __('auth.messages.admin_access_message') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center text-sm text-blue-100 space-y-2">
                {{-- <p>{{ __('auth.messages.protected_by') }}</p> --}}

                <div class="flex justify-center items-center space-x-4">
                    <a href="#" class="hover:text-white transition-colors duration-200">{{ __('auth.privacy') }}</a>
                    <span>•</span>
                    <a href="#" class="hover:text-white transition-colors duration-200">{{ __('auth.terms') }}</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        let isLoading = false;

        function initiateWordPressLogin() {
            if (isLoading) return;

            isLoading = true;
            const btn = document.getElementById('wp-login-btn');
            const btnContent = document.getElementById('btn-content');
            const loadingSpinner = document.getElementById('loading-spinner');

            // Show loading state
            btn.disabled = true;
            btnContent.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');

            // Show loading toast
            showToast("{{ __('auth.redirecting') }}", 'info');

            // Simulate slight delay for UX
            setTimeout(() => {
                window.location.href = "{{ route('auth.login.wordpress') }}";
            }, 800);
        }

        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');

            const bgColor = {
                'info': 'bg-blue-600',
                'success': 'bg-green-600',
                'error': 'bg-red-600',
                'warning': 'bg-yellow-600'
            } [type] || 'bg-blue-600';

            toast.className =
                `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-1">${message}</div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            `;

            toastContainer.appendChild(toast);

            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 100);

            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        // Handle back button navigation
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || window.performance.navigation.type === 2) {
                // Reset button state if user navigates back
                isLoading = false;
                const btn = document.getElementById('wp-login-btn');
                const btnContent = document.getElementById('btn-content');
                const loadingSpinner = document.getElementById('loading-spinner');

                btn.disabled = false;
                btnContent.classList.remove('hidden');
                loadingSpinner.classList.add('hidden');
            }
        });

        // Add keyboard navigation
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && !isLoading) {
                initiateWordPressLogin();
            }
        });

        // Add subtle parallax effect to background shapes
        document.addEventListener('mousemove', function(event) {
            const shapes = document.querySelectorAll('.shape');
            const mouseX = event.clientX / window.innerWidth;
            const mouseY = event.clientY / window.innerHeight;

            shapes.forEach((shape, index) => {
                const speed = 0.5 + (index * 0.1);
                const x = (mouseX - 0.5) * speed * 20;
                const y = (mouseY - 0.5) * speed * 20;
                shape.style.transform = `translate(${x}px, ${y}px)`;
            });
        });
    </script>
</body>

</html>
