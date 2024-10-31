<?php
	class RC_Output{
		// Options
			// Internal
			protected $group = ""; // Holds either the title or ID before validation, after validation it holds the title
			protected $groupid = 0; // Holds the group ID
			protected $limit = ''; // How many posts do we show? Syntax: x or x,y where x is lower limit and y is upper limit
			protected $data = ''; // Search and show only a part of our data? Example of use: $data = "A", should only return data that starts with A
			
			// Layout
			protected $header = 1; // Do we put a header on our table? (1/yes and 0/no)
			protected $footer = 1; // Do we put a footer on our table? (1/yes and 0/no)
			protected $columns = 1; // How many columns should we show
			protected $form_search = 0; // Do we output a search-form?
			protected $form_data = 0; // Do we output a data-select-box used to filter data?
			protected $form_pages = 0; // Do we output a page-select-box so we can filter which data a given page is associated with?
			
			protected $table_layout = 1;
			
			// Styling
			protected $stylesheet = ''; // Holds either an ID or title of our stylesheet
			private $stylesheet_obj = null; // Will hold the stylesheet object if found

			// Posted form data
			private $form_search_value = '';
			private $form_data_value = '';
			private $form_pages_value = '';
		
		private $err = "";
		
		public function __construct(){
			/* DEFAULT VALUES */
			// Internal
			$this->group = get_option('rc_default_group', '0');
			$this->limit = get_option('rc_default_limit', '');
			$this->data = get_option('rc_default_data', '');
			
			// Layout
			$this->table_layout = get_option('rc_default_table_layout');
			$this->header = get_option('rc_default_header', 1);
			$this->footer = get_option('rc_default_footer', 1);
			$this->columns = get_option('rc_default_columns', 1);
			
			$this->form_search = get_option('rc_default_form_search', 0);
			$this->form_data = get_option('rc_default_form_data', 0);
			$this->form_pages = get_option('rc_default_form_pages', 0);
			
			// Styling
			$this->stylesheet = get_option('rc_default_stylesheet', '');
		}
		
		public function __toString(){
			return print_r(get_object_vars($this),true);
		}
		
		public function __sleep()
		{
			return get_object_vars($this);
		}
		
		public function __wakeup(){
			// Do nothing?
		}
		
		private function exportOptions(){
			$str = "group=$this->group;limit=$this->limit;data=$this->data;stylesheet=$this->stylesheet;header=$this->header;footer=$this->footer;columns=$this->columns;table=$this->table_layout";
			return $str;
		}
		
		// Getters and Setters
		public function getArrayID(){ return $this->arrayID; }
		public function setArrayID($id){ $this->arrayID = $id; }
		
		/*
		* Outputs the table
		*/
		public function getOutput($type = 1){
			// Check for submitted data
			$this->checkSubmittedData();
			
			if($this->validateOptions()){
				if($type == 1){
					return $this->printDataTable();
				}else{
					return $this->printAjaxTable();
				}
			}else{
				return $this->err;
			}
		}
		
		/*
		* Validates the different options
		*/
		protected function validateOptions(){
			global $wpdb;
			global $rc_tables;
			
			// Valid yes/no data
			$yesno = array('yes', 1, 'no', 0);
		
			/* GROUP ID */
			if(!is_numeric($this->group)){
				// The group ID is not a number
				$groupid = $wpdb->get_var($wpdb->prepare("SELECT id FROM `".$rc_tables['groups']."` WHERE `title` = %s;", $this->group));
				if($groupid == null){
					$this->err.= "<p>".__('Invalid group ID. The group ID was not found in the database.', 'rexCrawler')."</p>";
				}

			}else{
				// The group ID is a number
				$group = $wpdb->get_results($wpdb->prepare("SELECT id, title FROM `".$rc_tables['groups']."` WHERE `id` = %d;", $this->group));
				
				if($group == null){
					$this->err.= "<p>".__('Invalid group. The group was not found in the database.', 'rexCrawler')."</p>";
				}
				$this->group = $group[0]->title;
				$groupid = $group[0]->id;
			}
			
			// Saving the group ID
			$this->groupid = $groupid;
			
			/* LIMIT */
			// Matching the syntax null, d or d,d:
			if(!preg_match('/^\d+(,\d*)?\d$/', $this->limit) && $this->limit != ""){
				$this->err.= "<p>".__('Invalid limit. The syntax for limits is <em>d</em> or <em>d,d</em> where <em>d</em> is an integer.', 'rexCrawler')."</p>";
			}
			
			/* DATA */
			// No validation since it's a search-string
			
			/* HEADER */
			if(!in_array($this->header, $yesno)){
				$this->err .= __('Invalid header-parameter. The header-parameter only accepts yes/1 and no/0.', 'rexCrawler');
			}
			
			/* FOOTER */
			if(!in_array($this->footer, $yesno)){
				$this->err .= __('Invalid footer-parameter. The footer-parameter only accepts yes/1 and no/0.', 'rexCrawler');
			}
			
			/* COLUMNS */
			if(!preg_match('/^\d*$/', $this->columns)){
				$this->err .= __('Invalid column specification. Only integers allowed.', 'rexCrawler');
			}else{
				if($this->columns < 1){
					$this->err .= __('The least columns you can have is 1.', 'rexCrawler');
				}
			}
			
			/* TABLE LAYOUT */
			if(!is_numeric($this->table_layout)){
				$this->err .= __('Invalid table-layout parameter. Only integers allowed.', 'rexCrawler');
			}
			
			/* SEARCH FORM */
			if(!in_array($this->form_search, $yesno)){
				$this->err .= __('Invalid Search UI-parameter. The parameter only accepts yes/1 and no/0.', 'rexCrawler');
			}
			
			/* DATA FORM */
			if(!in_array($this->form_data, $yesno)){
				$this->err .= __('Invalid Data UI-parameter. The parameter only accepts yes/1 and no/0.', 'rexCrawler');
			}
			
			/* PAGE FORM */
			if(!in_array($this->form_pages, $yesno)){
				$this->err .= __('Invalid Pages UI-parameter. The parameter only accepts yes/1 and no/0.', 'rexCrawler');
			}
			
			/* STYLESHEET */
			// Check if a stylesheet has been specified
			if($this->stylesheet != ''){
				// Search by ID or title?
				if(!is_numeric($this->stylesheet)){
					// Search by title
					$style = $wpdb->get_results($wpdb->prepare("SELECT `id`, `title`, `rows`, `col1`, `col2` FROM `$rc_tables[styles]` WHERE LOWER(`title`) = LOWER(%s)", $this->stylesheet));
				}else{
					// Search by Id
					$style = $wpdb->get_results($wpdb->prepare("SELECT `id`, `title`, `rows`, `col1`, `col2` FROM `$rc_tables[styles]` WHERE `id` = %d", $this->stylesheet));
				}
				
				// Was the stylesheet found?
				if($style == null){
					// Stylesheet not found!
					$this->err .= __('The specified stylesheet was not found.', 'rexCrawler');
				}else{
					// Save the title and id
					$this->stylesheet_obj = $style[0];
				}
			}

			// Return whether the options are validated or not
			return ($this->err == "");
		}
		
		/*
		* Takes an option-string and parses it, which fills out the different variables.
		*/
		public function parseOptions($options){
			// Split the options
			$opt = explode(";", $options);
			
			foreach($opt as $option){
				$opt = substr($option, 0, strpos($option, "="));
				$value = substr($option, strpos($option, "=")+1, strlen($option)-strpos($option, "=")-1);
				
				switch($opt){
					case 'group':
						$this->group = $value;
					break;
						
					case 'limit':
						$this->limit = $value;
					break;
						
					case 'data':
						$this->data = $value;
					break;
					
					case 'stylesheet':
						$this->stylesheet = $value;
					break;
						
					case 'header':
						$this->header = $value;
					break;
						
					case 'footer':
						$this->footer = $value;
					break;
						
					case 'columns':
						$this->columns = $value;
					break;					
					
					case 'table':
						$this->table_layout = $value;
					break;
				}
			}
		}
		
		/*
		* Check whether the form has been posted and saves the data
		*/
		private function checkSubmittedData(){
			if(isset($_POST['rc_output_submitted'])){
				if(isset($_POST['rc_output_search'])){
					$this->form_search_value = stripcslashes(urldecode($_POST['rc_output_search']));
				}
				
				if(isset($_POST['rc_output_data'])){
					$this->form_data_value = stripcslashes(urldecode($_POST['rc_output_data']));
				}
				
				if(isset($_POST['rc_output_pages'])){
					$this->form_pages_value = stripcslashes(urldecode($_POST['rc_output_pages']));
				}
			}
		}
		
		/*
		* This function returns the table, defined by options or just a standard table
		*/
		protected function printDataTable(){
			global $wpdb;
			global $rc_tables;
			
			$groupid = $this->groupid;
			
			if($this->stylesheet_obj == null){
				$c_pre = 'rc_no-stylesheet';
				$c_row = 1;
				$c_col1 = get_option('rc_default_col1_text', __('Data', 'rexCrawler'));
				$c_col2 = get_option('rc_default_col2_text', __('Data', 'rexCrawler'));
			}else{
				// Setting stylesheets			
				// Add style to our document
				wp_enqueue_style('rc_style_output_'.$this->stylesheet_obj->id);
				
				// Saving data
				$c_pre = 'rc_'.str_replace(array(' ', 'æ', 'ø', 'å'), array('-', 'ae', 'oe', 'aa'), strtolower($this->stylesheet_obj->title));
				$c_row = $this->stylesheet_obj->rows;
				$c_col1 = $this->stylesheet_obj->col1;
				$c_col2 = $this->stylesheet_obj->col2;
				
				// Check if we should use default column values
				if($c_col1 == ''){
					$c_col1 = get_option('rc_default_col1_text', __('Data', 'rexCrawler'));
				}
				
				if($c_col2 == ''){
					$c_col2 = get_option('rc_default_col2_text', __('Data', 'rexCrawler'));
				}
				
				// Quick validation of row
				if($c_row < 1){
					$c_row = 1;
				}
			}

			/* FETCH DATA */
			$sql = 'SELECT DISTINCT `'.$rc_tables['data'].'`.* FROM ((`'.$rc_tables['groups'].'` 
						INNER JOIN `'.$rc_tables['pages'].'` ON `'.$rc_tables['groups'].'`.`id` = `'.$rc_tables['pages'].'`.`group`) 
						INNER JOIN `'.$rc_tables['pagedata'].'` ON `'.$rc_tables['pages'].'`.`id` = `'.$rc_tables['pagedata'].'`.`pageid`) 
						INNER JOIN `'.$rc_tables['data'].'` ON `'.$rc_tables['pagedata'].'`.`dataid` = `'.$rc_tables['data'].'`.`id`
					WHERE ((`'.$rc_tables['groups'].'`.`id` = '.$groupid.')'.($this->data != '' ? ' AND (`'.$rc_tables['data'].'`.`data` LIKE \''.$wpdb->escape($this->data).'%'.'\')' : "").($this->form_search_value != '' ? ' AND (`'.$rc_tables['data'].'`.`data` LIKE \''.($this->data == '' ? '' : '%').$wpdb->escape($this->form_search_value).'%\')' : "").($this->form_data_value != '' ? ' AND (`'.$rc_tables['data'].'`.`data` LIKE \'%'.$wpdb->escape($this->form_data_value).'%'.'\')' : "").($this->form_pages_value != '' ? ' AND (`'.$rc_tables['pages'].'`.`id` = '.$wpdb->escape($this->form_pages_value).')' : '').' AND (`'.$rc_tables['pagedata'].'`.`runtime` = (SELECT MAX(runtime) FROM `'.$rc_tables['pagedata'].'`)))
					ORDER BY `'.$rc_tables['data'].'`.`data`'.($this->limit != "" ? " LIMIT ".$this->limit : "").';';
			

			$datalist = $wpdb->get_results($sql);
			
			/* TABLE SETUP */
			$table = '<div style="display: inline-block;" class="'.$c_pre.'_wrapper">';
			
			/* FORM OUTPUT */
			if($this->form_search == 1 || $this->form_data == 1 || $this->form_pages == 1){
				$table .= '<div style="display: inline-block" class="'.$c_pre.'_form-wrapper">';
							$table.="<form method='post' name='rc_output_form' id='rc_output_form' action='".$_SERVER['REQUEST_URI']."'>";
							$table.='<input type="hidden" name="rc_output_submitted" value="1" />';
							//$table.='<input type="hidden" name="rc_output_arrayid" id="rc_output_arrayid" value="'.$this->arrayID.'" />';
							$table.='<input type="hidden" name="rc_output_element_prefix" id="rc_output_element_prefix" value="'.$c_pre.'" />';
							$table.='<input type="hidden" name="rc_output_options" id="rc_output_options" value="'.urlencode($this->exportOptions()).'" />';
				
				
				// Search field
				if($this->form_search == 1){
					$table .= "<div style='float: left;' class='".$c_pre."_form-search-wrapper'>
									<label for='rc_output_search'>".__('Search:', 'rexCrawler')."</label> 
									<input type='text' value='$this->form_search_value' name='rc_output_search' id='rc_output_search' class='".$c_pre."_form-search-field' /> ";
					$table .= "</div>";
				}
				
				// Data filter
				if($this->form_data == 1){
					// Getting a unfiltered data list
					$sql = 'SELECT DISTINCT `'.$rc_tables['data'].'`.* FROM ((`'.$rc_tables['groups'].'` 
						INNER JOIN `'.$rc_tables['pages'].'` ON `'.$rc_tables['groups'].'`.`id` = `'.$rc_tables['pages'].'`.`group`) 
						INNER JOIN `'.$rc_tables['pagedata'].'` ON `'.$rc_tables['pages'].'`.`id` = `'.$rc_tables['pagedata'].'`.`pageid`) 
						INNER JOIN `'.$rc_tables['data'].'` ON `'.$rc_tables['pagedata'].'`.`dataid` = `'.$rc_tables['data'].'`.`id`
					WHERE ((`'.$rc_tables['groups'].'`.`id` = '.$groupid.')'.($this->data != '' ? ' AND (`'.$rc_tables['data'].'`.`data` LIKE \''.$wpdb->escape($this->data).'%'.'\')' : "").($this->form_search_value != '' ? ' AND (`'.$rc_tables['data'].'`.`data` LIKE \'%'.$wpdb->escape($this->form_search_value).'%\')' : "").($this->form_pages_value != '' ? ' AND (`'.$rc_tables['pages'].'`.`id` = '.$wpdb->escape($this->form_pages_value).')' : '').' AND (`'.$rc_tables['pagedata'].'`.`runtime` = (SELECT MAX(runtime) FROM `'.$rc_tables['pagedata'].'`)))
					ORDER BY `'.$rc_tables['data'].'`.`data`'.($this->limit != "" ? " LIMIT ".$this->limit : "").';';
					$filterlist = $wpdb->get_results($sql);
				
					$table .= '<div style="float: left;" class="'.$c_pre.'_form-data-wrapper"><label for="rc_output_data">'.__('Filter data:', 'rexCrawler').'</label> 
					<select name="rc_output_data" id="rc_output_data" class="'.$c_pre.'_form-data-field">';
					$table .= "<option value=''>".__('&lt;All&gt;', 'rexCrawler')."</option>";
					foreach($filterlist as $data){
						$table .= "<option value='$data->data'".($data->data == $this->form_data_value ? " selected" : "").">$data->data</option>";
					}
					
					$table .= '</select></div>';
				}
				
				// Pages-filter
				if($this->form_pages == 1){
					$table .= '<div style="float: left;" class="'.$c_pre.'_form-pages-wrapper"><label for="rc_output_pages">'.__('Filter pages:', 'rexCrawler').'</label> 
					<select name="rc_output_pages" id="rc_output_pages" class="'.$c_pre.'_form-pages-field">';
					
					$table .= '<option value="">'.__('&lt;All&gt;', 'rexCrawler').'</option>';
					
					$sql = $wpdb->prepare("SELECT `id`, `title` FROM `".$rc_tables['pages']."` WHERE `group` = %d", $groupid);
					$pages = $wpdb->get_results($sql);
					
					foreach($pages as $page){
						$table .= "<option value='$page->id'".($page->id == $this->form_pages_value ? " selected" : "").">$page->title</option>";
					}
					
					$table .= '</select></div>';
				}
				
				$table .= '</form></div>'; // DIV: _form-wrapper
			}
			
			/* NO DATA FOUND */
			if(empty($datalist)){
				if(!isset($_POST['rc_output_submitted'])){
					if($this->data == ""){
						$table .= "<p class='italic'>".sprintf(__('No data associated with the group &quot;%1$s&quot;.', 'rexCrawler'), $this->group)."</p>";
					}else{
						$table .= "<p class='italic'>".sprintf(__('No data associated with the group &quot;%1$s&quot; using data-filter &quot;%2$s&quot;.', 'rexCrawler'), $this->group, $this->data)."</p>";
					}
				}else{
					$table .="<p class='italic'>".__('No data found using the specified search parameters.', 'rexCrawler')."</p>";
				}
			}else{
			
				// Initialize the counter
				$counter = 0;
				$column_counter = 1;
				$posts_per_column = ceil(count($datalist)/$this->columns);
				
				/* TABLE OUTPUT */
				$table .= '<div class="'.$c_pre.'_data-wrapper">';
				// Checking table-layout
				if($this->table_layout == 1){
					// Default layout
					
					// Multiple columns
					if($this->columns > 1){
						$table .= '<div><div style="float: left;" class="'.$c_pre.'table-column-wrapper '.$c_pre.'_table-column-wrapper-1">';
					}

					// Setting up the table
					$table_start = '<table class="'.$c_pre.'_table">';
					
					// Header
					if($this->header == "yes" || $this->header == 1){
								$table_start .= '<thead class="'.$c_pre.'_table-header">
									<tr>
										<th class="'.$c_pre.'_table-header-column-1">'.$c_col1.'</th>
										<th class="'.$c_pre.'_table-header-column-2">'.$c_col2.'</th>
									</tr>
								</thead>';
					}
					
					// Footer
					if($this->footer == "yes" || $this->footer == 1){
								$table_start .= '<tfoot class="'.$c_pre.'_table-footer">
									<tr>
										<th class="'.$c_pre.'_table-footer-1">'.$c_col1.'</th>
										<th class="'.$c_pre.'_table-footer-2">'.$c_col2.'</th>
									</tr>
								</tfoot>';
					}
					
					// Content
					$table_start .= '<tbody class="'.$c_pre.'_table-body">';
				}elseif($this->table_layout == 2){
					// table layout
					$table_start = '<table class="'.$c_pre.'_table">
										<tbody class="'.$c_pre.'_table-body">
											<tr class="'.$c_pre.'_table-data-row'.($c_row == 1 ? '' : " ".$c_pre."_table-data-row-".(($counter % $c_row)+1)).'">';
				}
				
				// Appending the table-start
				$table .= $table_start;
		
				foreach($datalist as $data){
					
					// Get all pages associated with the data
					$sql = "SELECT `".$rc_tables['pages']."`.`title`, `".$rc_tables['pages']."`.`referurl` FROM (`".$rc_tables['data']."` INNER JOIN `".$rc_tables['pagedata']."` ON `".$rc_tables['data']."`.`id` = `".$rc_tables['pagedata']."`.`dataid`) INNER JOIN `".$rc_tables['pages']."` ON `".$rc_tables['pagedata']."`.`pageid` = `".$rc_tables['pages']."`.`id` WHERE (`".$rc_tables['data']."`.`id`= %d AND `".$rc_tables['pagedata']."`.`runtime` = (SELECT MAX(runtime) FROM `".$rc_tables['pagedata']."`));";
			
					$pages = $wpdb->get_results($wpdb->prepare($sql, $data->id));
				
					// Now we need to create the page list
					$pagelist = '';
					
					foreach($pages as $page){
						$pagelist.= "<a href='".stripcslashes($page->referurl)."' target='_BLANK'>".stripcslashes($page->title)."</a>, ";
					}
					
					// Remove the last colon and space
					$pagelist = substr($pagelist, 0, strlen($pagelist)-2);
				
					// Add the table data depending on table type
					if($this->table_layout == 1){
						$table.= "<tr class='".$c_pre."_table-data-row".($c_row == 1 ? '' : " ".$c_pre."_table-data-row-".(($counter % $c_row)+1))."'>
									<td class='".$c_pre."_table-data-column-1'>$data->data</td>
									<td class='".$c_pre."_table-data-column-2'>$pagelist</td>
								</tr>";
								
						if(($counter+1) % $posts_per_column == 0 && $this->columns > 1 && $this->columns > $column_counter){
							$table .= '</tbody></table></div><div style="float: left;" class="'.$c_pre.'_table-column-wrapper '.$c_pre.'_table-column-wrapper-'.($column_counter+1).'">'.$table_start;
							$column_counter++;
						}
					}elseif($this->table_layout == 2){
						$table .= "<td class='".$c_pre."_table-data ".$c_pre."_table-data-column-".$column_counter."><div class='".$c_pre."_table-data-data'>$data->data<span style='display: block;' class='".$c_pre."_table-data-pages'>$pagelist</span></div></td>";
						
						// Check if we need to reset the column
						if($column_counter == $this->columns){
							$table .= '</tr><tr class="'.$c_pre.'_table-data-row'.($c_row == 1 ? '' : " ".$c_pre."_table-data-row-".((($counter+1) % $c_row)+1)).'">';
							
							// Resetting the column counter
							$column_counter = 0;
						}
						
						// Incrementing the column counter
						$column_counter++;
					}
					$counter++;
				}
				
				// Closing the table
				if($this->table_layout == 1){
					$table .= '</tbody></table>';
				
					// Check if we've printed more than 1 column, and therefore need to close some div's
					if($this->columns > 1){
						$table .= '</div></div>';
					}
				}elseif($this->table_layout == 2){
					$table .= '</tr></tbody></table>';
				}
				
				$table .= '</div>'; // Div: _data-wrapper
			}
			
			$table.= '</div>'; // DIV: _wrapper
			
			/* RETURNING DATA */
			return $table;
		}
		
		
		public function printAjaxTable(){
			global $wpdb;
			global $rc_tables;
			
			$groupid = $this->groupid;
			
			if($this->stylesheet_obj == null){
				$c_pre = 'rc_no-stylesheet';
				$c_row = 1;
				$c_col1 = get_option('rc_default_col1_text', __('Data', 'rexCrawler'));
				$c_col2 = get_option('rc_default_col2_text', __('Data', 'rexCrawler'));
			}else{
				// Setting stylesheets			
				// Add style to our document
				wp_enqueue_style('rc_style_output_'.$this->stylesheet_obj->id);
				
				// Saving data
				$c_pre = 'rc_'.str_replace(array(' ', 'æ', 'ø', 'å'), array('-', 'ae', 'oe', 'aa'), strtolower($this->stylesheet_obj->title));
				$c_row = $this->stylesheet_obj->rows;
				$c_col1 = $this->stylesheet_obj->col1;
				$c_col2 = $this->stylesheet_obj->col2;
				
				// Check if we should use default column values
				if($c_col1 == ''){
					$c_col1 = get_option('rc_default_col1_text', __('Data', 'rexCrawler'));
				}
				
				if($c_col2 == ''){
					$c_col2 = get_option('rc_default_col2_text', __('Data', 'rexCrawler'));
				}
				
				// Quick validation of row
				if($c_row < 1){
					$c_row = 1;
				}
			}

			/* FETCH DATA */
			$sql = 'SELECT DISTINCT `'.$rc_tables['data'].'`.* FROM ((`'.$rc_tables['groups'].'` 
						INNER JOIN `'.$rc_tables['pages'].'` ON `'.$rc_tables['groups'].'`.`id` = `'.$rc_tables['pages'].'`.`group`) 
						INNER JOIN `'.$rc_tables['pagedata'].'` ON `'.$rc_tables['pages'].'`.`id` = `'.$rc_tables['pagedata'].'`.`pageid`) 
						INNER JOIN `'.$rc_tables['data'].'` ON `'.$rc_tables['pagedata'].'`.`dataid` = `'.$rc_tables['data'].'`.`id`
					WHERE ((`'.$rc_tables['groups'].'`.`id` = '.$groupid.')'.($this->data != '' ? ' AND (`'.$rc_tables['data'].'`.`data` LIKE \''.$wpdb->escape($this->data).'%'.'\')' : "").($this->form_search_value != '' ? ' AND (`'.$rc_tables['data'].'`.`data` LIKE \''.($this->data == '' ? '' : '%').$wpdb->escape($this->form_search_value).'%\')' : "").($this->form_data_value != '' ? ' AND (`'.$rc_tables['data'].'`.`data` LIKE \'%'.$wpdb->escape($this->form_data_value).'%'.'\')' : "").($this->form_pages_value != '' ? ' AND (`'.$rc_tables['pages'].'`.`id` = '.$wpdb->escape($this->form_pages_value).')' : '').' AND (`'.$rc_tables['pagedata'].'`.`runtime` = (SELECT MAX(runtime) FROM `'.$rc_tables['pagedata'].'`)))
					ORDER BY `'.$rc_tables['data'].'`.`data`'.($this->limit != "" ? " LIMIT ".$this->limit : "").';';
			

			$datalist = $wpdb->get_results($sql);
			$table = '';
			
			/* NO DATA FOUND */
			if(empty($datalist)){
				if(!isset($_POST['rc_output_submitted'])){
					if($this->data == ""){
						$table .= "<p class='italic'>".sprintf(__('No data associated with the group &quot;%1$s&quot;.', 'rexCrawler'), $this->group)."</p>";
					}else{
						$table .= "<p class='italic'>".sprintf(__('No data associated with the group &quot;%1$s&quot; using data-filter &quot;%2$s&quot;.', 'rexCrawler'), $this->group, $this->data)."</p>";
					}
				}else{
					$table .="<p class='italic'>".__('No data found using the specified search parameters.', 'rexCrawler')."</p>";
				}
			}else{
			
				// Initialize the counter
				$counter = 0;
				$column_counter = 1;
				$posts_per_column = ceil(count($datalist)/$this->columns);
				
				/* TABLE OUTPUT */

				// Checking table-layout
				if($this->table_layout == 1){
					// Default layout
					
					// Multiple columns
					if($this->columns > 1){
						$table .= '<div><div style="float: left;" class="'.$c_pre.'table-column-wrapper '.$c_pre.'_table-column-wrapper-1">';
					}

					// Setting up the table
					$table_start = '<table class="'.$c_pre.'_table">';
					
					// Header
					if($this->header == "yes" || $this->header == 1){
								$table_start .= '<thead class="'.$c_pre.'_table-header">
									<tr>
										<th class="'.$c_pre.'_table-header-column-1">'.$c_col1.'</th>
										<th class="'.$c_pre.'_table-header-column-2">'.$c_col2.'</th>
									</tr>
								</thead>';
					}
					
					// Footer
					if($this->footer == "yes" || $this->footer == 1){
								$table_start .= '<tfoot class="'.$c_pre.'_table-footer">
									<tr>
										<th class="'.$c_pre.'_table-footer-1">'.$c_col1.'</th>
										<th class="'.$c_pre.'_table-footer-2">'.$c_col2.'</th>
									</tr>
								</tfoot>';
					}
					
					// Content
					$table_start .= '<tbody class="'.$c_pre.'_table-body">';
				}elseif($this->table_layout == 2){
					// table layout
					$table_start = '<table class="'.$c_pre.'_table">
										<tbody class="'.$c_pre.'_table-body">
											<tr class="'.$c_pre.'_table-data-row'.($c_row == 1 ? '' : " ".$c_pre."_table-data-row-".(($counter % $c_row)+1)).'">';
				}
				
				// Appending the table-start
				$table .= $table_start;
		
				foreach($datalist as $data){
					
					// Get all pages associated with the data
					$sql = "SELECT `".$rc_tables['pages']."`.`title`, `".$rc_tables['pages']."`.`referurl` FROM (`".$rc_tables['data']."` INNER JOIN `".$rc_tables['pagedata']."` ON `".$rc_tables['data']."`.`id` = `".$rc_tables['pagedata']."`.`dataid`) INNER JOIN `".$rc_tables['pages']."` ON `".$rc_tables['pagedata']."`.`pageid` = `".$rc_tables['pages']."`.`id` WHERE (`".$rc_tables['data']."`.`id`= %d AND `".$rc_tables['pagedata']."`.`runtime` = (SELECT MAX(runtime) FROM `".$rc_tables['pagedata']."`));";
			
					$pages = $wpdb->get_results($wpdb->prepare($sql, $data->id));
				
					// Now we need to create the page list
					$pagelist = '';
					
					foreach($pages as $page){
						$pagelist.= "<a href='".stripcslashes($page->referurl)."' target='_BLANK'>".stripcslashes($page->title)."</a>, ";
					}
					
					// Remove the last colon and space
					$pagelist = substr($pagelist, 0, strlen($pagelist)-2);
				
					// Add the table data depending on table type
					if($this->table_layout == 1){
						$table.= "<tr class='".$c_pre."_table-data-row".($c_row == 1 ? '' : " ".$c_pre."_table-data-row-".(($counter % $c_row)+1))."'>
									<td class='".$c_pre."_table-data-column-1'>$data->data</td>
									<td class='".$c_pre."_table-data-column-2'>$pagelist</td>
								</tr>";
								
						if(($counter+1) % $posts_per_column == 0 && $this->columns > 1 && $this->columns > $column_counter){
							$table .= '</tbody></table></div><div style="float: left;" class="'.$c_pre.'_table-column-wrapper '.$c_pre.'_table-column-wrapper-'.($column_counter+1).'">'.$table_start;
							$column_counter++;
						}
					}elseif($this->table_layout == 2){
						$table .= "<td class='".$c_pre."_table-data ".$c_pre."_table-data-column-".$column_counter."><div class='".$c_pre."_table-data-data'>$data->data<span style='display: block;' class='".$c_pre."_table-data-pages'>$pagelist</span></div></td>";
						
						// Check if we need to reset the column
						if($column_counter == $this->columns){
							$table .= '</tr><tr class="'.$c_pre.'_table-data-row'.($c_row == 1 ? '' : " ".$c_pre."_table-data-row-".((($counter+1) % $c_row)+1)).'">';
							
							// Resetting the column counter
							$column_counter = 0;
						}
						
						// Incrementing the column counter
						$column_counter++;
					}
					$counter++;
				}
				
				// Closing the table
				if($this->table_layout == 1){
					$table .= '</tbody></table>';
				
					// Check if we've printed more than 1 column, and therefore need to close some div's
					if($this->columns > 1){
						$table .= '</div></div>';
					}
				}elseif($this->table_layout == 2){
					$table .= '</tr></tbody></table>';
				}
			}
			
			/* RETURNING DATA */
			return $table;
		}
	}
?>