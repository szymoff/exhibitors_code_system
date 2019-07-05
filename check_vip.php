<?php
	// Include Shortcodes
	include( plugin_dir_path( __FILE__ ) . 'shortcodes.php');
	// Exhibitors Codes List
	$vipCodesManual = explode(', ', get_option('vip_code_list'));
	// Code Usage Limit
	$user_limit = get_option('vip_users');
	// Make CSV file Array great again 
	$vip_csv_array = array();
	if (($handle = fopen(get_option("vip_csv_file"), "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 2048, ",")) !== FALSE) {
			$num = count($data);
			for ($c=0; $c < $num; $c++) {
				array_push($vip_csv_array, $data[$c]);
			}
		}
	}    
	// Connect with Database
	$passcode = $_GET['input_value'];
	$check_code_query = "SELECT * FROM `wp_gf_entry_meta` WHERE `meta_value` = '$passcode'";
	require_once(ABSPATH . 'wp-config.php');
	$connect = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	
	if (empty($_GET["input_value"])){
		// If Clean $_GET request, do form shortcode
		get_header();	
		echo do_shortcode('[form_check]');
		get_footer();
	}else{
		if(!in_array($passcode, $vipCodesManual) && !in_array($passcode, $vip_csv_array)){
			// If $_GET code is not in arrays
			get_header();
			// Show Check Form
			echo do_shortcode('[form_check]');
			// Show Error Notification
			echo do_shortcode('[error_code_not_in]');
			get_footer();
		}elseif(mysqli_num_rows(mysqli_query($connect, $check_code_query)) >= $user_limit){
			// If $_GET code was used too many times
			get_header();
			// Show Check Form
			echo do_shortcode('[form_check]');
			// Show Error Notification
			echo do_shortcode('[error_code_multiple]');
			get_footer();
		}else{
			// FILTER CONTENT
			add_filter( 'the_content', 'add_additional_php_code'); 
			// Show Page Content in current 'page.php' template
			echo do_shortcode("[phpinclude file='page']");	
		}
	}
?>