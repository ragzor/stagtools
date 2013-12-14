<?php
/**
 * Plugin Name: StagTools
 * Plugin URI: http://wordpress.org/plugins/stagtools/
 * Description: A poweful plugin to extend functionality to your WordPress themes offering shortcodes, font icons and useful widgets.
 * Version: 1.1.1
 * Author: Ram Ratan Maurya
 * Author URI: http://mauryaratan.me
 * License: GPL2
 * Requires at least: 3.5
 * Tested up to: 3.8
 * 
 * Text Domain: stag
 * Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'StagTools' ) ) {

/**
 * Main StagTools Class
 *
 * @package StagTools
 * @version 1.1
 * @author Ram Ratan Maurya (Codestag)
 * @link http://mauryaratan.me
 * @link http://codestag.com
 */

class StagTools {

	/**
	* @var string
	*/
	public $version = '1.1.1';
	
	/**
	* @var string
	*/
	public $plugin_url;
	
	/**
	* @var string
	*/
	public $plugin_path;
	
	/**
	* @var string
	*/
	public $template_url;

	/**
	 * StagTools Constructor.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Define version constant
		define( 'STAGTOOLS_VERSION', $this->version );

		// Hooks
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'admin_menu', array( &$this, 'stag_add_options_page' ) );
		add_action( 'admin_head', array( &$this, 'widget_styles' ) );

		// Include required files
		$this->includes();

	}

	/**
	 * action_links function.
	 *
	 * Adds custom links on plugins page.
	 *
	 * @access public
	 * @param mixed $links
	 * @return void
	 */
	public function action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'options-general.php?page=stagtools' ) . '">' . __( 'Settings', 'stag' ) . '</a>'
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Initiate all the stuff.
	 * 
	 * @return void
	 */
	function init() {
		$this->stag_load_textdomain();

		add_action( 'wp_enqueue_scripts', array( &$this, 'frontend_style' ), 0 );
		add_filter( 'body_class', array( &$this, 'body_class' ) );

		add_filter( 'contextual_help', array( &$this, 'contextual_help' ), 10, 3 );

		/**
		 * @deprecated 1.1.2
		 */
		if( current_theme_supports( 'stag-portfolio' ) ) 	include_once( 'post-type/portfolio.php' );
		if( current_theme_supports( 'stag-slides' ) ) 		include_once( 'post-type/slides.php' );
		if( current_theme_supports( 'stag-team' ) ) 		include_once( 'post-type/team.php' );
		if( current_theme_supports( 'stag-testimonials' ) ) include_once( 'post-type/testimonials.php' );
		
		/** @since 1.1.2 */
		if ( current_theme_supports( 'post-type' ) ) {
			$theme_supports = get_theme_support( 'post-type' )[0];

			foreach ( $theme_supports as $support ) {
				include_once( "post-type/{$support}.php" );
			}
		}

	}

	/**
	 * Add StagTools admin options.
	 *
	 * @global string $stag_options One true options page
	 * @return void
	 */
	function stag_add_options_page() {
		add_options_page( __( 'StagTools Options', 'stag' ), __( 'StagTools', 'stag' ), 'manage_options', 'stagtools', 'stagtools_options_page' );
	}

	/**
	 * Setup localisation.
	 * 
	 * @return void
	 */
	function stag_load_textdomain() {
		load_plugin_textdomain( 'stag', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Include admin and frontend files.
	 *
	 * @uses StagTools::admin_includes() Includes admin files
	 * @uses StagTools::frontend_includes() Includes frontend files
	 * @return void
	 */
	public function includes() {
		global $stag_options;

		require_once('settings/settings.php');
		$stag_options = stagtools_get_settings();

		if ( is_admin() ){
			$this->admin_includes();
		}
		if( !is_admin() ){
			$this->frontend_includes();
		}		

		// Widgets
		include_once( 'widgets/widget-dribbble.php' );
		include_once( 'widgets/widget-flickr.php' );
		include_once( 'widgets/widget-instagram.php' );
		include_once( 'widgets/widget-twitter.php' );
	}

	/**
	* Include admin files.
	*
	* @return void
	*/
	public function admin_includes(){
		include_once( 'shortcodes/stag-shortcodes.php' );
		include_once( 'settings/settings.php' );
	}

	/**
	 * Include frontend files.
	 * 
	 * @return void
	 */
	public function frontend_includes(){
		include_once( plugin_dir_path( __FILE__ ) .'shortcodes/shortcodes.php' );
	}

	/**
	 * Add frontend scripts and styles.
	 * 
	 * @return void
	 */
	public function frontend_style() {
		wp_register_style( 'stag-shortcode-styles', plugin_dir_url( __FILE__ )  . 'assets/css/stag-shortcodes.css' , '', $this->version, 'all' );
		wp_register_style( 'font-awesome', plugin_dir_url( __FILE__ )  . 'assets/css/font-awesome.css' , '', '4.0.3', 'all' );

		wp_register_script( 'stag-shortcode-scripts', plugin_dir_url( __FILE__ ) . 'assets/js/stag-shortcode-scripts.js', array( 'jquery', 'jquery-ui-accordion', 'jquery-ui-tabs' ), $this->version, true );

		wp_enqueue_style( 'stag-shortcode-styles' );
		wp_enqueue_style( 'font-awesome' );
		
		wp_enqueue_script( 'stag-shortcode-scripts' );
	}

	/**
	 * Plugin path.
	 * 
	 * @return string Plugin path
	 */
	public function plugin_path() {
		if ( $this->plugin_path ) return $this->plugin_path;

		return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Plugin url.
	 * 
	 * @return string Plugin url
	 */
	public function plugin_url() {
		if ( $this->plugin_url ) return $this->plugin_url;
		return $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Add stagtools to body class for use on frontend to check if plugin is active.
	 * 
	 * @since 1.0.0
	 * @return array $classes List of classes
	 */
	public function body_class( $classes ) {
		$classes[] = 'stagtools';
		return $classes;
	}

	/**
	* Widget styles.
	* 
	* @access public 
	* @return void 
	*/
	public function widget_styles() {
		global $pagenow;
		if( $pagenow != 'widgets.php' ) return;
		?>
		<style type="text/css">
		div[id*="_stag"] .widget-top{
		  background: #C8E5F3 !important;
		  border-color: #B4D0DD !important;
		  box-shadow: inset 0 1px 0 white !important;
		  -webkit-box-shadow: inset 0 1px 0 white !important;
		  -moz-box-shadow: inset 0 1px 0 white !important;
		  -ms-box-shadow: inset 0 1px 0 white !important;
		  -o-box-shadow: inset 0 1px 0 white !important;
		  background: -moz-linear-gradient(top,  #EAF8FF 0%, #C8E5F3 100%) !important;
		  background: -webkit-linear-gradient(top, #EAF8FF 0%,#C8E5F3 100%) !important;
		  background: linear-gradient(to bottom, #EAF8FF 0%,#C8E5F3 100%) !important;
		  border-bottom: 1px solid #98B3C0 !important;
		  margin-top: 0px;
		}
		</style>
		<?php
	}

	/**
	 * Check if the plugin Stag Custom Sidebars is active.
	 *
	 * @since 1.1
	 * @link http://wordpress.org/plugins/stag-custom-sidebars
	 * @return boolean
	 */
	public function is_scs_active(){
		include_once(ABSPATH .'wp-admin/includes/plugin.php');
		if( is_plugin_active('stag-custom-sidebars/stag-custom-sidebars.php') ) return true;
		return false;
	}

	/**
	 * Add help screen for StagTools settings page.
	 * 
	 * @param  string $contextual_help
	 * @param  string $screen_id       String of the settings page
	 * @param  object $screen          Current screen object containing all details
	 * @since  1.1
	 * @return object Help object
	 */
	function contextual_help( $contextual_help, $screen_id, $screen ) {
		if ( "settings_page_stagtools" != $screen_id )
			return;

		$screen->set_help_sidebar(
			'<p><strong>' . sprintf( __( 'For more information:', 'stag' ) . '</strong></p>' .
			'<p>' . sprintf( __( 'Visit the <a href="%s" target="_blank">documentation</a> on the Github.', 'stag' ), esc_url( 'https://github.com/mauryaratan/stagtools/wiki' ) ) ) . '</p>' .
			'<p>' . sprintf(
						__( '<a href="%s" target="_blank">Post an issue</a> on <a href="%s" target="_blank">GitHub</a>.', 'stag' ),
						esc_url( 'https://github.com/mauryaratan/stagtools/issues' ),
						esc_url( 'https://github.com/mauryaratan/stagtools' )
					) . '</p>'
		);

		$screen->add_help_tab( array(
			'id'	    => 'stagtools-help-oauth',
			'title'	    => __( 'Twitter oAuth Settings', 'stag' ),
			'content'	=>  '<p>' . __( 'Here you can find how to add twitter oAuth keys to get Twitter widget working.', 'stag' ) . '</p>'.
							'<h5>' . __( 'Where do I find these keys?', 'stag' ) . '</h5>'.
							'<p>' . sprintf( __( 'In order to use the new Twitter widget, you must first register a Twitter app, which will provide you with the keys you see above. Start by <a href="%s" target="_blank">signing-in</a> to the Twitter developer dashboard.', 'stag' ), esc_url( 'http://dev.twitter.com/apps' ) ) . '</p>'.
							'<h5>' . __( 'Where are my widgets?', 'stag' ) . '</h5>'.
							'<p>' . sprintf( __( 'In order to use the new Twitter widget, you must first register a Twitter app, which will provide you with the keys you see above. Start by <a href="%s" target="_blank">creating a new application</a> to the Twitter developer dashboard.', 'stag' ), esc_url( 'http://cl.ly/image/1H1U1i1T3u0h' ) ) . '</p>'.
							'<h5>' . __( 'Can I insert shortcodes manually instead of using shortcode generator?', 'stag' ) . '</h5>'.
							'<p>' . sprintf( __( 'Yes; although we have a shortcode builder you can also see a list of <a href="%s" target="_blank">all available shortcodes</a> and use it manually in any supported area.', 'stag' ), esc_url( 'http://gist.github.com/mauryaratan/6071262' ) ) . '</p>'
		) );

		if ( current_theme_supports( 'post-type',  array( 'portfolio' ) ) ) :
		$screen->add_help_tab( array(
			'id'	    => 'stagtools-help-portfolio',
			'title'	    => __( 'Portfolio Settings', 'stag' ),
			'content'	=>  '<p>'. __( 'You can use the following settigns to control the slug/taxonomies for custom post type portfolio and skills.', 'stag' ) .'</p>'.
							'<p>'. __( '<strong>Portfolio Slug</strong> - This settings is used to set the slug of custom post type &lsquo;portfolio&rsquo;.', 'stag' ) .'</p>'.
							'<p>'. __( '<strong>Skills Slug</strong> - This settings is used to set the slug of custom post taxonomy &lsquo;skill&rsquo;.', 'stag' ) .'</p>'
		) );
		endif;

		return $contextual_help;
	}

}

$GLOBALS['stagtools'] = new StagTools();

}


/**
 * Flush the rewrite rules on activation
 */
function stagtools_activation() {
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'stagtools_activation' );

/**
 * Also flush the rewrite rules on deactivation
 */
function stagtools_deactivation() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'stagtools_activation' );
