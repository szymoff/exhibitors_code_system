<?php
	// Exhibitors Codes List
	$existExhibitors = explode(', ', get_option('code_list'));
	// Make CSV file Array
	$csv_array = array();
	if (($handle = fopen(get_option("csv_file"), "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 2048, ",")) !== FALSE) {
			$num = count($data);
			for ($c=0; $c < $num; $c++) {
				array_push($csv_array, $data[$c]);
			}
		}
	}    
	// JavaScript Shortcode
	function styles_shortcode() {
		ob_start();
		if(!empty($_GET["input_value"])){ ?>
			<script type="text/javascript">
				document.querySelector("li.invitation_code div.ginput_container input").value = '<?= $_GET["input_value"] ?>';
				document.querySelector("li.invitation_code").style.display = 'none';
			</script>
		<?php }
		return ob_get_clean();
	}
	add_shortcode( 'styles', 'styles_shortcode' );
	// Check Form Shortcode
	function form_shortcode() {
		ob_start(); 
		get_header(); ?>
		<style>
			#check_user {
				height: 164px;
				width: 300px;
				position: absolute;
				left: calc(50% - 150px);
				border-radius: 10px;
				border-color: transparent;
				margin: 30px auto;
				text-align:center;
			}
			#check_user > input{
				margin-top: 22%;
				width: 95%;
				margin-left: auto;
				margin-right: auto;
				margin-bottom: 20px;
				-webkit-box-shadow: 5px 5px 10px 0px rgba(221,221,221,1);
				-moz-box-shadow: 5px 5px 10px 0px rgba(221,221,221,1);
				box-shadow: 5px 5px 10px 0px rgba(221,221,221,1);
				border: solid 1px #ccc;
			}
		</style>
			<div class="container relative text-center form-check-container">
			<h1><?php echo get_option('h1_heading'); ?></h1>
			<h3><?php echo get_option('h3_heading'); ?></h3>
				<form id="check_user" method="GET" action="">
					<input type="text" id="input_value" name="input_value" />
					<button type="submit" class="btn btn-success" name="save" id="save"><?php echo get_option('button_text'); ?></button>
				</form>
			</div>
		<?php 
		if ((!empty($_GET["input_value"]) && !mysqli_num_rows(mysqli_query($connect, $check_code_query)) > 0) || (!empty($_GET["input_value"]) && !in_array($passcode, $existExhibitors)) || (!empty($_GET["input_value"]) && !in_array($passcode, $csv_array))){
			echo "<h2 class='text-center text-danger mb-5'>".get_option('h2_heading')."</h2>";
		}
		get_footer();
		return ob_get_clean();
	}
	add_shortcode( 'form_check', 'form_shortcode' );

	// include PHP file
	function PHP_Include($params = array()) {
		extract(shortcode_atts(array(
			'file' => 'default'
		), $params));
		ob_start();
		include(get_theme_root() . '/' . get_template() . "/$file.php");
		return ob_get_clean();
	}
	// register shortcode
	add_shortcode('phpinclude', 'PHP_Include');

	// If Clean $_GET request, do form shortcode
	if (empty($_GET["input_value"])){
	echo do_shortcode('[form_check]');
	}
	// If not clean $_GET request decide what to do
	if (!empty($_GET["input_value"])){
		$passcode = $_GET['input_value'];
		$check_code_query = "SELECT * FROM `wp_gf_entry_meta` WHERE `meta_value` = '$passcode'";
		require_once(ABSPATH . 'wp-config.php');
		$connect = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		// Check connect
		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		if (mysqli_num_rows(mysqli_query($connect, $check_code_query)) > 0 || in_array($passcode, $existExhibitors) || in_array($passcode, $csv_array)) {

		// FILTER CONTENT
		function add_additional_php_code($content) {
			$original = $content;
			$content .= $original;
			$content .= do_shortcode('[styles]');
			return $content;
			}
		add_filter( 'the_content', 'add_additional_php_code'); 

		// ECHO PAGE SHORTCODE 
		echo do_shortcode("[phpinclude file='page']");	
		}else{
			echo do_shortcode('[form_check]');
		}
	}
?>