<?php
/*
* Output main admin page
*/
function rc_options()
{
	global $wpdb;
	global $rc_tables;
	
	$err = "";
	global $page_validation_failed;
	
	/* UPDATE DATABASE */
	if(isset($_GET['rc_install_db'])){
		if($_GET['rc_install_db'] == '1'){
			// Run the installation function
			rc_install();
		}
	}
	
	/* VALIDATION AND POST-DATA */
	// Check if we need to add/edit a page
	if(isset($_POST['page_save'])){
		// Getting edit ID if we have one
		if(isset($_POST['page_id'])){
			$id = $_POST['page_id'];
			
			// Valid id?
			if(!is_numeric($id)){
				$id = 0;
			}
		}else{
			$id = 0;
		}
		
		$page_data = array('title' => $_POST['page_title'],'referurl' => $_POST['page_referurl'], 'description' => $_POST['page_description'], 'regex' => $_POST['page_regex'], 'url' => $_POST['page_url'], 'group' => $_POST['page_group'], 'id' => $id);
		
		// Validation
		if($_POST['page_title'] == ""){
			$err.= '<p>'.__('You need to specify a title for the page.', 'rexCrawler').'</p>';
		}
		if($_POST['page_referurl'] == ""){
			$err.= '<p>'.__('You need to specify the link used with data output.', 'rexCrawler').'</p>';
		}
		if($_POST['page_url'] == ""){
			$err.= '<p>'.__('You need to specity the data collecting URL.', 'rexCrawler').'</p>';
		}
		if($_POST['page_regex'] == ""){
			$err.= '<p>'.__('You need to specity a search-pattern.', 'rexCrawler').'</p>';
		}
		
		if($err == ""){
			// We need to add/edit a page
			if($id == 0){
				// Add new page
				if($wpdb->get_var($wpdb->prepare("SELECT id FROM `".$rc_tables['pages']."` WHERE (url = %s AND regex = %s) OR title = %s",$_POST['page_url'],$_POST['page_regex'],$_POST['page_title'])) == null){
					$wpdb->insert(
								$rc_tables['pages'], 
								array(
									'title' => $_POST['page_title'], 
									'referurl' => $_POST['page_referurl'],
									'description' => $_POST['page_description'], 
									'regex' => $_POST['page_regex'], 
									'url' => $_POST['page_url'], 
									'group' => $_POST['page_group']), 
								array('%s', '%s', '%s', '%s', '%s', '%d')
					);
				}else{
					$err.= __('A page with the specified title or URL and search-pattern already exists.', 'rexCrawler');
					$page_validation_failed = 1;
				}
			}else{
				// Edit page
				$wpdb->update(
							$rc_tables['pages'],
							array(
								'title' => $_POST['page_title'], 
								'referurl' => $_POST['page_referurl'],
								'description' => $_POST['page_description'], 
								'regex' => $_POST['page_regex'], 
								'url' => $_POST['page_url'], 
								'group' => $_POST['page_group']), 
							array('id' => $id),
							array('%s', '%s', '%s', '%s', '%s','%d'),
							array('%d')
				);
			}
		}else{
			$page_validation_failed = 1;
		}
	}
	
	// Check if we need to add/edit a group
	if(isset($_POST['group_save'])){
		// Getting edit ID if we have one
		if(isset($_POST['group_id'])){
			$id = $_POST['group_id'];
			
			// Valid id?
			if(!is_numeric($id)){
				$id = 0;
			}
		}else{
			$id = 0;
		}
		
		// Saving data
		$group_data = array('title' => $_POST['group_title'], 'description' => $_POST['group_description'], 'id' => $id);
		
		// Starting the validation
		if($group_data['title'] == ""){
			$err.= '<p>'.__('You need to specify a title for your group', 'rexCrawler').'</p>';
		}
		
		if($err == ""){
			if($id == 0){
				if($wpdb->get_var($wpdb->prepare("SELECT id FROM `".$rc_tables['groups']."` WHERE title = %s", $_POST['group_title'])) == null){
					// Add new group
					$wpdb->insert(
								$rc_tables['groups'], 
								array(
									'title' => $_POST['group_title'], 
									'description' => $_POST['group_description']), 
								array('%s', '%s')
					);
				}else{
					$err.= '<p>'.__('A group with the specified title already exists.', 'rexCrawler').'</p>';
					$page_validation_failed = 1;
				}
			}else{
				// Edit group
				$wpdb->update(
							$rc_tables['groups'],
							array(
								'title' => $_POST['group_title'], 
								'description' => $_POST['group_description']), 
							array('id' => $_POST['group_id']),
							array('%s', '%s'),
							array('%d')
				);
			}
		}else{
			$page_validation_failed = 1;
		}
	}
	
	/* CHECK IF WE ARE GOING TO DELETE PAGES/GROUPS */
	if(isset($_GET['page_del_id'])){
		$exist = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM `".$rc_tables['pages']."` WHERE id = %d;", $_GET['page_del_id']));
		
		if($exist != 0){
			// Delete the page
			$wpdb->query($wpdb->prepare("DELETE FROM `".$rc_tables['pages']."` WHERE `id` = %d;", $_GET['page_del_id']));
			
			// Delete data relations
			$wpdb->query($wpdb->prepare("DELETE FROM `".$rc_tables['pagedata']."` WHERE `pageid` = %d;", $_GET['page_del_id']));
		}
	}
	if(isset($_GET['group_del_id'])){
		$exist = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM `".$rc_tables['groups']."` WHERE `id` = %d;", $_GET['group_del_id']));
		
		if($exist != 0){
			// Delete all data relations with the pages associated to our group
			$wpdb->query($wpdb->prepare("DELETE FROM `".$rc_tables['pagedata']."` WHERE `pageid` IN(SELECT id FROM `".$wpdb->prefix."rc_pages` WHERE `group` = %d);", $_GET['group_del_id']));
			
			// Delete all the pages associated with this group
			$wpdb->query($wpdb->prepare("DELETE FROM `".$rc_tables['pages']."` WHERE `group` = %d;", $_GET['group_del_id']));
			
			// Finally delete the group
			$wpdb->query($wpdb->prepare("DELETE FROM `".$rc_tables['groups']."` WHERE `id` = %d;", $_GET['group_del_id']));
		}
	}
	
	/* SHOW OPTIONS PAGE */
	
	echo '<div class="wrap">
			<div id="icon-tools" class="icon32">
				<br />
			</div>
			<h2>rexCrawler Options</h2>';
			
			if($err != ""){
				echo "<div class='err'>$err</div>";
			}
			
				echo '<p>'.__('These are the option pages where you&apos;re able to administrate rexCrawler.' ,'rexCrawler').'</p>
				<p>'.__('On this page you can specify the different crawl-groups and the pages which the crawler should visit.', 'rexCrawler').'</p>';
				
	/* rexCrawler installations error tester */
	$dbTest = false;
	foreach($rc_tables as $table){
		if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
			// Table doesnt exist!
			$dbTest = true;
		}
	}
	
	if($dbTest){
		// Show the database-error handler
		echo '<h3>'.__('Update database', 'rexCrawler').'</h3>
			<p>'.__('Your database doesn&apos;t include the tables needed for rexCrawler to run. To install the tables, push the button below:', 'rexCrawler').'</p>
			<p><a href="'.$_SERVER['REQUEST_URI'].'&rc_install_db=1" class="button-primary">'.__('Install database tables', 'rexCrawler').'</a></p>';
	}else{
				
			echo '<h3>'.__('Administrate groups', 'rexCrawler').'</h3>
				<p>'.__('Administrate the different crawl-groups.', 'rexCrawler').'</p>';
				
				// Get group_count
				$group_count = $wpdb->get_var("SELECT COUNT(id) FROM `".$rc_tables['groups']."`");
				
				// Check if we have any groups created at all
				if($group_count > 0){
					// Print list of groups
					echo '<table class="widefat">
								<thead>
									<tr>
										<th>'.__('Title', 'rexCrawler').'</th>
										<th>'.__('Description', 'rexCrawler').'</th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th>'.__('Title', 'rexCrawler').'</th>
										<th>'.__('Description', 'rexCrawler').'</th>
									</tr>
								</tfoot>
								<tbody>';
								
					$groups = $wpdb->get_results("SELECT id, title, description FROM `".$rc_tables['groups']."` ORDER BY title ASC");
						
					foreach($groups as $group){
						echo "<tr>
								<td>".stripcslashes($group->title)."
									<div class='row-actions'>
										<span class='edit'><a href='".$_SERVER['REQUEST_URI']."&group_id=$group->id'>".__('Edit', 'rexCrawler')."</a> |</span>
										<span class='delete'><a href=\"javascript: confirmMsg('".__('Are you sure you want to delete the group? Be aware that the related pages will also be deleted!', 'rexCrawler')."', '".$_SERVER['REQUEST_URI']."&group_del_id=$group->id');\">".__('Delete', 'rexCrawler')."</a></span>
									</div>
								</td>
								<td>".stripcslashes($group->description)."</td>
							</tr>";
					}
						
					echo '</tbody>
						</table>';
				}else{
					echo '<p class="italic">'.__('No groups found.', 'rexCrawler').'</p>';
				}
				
				echo "<p><a class='button-secondary' href='?page=rc_options&group_create=1'>".__('Create new group', 'rexCrawler')."</a>";
				
				rc_create_new_group($group_data);
				
			echo '</p>';
			echo '<h3>'.__('Administrate pages', 'rexCrawler').'</h3>
				<p>'.__('Administrate the pages associated to rexCrawler.', 'rexCrawler').'</p>';
				
				// No need to look for pages if we do not have any groups
				if($group_count > 0){
					// Get page_count
					$page_count = $wpdb->get_var("SELECT Count(id) FROM `".$rc_tables['pages']."`");
					
					// If there's any pages, we need to show them
					if($page_count != 0){
						$pages = $wpdb->get_results("SELECT id, title, description, url FROM `".$rc_tables['pages']."` ORDER BY title ASC");
						
						// Display list of all pages
						echo '<table class="widefat">
								<thead>
									<tr>
										<th>'.__('Title', 'rexCrawler').'</th>
										<th>'.__('Description', 'rexCrawler').'</th>
										<th>'.__('URL', 'rexCrawler').'</th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th>'.__('Title', 'rexCrawler').'</th>
										<th>'.__('Description', 'rexCrawler').'</th>
										<th>'.__('URL', 'rexCrawler').'</th>
									</tr>
								</tfoot>
								<tbody>';
						
						foreach($pages as $page){
							echo "<tr>
									<td>".stripcslashes($page->title)."
									<div class='row-actions'>
										<span class='edit'><a href='".$_SERVER['REQUEST_URI']."&page_id=$page->id'>".__('Edit', 'rexCrawler')."</a> |</span>
										<span class='delete'><a href=\"javascript: confirmMsg('".__('Are you sure you want to delete the page?', 'rexCrawler')."', '".$_SERVER['REQUEST_URI']."&page_del_id=$page->id');\">".__('Delete', 'rexCrawler')."</a></span>
									</div>
									</td>
									<td>".stripcslashes($page->description)."</td>
									<td>".stripcslashes($page->url)."</td>
								</tr>";
						}
						
						echo '</tbody>
							</table>';
					}else{
						echo '<p class="italic">'.__('No pages found in the rexCrawler database.', 'rexCrawler').'</p>';
					}
					
					echo "<p><a class='button-secondary' href='?page=rc_options&page_create=1'>".__('Create new page', 'rexCrawler')."</a>";

					rc_create_new_page($page_data);
					echo '</p>';
				}else{
					// No groups created
					echo '<p class="italic">'.__('You need to create at least one group before you can create pages.', 'rexCrawler').'</p>';
				}
				
		  echo '</div>';
	}
}

