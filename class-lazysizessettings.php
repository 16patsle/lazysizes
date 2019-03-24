<?php
/**
 * The plugin settings file
 *
 * @package Lazysizes
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * The plugin settings class
 */
class LazysizesSettings {

	/**
	 * Plugin version.
	 */
	const VER = '0.5.1';
	/**
	 * The default plugin settings values
	 *
	 * @var array[]
	 */
	protected $defaults = array(
		'general' => array(
			'lazysizes_minimize_scripts' => 1,
			'lazysizes_thumbnails'       => 1,
			'lazysizes_textwidgets'      => 1,
			'lazysizes_avatars'          => 1,
			'lazysizes_load_extras'      => 1,
			'lazysizes_excludeclasses'   => '',
			'lazysizes_img'              => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
		),
	);

	/**
	 * Set up actions needed for the plugin's admin interface
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'lazysizes_add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'lazysizes_settings_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'lazysizes_enqueue_admin' ) );
		add_action( 'upgrader_process_complete', array( $this, 'update' ) );
	}

	/**
	 * Runs on first activation, sets default settings
	 *
	 * @since 0.1.0
	 */
	public function first_time_activation() {
		$defaults = $this->defaults;
		foreach ( $defaults as $key => $val ) {
			if ( get_option( 'lazysizes_' . $key, false ) === false ) {
				update_option( 'lazysizes_' . $key, $val );
			}
		}
		update_option( 'lazysizes_version', self::VER );
	}

	/**
	 * Runs after an update to the plugin. Updates plugin settings if needed.
	 *
	 * @since 0.1.0
	 */
	public function update() {
		$defaults = $this->defaults;
		$ver      = self::VER;
		$dbver    = get_option( 'lazysizes_version', '' );
		if ( version_compare( $ver, $dbver, '>' ) ) {
			update_option( 'lazysizes_version', $ver );
		}
	}

	/**
	 * Adds an entry to the sidebar admin menu in the backend
	 *
	 * @since 0.1.0
	 */
	public function lazysizes_add_admin_menu() {
		$admin_page = add_options_page( 'Lazysizes', 'Lazysizes', 'manage_options', 'lazysizes', array( $this, 'settings_page' ) );
	}

	/**
	 * Load all the lazysizes scripts for the backend
	 *
	 * @since 0.1.0
	 */
	public function lazysizes_enqueue_admin() {
		$screen = get_current_screen();
		if ( 'settings_page_lazysizes' === $screen->base ) {
			wp_enqueue_style( 'thickbox-css' );
			// add_action( 'admin_notices', array($this,'ask_for_feedback') );//.
		}
	}

	/**
	 * Ask users for feedback about the plugin. Not currently used.
	 *
	 * @since 0.1.0
	 */
	public function ask_for_feedback() {
		?>
		<div class="updated">
			<p>
				<?php
				printf(
					/* translators: 1: <a> (opening tag), 2: </a> (closing tag). */
					esc_html__( 'Help improve lazysizes: %1$ssubmit feedback, questions, and bug reports%2$s.', 'lazysizes' ),
					'<a href="https://wordpress.org/support/plugin/lazysizes" target="_blank">',
					'</a>'
				);
				?>
			</p>
		</div>
		<?php
		wp_enqueue_script( 'thickbox' );
	}

	/**
	 * Generate link to the settings page
	 *
	 * @since 0.1.0
	 * @param array $links The links.
	 * @return string[]
	 */
	public function lazysizes_action_links( $links ) {
		$links[] = '<a href="options-general.php?page=lazysizes">' . esc_html__( 'Settings', 'lazysizes' ) . '</a>';
		return $links;
	}

