<?php
	namespace UI;
?>
<style>
.ui_progress_bar {
	width: 100%;
	background-color: #ddd;
	
	-webkit-transition: width 1s;
	transition: width 1s;
}

.ui_progress_bar:first-child {
	width: 0%;
	height: 30px;
	background-color: #4CAF50;
	text-align: center;
	line-height: 30px;
	color: white;
}
</style>
<script>
function UI_progressBar_set(id, amount){
	let progressBar = document.getElementById(id);
	progressBar.style.width = amount + '%';
	progressBar.innerHTML = Math.floor(amount * 1)  + '%';
}
</script>

<?php
class ProgressBarTask{
	private $weight;
	private $func;
	private static $totalWeight = 0;
	
	function __construct($func, $weight){
		$this->weight = $weight;
		self::$totalWeight += $this->weight;
		
		if(is_callable($func)){
			$this->func = $func;
		}else{
			$this->func = function(){};
		}
	}
	
	function getWeight(){
		return $this->weight;
	}
	function getTotalWeight(){
		return $this->totalWeight;
	}
	
	function execute(){
		($this->func)();
		return $this->weight;
	}
}
class ProgressBar{
	private $id;
	private $className = 'ui_progress_bar';

	private $currentProgress = 0;
	
	private $tasks = [];
	public function __construct(){
		static $tmp_id = 0;
		++$tmp_id;
		$this->id = 'ui_progress_bar_' . $tmp_id;
	}
	public function remove($sleep = 1){
		if(is_int($sleep)){
			sleep($sleep);
		}else{
			usleep($sleep * 1000000);
		}
		echo "<script>" . $this->id .  ".remove();</script>";
		flush();
	}
	
	public function draw(){
		static $ob_ended = false;
		if(!$ob_ended){
			ob_end_flush();
			$ob_ended = true;
		}
		?>
		<div id="<?php echo $this->id; ?>" class="<?php echo $this->className; ?>">
			<div>0%</div>
		</div>
		<?php
	}
	
	public function add($i){
		$this->currentProgress += $i;
		echo "<script>UI_progressBar_set('" . $this->id .  "', " . $this->currentProgress . ");</script>";
		flush();
	}
	public function set($i){
		$this->currentProgress = $i;
		echo "<script>UI_progressBar_set('" . $this->id .  "', " . $this->currentProgress . ");</script>";
		flush();
	}
	
	public function addTask($task, $weight){
		$this->tasks[] = new ProgressBarTask($task, $weight);
	}
	public function execute(){
		$totalWeight = 0;
		foreach($this->tasks as $task){
			$totalWeight += $task->getWeight();
		}
		
		$weightSoFar = 0;
		flush();
		foreach($this->tasks as $task){
			$currentWeight = $task->execute();
			$weightSoFar += $currentWeight;
			
			echo "<script>UI_progressBar_set('" . $this->id .  "', " . ($weightSoFar*(100/$totalWeight)) . ");</script>";
			flush();
		}
	}
}
?>