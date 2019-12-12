<?php
/**
 * WordPress Beta Tester
 *
 * @package WordPress_Beta_Tester
 * @author Andy Fragen, original author Peter Westwood.
 * @license GPLv2+
 * @copyright 2009-2016 Peter Westwood (email : peter.westwood@ftwr.co.uk)
 */

/**
 * WPBT_Bootstrap
 */
class WPBT_Bootstrap {

	/**
	 * Holds main plugin file.
	 *
	 * @var $file
	 */
	protected $file;

	/**
	 * Holds main plugin directory.
	 *
	 * @var $dir
	 */
	protected $dir;

	/**
	 * Holds plugin options.
	 *
	 * @var $options
	 */
	protected static $options;

	/**
	 * Constructor.
	 *
	 * @param string $file Main plugin file.
	 * @return void
	 */
	public function __construct( $file ) {
		$this->file    = $file;
		$this->dir     = dirname( $file );
		self::$options = get_site_option( 'wp_beta_tester', array( 'stream' => 'point' ) );
	}

	/**
	 * Let's get started.
	 *
	 * @return void
	 */
	public function run() {
		// require_once $this->dir . '/vendor/autoload.php';
		$this->load_requires(); // TODO: replace with composer's autoload.
		$this->load_hooks();
		// TODO: I really want to do this, but have to wait for PHP 5.4.
		// ( new WP_Beta_Tester( $this->file ) )->run( $this->options );
		$wpbt = new WP_Beta_Tester( $this->file );
		$wpbt->run( self::$options );
	}

	/**
	 * Load hooks.
	 *
	 * @return void
	 */
	public function load_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		register_activation_hook( $this->file, array( $this, 'activate' ) );
		register_deactivation_hook( $this->file, array( $this, 'deactivate' ) );
	}

	/**
	 * Load textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wordpress-beta-tester' );
	}

	/**
	 * Run on plugin activation.
	 *
	 * Delete 'update_core' transient and add any saved extra settings to wp-config.php.
	 *
	 * @return void
	 */
	public function activate() {
		delete_site_transient( 'update_core' );
		$wpbt        = new WP_Beta_Tester( $this->file );
		$wpbt_extras = new WPBT_Extras( $wpbt, self::$options );
		$wpbt_extras->activate();
	}

	/**
	 * Run on plugin deactivation.
	 *
	 * Delete 'update_core' transient and remove any extras settings from wp-config.php.
	 *
	 * @return void
	 */
	public function deactivate() {
		delete_site_transient( 'update_core' );
		$wpbt        = new WP_Beta_Tester( $this->file );
		$wpbt_extras = new WPBT_Extras( $wpbt, self::$options );
		$wpbt_extras->deactivate();
	}

	/**
	 * <sarcasm>Poor man's autoloader.</sarcasm>
	 * // TODO: replace with composer's autoload.
	 *
	 * @return void
	 */
	public function load_requires() {
		require_once $this->dir . '/src/WP_Beta_Tester.php';
		require_once $this->dir . '/src/WPBT_Settings.php';
		require_once $this->dir . '/src/WPBT_Core.php';
		require_once $this->dir . '/src/WPBT_Extras.php';
		require_once $this->dir . '/vendor/WPConfigTransformer.php';
	}
}
