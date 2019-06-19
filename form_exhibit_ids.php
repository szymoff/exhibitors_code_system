<?php
/*
Plugin Name: Form Exhibitors Code System
Description: Wtyczka umożliwiająca generowanie kodów zaproszeniowych dla wystawców oraz tworzenie 'reflinków'.
Version: 1.4
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
			'page-registerme.php' => 'Exhibitors Code Maker'
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

function my_cool_plugin_create_menu() {

	//create new top-level menu
	add_menu_page('Exhibitors Code System Plugin Settings', 'Exhibitors Code System Settings', 'administrator', __FILE__, 'exhibitors_options_page' , plugins_url('icon_small.png', __FILE__) );

	//call register settings function
	add_action( 'admin_init', 'register_exhibitors_system_settings' );
}


function register_exhibitors_system_settings() {
	//register our settings
	register_setting( 'exhibitors-code-system-option', 'code-prefix' );
	register_setting( 'exhibitors-code-system-option', 'code-list' );
	register_setting( 'exhibitors-code-system-option', 'form-id' );
}

function exhibitors_options_page() {
?>
	<div class="wrap">
		<h1>Exhibitors Code System - Ustawienia</h1>
		<div class="info notice-success notice">
			<p>Zanim zaczniesz ustawiać wtyczkę zastosuj się do instrukcji. Wtyczka działa jedynie z formularzami Gravity Forms.</p>
		</div>
		<div class="info notice-error notice">
			<p>Wtyczka nie działa na szablonie AVADA!</p>
		</div>
		<div id="col-container">
			<div id="col-left" class="postbox-container">
				<div class="col-wrap">
					<h2>Ustawienia</h2>
					<div class="form-wrap">
						<form method="post" action="options.php">
							<?php settings_fields( 'exhibitors-code-system-option' ); ?>
							<?php do_settings_sections( 'exhibitors-code-system-option' ); ?>
							<div class="form-field">
								<label>Prefix dla generowanych kodów (np. <code>'BIO2019'</code>)</label>
								<input type="text" name="code-prefix" value="<?php echo esc_attr( get_option('code-prefix') ); ?>" />
								<p>Prefix do generowania nowych kodów dla wystawców.<br> Działanie: KOD123__ ; gdzie '__' numer wystawcy z kolei liczony od zera.</p>
							</div>
							<div class="form-field">
								<label>Lista kodów istniejących wystawców (zarejestrowanych przed instalacją tej wtyczki)</label>
								<input type="text" name="code-list" value="<?php echo esc_attr( get_option('code-list') ); ?>" />
								<p>Odziel kody przecinkami, <strong>ostatni kod bez przecinka na końcu</strong> przykład: <code>XXX,YYY,ZZZ</code> lub zostaw puste.</p>
							</div>
							<div class="form-field">
								<label>ID formularza rejestrującego wystawcę i nadającego mu kod zaproszenia</label>
								<input type="text" name="form-id" value="<?php echo esc_attr( get_option('form-id') ); ?>" />
								<p>ID formularza Gravity Forms który generuje kod wystawcy.</p>
							</div>			
							<?php submit_button(); ?>
						</form>
					</div>
				</div>	
			</div>
			<div id="col-right">
				<div class="col-wrap"><h2>Instrukcja</h3>
					<div class="postbox-container" style="width: 100%">
						<div class="postbox">
							<div class="inside">
								<div class="main">
									<p><strong>O wtyczce:</strong><br>
									Wtyczka umożliwa <strong>automatyczne generowanie kodów zaproszeń</strong> dla wystawców, który później <strong>zostaje wryfikowany przy rejestracji</strong> osoby zaproszonej. Dla uprzednio zarejestrowanych wystawców jest możliwośc dodania własnej <strong><em>(nieograniczonej znakowo)</em> puli kodów</strong>, która również zostaje weryfikowana podczas rejestracji osoby zaproszonej.</p>
									<ol>
										<li>Dodanie do <strong>formularza wystawcy</strong> pola <code>type="text"</code> o klasie <code>code</code>,</li>
										<li>Załączenie tego pola oraz linka do odpowiedniej podstrony w mailu potwierdzającym <strong>dla wystawcy</strong>,</li>
										<li>Dodanie do <strong>formularza rejestracji dla użytkowników</strong> pola <code>type="text"</code> o klasie <code>invitation_code</code>,</li>
										<li>Załączenie tego pola w mailu potwierdzającym <strong>dla nas</strong>,</li>
										<li>Ustawienie <strong>szablonu podstrony z rejestracją wystawców</strong> na <code>Exhibitors Code Maker</code></li>
										<li>Ustawienie <strong>szablonu podstrony z rejestracją odwiedzających</strong> na <code>Exhibitors Code Checker</code></li>
										<li>Uzupełnienie ustawień wtyczki odpowiednimi danymi.</li>
									</ol>
									<div style="text-align:right">
										<img src="<?php echo plugins_url('icon.png', __FILE__)?>" alt="logo" >
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php } ?>