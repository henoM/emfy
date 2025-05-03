<?php
namespace App;

class EventDeduplicator {
    private $file;
    private $events;
    
    public function __construct($file) {
        $this->file = $file;
        $this->events = file_exists($file) ? json_decode(file_get_contents($file), true) ?: [] : [];
    }

    public function getEventKey($eventInfo) {
        $type = $eventInfo['eventType'] ?? '';
        $id = $eventInfo['entityId'] ?? '';
        return md5($type . '_' . $id);
    }

    public function isDuplicate($eventKey, $eventInfo) {
        $now = time();
        $hash = md5(json_encode($eventInfo));
        if (isset($this->events[$eventKey]) &&
            $this->events[$eventKey]['hash'] === $hash &&
            ($now - $this->events[$eventKey]['time']) < 10) {
            return true;
        }
        return false;
    }

    public function markProcessed($eventKey, $eventInfo) {
        $this->events[$eventKey] = [
            'time' => time(),
            'hash' => md5(json_encode($eventInfo))
        ];
        if (count($this->events) > 1000) {
            $this->events = array_slice($this->events, -1000, true);
        }
        file_put_contents($this->file, json_encode($this->events));
    }
} 