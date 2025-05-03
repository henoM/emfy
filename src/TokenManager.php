<?php
namespace App;

use GuzzleHttp\Client;

class TokenManager {
    private $env;
    private $file;
    private $tokens;
    
    public function __construct($env) {
        $this->env = $env;
        $this->file = __DIR__ . '/../tokens.json';
        $this->tokens = file_exists($this->file) ? json_decode(file_get_contents($this->file), true) : null;
    }

    public function getValidTokens() {
        if (!$this->tokens) {
            throw new \Exception('No tokens available');
        }
        $timeSinceCreation = time() - $this->tokens['created_at'];
        if ($timeSinceCreation > 1200) {
            $client = new Client();
            $url = 'https://' . $this->env['AMOCRM_SUBDOMAIN'] . '/oauth2/access_token';
            $res = $client->post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'client_id' => $this->env['AMOCRM_CLIENT_ID'],
                    'client_secret' => $this->env['AMOCRM_CLIENT_SECRET'],
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->tokens['refresh_token'],
                    'redirect_uri' => $this->env['AMOCRM_REDIRECT_URI'],
                ],
                'http_errors' => false
            ]);
            $status = $res->getStatusCode();
            $body = $res->getBody()->getContents();
            if ($status === 200) {
                $newTokens = json_decode($body, true);
                $newTokens['created_at'] = time();
                file_put_contents($this->file, json_encode($newTokens));
                $this->tokens = $newTokens;
            } else {
                throw new \Exception('Failed to refresh token: ' . $body);
            }
        }
        return $this->tokens;
    }
} 