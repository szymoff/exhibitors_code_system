<?php
/*
Plugin Name: Form Exhibitors Code System
Description: Wtyczka umożliwiająca generowanie kodów zaproszeniowych dla wystawców oraz tworzenie 'reflinków'.
Version: 1.9
Author: Szymon Kaluga
Author URI: http://skaluga.pl/
*/

include( plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php');

$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/szymoff/exhibitors_code_system',
	__FILE__,
	'exhibitors-code-system'
);

$myUpdateChecker->getVcsApi()->enableReleaseAssets();


class PageTemplater {

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;

	/**
	 * Returns an instance of this class.
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new PageTemplater();
		}

		return self::$instance;

	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {

		$this->templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

			// 4.6 and older
			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_project_templates' )
			);

		} else {

			// Add a filter to the wp 4.7 version attributes metabox
			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);

		}

		// Add a filter to the save post to inject out template into the page cache
		add_filter(
			'wp_insert_post_data',
			array( $this, 'register_project_templates' )
		);


		// Add a filter to the template include to determine if the page has our
		// template assigned and return it's path
		add_filter(
			'template_include',
			array( $this, 'view_project_template')
		);


		// Add your templates to this array.
		$this->templates = array(
			'page-checkform.php' => 'Exhibitors Code Checker',
			'page-registerme.php' => 'Exhibitors Code Maker',
			'page-checkformexhibitor.php' => 'Check Exhibitor'
		);

	}

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	}

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {
		// Return the search template if we're searching (instead of the template for the first result)
		if ( is_search() ) {
			return $template;
		}

		// Get global post
		global $post;

		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}

		// Return default template if we don't have a custom one defined
		if ( ! isset( $this->templates[get_post_meta(
			$post->ID, '_wp_page_template', true
		)] ) ) {
			return $template;
		}

		// Allows filtering of file path
		$filepath = apply_filters( 'page_templater_plugin_dir_path', plugin_dir_path( __FILE__ ) );

		$file =  $filepath . get_post_meta(
			$post->ID, '_wp_page_template', true
		);

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		// Return template
		return $template;

	}

}
add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );

// create custom plugin settings menu
add_action('admin_menu', 'my_cool_plugin_create_menu');


    function add_new_menu_items()
    {
        add_menu_page(
            "Exhibitors Code System Settings",
            "Exhibitors Code System Settings",
            "manage_options",
            "code-maker",
            "theme_options_page",
            plugins_url('icon_small.png', __FILE__) ,
            100
        );

    }

    function theme_options_page()
    {
        ?>
            <div class="wrap" style="margin-top:40px">
            <div id="icon-options-general" class="icon32"></div>
           
            <!-- run the settings_errors() function here. -->
            <?php settings_errors(); ?>
				<div id="col-left" class="postbox-container">
					<div class="col-wrap">
						<div class="postbox">
							<div class="inside">
								<div class="main">
									<p><center><strong>O Exhibitors Code System:</strong></center><hr>
									Wtyczka umożliwa <strong>automatyczne generowanie kodów zaproszeń</strong> dla wystawców, który później <strong>zostaje wryfikowany przy rejestracji</strong> osoby zaproszonej. Dla uprzednio zarejestrowanych wystawców jest możliwośc dodania własnej <strong><em>(nieograniczonej znakowo)</em> puli kodów</strong>, która również zostaje weryfikowana podczas rejestracji osoby zaproszonej.</p>
								</div>
							</div>
						</div>
						<div class="postbox">
							<div class="inside">
								<div class="main">
								<p><center><strong>Instrukcja:</strong></center><hr></p>
									<ol>
										<li>Dodanie do <strong>formularza wystawcy</strong> pola <code>type="text"</code> o klasie <code>code</code>,</li>
										<li>Załączenie tego pola oraz linka do odpowiedniej podstrony w mailu potwierdzającym <strong>dla wystawcy</strong>,</li>
										<li>Dodanie do <strong>formularza rejestracji dla użytkowników</strong> pola <code>type="text"</code> o klasie <code>invitation_code</code>,</li>
										<li>Załączenie tego pola w mailu potwierdzającym <strong>dla nas</strong>,</li>
										<li>Ustawienie <strong>szablonu podstrony z rejestracją wystawców</strong> na <code>Exhibitors Code Maker</code></li>
										<li>Ustawienie <strong>szablonu podstrony z rejestracją odwiedzających</strong> na <code>Exhibitors Code Checker</code></li>
										<li>Uzupełnienie ustawień wtyczki odpowiednimi danymi.</li>
									</ol>
								</div>
							</div>
						</div>
						<div class="postbox">
							<div class="inside">
								<div class="main">
								<p><center><strong>Pomoc:</strong></center><hr></p>
									<center>Potrzebujesz pomocy, nowej funkcjonalności, zauważyłeś błąd? Napisz:<br>
									<strong>Autor wtyczki:</strong> Szymon Kaluga<br>
									<em>s.kaluga@warsawexpo.eu</em></center>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="col-right" class="postbox-container">
					<div class="col-wrap">
						<div class="form-wrap">
							<form method="POST" action="options.php" enctype="multipart/form-data">
								<div class="postbox">
									<div class="inside">
										<div class="main">
											<?php
											settings_fields("code_checker");
											do_settings_sections("code-checker");
											submit_button();
											?>    
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
        	</div>
        <?php
    }

    add_action("admin_menu", "add_new_menu_items");

    function display_options()
    {

		add_settings_section("code_checker", "Code System Checker", "display_header_options_content", "code-checker");
		
		add_settings_field("code_prefix", "Code Prefix", "display_code_prefix", "code-checker", "code_checker");
		register_setting("code_checker", "code_prefix");
		
		add_settings_field("form_id", "Form ID", "display_form_id", "code-checker", "code_checker");
		register_setting("code_checker", "form_id");

		add_settings_field("code_list", "List of Exhibitors Codes<hr><p>Lista kodów Wystawców</p>", "display_code_list", "code-checker", "code_checker");      
		register_setting("code_checker", "code_list");
		
		add_settings_field("csv_file", "Exhibitors code list in .csv<hr><p>Plik .csv w którym znajdują się kody wystawców.</p>", "display_csv_file_upload", "code-checker", "code_checker");
		register_setting("code_checker", "csv_file", "csv_file_upload");
		
		add_settings_field("h1_heading", "Form Heading<hr><p>Napis, który pojawia się na samej górze nad polem do wpisania kodu.</p>", "display_h1_heading", "code-checker", "code_checker");      
		register_setting("code_checker", "h1_heading");

		add_settings_field("h3_heading", "Form Subheading<hr><p>Napis, który pojawia się tuż nad polem do wpisania kodu.</p>", "display_h3_heading", "code-checker", "code_checker");      
		register_setting("code_checker", "h3_heading");

		add_settings_field("h2_heading", "Error Text<hr><p>Napis błędu, który pojawia się po złym wpisaniu kodu.</p>", "display_h2_heading", "code-checker", "code_checker");      
		register_setting("code_checker", "h2_heading");

		add_settings_field("button_text", "Button save text<hr><p>Napis, który pojawia się na przycisku.</p>", "display_button_text", "code-checker", "code_checker");      
		register_setting("code_checker", "button_text");
       
    }

    function csv_file_upload($options)
    {
        if(!empty($_FILES["csv_file"]["tmp_name"]))
        {
            $urls = wp_handle_upload($_FILES["csv_file"], array('test_form' => FALSE));
            $temp = $urls["url"];
            return $temp;  
        }

        return get_option("csv_file");
    }


    function display_header_options_content(){echo "";}
    function display_csv_file_upload()
    {
        ?>
			<div class="form-field">
				<input type="file" name="csv_file" id="csv_file"  value="<?php echo get_option('csv_file'); ?>" />
				<p>Aktualny plik csv: <code><?php echo get_option("csv_file"); ?></code></p>
			</div>
        <?php
    }
    function display_code_prefix()
    {
        ?>
			<div class="form-field">
				<input type="text" name="code_prefix" id="code_prefix" value="<?php echo get_option('code_prefix'); ?>" />
				<p>Prefix do generowania <strong>nowych kodów</strong> dla wystawców.<br>Działanie: <code>PREFIX__</code> gdzie <code>'__'</code> numer wystawcy z kolei liczony od zera.</p>
			</div>
            
        <?php
	}
	function display_form_id()
    {
        ?>	
			<div class="form-field">
				<input type="text" name="form_id" id="form_id" value="<?php echo get_option('form_id'); ?>" />
				<p>ID formularza Gravity Forms który generuje kod wystawcy.</p>
			</div>
        <?php
    }
    function display_code_list()
    {
        ?>
		<div class="form-field">
			<input type="text" name="code_list" id="code_list" value="<?php echo get_option('code_list'); ?>" />
			<p>Odziel kody przecinkami, ostatni kod bez przecinka na końcu przykład: XXX,YYY,ZZZ lub zostaw puste.</p>
		</div>
        <?php
	}
	
	function display_h1_heading()
    {
        ?>
		<div class="form-field">
			<input type="text" name="h1_heading" id="h1_heading" value="<?php echo get_option('h1_heading'); ?>" />
			<p>"Wpisz kod zaproszenia, który otrzymałeś od wystawcy"</p>
		</div>
        <?php
	}

	function display_h2_heading()
    {
        ?>
			<div class="form-field">
				<input type="text" name="h2_heading" id="h2_heading" value="<?php echo get_option('h2_heading'); ?>" />
				<p>"Błędny kod, spróbuj ponownie"</p>
			</div>
        <?php
	}
	
	function display_h3_heading()
    {
        ?>
			<div class="form-field">
            	<input type="text" name="h3_heading" id="h3_heading" value="<?php echo get_option('h3_heading'); ?>" />
				<p>"Zostaniesz przekierowany wtedy do formularza rejestracji"</p>
			</div>	
        <?php
	}
	
	function display_button_text()
    {
        ?>
			<div class="form-field">
				<input type="text" name="button_text" id="button_text" value="<?php echo get_option('button_text'); ?>" />
				<p>"WYŚLIJ"</p>
			</div>
        <?php
	}

    add_action("admin_init", "display_options");