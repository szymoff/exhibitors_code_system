<?php
	function javascript_shortcode() {
		ob_start();
		// $form_id = mysqli_real_escape_string ($form_id);
		$form_id = get_option('form-id'); //FORM PREFIX
		$code_prefix = get_option('code-prefix'); // PASSWORD PREFIX
		$query = "SELECT * FROM `wp_gf_entry` WHERE `form_id` = '$form_id'";
		require_once(ABSPATH . 'wp-config.php');
		$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		// Check connection
		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		if ($result = mysqli_query($connection, $query)) {
			$user_number = mysqli_num_rows($result) + 1;
			mysqli_free_result($result);
		}else{
			echo 'nie robi sie!';
		} ?>
			  	<script type="text/javascript">
					document.querySelector("li.code div.ginput_container input").value = '<?= $code_prefix.$user_number; ?>';
					document.querySelector("li.code").style.display = 'none';
				</script>
		<?php 
		return ob_get_clean();
	}
	add_shortcode( 'javascript', 'javascript_shortcode' );

	// FILTER CONTENT
	function add_additional_php_code($content) {
		$original = $content;
		$content .= $original;
		$content .= do_shortcode('[javascript]');
		return $content;
		}
	add_filter( 'the_content', 'add_additional_php_code'); 
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
	// Include page.php file
	echo do_shortcode("[phpinclude file='page']");
    ?>