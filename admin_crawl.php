<?php
function rc_start_crawler(){
	global $wpdb;
	global $rc_tables;
	
	$err = "";
	
	// Check if the start-button has been pressed
	if(isset($_POST['run_rc'])){
		// It's time to start the crawler!
		require_once('crawler.php');
		
		// Get the group id
		$group = $_POST['group'];
		
		// Get the different pages we're gonna crawl
		$pages = $wpdb->get_results("SELECT `id`, `title`, `url`, `regex` FROM `".$rc_tables['pages']."` WHERE `group` = $group");
		
		// Did we get any pages?
		if($pages != null){
			// Save the time for our crawl
			$crawltime = date('Y-m-d H:i:s');
			
			// Loop through and crawl!
			foreach($pages as $page){
				$crawl = new Crawler(stripcslashes($page->url));
				$data = $crawl->getFromRegex(array_map("trim", explode("\n", (stripcslashes($page->regex)))));
				
				// Now that we have our data, we need to insert it into the database
				if(!empty($data)){
					foreach($data as $str){
						// First check if the data already exists
						$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM `".$rc_tables['data']."` WHERE LOWER(`data`) = LOWER(%s)", $str));
						if($exists == null){
							// The data doesnt exist - add it to the database
							$wpdb->insert($wpdb->prefix.'rc_data', array('data' => $str));
						}else{
							// The data already exists - do nothing
						}
						
						// Now the data is sorted out, and we need to create a relation!
						// We need to get the id of our data
						if($exists == null){
							// Since we just added our data before, we need to fetch the ID from the database
							$dataID = $wpdb->get_var($wpdb->prepare("SELECT id FROM `".$rc_tables['data']."` WHERE LOWER(`data`) = LOWER(%s)", $str));
						}else{
							// The data already existed, so we're just saving the already found ID
							$dataID = $exists;
						}
						
						// Time to check if there's already a relation
						$relation = $wpdb->get_var("SELECT COUNT(*) FROM `".$rc_tables['pagedata']."` WHERE pageid = $page->id AND dataid = $dataID AND runtime = $crawltime;");
						
						if($relation == 0){
							// Add our relation
							$wpdb->insert($wpdb->prefix.'rc_pagedata', array('pageid' => $page->id, 'dataid' => $dataID, 'runtime' => $crawltime));
						}
					}
				}else{
					$err.= "<p>".sprintf(__('Could not load data from &apos;%1$s&apos;.', 'rexCrawler'), $page->title)."</p>";
				}
			}
		}else{
			$temp = $wpdb->get_var($wpdb->prepare("SELECT `title` FROM `".$rc_tables['groups']."` WHERE `id` = %d", $group));
			$err.= '<p>'.sprintf(__('No data associated with the group &quot;%1$s&quot;.', 'rexCrawler'), $temp).'</p>';
		}
	}
	
	echo '<div class="wrap">
			<div id="icon-tools" class="icon32">
				<br />
			</div>';
			
			echo "<form action='".$_SERVER['REQUEST_URI']."' method='post'>";
			
			echo '<h2>'.__('Start rexCrawler', 'rexCrawler').'</h2>';
			
			if($err != ""){
				echo "<div class='err'>$err</div>";
			}
			
			echo '<h3>'.__('Step 1: Select group', 'rexCrawler').'</h3>
			<p>'.__('Before you can start rexCrawler, you need to select a data-group to collect data from.', 'rexCrawler').'</p>';
			
			$select_group = '<select name="group">';
			$groups = $wpdb->get_results("SELECT `id`, `title` FROM `".$rc_tables['groups']."` ORDER BY `title` ASC");
			
			foreach($groups as $group){
				$select_group.= "<option value='$group->id'>$group->title</option>";
			}
			
			$select_group .= '</select>';
			echo '<p>'.sprintf(__('Select group: %1$s', 'rexCrawler'),$select_group).'
			<h3>'.__('Step 2: Run rexCrawler', 'rexCrawler').'</h3>
			<p>'.__('You&apos;re now ready to run rexCrawler. Push the button below this text to start collecting data.', 'rexCrawler').'</p>
			<div class="submit"><input class="button-primary" type="submit" name="run_rc" value="'.__('Run rexCrawler', 'rexCrawler').'" /></div>
			</form>';

	echo '</div>';
}
?>