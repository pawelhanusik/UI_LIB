<?php
	namespace UI;
?>
<style>
.ui_table, .ui_table_control {
	border-collapse: collapse;
	border: 2px solid #888;
}
.ui_table {
	margin: auto;
	width: 100%;	
}
.ui_table_control {
	margin: auto 0 auto auto;
	border-bottom: none;
}


.ui_table td, .ui_table th {
	border: 1px solid #aaa;
	text-align: center;
	padding: 8px 12px;
}
.ui_table_control td, .ui_table_control th {
	padding: 6px;
}
.ui_table_control td:first-child {
	border-right: 2px solid #888;
	cursor: pointer;
}
.ui_table tr:nth-child(even) {
	background-color: #ddd;
}
.ui_table .colFitContents {
	width: 1%;
	white-space: nowrap;
}
.ui_table th a {
  cursor: pointer;
}
.ui_table input{
	width: 100%;
}

.ui_table_control input {
	text-align: center;
}

</style>
<script>
class UI_Table{
	constructor(id, className = 'ui_table', classNameControl = 'ui_table_control'){
		this.id = id;
		this.className = className;
		this.classNameControl = classNameControl;
		this.header = [];
		this.data = [];
		
		this.show_at_once = 10;
		this.show_page_no = 0;
		
		this.sorted_col = 0;
		this.sorted_asc = 1; //1 = asc; -1 = desc
		
		this.filters_inputs_visibility = false;
		this.filters = [];
		this.filteredData = [];
	}
	drawIntoTableId(emptyTableId = this.id){
		let mainDiv = document.getElementById(emptyTableId);
		let htmlTable = document.createElement("table");
		
		htmlTable.id = this.id;
		htmlTable.className = this.className;
		
		//table controls
		let tableControls = document.createElement("table");
		tableControls.id = "";
		tableControls.className = this.classNameControl;//"bordered";
		let tr = document.createElement("tr");
		tr.innerHTML = ' \
			<td onclick="' + this.id + '.change_filters_visibility();"><svg width="5mm" height="5mm" version="1.1" viewBox="0 0 90 90" xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"><metadata><rdf:RDF><cc:Work rdf:about=""><dc:format>image/svg+xml</dc:format><dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage"/><dc:title/></cc:Work></rdf:RDF></metadata><g transform="translate(0,-207)"><path d="m83.436 207.58-38.757 67.734-39.28-67.431 39.019-0.15107z" stroke-width=".26458"/><g transform="matrix(1.0029 .047256 -.046672 1.015 -22.678 165)"><path d="m83.329 112.62-21.784 13.937-1.178-25.834 11.481 5.9485z" stroke-width=".26458"/></g><rect x="33.165" y="238.95" width="22.475" height="44.299" stroke-width=".26458"/></g></svg></td>\
			\
			<td><button onclick="' + this.id + '.prev_page();">&lt;</button></td> \
			<td><input type="text" value="' + (this.show_page_no+1) + '" size="' + Math.max(Math.ceil(Math.log10(this.data.length / this.show_at_once)) - 1, 1) + '" onkeyup="if(!isNaN(parseInt(this.value-1)))' + this.id + '.change_page(parseInt(this.value-1));"></td> \
			<td><a> / </a></td> \
			<td><a>' + Math.ceil(this.data.length / this.show_at_once) + '</a></td> \
			<td><button onclick="' + this.id + '.next_page();">&gt;</button></td> \
		';
		tableControls.appendChild(tr);
		
		mainDiv.appendChild(tableControls);
		mainDiv.appendChild(htmlTable);
		//htmlTable.parentElement.insertBefore(tableControls, htmlTable);
		//end of table controls
		
		
		//header
		htmlTable.appendChild( document.createElement("tr") );
		for(let i = 0; i < this.header.length; ++i){
			let ih = "";
			ih += '<a onclick="' + htmlTable.id + '.sort(' + i + ').updateTable()">';
			ih += this.header[i];
			
			if(this.sorted_col === i){
				ih += ((this.sorted_asc===1) ? " &#x25BD;" : " &#x25B3;");
			}
			ih += '</a>';
			//filter's input
			ih += '<input type="text" style="display: none;" value="' + this.get_filter(i) +'" onchange="' + this.id + '.setFilter(' + i + ', this.value); ' + this.id + '.updateTable();">';
			
			let th = document.createElement("th");
			th.innerHTML = ih;
			htmlTable.rows[0].appendChild( th );
		}
		
		//data
		for(let i = 0; i < Math.min(this.data.length, this.show_at_once); ++i){
			htmlTable.appendChild( document.createElement("tr") );
			for(let j = 0; j < this.data[i].length; ++j){
				let td = document.createElement("td");
				td.innerHTML = this.data[i][j];
				htmlTable.rows[i+1].appendChild( td );
			}
		}
	}
	
