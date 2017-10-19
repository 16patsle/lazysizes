<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class LazysizesSettings {

  const ver = '0.1.1'; // Plugin version
  const ns = 'lazy-load-xt';
  protected $defaults = array(
    'general' => array(
      'lazysizes_minimize_scripts' => 1,
      'lazysizes_thumbnails' => 1,
      'lazysizes_textwidgets' => 1,
      'lazysizes_avatars' => 1,
      'lazysizes_load_extras' => 1,
      'lazysizes_excludeclasses' => '',
      'lazysizes_img' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
    ),
  );

  public function __construct() {
    add_action( 'admin_menu', array($this,'lazysizes_add_admin_menu') );
    add_action( 'admin_init', array($this,'lazysizes_settings_init') );
    add_action( 'admin_enqueue_scripts', array($this,'lazysizes_enqueue_admin') );
    add_action( 'upgrader_process_complete', array($this,'update') );
  }

  function first_time_activation() {
    // Set default settings
    $defaults = $this->defaults;
    foreach ($defaults as $key => $val) {
      if (get_option('lazysizes_'.$key,false) == false) {
        update_option('lazysizes_'.$key,$val);
      }
    }
    update_option('lazysizes_version',self::ver);
  }

  function update() {
    $defaults = $this->defaults;
    $ver = self::ver;
    $dbver = get_option('lazysizes_version','');
    if (version_compare($ver,$dbver,'>')) {
      update_option('lazysizes_version',$ver);
    }
  }


  function lazysizes_add_admin_menu() {
    $admin_page = add_options_page( 'Lazysizes', 'Lazysizes', 'manage_options', 'lazysizes', array($this,'settings_page') );
  }
  function lazysizes_enqueue_admin() {
    $screen = get_current_screen();
    if ($screen->base == 'settings_page_lazysizes') {
      wp_enqueue_style('thickbox-css');
      //add_action( 'admin_notices', array($this,'ask_for_feedback') );
    }
  }

  /**
   * Ask users for feedback about the plugin.
   *
   * @since Lazysizes 0.1.0
   */
  function ask_for_feedback() {
    ?>
    <div class="updated">
        <p><?php _e( 'Help improve lazysizes: <a href="https://wordpress.org/support/plugin/lazysizes" target="_blank">submit feedback, questions, and bug reports</a>.', self::ns ); ?></p>
    </div>
    <?php
    wp_enqueue_script('thickbox');
  }
  function lazysizes_action_links( $links ) {
    $links[] = '<a href="options-general.php?page=lazysizes">'.__('Settings',self::ns).'</a>';
    return $links;
  }

  function lazysizes_settings_init() {

    register_setting( 'basicSettings', 'lazysizes_general' );
    register_setting( 'basicSettings', 'lazysizes_effects' );
    register_setting( 'basicSettings', 'lazysizes_addons' );

    add_settings_section(
      'lazysizes_basic_section',
      __( 'General Settings', self::ns ),
      array($this,'lazysizes_basic_section_callback'),
      'basicSettings'
    );

    add_settings_field(
      'lazysizes_general',
      __( 'Basics', self::ns ),
      array($this,'lazysizes_general_render'),
      'basicSettings',
      'lazysizes_basic_section'
    );

    add_settings_field(
      'lazysizes_effects',
      __( 'Effects', self::ns ),
      array($this,'lazysizes_effects_render'),
      'basicSettings',
      'lazysizes_basic_section'
    );

    add_settings_field(
      'lazysizes_addons',
      __( 'Addons', self::ns ),
      array($this,'lazysizes_addons_render'),
      'basicSettings',
      'lazysizes_basic_section'
    );

  }


  /**
   * Output HTML for General Settings.
   *
   * @since Lazysizes 0.1.0
   */
  function lazysizes_general_render() {

    $options = get_option( 'lazysizes_general' );
    ?>
    <fieldset>
      <legend class="screen-reader-text">
        <span><?php _e('Basic settings', self::ns ); ?></span>
      </legend>
      <label for="lazysizes_minimize_scripts">
        <input type='checkbox' id='lazysizes_minimize_scripts' name='lazysizes_general[lazysizes_minimize_scripts]' <?php $this->checked_r( $options, 'lazysizes_minimize_scripts', 1 ); ?> value="1">
        <?php _e('Load minimized versions of javascript and css files.', self::ns ); ?>
      </label>
      <br />
      <label for="lazysizes_footer">
        <input type='checkbox' id='lazysizes_footer' name='lazysizes_general[lazysizes_footer]' <?php $this->checked_r( $options, 'lazysizes_footer', 1 ); ?> value="1">
        <?php _e('Load scripts in the footer.','lazy-load-xt'); ?>
      </label>
    </fieldset>
    <fieldset>
      <legend class="screen-reader-text">
        <span><?php _e('Lazy Load settings','lazy-load-xt'); ?></span>
      </legend>
      <br />
      <label for="lazysizes_load_extras">
        <input type='checkbox' id='lazysizes_load_extras' name='lazysizes_general[lazysizes_load_extras]' <?php $this->checked_r( $options, 'lazysizes_load_extras', 1 ); ?> value="1">
        <?php _e('Lazy load YouTube and Vimeo videos, iframes, audio, etc.', self::ns ); ?>
      </label>
      <br />
      <label for="lazysizes_thumbnails">
        <input type='checkbox' id='lazysizes_thumbnails' name='lazysizes_general[lazysizes_thumbnails]' <?php $this->checked_r( $options, 'lazysizes_thumbnails', 1 ); ?> value="1">
        <?php _e('Lazy load post thumbnails.', self::ns ); ?>
      </label>
      <br />
      <label for="lazysizes_textwidgets">
        <input type='checkbox' id='lazysizes_textwidgets' name='lazysizes_general[lazysizes_textwidgets]' <?php $this->checked_r( $options, 'lazysizes_textwidgets', 1 ); ?> value="1">
        <?php _e('Lazy load text widgets.', self::ns ); ?>
      </label>
      <br />
      <label for="lazysizes_avatars">
        <input type='checkbox' id='lazysizes_avatars' name='lazysizes_general[lazysizes_avatars]' <?php $this->checked_r( $options, 'lazysizes_avatars', 1 ); ?> value="1">
        <?php _e('Lazy load gravatars.', self::ns ); ?>
      </label>
      <br />
      <label for="lazysizes_excludeclasses">
        <?php _e('Skip lazy loading on these classes:', self::ns ); ?><br />
        <textarea id='lazysizes_excludeclasses' name='lazysizes_general[lazysizes_excludeclasses]' rows="3" cols="60"><?php echo $options['lazysizes_excludeclasses']; ?></textarea>
        <p class="description"><?php _e('Prevent objects with the above classes from being lazy loaded. (List classes separated by a space and without the proceding period. e.g. "skip-lazy-load size-thumbnail".)', self::ns ); ?></p>
      </label>
    </fieldset>
    <?php

  }

  /**
   * Output HTML for Effects Settings.
   *
   * @since Lazysizes 0.1.0
   */
  function lazysizes_effects_render() {

    $options = get_option( 'lazysizes_effects' );
    ?>
    <fieldset>
      <legend class="screen-reader-text">
        <span><?php _e('Effects settings', self::ns ); ?></span>
      </legend>
      <label for="lazysizes_fade_in">
        <input type='checkbox' id='lazysizes_fade_in' name='lazysizes_effects[lazysizes_fade_in]' <?php $this->checked_r( $options, 'lazysizes_fade_in', 1 ); ?> value="1">
        <?php _e('Fade in lazy loaded objects.', self::ns ); ?>
      </label>
      <br />
      <label for="lazysizes_spinner">
        <input type='checkbox' id='lazysizes_spinner' name='lazysizes_effects[lazysizes_spinner]' <?php $this->checked_r( $options, 'lazysizes_spinner', 1 ); ?> value="1">
        <?php _e('Show spinner while objects are loading.', self::ns ); ?>
      </label>
    </fieldset>
    <?php

  }

  /**
   * Output HTML for AddOns Settings.
   *
   * @since Lazysizes 0.1.0
   */
  function lazysizes_addons_render() {

    $options = get_option( 'lazysizes_addons' ); ?>
    <fieldset>
      <legend class="screen-reader-text">
        <span><?php _e('Addons settings', self::ns ); ?></span>
      </legend>
      <label for="lazysizes_auto_load">
        <input type='checkbox' id='lazysizes_auto_load' name='lazysizes_addons[lazysizes_auto_load]' <?php $this->checked_r( $options, 'lazysizes_auto_load', 1 ); ?> value="1">
        <?php _e('Automatically load all objects, even those not in view.', self::ns ); ?>
      </label>
    </fieldset>
    <?php

  }

  /**
   * Output HTML for Advanced Settings.
   *
   * @since Lazysizes 0.1.0
   */
  function lazysizes_advanced_render() {

    $options = get_option( 'lazysizes_advanced' ); ?>
    <fieldset>
      <legend class="screen-reader-text">
        <span><?php _e( 'Advanced settings', self::ns ); ?></span>
      </legend>
      <label for="lazysizes_enabled">
        <input type='checkbox' id='lazysizes_enabled' name='lazysizes_advanced[lazysizes_enabled]' <?php $this->checked_r( $options, 'lazysizes_enabled', 1 ); ?> value="1">
        <?php _e( 'Enable advanced options. ', self::ns ); ?>
        <p class="description"><?php _e( 'The following settings will only go into effect if advanced options are enabled.', self::ns ); ?></p>
        <p class="description"><?php _e( 'Refer to RESS.io\'s <a href="https://github.com/ressio/lazy-load-xt#options">documentation on github</a> for further explanation of each option.', self::ns ); ?></p>
      </label>
      <br />
      <?php
      /*
        autoInit
        selector
        srcAttr
        blankImage
      */
      ?>
      <label for="lazysizes_edgeY">
        <?php _e( 'Edge Y:', self::ns ); ?><br />
        <input type='number' id='lazysizes_edgeY' name='lazysizes_advanced[lazysizes_edgeY]' value="<?php echo $options['lazysizes_edgeY']; ?>">
        <?php _e( 'pixels', self::ns ); ?>
      </label>
      <br />
      <label for="lazysizes_edgeX">
        <?php _e( 'Edge X:', self::ns ); ?><br />
        <input type='number' id='lazysizes_edgeX' name='lazysizes_advanced[lazysizes_edgeX]' value="<?php echo $options['lazysizes_edgeX']; ?>">
        <?php _e( 'pixels', self::ns ); ?>
      </label>
      <br />
      <label for="lazysizes_throttle">
        <?php _e( 'Throttle:', self::ns ); ?><br />
        <input type='number' id='lazysizes_throttle' name='lazysizes_advanced[lazysizes_throttle]' value="<?php echo $options['lazysizes_throttle']; ?>">
        <?php _e( 'ms', self::ns ); ?>
      </label>
      <br />
      <label for="lazysizes_visibleOnly">
        <input type='checkbox' id='lazysizes_visibleOnly' name='lazysizes_advanced[lazysizes_visibleOnly]' <?php $this->checked_r( $options, 'lazysizes_visibleOnly', 1 ); ?> value="1">
        <?php _e( 'Visible only', self::ns ); ?>
      </label>
      <br />
      <label for="lazysizes_checkDuplicates">
        <input type='checkbox' id='lazysizes_checkDuplicates' name='lazysizes_advanced[lazysizes_checkDuplicates]' <?php $this->checked_r( $options, 'lazysizes_checkDuplicates', 1 ); ?> value="1">
        <?php _e( 'Check duplicates', self::ns ); ?>
      </label>
    </fieldset>
    <?php

  }

  /**
   * Callback for the settings section.
   *
   * @since Lazysizes 0.1.0
   */
  function lazysizes_basic_section_callback() {
    _e( 'Customize the basic features of lazysizes.', self::ns );
  }


  /**
   * Render the settings form.
   *
   * @since Lazysizes 0.1.0
   */
  function settings_page() {

    ?>
    <div class="wrap">
      <h2><?php _e('lazysizes'); ?></h2>
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

  function checked_r($option, $key, $current = true, $echo = true) {
    if (is_array($option) && array_key_exists($key, $option)) {
      checked( $option[$key],$current,$echo );
    }
  }

}
