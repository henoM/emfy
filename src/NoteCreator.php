<?php
namespace App;

use GuzzleHttp\Client;

class NoteCreator {
    private $env;
    public function __construct(array $env) {
        $this->env = $env;
    }
    public function createNote(array $eventInfo, string $accessToken): bool {
        $entityType = $eventInfo['entityType'];
        $entityId = $eventInfo['entityId'];
        $noteText = $eventInfo['eventType'] . ' - Name: ' . $eventInfo['name'] . ', Responsible: ' . $eventInfo['responsiblePerson'] . ', Time: ' . $eventInfo['time'];
        $payload = [
            [
                'note_type' => 'common',
                'params' => [
                    'text' => $noteText
                ]
            ]
        ];
        $url = 'https://' . $this->env['AMOCRM_SUBDOMAIN'] . "/api/v4/$entityType/$entityId/notes";
        $client = new Client();
        $res = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
            'http_errors' => false
        ]);
        $status = $res->getStatusCode();
        $body = $res->getBody()->getContents();
        return $status === 200;
    }
} 