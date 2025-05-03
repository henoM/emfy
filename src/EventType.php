<?php

namespace App;

class EventType
{
    public function detect($payload)
    {
        $map = [
            ['leads', 'update', 'Deal updated'],
            ['leads', 'add', 'Deal added'],
            ['contacts', 'update', 'Contact updated'],
            ['contacts', 'add', 'Contact added'],
        ];

        foreach ($map as [$entity, $action, $eventType]) {
            if (isset($payload[$entity][$action][0])) {
                $item = $payload[$entity][$action][0];
                return [
                    'eventType' => $eventType,
                    'entityType' => $entity,
                    'entityId' => $item['id'],
                    'name' => $item['name'] ?? ($entity === 'leads' ? 'Unnamed Deal' : 'Unnamed Contact'),
                    'responsiblePerson' => $item['responsible_user_id'] ?? 'Unknown',
                    'time' => date('Y-m-d H:i:s'),
                ];
            }
        }
        return null;
    }
}
