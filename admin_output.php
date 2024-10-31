<?php

/****************************
* Data output administration
****************************/
function rc_data_output(){
	global $wpdb;
	global $rc_tables;
	
	// Get group id
	if(isset($_POST['group_id'])){
		$groupid = $_POST['group_id'];
	}else{
		$groupid = $wpdb->get_var("SELECT id FROM `".$rc_tables['groups']."` ORDER BY title ASC LIMIT 1");
	}
	
	// Echo header etc
	echo "<div class='wrap'>
			<div id='icon-tools' class='icon32'>
				<br />
			</div>
			<h2>".__('rexCrawler data output', 'rexCrawler')."</h2>";
			
	// Echo form and table with data
	if($groupid != null){
		echo "<form method='post' name='change_group' action='".$_SERVER['REQUEST_URI']."'>";
		
			$select_group = rc_get_groups('group_id', $groupid,0, 'onChange="this.form.submit();"');
		
		echo "<p>".sprintf(__('Select group: %1$s', 'rexCrawler'), $select_group)."</p>
			<p>".__('List of data output from rexCrawler:', 'rexCrawler')."</p>";
		
		$output = new RC_Output();
		$output->parseOptions("group=".$groupid);
		echo $output->getOutput();
	}else{
		echo '<p>'.__('No groups created.', 'rexCrawler').'</p>';
	}
	echo '</div>';
}

