<?php
/*
Plugin Name: rexCrawler
Plugin URI: http://www.rexcrawler.com
Description: This plugin crawls given websites and retrieves information specified by regular expressions, which are then saved in the WordPress-database
Author: Lars Rasmussen
Author URI: http://www.rexcrawler.com
Text Domain: rexCrawler
Version: 1.0.15
*/

/****************************
* Global variables
****************************/
$rc_version = "1.0.13";
$page_validation_failed = 0;
$rc_tables = array( 'data' => $wpdb->prefix."rc_data", 
					'pages' => $wpdb->prefix."rc_pages", 
					'groups' => $wpdb->prefix."rc_groups", 
					'pagedata' => $wpdb->prefix."rc_pagedata",
					'styles' => $wpdb->prefix."rc_stylesheets");

/****************************
* Includes
****************************/

if(is_admin()){
	// Admin page
	require_once('admin_main.php');
	require_once('admin_crawl.php');
	require_once('admin_output.php');
	require_once('admin_regex_test.php');
}else{
	// Not admin page
}

require_once('public_content.php');


/****************************
* Actions and hooks
****************************/
add_action('init', 'rc_init');
register_activation_hook(__FILE__, 'rc_install'); 						// Plugin activation and installation
add_action('admin_init', 'rc_admin_init');								// Initialize administration menu
add_action('admin_menu', 'rc_admin_menu'); 								// Administration menu
add_action('wp_head', 'rc_load_output_stylesheets');					// Add stylesheets to our header

add_filter('the_content', 'rc_public_content');							// Initialize the content filter
add_filter('widget_text', 'rc_public_content');
add_filter('plugin_row_meta', 'rc_row_meta', 10, 2);
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'rc_plugin_actlinks');

add_action('wp_ajax_nopriv_rc_ajax_output', 'rc_ajax_output');
add_action('wp_ajax_rc_ajax_output', 'rc_ajax_output');
add_action('wp_ajax_rc_ajax_admin_stylesheet_name', 'rc_ajax_admin_stylesheet_name');
add_action('wp_ajax_rc_ajax_admin_stylesheet_get_data', 'rc_ajax_admin_stylesheet_get_data');

/****************************
* Installation and activation
****************************/
function rc_install()
{
	global $wpdb;
	global $rc_version;
	
	// Create the table to hold saved expressions
	$table = $wpdb->prefix.'rc_groups'; //$this->rc_tables['groups'];
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
		$structure = "CREATE TABLE  $table ( 
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`title` VARCHAR( 100 ) NOT NULL,
		`description` TEXT NOT NULL
		) ENGINE = MYISAM COMMENT =  'Groups the different pages';";
		$wpdb->query($structure);
	}
	
	// Create the table to hold saved sites to crawl
	$table = $wpdb->prefix.'rc_pages'; //$this->rc_tables['pages'];
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
		$structure = "CREATE TABLE  $table ( 
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`title` VARCHAR( 100 ) NOT NULL,
		`description` TEXT,
		`referurl` TEXT, 
		`regex` TEXT NOT NULL,
		`url` TEXT NOT NULL,
		`group` INT NOT NULL
		) ENGINE = MYISAM COMMENT =  'Holds the saved sites';";
		$wpdb->query($structure);
	}
	
	// Create the table to hold data
	$table = $wpdb->prefix.'rc_data'; //$this->rc_tables['data'];
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
		$structure = "CREATE TABLE  $table (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`data` TEXT NOT NULL
		) ENGINE = MYISAM COMMENT =  'Holds all the data crawled';";
		$wpdb->query($structure);
	}
	
	// Create the relation table between sites and data
	$table = $wpdb->prefix.'rc_pagedata'; //$this->rc_tables['pagedata'];
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
		$structure = "CREATE TABLE  $table (
		`pageid` INT NOT NULL,
		`dataid` INT NOT NULL,
		`runtime` DATETIME NOT NULL,
		PRIMARY KEY (`pageid`,`dataid`,`runtime`)
		) ENGINE = MYISAM COMMENT =  'Creates the relation between data and pages';";
		$wpdb->query($structure);
	}
	
	// Create the table that holds the different stylesheets used to style output tables
	$table = $wpdb->prefix.'rc_stylesheets'; //$this->rc_tables['styles'];
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table){
		$structure = "CREATE TABLE  `$table` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`title` VARCHAR( 100 ) NOT NULL ,
		`style` TEXT NOT NULL ,
		`rows` INT NOT NULL ,
		`col1` VARCHAR( 50 ) ,
		`col2` VARCHAR( 50 )
		) ENGINE = MYISAM COMMENT =  'Saves the different stylesheets used to style output tables';";
		$wpdb->query($structure);
	}
	
	add_option("rc_version", $rc_version);
}

