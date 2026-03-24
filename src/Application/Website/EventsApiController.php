<?php

declare(strict_types=1);

namespace Darkheim\Application\Website;

final class EventsApiController
{
    /** @var array<string,array<string,mixed>> */
    private const array EVENT_TIMES = [
        'bloodcastle' => [
            'name'     => 'Blood Castle',
            'opentime' => 300,
            'duration' => 0,
            'schedule' => ['01:00','03:00','05:00','07:00','09:00','11:00','13:00','15:00','17:00','19:00','21:00','23:00'],
        ],
        'devilsquare' => [
            'name'     => 'Devil Square',
            'opentime' => 300,
            'duration' => 0,
            'schedule' => ['00:00','02:00','04:00','06:00','08:00','10:00','12:00','14:00','16:00','18:00','20:00','22:00'],
        ],
        'chaoscastle' => [
            'name'     => 'Chaos Castle',
            'opentime' => 300,
            'duration' => 0,
            'schedule' => ['03:30','07:30','11:30','15:30','19:30','23:30'],
        ],
        'dragoninvasion' => [
            'name'     => 'Dragon Invasion',
            'opentime' => 0,
            'duration' => 900,
            'schedule' => ['03:15','07:15','11:15','15:15','19:15','23:15'],
        ],
        'goldeninvasion' => [
            'name'     => 'Golden Invasion',
            'opentime' => 0,
            'duration' => 900,
            'schedule' => ['04:45','10:45','16:45','22:45'],
        ],
        'castlesiege' => [
            'name'     => 'Castle Siege',
            'opentime' => 0,
            'duration' => 7200,
            'day'      => 'Saturday',
            'time'     => '22:30',
        ],
    ];

    public function render(): void
    {
        header('Content-Type: application/json');
        date_default_timezone_set('America/Winnipeg');

        try {
            $result = [];
            foreach (self::EVENT_TIMES as $eventId => $event) {
                if (! array_key_exists('day', $event)) {
                    $schedule = $event['schedule'];
                    $lastTime = $this->getEventPreviousTime($schedule);
                    $nextTime = $this->getEventNextTime($schedule);
                } else {
                    $lastTime = $this->getWeeklyEventPreviousTime((string) $event['day'], (string) $event['time']);
                    $nextTime = $this->getWeeklyEventNextTime((string) $event['day'], (string) $event['time']);
                }

                $result[$eventId] = [
                    'event'    => $event['name'],
                    'opentime' => $event['opentime'],
                    'duration' => $event['duration'],
                    'last'     => $lastTime,
                    'next'     => $nextTime,
                    'nextF'    => date('D g:i A', strtotime($nextTime)),
                    'offset'   => strtotime($nextTime) - strtotime($lastTime),
                    'timeleft' => strtotime($nextTime) - time(),
                ];
            }

            $requestedEvent = (string) ($_GET['event'] ?? '');
            if ($requestedEvent !== '' && array_key_exists($requestedEvent, $result)) {
                $result = $result[$requestedEvent];
            }

            http_response_code(200);
            echo json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR);
        }
    }

    /** @param array<int,string> $eventSchedule */
    private function getEventNextTime(array $eventSchedule): string
    {
        $currentTime = date('H:i');
        foreach ($eventSchedule as $time) {
            if ($time > $currentTime) {
                return date('Y-m-d ') . $time;
            }
        }

        $tomorrow = date('d', strtotime('tomorrow'));
        return date("Y-m-$tomorrow ") . $eventSchedule[0];
    }

    /** @param array<int,string> $eventSchedule */
    private function getEventPreviousTime(array $eventSchedule): string
    {
        $currentTime = date('H:i');
        foreach ($eventSchedule as $key => $time) {
            if ($time > $currentTime) {
                $last = $key - 1;
                if ($last < 0) {
                    $yesterday = date('d', strtotime('yesterday'));
                    return date("Y-m-$yesterday ") . end($eventSchedule);
                }

                return date('Y-m-d ') . $eventSchedule[$last];
            }
        }

        return date('Y-m-d ') . end($eventSchedule);
    }

    private function getWeeklyEventNextTime(string $day, string $time): string
    {
        $currentDay  = strtolower(date('l'));
        $currentTime = date('H:i');
        if (($currentDay === strtolower($day)) && $currentTime < $time) {
            return date('Y-m-d H:i', strtotime('today ' . $time));
        }

        return date('Y-m-d H:i', strtotime('next ' . $day . ' ' . $time));
    }

    private function getWeeklyEventPreviousTime(string $day, string $time): string
    {
        $currentDay  = strtolower(date('l'));
        $currentTime = date('H:i');
        if (($currentDay === strtolower($day)) && $currentTime > $time) {
            return date('Y-m-d H:i', strtotime('today ' . $time));
        }

        return date('Y-m-d H:i', strtotime('last ' . $day . ' ' . $time));
    }
}
