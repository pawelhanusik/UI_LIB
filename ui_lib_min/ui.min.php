<?php
$ui_classes=["ui_table"];
foreach($ui_classes as $filename){ include_once(realpath(dirname(__FILE__)) . "/classes.min/" .  $filename . ".min.php"); }
?>