	next_page(){
		this.change_page(this.show_page_no + 1);
	}
	prev_page(){
		this.change_page(this.show_page_no - 1);
	}
	change_page(newPage){
		if( newPage >= 0 && newPage < Math.ceil(this.filteredData.length / this.show_at_once) ){
			this.show_page_no = newPage;
			this.updateTable();
		}
	}
	
	change_filters_visibility(){
		this.filters_inputs_visibility = !this.filters_inputs_visibility;
		this.filter_inputs_show(this.filters_inputs_visibility);
	}
	filter_inputs_show(show){
		let style_display = '';
		let controlTableFilterButton = document.getElementById(this.id).getElementsByTagName("table")[0].getElementsByTagName("td")[0];
		if(!show){
			style_display = 'none';
			controlTableFilterButton.style.backgroundColor = '';
		}else{
			controlTableFilterButton.style.backgroundColor = '#0002';
		}
		let htmlTable = document.getElementById(this.id).getElementsByTagName("table")[1];
		for(let cell of htmlTable.rows[0].cells){
			let input = cell.getElementsByTagName("input")[0];
			if(input !== undefined){
				input.style.display = style_display;
			}
		}
	}
	
	get_filter(filterID){
		return ((this.filters[filterID] == undefined) ? "" : this.filters[filterID]);
	}
	
	sort(sortColumnID){
		if(this.sorted_col === sortColumnID){
			this.sorted_asc *= -1;
		}else{
			this.sorted_col = sortColumnID;
			this.sorted_asc = 1;
		}
		
		let tmp_sorted_asc = this.sorted_asc;
		this.data.sort(function(a, b) {
			let value_a = parseInt(a[sortColumnID]);
			let value_b = parseInt(b[sortColumnID]);
			if(isNaN(value_a) || isNaN(value_b)){
				value_a = a[sortColumnID];
				value_b = b[sortColumnID];
			}
			
			if (value_a > value_b) {
				return 1 * tmp_sorted_asc;
			}
			if (value_a < value_b) {
				return -1 * tmp_sorted_asc;
			}
			return 0;
		});
		
		
		return this;
	}
	
	filter(){
		let ret = [];
		
		for(let i = 0; i < this.data.length; ++i){
			let show = true;
			for(let filterID = 0; filterID < this.filters.length; ++filterID){
				if(this.filters[filterID] !== undefined){
					if(this.data[i][filterID].indexOf(this.filters[filterID]) === -1){
						show = false;
						break;
					}
				}
			}
			if(show){
				ret.push(this.data[i]);
			}
		}

		return ret;
	}
	setFilter(filterID, newValue){
		if(newValue == ""){
			this.filters[filterID] = undefined;
		}else{
			this.filters[filterID] = newValue;
		}
	}
	
