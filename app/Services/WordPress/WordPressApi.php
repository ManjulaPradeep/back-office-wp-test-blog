<?php

namespace App\Services\WordPress;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class WordPressApi
{
    protected Client $http;
    protected string $base = 'https://public-api.wordpress.com/rest/v1.1';

    public function __construct(string $accessToken)
    {
        $this->http = new Client([
            'base_uri' => $this->base,
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Accept' => 'application/json',
            ],
            'timeout' => 10,
        ]);
    }

    protected function decode(ResponseInterface $res)
    {
        return json_decode((string) $res->getBody(), true);
    }

    public function meSites()
    {
        $res = $this->http->get('/me/sites');
        return $this->decode($res);
    }

    public function sitePosts(string $siteId, array $params = [])
    {
        $res = $this->http->get("/sites/{$siteId}/posts", ['query' => $params]);
        return $this->decode($res);
    }

    public function createPost(string $siteId, array $payload)
    {
        $res = $this->http->post("/sites/{$siteId}/posts/new", ['form_params' => $payload]);
        return $this->decode($res);
    }

    public function updatePost(string $siteId, int $postId, array $payload)
    {
        $res = $this->http->post("/sites/{$siteId}/posts/{$postId}", ['form_params' => $payload]);
        return $this->decode($res);
    }

    public function deletePost(string $siteId, int $postId)
    {
        $res = $this->http->post("/sites/{$siteId}/posts/{$postId}/delete");
        return $this->decode($res);
    }

}