/****************************
* Wordpress initialization
****************************/
function rc_init(){
	$plugin_dir = basename(dirname(__FILE__));
	// Load text-domain
	load_plugin_textdomain('rexCrawler', null, $plugin_dir);
	
	// AJAX scripts
	// embed javascript file that makes the AJAX request
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'json2' );
	wp_enqueue_script( 'rc_ajax_output', plugin_dir_url( __FILE__ ) . 'js/ajax.js', array( 'jquery', 'json2' ) );
	
	// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
	wp_localize_script('rc_ajax_output', 'rexCrawlerAJAX', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'pluginurl' => get_bloginfo('url').'/wp-content/plugins/rexcrawler/' ) );
}

/****************************
* Links in the Wordpress Plugin-page
****************************/
// Meta links (Near Author, Plugin URL etc)
function rc_row_meta($links, $file){
	$plugin = plugin_basename(__FILE__);

	if($file == $plugin){
		$rc_links[] = '<a href="http://www.rexcrawler.com/go/documentation/" title="' . __( 'Documentation', 'rexCrawler' ) . '">' . __( 'Documentation', 'rexCrawler' ) . '</a>';
		$rc_links[] = '<a href="http://www.rexcrawler.com/go/faq/" title="' . __( 'rexCrawler FAQ', 'rexCrawler' ) . '">' . __( 'FAQ', 'rexCrawler' ) . '</a>';
		$rc_links[] = '<a href="http://www.rexcrawler.com/go/support/" title="' . __( 'rexCrawler Support', 'rexCrawler' ) . '">' . __( 'Support', 'rexCrawler' ) . '</a>';
		$rc_links[] = '<strong><a href="http://www.rexcrawler.com/go/donate/" title="' . __( 'Donate to rexCrawler', 'rexCrawler' ) . '">' . __( 'Donate', 'rexCrawler' ) . '</a></strong>';
		
		$links = array_merge($links, $rc_links);
	}
	
	return $links;
}

// Action-links (Near Deactivate, edit etc)
function rc_plugin_actlinks($links){
	$rc_options = '<a href="admin.php?page=rc_options">'.__('Settings', 'rexCrawler').'</a>';
	array_unshift($links, $rc_options);
	
	return $links;
}


/****************************
* Wordpress stylesheets
****************************/
function rc_load_output_stylesheets(){
	global $wpdb;
	global $rc_tables;
	
	// Get the different stylesheets
	$stylesheets = $wpdb->get_results("SELECT `title`, `style` FROM `$rc_tables[styles]` ORDER BY `id` ASC");

	if(stylesheets != null){
		$output = "<style type='text/css'>\n";
		
		// Loop through and register stylesheets
		foreach($stylesheets as $style){
			// Add our admin-stylesheet
			$output .= "/***** REXCRAWLER STYLESHEET: $style->title *****/\n";
			$output .= "\n/***** REXCRAWLER STYLESHEET END *****/\n\n";
			$output .= $style->style;
		}
		
		$output .= "</style>\n";
		
		echo $output;
	}
}

/****************************
* AJAX output implementation
****************************/

// The PHP function run when generating output-tables
function rc_ajax_output(){
	global $wpdb; // this is how you get access to the database
	
	$output = new RC_Output();
	$output->parseOptions(urldecode($_POST['rc_output_options']));
	// Checking if we have set anything
	echo $output->getOutput(0);

	die();
}

// PHP Function used to generate an AJAX sanitized stylesheet name
function rc_ajax_admin_stylesheet_name(){
	global $wpdb;
	
	echo sanitize_title($_POST['styleName'], __('[Not generated]', 'rexCrawler'));
	
	die();
}

function rc_ajax_admin_stylesheet_get_data(){
	global $wpdb;
	global $rc_tables;
	
	// Default values
	$style['css'] = __('Error getting data. Please try again.', 'rexCrawler');
	$style['rows'] = '';
	$style['col1'] = '';
	$style['col2'] = '';
	
	// Get the ID if possible
	if(isset($_POST['styleID'])){
		// Saving the ID
		$style_id = $_POST['styleID'];
		
		// Checking if we need to fetch data about the stylesheet
		if($style_id != 0){
			$style_obj = $wpdb->get_results($wpdb->prepare("SELECT `style`, `rows`, `col1`, `col2` FROM `$rc_tables[styles]` WHERE `id` = %d", $style_id));

			if($style_obj != null){
				$style['css'] = $style_obj[0]->style;
				$style['rows'] = $style_obj[0]->rows;
				$style['col1'] = $style_obj[0]->col1;
				$style['col2'] = $style_obj[0]->col2;
			}
		}
	}
	
	// Return data
	echo json_encode($style);
	die();
}