	updateTable(){
		let controlTable = document.getElementById(this.id).getElementsByTagName("table")[0];
		controlTable.getElementsByTagName("input")[0].value = this.show_page_no+1;
		///======================================================================
		let htmlTable = document.getElementById(this.id).getElementsByTagName("table")[1];
		
		htmlTable.className = this.className;
		
		//header
		while(this.header.length - htmlTable.rows[0].cells.length > 0){
			htmlTable.rows[0].appendChild( document.createElement("th") );
		}
		while(this.header.length - htmlTable.rows[0].cells.length < 0){
			htmlTable.rows[0].cells[0].remove();
		}
		for(let i = 0; i < this.header.length; ++i){
			let ih = "";
			ih += '<a onclick="' + htmlTable.id + '.sort(' + i + ').updateTable()">';
			ih += this.header[i];
			
			if(this.sorted_col === i){
				ih += ((this.sorted_asc===1) ? " &#x25BD;" : " &#x25B3;");
			}
			ih += '</a>';
			//filter's input
			ih += '<input type="text"' + ((this.filters_inputs_visibility) ? '' : ' style="display: none;" ') + 'value="' + this.get_filter(i) +'" onchange="' + this.id + '.setFilter(' + i + ', this.value); ' + this.id + '.updateTable();">';
			htmlTable.rows[0].cells[i].innerHTML = ih;
		}
		
		
		//data
		this.filteredData = this.filter();
		
		let tmp_start_row = this.show_page_no*this.show_at_once;
		let tmp_rows_to_show = Math.min(this.filteredData.length-tmp_start_row, this.show_at_once);
		while(tmp_rows_to_show - htmlTable.rows.length + 1 > 0){
			htmlTable.appendChild( document.createElement("tr") );
		}
		while(tmp_rows_to_show - htmlTable.rows.length + 1 < 0){
			htmlTable.rows[1].remove();
		}
		for(let i = tmp_start_row; i < Math.min(this.filteredData.length, tmp_start_row + this.show_at_once); ++i){
			while(this.filteredData[i].length - htmlTable.rows[i+1-tmp_start_row].cells.length > 0){
				htmlTable.rows[i+1-tmp_start_row].appendChild( document.createElement("td") );
			}
			while(this.filteredData[i].length - htmlTable.rows[i+1-tmp_start_row].cells.length < 0){
				htmlTable.rows[i+1-tmp_start_row].cells[0].remove();
			}
			for(let j = 0; j < this.filteredData[i].length; ++j){
				htmlTable.rows[i+1-tmp_start_row].cells[j].innerHTML = this.filteredData[i][j];
			}
		}
		
		//update tableControls' page counter
		controlTable.getElementsByTagName("td")[4].innerHTML = '<a>' + Math.ceil(this.filteredData.length / this.show_at_once) + '</a>';
	}
}
</script>
<?php

class Table
{
	private $table_id;
	private $className = 'ui_table classic';
	public $head = array();
	public $data = array();
	
	private $show_at_once = 100;
	
	public function __construct(){
		static $tmp_table_id = 0;
		++$tmp_table_id;
		$this->table_id = 'ui_table_' . $tmp_table_id;
	}
	
	public function show($amount){
		$this->show_at_once = $amount;
	}
	
	public function draw(){
		?>
		<div id="<?php echo $this->table_id; ?>"></div>
		<script>
			var <?php echo $this->table_id; ?> = new UI_Table(
				"<?php echo $this->table_id; ?>",
				"<?php echo $this->className; ?>"
			);
			<?php echo $this->table_id; ?>.show_at_once = <?php echo $this->show_at_once; ?>;
			<?php echo $this->table_id; ?>.header = [
				<?php
				if(is_array($this->head)){
					for($i = 0; $i < count($this->head); ++$i){
						if($i > 0) echo ',';
						echo '"' . $this->head[$i] . '"';
					}
				}
				?>
			];
			<?php echo $this->table_id; ?>.data = [
				<?php
				if(is_array($this->data)){
					for($i = 0; $i < count($this->data); ++$i){
						$dataRow = $this->data[$i];
						if($i > 0) echo ',';
						echo '[';
						if(is_array($dataRow)){
							for($j = 0; $j < count($dataRow); ++$j){
								if($j > 0) echo ',';
								echo '"' . $dataRow[$j] . '"';
							}
						}
						echo ']';
					}
				}
				?>
			];
			<?php echo $this->table_id; ?>.drawIntoTableId();
		</script>
		<?php
	}
	
	public function setHeader($header){
		$this->head = $header;
	}
	public function addRow($row){
		$this->data[] = $row;
	}
}

?>