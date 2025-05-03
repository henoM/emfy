<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\TokenManager;
use App\EventDeduplicator;
use App\NoteCreator;
use App\EventType;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$tokenManager = new TokenManager($_ENV);
$deduplicator = new EventDeduplicator(__DIR__ . '/../processed_events.json');
$noteCreator = new NoteCreator($_ENV);
$event = new EventType();

$input = file_get_contents('php://input');
parse_str($input, $payload);

$eventInfo = $event->detect($payload);

if (!$eventInfo) {
    echo json_encode(['error' => 'No valid event found.']);
    exit;
}

$eventKey = $deduplicator->getEventKey($eventInfo);
if ($deduplicator->isDuplicate($eventKey, $eventInfo)) {
    exit;
}
$deduplicator->markProcessed($eventKey, $eventInfo);

$tokens = $tokenManager->getValidTokens();
$noteCreator->createNote($eventInfo, $tokens['access_token']);
