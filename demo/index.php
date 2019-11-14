<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>UI_LIB Demo</title>
	<meta name="description" content="UI_LIB Demo"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<?php
		//Include UI_LIB in header in order to put all <style>s and <script>s into <head>
		//(doing it in <body> should also work)
		include_once("../ui_lib_min/ui.min.php");
	?>
</head>
<body>

<?php
$names = explode("\n", file_get_contents("names.txt"));
$surnames = explode("\n", file_get_contents("surnames.txt"));

//Init progressBar and add tasks of waiting (in order to accomplish fake loading screen)
$progressBar = new UI\ProgressBar();
for($i = 0; $i < 100; ++$i){
	$progressBar->addTask(function(){usleep(1000);}, 1);
}

//Init table
$table = new UI\Table();
//Set columns' names
$table->setheader(
	["ID", "Name", "Surname", "Year"]
);
//Add contents to the table
$rows_count = 5627;
$table->show(15);
for($i = 0; $i < $rows_count; ++$i){
	$table->addRow(
		[$i, $names[rand(0, count($names)-1)], $surnames[rand(0, count($surnames)-1)], rand(1900, 2019)]
	);
}
?>

<!-- Draw progress bar into custom div positioned at the bottom -->
<div style="
	 position: fixed;
	 top: 90%;
	 width: 100%;
">
<?php
	$progressBar->draw();
?>
</div>
<?php
//Execute progressBar
$progressBar->execute();
//Remove progressBar after waiting for 1 second in order to allow user to see 100%
$progressBar->remove(1);

//Draw table
$table->draw();
?>
</body>