/*
* Prints out the 'Create new page'-form
*/
function rc_create_new_page($page_data){
	global $wpdb;
	global $rc_tables;
	
	// Checks if we're going to edit a page
	if(isset($_GET['page_id'])){		
		// Get the page ID
		$id = $_GET['page_id'];
		
		// Check if it's a numeric value
		if(is_numeric($id)){
			// Get the current data
			$data = $wpdb->get_row("SELECT * FROM `".$rc_tables['pages']."` WHERE id = $id");
			
			// prepare the data to use as values
			$title = stripcslashes($data->title);
			$referurl = stripcslashes($data->referurl);
			$url = stripcslashes($data->url);
			$regex = stripcslashes($data->regex);
			$description = stripcslashes($data->description);
			$groupid = stripcslashes($data->group);
			$id = "<input type='hidden' name='page_id' value='$data->id' />";
		}
	}
	
	// Check if we need to load failed validation info
	global $page_validation_failed;
	if($page_validation_failed == 1){
		// prepare the data to use as values
		$title = stripcslashes($page_data['title']);
		$referurl = stripcslashes($page_data['referurl']);
		$url = stripcslashes($page_data['url']);
		$regex = stripcslashes($page_data['regex']);
		$description = stripcslashes($page_data['description']);
		$groupid = stripcslashes($page_data['group']);
		
		if($page_data['id'] != 0){
			$id = "<input type='hidden' name='page_id' value='".$page_data['id']."' />";
		}
	}
	
	// Echo the form
	echo "<form method='post' action='?page=rc_options'>
			<div id='poststuff' class='metabox-holder'>
			<div class='stuffbox' id='rc_create_new_page'><h3><label for='page_title'>".($id == "" ? __('Create new page', 'rexCrawler') : __('Edit page', 'rexCrawler'))."</label></h3>";

	echo "$id
			<div class='inside'>
			<table class='form-table'>
				<tbody>
					<tr>
						<td class='first'><label for='page_title'>".__('Title*:', 'rexCrawler')."</label></td>
						<td><input type='text' id='page_title' maxlength='100' name='page_title' value='$title' style='width: 100%;' /></td>
					</tr>
					<tr>
						<td class='first'><label for='page_referurl'>".__('Link*:', 'rexCrawler')."</label></td>
						<td><input type='text' id='page_referurl' name='page_referurl' value='$referurl' style='width: 100%;' /></td>
					</tr>
					<tr>
						<td><label for='page_url'>".__('Crawl URL*:', 'rexCrawler')."</label></td>
						<td><input type='text' id='page_url' name='page_url' value='$url' style='width: 100%;' /></td>
					</tr>
					<tr>
						<td class='first' colspan='2'><label for='page_regex'>".__('Search-pattern*: (one per line)', 'rexCrawler')."</label></td>
					</tr>
					<tr>
						<td colspan='2'><textarea id='page_regex' name='page_regex' style='width: 100%;' rows='3'>$regex</textarea></td>
					</tr>
					<tr>
						<td class='first'><label for='page_description'>".__('Description:', 'rexCrawler')."</label></td>
					</tr>
					<tr>
						<td colspan='2'><textarea id='page_description' name='page_description' rows='3' style='width: 100%;'>$description</textarea></td>
					</tr>
					<tr>
						<td class='first'><label for='page_group'>".__('Group*:', 'rexCrawler')."</label></td>
						<td>";
							echo rc_get_groups('page_group', $groupid);
						echo "</td>
					</tr>
					<tr>
						<td colspan='2'><input type='submit' class='button-primary' name='page_save' value='".__('Save', 'rexCrawler')."' /> <a class='button-secondary' href='?page=rc_options'>".__('Cancel', 'rexCrawler')."</a></td>
					</tr>
				</tbody>
			</table>
			</div>
		</div></div></form>";
		
		// If we're editing a page or creating a new one, we want to show the form
		if($id != "" || isset($_GET['page_create']) || $page_validation_failed == 1){
			echo "<script type='text/javascript'>toggleLayer('rc_create_new_page');</script>";
		}
}