/****************************
* Adding filters for content
****************************/
function rc_public_content($content = ''){
	// Checking if the page has been disabled!
	if(!preg_match('/\[rexCrawler\|OFF\]/i', $content)){
		$pattern = '/(\[rexCrawler[^\]]*\])/';
		preg_match_all($pattern, $content, $match);
		
		// Run through all matches
		$onlyone = false; // If we're filtering data, we only want to show 1 table
		foreach($match[0] as $table){
			if(!$onlyone){
				$output = new RC_Output();
				// Checking if we need to fetch a custom table or not
				if(strpos($table, "|", 11) === false){
					// Just show the standard table
					$content = str_replace($table, $output->getOutput(), $content);
				}else{
					// Custom table
					// Get the options
					$options = substr($table, strpos($table, "|")+1, strlen($table)-strpos($table, "|")-2);
					$output->parseOptions($options);
					$content = str_replace($table, $output->getOutput(), $content);
				}
				if(isset($_POST['rc_output_submitted'])){
					$onlyone = true;
				}
			}else{
				$content = str_replace($table, '', $content);
			}
		}
	}else{
		// Remove the [rexCrawler|OFF]-shortcode from the page
		$content = preg_replace('/\[rexCrawler\|OFF\]/i', '', $content);
	}
	
	return $content;
}

/****************************
* Adding administration page
****************************/
// Functions adds custom stylesheets/javascripts to our admin pages
function add_admin_styles(){
	wp_enqueue_style('rc_style_admin');
}

// Administration menu initialization
function rc_admin_init(){
	// Add our admin-stylesheet
	$src = plugin_dir_url( __FILE__ ).'css/admin.css';
	
	// Register stylesheet
	wp_register_style('rc_style_admin', $src);
	
	// Add our admin javascript
	wp_enqueue_script( 'rc_ajax_admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ) );
}

// Creating the administration menu
function rc_admin_menu()
{
	// Creating our admin-pages
	$main = add_menu_page(__('rexCrawler Options', 'rexCrawler'), __('rexCrawler', 'rexCrawler'), 'update_plugins', 'rc_options', 'rc_options');
	$sub = add_submenu_page('rc_options', __('Start crawler', 'rexCrawler'), __('Start crawler', 'rexCrawler'), 'update_plugins', 'rc_start_crawler', 'rc_start_crawler');
	$sub2 = add_submenu_page('rc_options', __('Data output', 'rexCrawler'), __('Data output', 'rexCrawler'), 'update_plugins', 'rc_data_output', 'rc_data_output');
	$sub3 = add_submenu_page('rc_options', __('Output options', 'rexCrawler'), __('Output options', 'rexCrawler'), 'update_plugins', 'rc_data_output_options', 'rc_data_output_options');
	$sub4 = add_submenu_page('rc_options', __('Search-pattern tester', 'rexCrawler'), __('Search-pattern tester', 'rexCrawler'), 'update_plugins', 'rc_regex_test', 'rc_regex_test');
	
	// Fetching stylesheets for our admin pages
	add_action('admin_print_styles-' . $main, 'add_admin_styles');
	add_action('admin_print_styles-' . $sub, 'add_admin_styles');
	add_action('admin_print_styles-' . $sub2, 'add_admin_styles');
	add_action('admin_print_styles-' . $sub3, 'add_admin_styles');
}

/****************************
* Helper functions
****************************/
// This function returns a select-box with all the groups
function rc_get_groups($name, $selectedID, $width = 1, $options = ""){
	global $wpdb;
	global $rc_tables;
	
	$str = "<select id='$name' name='$name'".($width == 1 ? " style='width: 100%;'" : "")." $options>";
	$groups = $wpdb->get_results("SELECT id, title FROM `".$rc_tables['groups']."` ORDER BY `title` ASC");
	
	if($groups == null){
		return __('No groups found.', 'rexCrawler');
	}
	
	foreach($groups as $group){
		$str .= "<option value='$group->id'";
		if($group->id == $selectedID){ $str.= ' selected';	}
		$str .= ">$group->title</option>";
	}
	
	$str .= "</select>";
	
	return $str;
}

// Returns two radio-buttons with Yes and No-answers
function rc_output_yesno($name, $default_value){
	return "<input type='radio' name='$name' id='".$name."_1' value='1'".($default_value == "1" ? " checked" : "")." /> <label for='".$name."_1'>Yes</label> <input type='radio' name='$name' id='".$name."_0' value='0'".($default_value == "0" ? " checked" : "")."> <label for='".$name."_0'>No</label>";
}
?>