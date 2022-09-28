<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../config/config.php';

use Sabre\VObject;

function create_calendar($courses)
{
    $vcalendar = new VObject\Component\VCalendar();
    $tz = $vcalendar->createComponent('VTIMEZONE', [
        'TZID' => 'CET',
    ]);
    $standard = $vcalendar->createComponent('STANDARD', [
        'DTSTART' => '16010101T030000',
        'TZOFFSETFROM' => '+0200',
        'TZOFFSETTO' => '+0100',
        'RRULE' => 'FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10',
    ]);
    $daylight = $vcalendar->createComponent('DAYLIGHT', [
        'DTSTART' => '16010101T020000',
        'TZOFFSETFROM' => '+0100',
        'TZOFFSETTO' => '+0200',
        'RRULE' => 'FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3',
    ]);

    $tz->add($standard);
    $tz->add($daylight);
    $vcalendar->add($tz);

    $db = new PDO("sqlite:".__DIR__."/../roosters.db");
    $in = str_repeat('?,', count($courses) - 1).'?';
    $stmt = $db->prepare("SELECT file FROM courses where id IN ($in)");
    $stmt->execute($courses);
    $files = $stmt->fetchAll(PDO::FETCH_COLUMN);


    foreach ($files as $file) {
        $uids = [];
        $v = VObject\Reader::read(
            fopen(__DIR__."/../var/cache/$file", 'r'),
            VObject\Reader::OPTION_FORGIVING
        );

        /** @var VObject\Component\VEvent $event */
        foreach ($v->VEVENT as $event) {
            if (!in_array((string)$event->UID, $uids)) {
                // Map location to aula
                $location = (string)$event->LOCATION;
                if (isset(AULAS[$location])) {
                    $event->LOCATION = AULAS[$location];
                }

                $vcalendar->add($event);
                $uids[] = $event->UID;
            }
        }
    }

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="cw.ics"');
    echo $vcalendar->serialize();
}

function create_url($courses)
{
    include __DIR__.'/../src/header.html';

    sort($courses);
    $courseStr = implode(":", $courses);
    $id = substr(md5($courseStr), 0, 8);
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]/".urlencode(
            $id
        );

    $db = new PDO("sqlite:".__DIR__."/../short_links.db");
    $stmt = $db->prepare("SELECT * FROM links WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $stmt = $db->prepare("INSERT INTO links (id, `courses`) VALUES(?,?)");
        $stmt->execute([$id, $courseStr]);
    }

    echo '<h5>Use the following url to download the ical file or use it in Google Calendar:</h5>';
    echo "<input type=\"text\" class=\"form-control\" placeholder=\"\" value='$actual_link'";
    include __DIR__.'/../src/footer.html';
}

if (!empty($_GET['course'])) {
    create_url($_GET['course']);
} elseif ($_SERVER['PATH_INFO'] !== '') {
    $id = urldecode(substr($_SERVER['PATH_INFO'], 1));

    $db = new PDO("sqlite:".__DIR__."/../short_links.db");
    $stmt = $db->prepare("SELECT * FROM links WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    create_calendar(explode(':', urldecode($row["courses"])));
}