/****************************
* Data output options
****************************/
function rc_data_output_options(){

	global $rc_tables;
	global $wpdb;
	$msg = '';

	// Echo header etc
	echo "<div class='wrap'>
			<div id='icon-tools' class='icon32'>
				<br />
			</div>
			<h2>".__('rexCrawler data output options', 'rexCrawler')."</h2>";
			
	/**** Default values ****/
	// Check if we need to save default values
	if(isset($_POST['def_save'])){
		// Saving default options
		update_option('rc_default_group', $_POST['def_group']);
		update_option('rc_default_limit', $_POST['def_limit']);
		update_option('rc_default_data', $_POST['def_data']);
		
		update_option('rc_default_table_layout', $_POST['def_table_layout']);
		update_option('rc_default_header', $_POST['def_header']);
		update_option('rc_default_footer', $_POST['def_footer']);
		update_option('rc_default_columns', $_POST['def_columns']);
		
		update_option('rc_default_form_search', $_POST['def_form_search']);
		update_option('rc_default_form_data', $_POST['def_form_data']);
		update_option('rc_default_form_pages', $_POST['def_form_pages']);
		
		update_option('rc_default_stylesheet', $_POST['def_stylesheet']);
		
		update_option('rc_default_col1_text', $_POST['def_col1_text']);
		update_option('rc_default_col2_text', $_POST['def_col2_text']);
		
		$msg = '<div class="okMsg">'.__('Default values saved.', 'rexCrawler').'</div>';
	}
	
	// Get the already saved default values
	$def_group = get_option('rc_default_group', '0');
	$def_limit = get_option('rc_default_limit', '');
	$def_data = get_option('rc_default_data', '');
	
	$def_table_layout = get_option('rc_default_table_layout', 1);
	$def_header = get_option('rc_default_header', '1');
	$def_footer = get_option('rc_default_footer', '1');
	$def_columns = get_option('rc_default_columns', '1');
	$def_col1_text = get_option('rc_default_col1_text', __('Data', 'rexCrawler'));
	$def_col2_text = get_option('rc_default_col2_text', __('Page', 'rexCrawler'));
	
	$def_form_search = get_option('rc_default_form_search', 0);
	$def_form_data = get_option('rc_default_form_data', 0);
	$def_form_pages = get_option('rc_default_form_pages', 0);
	
	$def_stylesheet = get_option('rc_default_stylesheet', '');
	
	echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		  <div id="poststuff" class="metabox-holder">
			<div class="stuffbox">
				<h3>'.__('Default values', 'rexCrawler').'</h3>
				<div class="inside">'.$msg;
					echo "<table>
						<tbody>
							<tr>
								<td><label for='def_group'>".__('Group:', 'rexCrawler')."</label></td>
								<td>".rc_get_groups('def_group', $def_group)."</td>
							</tr>
							<tr>
								<td><label for='def_limit'>".__('Limit:', 'rexCrawler')."</label></td>
								<td><input type='text' name='def_limit' id='def_limit' value='$def_limit' /></td>
							</tr>
							<tr>
								<td><label for='def_data'>".__('Data:', 'rexCrawler')."</label></td>
								<td><input type='text' name='def_data' id='def_data' value='$def_data' /></td>
							</tr>
							<tr>
								<td><label for='def_table_layout'>".__('Table layout:', 'rexCrawler')."</label></td>
								<td><select name='def_table_layout' id='def_table_layout'>
										<option value='1'".($def_table_layout == 1 ? ' selected' : '').">".__('List', 'rexCrawler')."</option>
										<option value='2'".($def_table_layout == 2 ? ' selected' : '').">".__('Blocks', 'rexCrawler')."</option>
									</select>
								</td>
							</tr>
							<tr id='header_option'>
								<td><label for='def_header_1'>".__('Show header:', 'rexCrawler')."</label></td>
								<td>".rc_output_yesno('def_header', $def_header)."</td>
							</tr>
							<tr id='footer_option'>
								<td><label for='def_footer_1'>".__('Show footer:', 'rexCrawler')."</label></td>
								<td>".rc_output_yesno('def_footer', $def_footer)."</td>
							</tr>
							<tr>
								<td><label for='def_columns'>".__('Columns:', 'rexCrawler')."</label></td>
								<td><input type='text' name='def_columns' id='def_columns' value='$def_columns' /></td>
							</tr>
							<tr>
								<td><label for='def_form_search'>".__('Show search-bar:', 'rexCrawler')."</label></td>
								<td>".rc_output_yesno('def_form_search', $def_form_search)."</td>
							</tr>
							<tr>
								<td><label for='def_form_data'>".__('Show data-list:', 'rexCrawler')."</label></td>
								<td>".rc_output_yesno('def_form_data', $def_form_data)."</td>
							</tr>
							<tr>
								<td><label for='def_form_pages'>".__('Show page-list:', 'rexCrawler')."</label></td>
								<td>".rc_output_yesno('def_form_pages', $def_form_pages)."</td>
							</tr>
							
							<tr>
								<td><label for='def_group'>".__('Stylesheet:', 'rexCrawler')."</label></td>
								<td>";
									
									$styles = $wpdb->get_results("SELECT * FROM `$rc_tables[styles]` ORDER BY `title` ASC");
									if($styles != null){
										echo "<select name='def_stylesheet'>";
										
										foreach($styles as $style){
											echo "<option value='$style->id'".($style->id == $def_stylesheet ? ' selected' : '').">$style->title</option>";
										}
										
										echo '</select>';
									}else{
										echo __('No stylesheets found.', 'rexCrawler');
									}
									
							echo "</td>
							</tr>
							<tr>
								<td><label for='def_col1_text'>".__('Column 1 text:', 'rexCrawler')."</label></td>
								<td><input type='text' name='def_col1_text' id='def_col1_text' value='$def_col1_text' /></td>
							</tr>
							<tr>
								<td><label for='def_col2_text'>".__('Column 2 text:', 'rexCrawler')."</label></td>
								<td><input type='text' name='def_col2_text' id='def_col2_text' value='$def_col2_text' /></td>
							</tr>
							<tr>
								<td colspan='2'><br /><input type='submit' name='def_save' value='".__('Save', 'rexCrawler')."' class='button-primary' /></td>
							</tr>
						</tbody>
					</table>";			
			echo '</div>
			</div>
		</div>
		</form>';
		
	/**** Table designer ****/
	$msg = '';
	
	if(isset($_POST['style_save'])){
		// Create or edit a stylesheet
		$style_css = $_POST['style_css'];
		$style_rows = $_POST['style_row_diff'];
		$style_col1 = $_POST['style_col1'];
		$style_col2 = $_POST['style_col2'];
		
		if($_POST['style_name'] == 0){
			// Create new!
			$style_name = $_POST['style_name_new'];
			$style_id = 0;
		}else{
			$style_id = $_POST['style_name'];
			$style_name = '';
		}
		
		/** Validating **/
		$err = '';
		
		// Validating name
		if($style_name == '' && $style_id == 0){
			$err.= '<p>'.__('You need to specify a name.', 'rexCrawler').'</p>';
		}
		
		// Checking if the name already exists
		$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM `$rc_tables[styles]` WHERE `title` = %s", $style_name));
		if($count != 0 && $style_id == 0){
			$err.= '<p>'.__('There is already a stylesheet with the specified name.', 'rexCrawler').'</p>';
		}
		
		// If no errors, add data
		if($err == ''){
			if($style_id == 0){
				// Create new stylesheet
				$wpdb->insert($rc_tables['styles'], array('title' => $style_name, 'style' => $style_css, 'rows' => $style_rows, 'col1' => $style_col1, 'col2' => $style_col2), array('%s', '%s', '%d', '%s', '%s'));
			
				// Get the stylesheet ID for CSS file
				//$style_id = $wpdb->get_var($wpdb->prepare("SELECT `id` FROM `$rc_tables[styles] WHERE `title` = %s", $style_name));
				
				$msg = '<p class="okMsg">'.__('Stylesheet saved.', 'rexCrawler').'</p>';
			}else{
				// Edit existing stylesheet
				$wpdb->update($rc_tables['styles'], array('style' => $style_css, 'rows' => $style_rows, 'col1' => $style_col1, 'col2' => $style_col2), array('id' => $style_id), array('%s', '%d', '%s', '%s'), array('%d'));
				$msg = '<p class="okMsg">'.__('Stylesheet updated.', 'rexCrawler').'</p>';
			}
			
			/*// Save the file
			$css_file = dirname(__FILE__)."/css/$style_id.css";
			$fh = fopen($css_file, 'w');
			
			// Checking if we're successful and ready to write!
			if($fh != false){
				fwrite($fh, $style_css);
				fclose($fh);
			}*/
			
			
			// Resetting variables
			$style_id = 0;
			$style_css = '';
			$style_rows = 1;
			$style_col1 = '';
			$style_col2 = '';
			$styles = $wpdb->get_results("SELECT * FROM `$rc_tables[styles]` ORDER BY `title` ASC");
		}
	}
	
	echo '<div id="poststuff" class="metabox-holder">
			<div class="stuffbox">
				<h3>'.__('Table designer', 'rexCrawler').'</h3>
				<div class="inside">'.$msg.'
					<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
					'.$err.'
					<div>
						<table>
							<tr>
								<td>'.__('Stylesheet:', 'rexCrawler').'</td>';
							echo "<td>
									<select name='style_name' id='style_name'>
										<option value='0'>".__('&lt;Create new&gt;', 'rexCrawler')."</option>";
									
										foreach($styles as $style){
											echo "<option value='$style->id'".($style_id != 0 && $style_id == $style->id ? " selected" : "").">$style->title</option>";
										}
										
								echo '</select>
								</td>
							</tr>
							<tr id="style_new_name"'.($style_id != 0 ? ' style="display: none;"' : '').'>
								<td>'.__('Name:', 'rexCrawler').'</td>
								<td><input type="text" id="style_name_new" name="style_name_new" /></td>
							</tr>
							<tr>
								<td>'.__('Column 1 text:', 'rexCrawler').'</td>
								<td><input type="text" name="style_col1" value="'.$style_col1.'" id="style_col1" /> '.__('(Leave empty to use standard values)', 'rexCrawler').'</td>
							</tr>
							<tr>
								<td>'.__('Column 2 text:', 'rexCrawler').'</td>
								<td><input type="text" name="style_col2" value="'.$style_col2.'" id="style_col2" /> '.__('(Leave empty to use standard values)', 'rexCrawler').'</td>
							</tr>
							<tr>
								<td colspan="2">'.__('CSS:', 'rexCrawler').'</td>
							</tr>
							<tr>
								<td colspan="2"><textarea name="style_css" id="style_css" style="height: 250px; width: 375px;">'.$style_css.'</textarea></td>
							</tr>
							<tr>
								<td>Sanitized name:</td>
								<td><div id="style_sanitize">'.__('[Not generated]', 'rexCrawler').'</div></td>
							</tr>
							<tr>
								<td>'.__('Row differentation:', 'rexCrawler').'</td>
								<td>
									<select name="style_row_diff" id="style_row_diff">';
									
									for($i = 1; $i <= 5; $i++){
										echo "<option value='$i'".($style_rows == $i ? ' selected' : '').">$i</option>";
									}
								echo '</select>
								</td>
							</tr>
							<tr>
								<td colspan="2"><input type="submit" class="button-primary" name="style_save" value="'.__('Save', 'rexCrawler').'" /></td>
							</tr>
						</table>
					</div>
					</form>
				</div>
			</div>
		</div>';
		
	
	
	echo '</div>';
}
?>