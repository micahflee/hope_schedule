<?php
require_once("head.inc.php");

class Notice {
    function __construct() {
        global $db;

        $notice_rows = $db->query("select * from ".$db->pre."notice");
        $notice_row = $db->fetch_array($notice_rows);
        $this->notice = $notice_row['notice'];
    }
}

echo json_encode(new Notice());

?>
