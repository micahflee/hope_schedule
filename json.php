<?php
require_once("head.inc.php");

class Event {
    function __construct($id) {
        global $db;

        $event_rows = $db->query("select * from ".$db->pre."event where id='".$db->escape($id)."'");
        $event_row = $db->fetch_array($event_rows);
        $this->id = $event_row['id'];
        $this->title = $event_row['title'];
        $this->description = $event_row['description'];
        $this->timestamp = $event_row['timestamp'];

        $location_rows = $db->query("select * from ".$db->pre."location where id='".$db->escape($event_row['location_id'])."'");
        $location_row = $db->fetch_array($location_rows);
        $this->location = $location_row['location'];

        $this->speakers = array();
        $speaker_rows = $db->query("select ".$db->pre."speaker.* from ".$db->pre."speaker left join ".$db->pre."event_speaker on ".$db->pre."event_speaker.speaker_id=".$db->pre."speaker.id where ".$db->pre."event_speaker.event_id='".$event_row['id']."' order by ".$db->pre."speaker.name");
        while($speaker_row = $db->fetch_array($speaker_rows)) {
            $this->speakers[] = array('name' => $speaker_row['name'], 'bio' => $speaker_row['bio']);
        }
    }
}

$events = array();
$event_rows = $db->query("select id from ".$db->pre."event order by timestamp");
while($event_row = $db->fetch_array($event_rows)) {
    $events[] = new Event($event_row['id']);
}

echo json_encode($events);

?>
