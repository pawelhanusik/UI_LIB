<?php
include_once("../ui_lib/ui.php");

$names = explode("\n", file_get_contents("names.txt"));
$surnames = explode("\n", file_get_contents("surnames.txt"));

$pb = new UI\ProgressBar();
echo "<div style=\"
	 position: fixed;
	 top: 90%;
	 width: 100%;
\">";
$pb->draw();
echo "</div>";

for($i = 0; $i < 100; ++$i){
	$pb->addTask(function(){usleep(1000);}, 1);
}
$pb->execute();
sleep(1);
$pb->remove();

$table = new UI\Table();
$table->setheader(
	["ID", "Name", "Surname", "Year"]
);

$rows_count = 5627;
$table->show(15);
for($i = 0; $i < $rows_count; ++$i){
	$table->addRow(
		[$i, $names[rand(0, count($names)-1)], $surnames[rand(0, count($surnames)-1)], rand(1900, 2019)]
	);
}

$table->draw();
?>