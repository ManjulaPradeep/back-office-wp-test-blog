<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class PostController extends Controller
{
    /**
     * Get WordPress API base URL for current user's site
     */
    private function getWpApiBase(): string
    {
        $siteId = Session::get('wp_site_id');

        if (!$siteId) {
            throw new \Exception('No WordPress site ID found in session');
        }

        $wpBaseUrl = config('services.wordpress.wp_base_url');
        $apiVersion = config('services.wordpress.wp_dev_api_version');
        $api = "rest/{$apiVersion}/sites/{$siteId}";
        return "{$wpBaseUrl}/{$api}";
    }

    /**
     * Get WordPress token from session
     */
    private function getWpToken(): string
    {
        $token = Session::get('wp_token');

        if (!$token) {
            throw new \Exception('No WordPress token found in session');
        }

        return $token;
    }

    /**
     * List posts (sync from WP first).
     */
    public function index(Request $request)
    {
        try {
            $token = $this->getWpToken();
            $wpApiBase = $this->getWpApiBase();

            $response = Http::withToken($token)->get("{$wpApiBase}/posts", [
                'number' => 100,
                'context' => 'edit',
                'status' => 'publish,draft,private'
            ]);

            if ($response->failed()) {
                Log::error('Failed to fetch posts from WordPress', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['error' => 'Failed to fetch posts from WordPress'], 500);
            }

            $responseData = $response->json();
            $wpPosts = $responseData['posts'] ?? [];

            Log::info('Fetched posts from WordPress', ['count' => count($wpPosts)]);

            foreach ($wpPosts as $wpPost) {
                Post::updateOrCreate(
                    ['wp_id' => $wpPost['ID']],
                    [
                        'title' => $wpPost['title'] ?? '',
                        'content' => $wpPost['content'] ?? '',
                        'status' => $wpPost['status'] ?? 'draft',
                        'wp_created_at' => isset($wpPost['date']) ?
                            Carbon::parse($wpPost['date']) : null,
                        'wp_modified_at' => isset($wpPost['modified']) ?
                            Carbon::parse($wpPost['modified']) : null,
                    ]
                );
            }

            $posts = Post::orderBy('priority', 'desc')
                         ->orderBy('wp_created_at', 'desc')
                         ->get();

            return response()->json([
                'posts' => $posts,
                'synced_count' => count($wpPosts),
                'total_count' => $posts->count()
            ]);

        } catch (\Exception $e) {
            dd($e);
            Log::error('Error in posts index: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load posts'], 500);
        }
    }

    /**
     * Create new post (sync to WP).
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'status' => 'required|string|in:publish,draft,private',
            'priority' => 'nullable|integer|min:0|max:100'
        ]);

        try {
            $token = $this->getWpToken();
            $wpApiBase = $this->getWpApiBase();

            $response = Http::withToken($token)->post("{$wpApiBase}/posts/new", [
                'title' => $request->title,
                'content' => $request->content ?? '',
                'status' => $request->status,
            ]);

            if ($response->failed()) {
                Log::error('Failed to create post on WordPress', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['error' => 'Failed to create post on WordPress'], 400);
            }

            $wpPost = $response->json();

            $post = Post::create([
                'wp_id' => $wpPost['ID'],
                'title' => $wpPost['title'] ?? $request->title,
                'content' => $wpPost['content'] ?? $request->content,
                'status' => $wpPost['status'] ?? $request->status,
                'priority' => $request->priority ?? 0,
                'wp_created_at' => isset($wpPost['date']) ?
                    Carbon::parse($wpPost['date']) : now(),
                'wp_modified_at' => isset($wpPost['modified']) ?
                    Carbon::parse($wpPost['modified']) : now(),
            ]);

            Log::info('Post created successfully', ['post_id' => $post->id, 'wp_id' => $wpPost['ID']]);

            return response()->json($post, 201);

        } catch (\Exception $e) {
            Log::error('Error creating post: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create post'], 500);
        }
    }

    /**
     * Get single post
     */
    public function show(Post $post)
    {
        return response()->json($post);
    }

    /**
     * Update post (both WP + local).
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'status' => 'required|string|in:publish,draft,private',
            'priority' => 'nullable|integer|min:0|max:100'
        ]);

        try {
            $token = $this->getWpToken();
            $wpApiBase = $this->getWpApiBase();

            $response = Http::withToken($token)->post("{$wpApiBase}/posts/{$post->wp_id}", [
                'title' => $request->title,
                'content' => $request->content ?? '',
                'status' => $request->status,
            ]);

            if ($response->failed()) {
                Log::error('Failed to update post on WordPress', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'wp_id' => $post->wp_id
                ]);
                return response()->json(['error' => 'Failed to update post on WordPress'], 400);
            }

            $wpPost = $response->json();

            $post->update([
                'title' => $wpPost['title'] ?? $request->title,
                'content' => $wpPost['content'] ?? $request->content,
                'status' => $wpPost['status'] ?? $request->status,
                'priority' => $request->priority ?? $post->priority,
                'wp_modified_at' => isset($wpPost['modified']) ?
                    Carbon::parse($wpPost['modified']) : now(),
            ]);

            Log::info('Post updated successfully', ['post_id' => $post->id, 'wp_id' => $post->wp_id]);

            return response()->json($post);

        } catch (\Exception $e) {
            Log::error('Error updating post: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update post'], 500);
        }
    }

    /**
     * Update only priority (Laravel-only feature)
     */
    public function updatePriority(Request $request, Post $post)
    {
        $request->validate([
            'priority' => 'required|integer|min:0|max:100'
        ]);

        $post->update(['priority' => $request->priority]);

        return response()->json($post);
    }

    /**
     * Delete post (both WP + local).
     */
    public function destroy(Post $post)
    {
        try {
            $token = $this->getWpToken();
            $wpApiBase = $this->getWpApiBase();

            $response = Http::withToken($token)->post("{$wpApiBase}/posts/{$post->wp_id}/delete");

            if ($response->failed()) {
                Log::error('Failed to delete post on WordPress', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'wp_id' => $post->wp_id
                ]);
                return response()->json(['error' => 'Failed to delete post on WordPress'], 400);
            }

            $post->delete();

            Log::info('Post deleted successfully', ['wp_id' => $post->wp_id]);

            return response()->json(['success' => true, 'message' => 'Post deleted successfully']);

        } catch (\Exception $e) {
            Log::error('Error deleting post: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete post'], 500);
        }
    }

    /**
     * Sync posts from WordPress (manual sync endpoint)
     */
    public function syncFromWordPress()
    {
        try {
            $token = $this->getWpToken();
            $wpApiBase = $this->getWpApiBase();

            $response = Http::withToken($token)->get("{$wpApiBase}/posts", [
                'number' => 100,
                'context' => 'edit',
                'status' => 'publish,draft,private'
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to sync from WordPress'], 500);
            }

            $responseData = $response->json();
            $wpPosts = $responseData['posts'] ?? [];
            $syncedCount = 0;

            foreach ($wpPosts as $wpPost) {
                Post::updateOrCreate(
                    ['wp_id' => $wpPost['ID']],
                    [
                        'title' => $wpPost['title'] ?? '',
                        'content' => $wpPost['content'] ?? '',
                        'status' => $wpPost['status'] ?? 'draft',
                        'wp_created_at' => isset($wpPost['date']) ?
                            Carbon::parse($wpPost['date']) : null,
                        'wp_modified_at' => isset($wpPost['modified']) ?
                            Carbon::parse($wpPost['modified']) : null,
                    ]
                );
                $syncedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$syncedCount} posts from WordPress"
            ]);

        } catch (\Exception $e) {
            Log::error('Error syncing from WordPress: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to sync from WordPress'], 500);
        }
    }
}
