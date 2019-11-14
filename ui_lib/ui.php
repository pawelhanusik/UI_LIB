<?php
$ui_classes = [
	"ui_table",
	"ui_progress_bar"
];

foreach($ui_classes as $filename){
	include_once(realpath(dirname(__FILE__)) . "/classes/" .  $filename . ".php");
}
?>