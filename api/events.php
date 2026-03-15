<?php
define('access', 'api');
header('Content-Type: application/json');

// http://php.net/manual/en/timezones.php
date_default_timezone_set('America/Winnipeg');

$eventTimes = array(
	'bloodcastle' => array(
		'name' => 'Blood Castle',
		'opentime' => 300,
		'duration' => 0,
		'schedule' => array(
			'01:00','03:00','05:00','07:00','09:00','11:00','13:00','15:00','17:00','19:00','21:00','23:00',
		),
	),
	'devilsquare' => array(
		'name' => 'Devil Square',
		'opentime' => 300,
		'duration' => 0,
		'schedule' => array(
			'00:00','02:00','04:00','06:00','08:00','10:00','12:00','14:00','16:00','18:00','20:00','22:00',
		),
	),
	'chaoscastle' => array(
		'name' => 'Chaos Castle',
		'opentime' => 300,
		'duration' => 0,
		'schedule' => array(
			'03:30','07:30','11:30','15:30','19:30','23:30',
		),
	),
	'dragoninvasion' => array(
		'name' => 'Dragon Invasion',
		'opentime' => 0,
		'duration' => 900,
		'schedule' => array(
			'03:15','07:15','11:15','15:15','19:15','23:15',
		),
	),
	'goldeninvasion' => array(
		'name' => 'Golden Invasion',
		'opentime' => 0,
		'duration' => 900,
		'schedule' => array(
			'04:45','10:45','16:45','22:45',
		),
	),
	'castlesiege' => array(
		'name' => 'Castle Siege',
		'opentime' => 0,
		'duration' => 7200,
		'day' => 'Saturday',
		'time' => '22:30',
	),
);

// DO NOT EDIT BELOW THIS LINE

function getEventNextTime($eventSchedule): string {
	$currentTime = date("H:i");
	foreach($eventSchedule as $time) {
		if($time > $currentTime) {
			return date("Y-m-d ") . $time;
		}
	}
	$tomorrow = date('d', strtotime('tomorrow'));
	return date("Y-m-$tomorrow ") . $eventSchedule[0];
}

function getEventPreviousTime($eventSchedule): string {
	$currentTime = date("H:i");
	foreach($eventSchedule as $key => $time) {
		if($time > $currentTime) {
			$last = $key - 1;
			if($last < 0) {
				$yesterday = date('d', strtotime('yesterday'));
				return date("Y-m-$yesterday ") . end($eventSchedule);
			}
			return date("Y-m-d ") . $eventSchedule[$last];
		}
	}
	return date("Y-m-d ") . end($eventSchedule);
}

function getWeeklyEventNextTime($day, $time): string {
	$currentDay = strtolower(date("l"));
	$currentTime = date("H:i");
	if(($currentDay == strtolower($day)) && $currentTime < $time) {
		return date("Y-m-d H:i", strtotime('today '.$time));
	}
	return date("Y-m-d H:i", strtotime('next '.$day.' '.$time));
}

function getWeeklyEventPreviousTime($day, $time): string {
	$currentDay = strtolower(date("l"));
	$currentTime = date("H:i");
	if(($currentDay == strtolower($day)) && $currentTime > $time) {
		return date("Y-m-d H:i", strtotime('today '.$time));
	}
	return date("Y-m-d H:i", strtotime('last '.$day.' '.$time));
}

try {
	foreach($eventTimes as $eventId => $event) {
		$active = 0;
		$open = 0;
		if(!array_key_exists('day', $event)) {
			$lastTime = getEventPreviousTime($event['schedule']);
			$nextTime = getEventNextTime($event['schedule']);
		} else {
			$lastTime = getWeeklyEventPreviousTime($event['day'], $event['time']);
			$nextTime = getWeeklyEventNextTime($event['day'], $event['time']);
		}
		$nextTimeF = date("D g:i A", strtotime($nextTime));
		$offset    = strtotime($nextTime) - strtotime($lastTime);
		$timeLeft  = strtotime($nextTime) - time();

		$result[$eventId] = array(
			'event'    => $event['name'],
			'opentime' => $event['opentime'],
			'duration' => $event['duration'],
			'last'     => $lastTime,
			'next'     => $nextTime,
			'nextF'    => $nextTimeF,
			'offset'   => $offset,
			'timeleft' => $timeLeft,
		);
	}

	if(isset($_GET['event']) && array_key_exists($_GET['event'], $result)) {
		$result = $result[$_GET['event']];
	}

	http_response_code(200);
	echo json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

} catch(Exception $e) {
	http_response_code(500);
	echo json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR);
}