	/**
	 * Registers the settings with WordPress
	 *
	 * @since 0.1.0
	 */
	public function lazysizes_settings_init() {
		register_setting( 'basicSettings', 'lazysizes_general' );
		register_setting( 'basicSettings', 'lazysizes_effects' );
		register_setting( 'basicSettings', 'lazysizes_addons' );

		add_settings_section(
			'lazysizes_basic_section',
			__( 'General Settings', 'lazysizes' ),
			array( $this, 'lazysizes_basic_section_callback' ),
			'basicSettings'
		);

		add_settings_field(
			'lazysizes_general',
			__( 'Basics', 'lazysizes' ),
			array( $this, 'lazysizes_general_render' ),
			'basicSettings',
			'lazysizes_basic_section'
		);

		add_settings_field(
			'lazysizes_effects',
			__( 'Effects', 'lazysizes' ),
			array( $this, 'lazysizes_effects_render' ),
			'basicSettings',
			'lazysizes_basic_section'
		);

		add_settings_field(
			'lazysizes_addons',
			__( 'Addons', 'lazysizes' ),
			array( $this, 'lazysizes_addons_render' ),
			'basicSettings',
			'lazysizes_basic_section'
		);
	}


	/**
	 * Output HTML for General Settings.
	 *
	 * @since 0.1.0
	 */
	public function lazysizes_general_render() {
		$options = get_option( 'lazysizes_general' );
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span>
					<?php esc_html_e( 'Basic settings', 'lazysizes' ); ?>
				</span>
			</legend>
			<label for="lazysizes_minimize_scripts">
				<input type='checkbox' id='lazysizes_minimize_scripts' name='lazysizes_general[lazysizes_minimize_scripts]' <?php $this->checked_r( $options, 'lazysizes_minimize_scripts', 1 ); ?> value="1">
				<?php esc_html_e( 'Load minimized versions of javascript and css files.', 'lazysizes' ); ?>
			</label>
			<br />
			<label for="lazysizes_footer">
				<input type='checkbox' id='lazysizes_footer' name='lazysizes_general[lazysizes_footer]' <?php $this->checked_r( $options, 'lazysizes_footer', 1 ); ?> value="1">
				<?php esc_html_e( 'Load scripts in the footer.', 'lazysizes' ); ?>
			</label>
		</fieldset>
		<fieldset>
			<legend class="screen-reader-text">
				<span>
					<?php esc_html_e( 'Lazy Load settings', 'lazysizes' ); ?>
				</span>
			</legend>
			<br />
			<label for="lazysizes_load_extras">
				<input type='checkbox' id='lazysizes_load_extras' name='lazysizes_general[lazysizes_load_extras]' <?php $this->checked_r( $options, 'lazysizes_load_extras', 1 ); ?> value="1">
				<?php esc_html_e( 'Lazy load YouTube and Vimeo videos, iframes, audio, etc.', 'lazysizes' ); ?>
			</label>
			<br />
			<label for="lazysizes_thumbnails">
				<input type='checkbox' id='lazysizes_thumbnails' name='lazysizes_general[lazysizes_thumbnails]' <?php $this->checked_r( $options, 'lazysizes_thumbnails', 1 ); ?> value="1">
				<?php esc_html_e( 'Lazy load post thumbnails.', 'lazysizes' ); ?>
			</label>
			<br />
			<label for="lazysizes_textwidgets">
				<input type='checkbox' id='lazysizes_textwidgets' name='lazysizes_general[lazysizes_textwidgets]' <?php $this->checked_r( $options, 'lazysizes_textwidgets', 1 ); ?> value="1">
				<?php esc_html_e( 'Lazy load text widgets.', 'lazysizes' ); ?>
			</label>
			<br />
			<label for="lazysizes_avatars">
				<input type='checkbox' id='lazysizes_avatars' name='lazysizes_general[lazysizes_avatars]' <?php $this->checked_r( $options, 'lazysizes_avatars', 1 ); ?> value="1">
				<?php esc_html_e( 'Lazy load gravatars.', 'lazysizes' ); ?>
			</label>
			<br />
			<label for="lazysizes_attachment_image">
				<input type='checkbox' id='lazysizes_attachment_image' name='lazysizes_general[lazysizes_attachment_image]' <?php $this->checked_r( $options, 'lazysizes_attachment_image', 1 ); ?> value="1">
				<?php esc_html_e( 'Lazy load images loaded with wp_get_attachment_image.', 'lazysizes' ); ?>
				<p class="description">
					<?php esc_html_e( 'You can try this if your theme doesn\'t work with the plugin. Caveat: Does not add fallback for users with JavaScript disabled.', 'lazysizes' ); ?>
				</p>
			</label>
			<br />
			<label for="lazysizes_excludeclasses">
				<?php esc_html_e( 'Skip lazy loading on these classes:', 'lazysizes' ); ?><br />
				<textarea id='lazysizes_excludeclasses' name='lazysizes_general[lazysizes_excludeclasses]' rows="3" cols="60"><?php echo esc_html( $options['lazysizes_excludeclasses'] ); ?></textarea>
				<p class="description">
					<?php esc_html_e( 'Prevent objects with the above classes from being lazy loaded. (List classes separated by a space and without the proceding period. e.g. "skip-lazy-load size-thumbnail".)', 'lazysizes' ); ?>
				</p>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Output HTML for Effects Settings.
	 *
	 * @since 0.1.0
	 */
	public function lazysizes_effects_render() {
		$options = get_option( 'lazysizes_effects' );
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span>
					<?php esc_html_e( 'Effects settings', 'lazysizes' ); ?>
				</span>
			</legend>
			<label for="lazysizes_fade_in">
				<input type='checkbox' id='lazysizes_fade_in' name='lazysizes_effects[lazysizes_fade_in]' <?php $this->checked_r( $options, 'lazysizes_fade_in', 1 ); ?> value="1">
				<?php esc_html_e( 'Fade in lazy loaded objects.', 'lazysizes' ); ?>
			</label>
			<br />
			<label for="lazysizes_spinner">
				<input type='checkbox' id='lazysizes_spinner' name='lazysizes_effects[lazysizes_spinner]' <?php $this->checked_r( $options, 'lazysizes_spinner', 1 ); ?> value="1">
				<?php esc_html_e( 'Show spinner while objects are loading.', 'lazysizes' ); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Output HTML for AddOns Settings.
	 *
	 * @since 0.1.0
	 */
	public function lazysizes_addons_render() {
		$options = get_option( 'lazysizes_addons' );
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span>
					<?php esc_html_e( 'Addons settings', 'lazysizes' ); ?>
				</span>
			</legend>
			<label for="lazysizes_auto_load">
				<input type='checkbox' id='lazysizes_auto_load' name='lazysizes_addons[lazysizes_auto_load]' <?php $this->checked_r( $options, 'lazysizes_auto_load', 1 ); ?> value="1">
				<?php esc_html_e( 'Automatically load all objects, even those not in view.', 'lazysizes' ); ?>
			</label>
			<br />
			<label for="lazysizes_aspectratio">
				<input type='checkbox' id='lazysizes_aspectratio' name='lazysizes_addons[lazysizes_aspectratio]' <?php $this->checked_r( $options, 'lazysizes_aspectratio', 1 ); ?> value="1">
				<?php esc_html_e( 'Keep original aspect ratio before the object is loaded.', 'lazysizes' ); ?>
				<p class="description">
					<?php esc_html_e( 'Currently this needs images to have a defined width and height. Make sure to set a size for the images in your posts.', 'lazysizes' ); ?>
				</p>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Callback for the settings section.
	 *
	 * @since 0.1.0
	 */
	public function lazysizes_basic_section_callback() {
		esc_html_e( 'Customize the basic features of lazysizes.', 'lazysizes' );
	}


	/**
	 * Render the settings form.
	 *
	 * @since 0.1.0
	 */
	public function settings_page() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'lazysizes', 'lazysizes' ); ?></h2>
			<form id="basic" action='options.php' method='post' style='clear:both;'>
				<?php
				settings_fields( 'basicSettings' );
				do_settings_sections( 'basicSettings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Determine if an option should be presented as checked.
	 * Compares the value at $option[$key] with $current.
	 * If they match, the 'checked' HTML attribute is returned
	 *
	 * @since 0.1.0
	 * @param mixed[] $option Array of all options.
	 * @param string  $key The key of the option to compare.
	 * @param mixed   $current The other value to compare if not just true.
	 * @param bool    $echo Whether to echo or just return the string.
	 * @return string|void html attribute or empty string.
	 */
	public function checked_r( $option, $key, $current = true, $echo = true ) {
		if ( is_array( $option ) && array_key_exists( $key, $option ) ) {
			checked( $option[ $key ], $current, $echo );
		}
	}

}
