<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class WordPressAuthController extends Controller
{
    /**
     * Redirect to WordPress for authentication.
     */
    public function redirectToWP()
    {
        return Socialite::driver('wordpress')->scopes(['global'])->redirect();
    }

    private function getWpApiBase(): string
    {
        $siteId = Session::get('wp_site_id');

        if (!$siteId) {
            throw new \Exception('No WordPress site ID found in session');
        }

        $wpBaseUrl = config('services.wordpress.wp_base_url');
        $apiVersion = config('services.wordpress.wp_dev_api_version');
        $api = "rest/{$apiVersion}";
        return "{$wpBaseUrl}/{$api}";
    }

    /**
     * Handle callback from WordPress.
     */
    public function handleWPCallback()
    {
        try {
            $wpUser = Socialite::driver('wordpress')->user();
        } catch (\Exception $e) {
            Log::error('WordPress OAuth failed: ' . $e->getMessage());
            return redirect('/auth/login')->withErrors(__('errors.auth.wp_login_failed'));
        }

        try {
            $wpApiBase = $this->getWpApiBase();

            $sitesResponse = Http::withToken($wpUser->token)->get("{$wpApiBase}/me/sites");

            if ($sitesResponse->failed()) {
                Log::error('Failed to fetch user sites', [
                    'status' => $sitesResponse->status(),
                    'body' => $sitesResponse->body()
                ]);
                return redirect('/auth/login')->withErrors(__('errors.auth.wp_api_failed'));
            }

            $sitesData = $sitesResponse->json();
            $sites = $sitesData['sites'] ?? [];

            if (empty($sites)) {
                return redirect('/auth/login')->withErrors(__('errors.auth.no_sites'));
            }

            $adminSite = null;
            foreach ($sites as $site) {
                if ($this->isUserAdminOfSite($site)) {
                    $adminSite = $site;
                    break;
                }
            }

            if (!$adminSite) {
                Log::warning('User has no admin sites', [
                    'user_email' => $wpUser->getEmail(),
                    'sites_count' => count($sites)
                ]);
                return redirect('/auth/login')->withErrors(__('errors.auth.not_admin'));
            }

            $user = User::updateOrCreate(
                ['email' => $wpUser->getEmail()],
                [
                    'name' => $wpUser->getName() ?? $wpUser->getNickname(),
                    'password' => bcrypt(str()->random(16)),
                    'wp_site_id' => $adminSite['ID'],
                    'wp_site_url' => $adminSite['URL'],
                    'wp_site_name' => $adminSite['name'],
                    'wp_user_id' => $wpUser->getId(),
                ]
            );

            Session::put('wp_token', $wpUser->token);
            Session::put('wp_site_id', $adminSite['ID']);
            Session::put('wp_site_url', $adminSite['URL']);

            Log::info('Admin user logged in successfully', [
                'user_id' => $user->id,
                'wp_site_id' => $adminSite['ID'],
                'wp_site_name' => $adminSite['name']
            ]);

            Auth::login($user, true);

            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            Log::error('WordPress authentication process failed: ' . $e->getMessage());
            return redirect('/auth/login')->withErrors(__('errors.auth.wp_login_failed'));
        }
    }

    /**
     * Check if user is admin of a specific site
     */
    private function isUserAdminOfSite(array $site): bool
    {

        if (isset($site['user_can_manage']) && $site['user_can_manage']) {
            return true;
        }

        if (isset($site['capabilities']) && is_array($site['capabilities'])) {
            $adminCapabilities = ['manage_options', 'edit_users', 'install_plugins'];
            foreach ($adminCapabilities as $cap) {
                if (isset($site['capabilities'][$cap]) && $site['capabilities'][$cap]) {
                    return true;
                }
            }
        }

        if (isset($site['roles']) && in_array('administrator', $site['roles'])) {
            return true;
        }

        return false;
    }

    /**
     * Logout user
     */
    public function logout()
    {
        Session::forget(['wp_token', 'wp_site_id', 'wp_site_url']);
        Auth::logout();
        return redirect('/auth/login');
    }
}
