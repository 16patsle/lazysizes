<?php
/**
 * The plugin settings file
 *
 * @package Lazysizes
 */

namespace Lazysizes;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * The plugin settings class
 */
class Settings {

	/**
	 * Plugin version.
	 */
	const VER = '1.3.3';
	/**
	 * The default plugin settings values
	 *
	 * @var array[]
	 */
	protected $defaults = array(
		'general' => array(
			'lazysizes_minimize_scripts'         => 1,
			'lazysizes_optimized_scripts_styles' => 1,
			'lazysizes_thumbnails'               => 1,
			'lazysizes_textwidgets'              => 1,
			'lazysizes_avatars'                  => 1,
			'lazysizes_add_noscript'             => 1,
			'lazysizes_load_extras'              => 1,
			'lazysizes_excludeclasses'           => '',
			'lazysizes_img'                      => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
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

			if ( version_compare( $dbver, '0.3.0', '<=' ) ) {
				$general                           = get_option( 'lazysizes_general' );
				$general['lazysizes_add_noscript'] = $this->defaults['general']['lazysizes_add_noscript'];
				update_option( 'lazysizes_general', $general );
			}
			if ( version_compare( $dbver, '1.3.0', '<=' ) ) {
				$general                                       = get_option( 'lazysizes_general' );
				$general['lazysizes_optimized_scripts_styles'] = $this->defaults['general']['lazysizes_optimized_scripts_styles'];
				update_option( 'lazysizes_general', $general );
			}
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
		$settings = array( '<a href="options-general.php?page=lazysizes">' . esc_html__( 'Settings', 'lazysizes' ) . '</a>' );
		return array_merge( $settings, $links );
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
			<label for="lazysizes_optimized_scripts_styles">
				<input type='checkbox' id='lazysizes_optimized_scripts_styles' name='lazysizes_general[lazysizes_optimized_scripts_styles]' <?php $this->checked_r( $options, 'lazysizes_optimized_scripts_styles', 1 ); ?> value="1">
				<?php esc_html_e( 'Load custom lazysizes scripts and styles, optimized for performance.', 'lazysizes' ); ?>
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
			<label for="lazysizes_add_noscript">
				<input type='checkbox' id='lazysizes_add_noscript' name='lazysizes_general[lazysizes_add_noscript]' <?php $this->checked_r( $options, 'lazysizes_add_noscript', 1 ); ?> value="1">
				<?php esc_html_e( 'Add fallback for users without JavaScript.', 'lazysizes' ); ?>
				<p class="description">
					<?php esc_html_e( 'Disabling this will make images invisible when JavaScript is not available.', 'lazysizes' ); ?>
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
					<?php esc_html_e( 'Currently this needs images to either have a defined width or a defined height in the post content. For external images, both width and height are needed. Make sure to set a size for the images in your posts if you wish to use this.', 'lazysizes' ); ?>
				</p>
			</label>
			<br />
			<label for="lazysizes_acf_content">
				<input type='checkbox' id='lazysizes_acf_content' name='lazysizes_addons[lazysizes_acf_content]' <?php $this->checked_r( $options, 'lazysizes_acf_content', 1 ); ?> value="1">
				<?php esc_html_e( 'Lazy load images/iframes in WYSIWYG fields from Advanced Custom Fields.', 'lazysizes' ); ?>
				<p class="description">
					<?php esc_html_e( 'Depending on how the WYSIWYG content is shown you might want to change this.', 'lazysizes' ); ?>
				</p>
			</label>
			<br>
			<label for="lazysizes_native_lazy">
				<input type='checkbox' id='lazysizes_native_lazy' name='lazysizes_addons[lazysizes_native_lazy]' <?php $this->checked_r( $options, 'lazysizes_native_lazy', 1 ); ?> value="1">
				<?php esc_html_e( 'Allow native browser lazy loading to delay image loading further, when supported.', 'lazysizes' ); ?>
				<p class="description">
					<?php esc_html_e( 'Chrome 76+, Firefox 75+ and Edge 84+ supports native lazy loading. When lazysizes decides to start loading the image, it will let the browser decide whether to start loading immediately or wait some more. Not compatible with full native loading.', 'lazysizes' ); ?>
				</p>
			</label>
			<br />
			<label for="lazysizes_full_native">
				<input type='checkbox' id='lazysizes_full_native' name='lazysizes_addons[lazysizes_full_native]' <?php $this->checked_r( $options, 'lazysizes_full_native', 1 ); ?> value="1">
				<?php esc_html_e( 'Use full native lazy loading when supported.', 'lazysizes' ); ?>
				<p class="description">
					<?php esc_html_e( 'Chrome 76+, Firefox 75+ and Edge 84+ supports native lazy loading, giving the browser control over when to load images and iframes. This option gives supporting browsers full control over when to load the image, while keeping support for image loading effects like fade and Blurhash. Not compatible with native loading elay after lazysizes option above', 'lazysizes' ); ?>
				</p>
			</label>
			<br />
			<label for="lazysizes_skip_src">
				<input type='checkbox' id='lazysizes_skip_src' name='lazysizes_addons[lazysizes_skip_src]' <?php $this->checked_r( $options, 'lazysizes_skip_src', 1 ); ?> value="1">
				<?php esc_html_e( 'Skip adding a placeholder src and hide broken image icon.', 'lazysizes' ); ?>
				<p class="description">
					<?php esc_html_e( 'Experimental. Does not add a placeholder image in the src attribute, allowing the browser to load and render the image progressively. Also adds a small amount of CSS to hide the broken image icon browsers may show when the src is missing.', 'lazysizes' ); ?>
					<br>
					<?php esc_html_e( 'Note: Not compatible with the fade effect due to how browsers handle image loading.', 'lazysizes' ); ?>
				</p>
			</label>
			<br />
			<?php
			$blurhash_unsupported = ! extension_loaded( 'imagick' ) && ! extension_loaded( 'gd' );
			?>
			<label for="lazysizes_blurhash">
				<input type='checkbox' id='lazysizes_blurhash' name='lazysizes_addons[lazysizes_blurhash]' <?php $this->checked_r( $options, 'lazysizes_blurhash', 1 ); ?> <?php echo $blurhash_unsupported ? 'disabled' : ''; ?> value="1">
				<?php esc_html_e( 'Use Blurhash to generate blurry low-res placeholder images.', 'lazysizes' ); ?>
				<p class="description">
					<?php esc_html_e( 'Experimental. Currently only works on image attachments. Placeholders will need to be pregenerated, which can be done for each image in the Media Library. Images without a Blurhash string will show the regular blank placeholder.', 'lazysizes' ); ?>
					<br>
					<?php esc_html_e( 'Note: Limited compatibility with native loading and the fade in effect for images added through custom HTML or by some plugins.', 'lazysizes' ); ?>
				</p>
				<?php
				if ( $blurhash_unsupported ) {
					?>
					<p class="description">
						<?php esc_html_e( 'NOT SUPPORTED: Your current WordPress installation does not support Blurhash, since it is running on a version of PHP without image editing capabilities.', 'lazysizes' ); ?>
					</p>
					<?php
				}
				?>
			</label>
			<br />
			<label for="lazysizes_blurhash_onload">
				<input type='checkbox' id='lazysizes_blurhash_onload' name='lazysizes_addons[lazysizes_blurhash_onload]' <?php $this->checked_r( $options, 'lazysizes_blurhash_onload', 1 ); ?> <?php echo $blurhash_unsupported ? 'disabled' : ''; ?> value="1">
				<?php esc_html_e( 'When Blurhash is activated, generate missing Blurhash placeholders on page load.', 'lazysizes' ); ?>
				<p class="description">
					<?php esc_html_e( 'WARNING: Only use for debug and setup purposes. Generating Blurhash placeholders can be very computationally expensive, and will add several seconds to the page load time. After the first run, the placeholders will be saved, and will not need to be re-generated, so you can use this option to easily generate Blurhash placeholders for existing images.', 'lazysizes' ); ?>
				</p>
			</label>
			<br />
			<label for="lazysizes_blurhash_never_fancy">
				<input type='checkbox' id='lazysizes_blurhash_never_fancy' name='lazysizes_addons[lazysizes_blurhash_never_fancy]' <?php $this->checked_r( $options, 'lazysizes_blurhash_never_fancy', 1 ); ?> <?php echo $blurhash_unsupported ? 'disabled' : ''; ?> value="1">
				<?php esc_html_e( 'Never use the advanced Blurhash reveal effect, even when supported.', 'lazysizes' ); ?>
				<p class="description">
					<?php esc_html_e( 'The advanced Blurhash reveal effect creates an additional image element positioned under the regular image. This gives the best result in combination with the fade effect, but might not support all WordPress themes. Safeguards exist to prevent using the advanced effect when not supported, but in some cases problems may still occur. This setting lets you override the advanced reveal, and never use it.', 'lazysizes' ); ?>
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