/*
* Prints out the 'Create new group'-form
*/
function rc_create_new_group($group_data){
	// Checks if we're going to edit a group
	if(isset($_GET['group_id'])){
		global $wpdb;
		global $rc_tables;
		
		// Get the group ID
		$id = $_GET['group_id'];
		
		// Check if it's a numeric value
		if(is_numeric($id)){
			// Get the current data
			$data = $wpdb->get_row("SELECT * FROM `".$rc_tables['groups']."` WHERE id = $id");
			
			// prepare the data to use as values
			$title = stripcslashes($data->title);
			$description = stripcslashes($data->description);
			$id = "<input type='hidden' name='group_id' value='$data->id' />";
		}
	}
	
	// Check if we need to load failed validation info
	global $page_validation_failed;
	if($page_validation_failed == 1){
		// prepare the data to use as values
		$title = stripcslashes($group_data['title']);
		$description = stripcslashes($group_data['description']);
		
		if($group_data['id'] != 0){
			$id = "<input type='hidden' name='group_id' value='".$group_data['id']."' />";
		}
	}
	
	// Echo the form
	echo "<form method='post' action='?page=rc_options'>
			<div id='poststuff' class='metabox-holder'>
			<div class='stuffbox' id='rc_create_new_group'><h3>".($id == "" ? __('Create new group', 'rexCrawler') : __('Edit group', 'rexCrawler'))."</h3>";

	echo "$id
			<div class='inside'>
			<table class='form-table'>
				<tbody>
					<tr>
						<td class='first'><label for='group_title'>".__('Title*:', 'rexCrawler')."</label></td>
						<td><input type='text' id='group_title' name='group_title' maxlength='100' value='$title' style='width: 100%;' /></td>
					</tr>
					<tr>
						<td class='first'><label for='group_description'>".__('Description:', 'rexCrawler')."</label></td>
					</tr>
					<tr>
						<td colspan='2'><textarea name='group_description' id='group_description' rows='3' style='width: 100%;'>$description</textarea></td>
					</tr>
					<tr>
						<td colspan='2'><input type='submit' class='button-primary' name='group_save' value='".__('Save', 'rexCrawler')."' /> <a class='button-secondary' href='?page=rc_options'>".__('Cancel', 'rexCrawler')."</a></td>
					</tr>
				</tbody>
			</table>
			</div>
		</div>
		</div>
		</form>";
		
	// If we're editing a group, we want to show the form
	if($id != "" || isset($_GET['group_create']) || $page_validation_failed == 1){
		echo "<script type='text/javascript'>toggleLayer('rc_create_new_group');</script>";
	}
}
?>