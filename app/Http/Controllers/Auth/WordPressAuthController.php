<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class WordPressAuthController extends Controller
{
    /**
     * Redirect to WordPress for authentication.
     */
    public function redirectToWP()
    {
        return Socialite::driver('wordpress')->redirect();
    }

    /**
     * Handle callback from WordPress.
     */
    public function handleWPCallback()
    {
        try {
            // @todo [MP-2025]: For future use, if we need stateless (API or SPA flow), uncomment the line below
            // $wpUser = Socialite::driver('wordpress')->stateless()->user();
            $wpUser = Socialite::driver('wordpress')->user();
            $loginErrorMessage = __('errors.auth.wp_login_failed');
        } catch (\Exception $e) {
            return redirect('/auth/login')->withErrors($loginErrorMessage);
        }

        $user = User::firstOrCreate(
            ['email' => $wpUser->getEmail()],
            [
                'name' => $wpUser->getName() ?? $wpUser->getNickname(),
                'password' => bcrypt(str()->random(16)),
            ]
        );

        Auth::login($user, true);

        return redirect('/dashboard');
    }
}
