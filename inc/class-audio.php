<?php

class Meks_AP {

    /**
     *  Hold the class instance.
     */
    private static $instance = null;

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Settings key in database, used in get_option() as first parameter
     *
     * @var string
     */
    private $settings_key = 'meks_ap_settings';

    /**
     * Slug of the page, also used as identifier for hooks
     *
     * @var string
     */
    private $slug = 'meks-audio-player';

    /**
     * Options group id, will be used as identifier for adding fields to options page
     *
     * @var string
     */
    private $options_group_id = 'meks-ap-settings';

    /**
     * Array of all fields that will be printed on the settings page
     *
     * @var array
     */

    private $fields;


    /**
     * Start up
     */
    function __construct() {

        //delete_option('meks_ap_settings');

        $this->fields = $this->get_fields();
        $this->options = $this->get_options();

        add_action( 'admin_menu', array( $this, 'add_plugin_menu_page' ) );
        add_action( 'admin_init', array( $this, 'settings_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );

        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'script_compatibility' ), 99 );

        add_action( 'wp_footer', array( $this, 'display_player' ) );

    }

    public static function get_instance() {
        if ( self::$instance == null ) {
            self::$instance = new Meks_AP();
        }
        return self::$instance;
    }

    /* Load translation file */
    function load_textdomain() {
        load_plugin_textdomain( 'meks-audio-player', false, dirname( MEKS_AP_BASENAME ) . '/languages' );
    }


    /* Add the plugin settings link */
    function plugin_settings_link( $actions, $file ) {

        if ( $file != MEKS_AP_BASENAME ) {
            return $actions;
        }

        $actions['meks_ap_settings'] = '<a href="' . esc_url( admin_url( 'options-general.php?page='.$this->slug ) ) . '" aria-label="settings"> '. __( 'Settings', 'meks-audio-player' ) . '</a>';

        return $actions;
    }


    /**
     * Add options page
     */
    function add_plugin_menu_page() {

        add_options_page(
            esc_html__( 'Meks Audio Player', 'meks-audio-player' ),
            esc_html__( 'Meks Audio Player', 'meks-audio-player' ),
            'manage_options',
            $this->slug,
            array( $this, 'display_settings_page' )
        );
    }


    /* Get fields data */
    function get_fields() {

        $fields = array(

            'colors' => array(
                'id' => 'colors',
                'title' => esc_html__( 'Colors', 'meks-audio-player' ),
                'sanitize' => 'text',
                'default' => array(
                    'bg' => '#000',
                    'controls' => '#FFF'
                )
            ),

            'controls' => array(
                'id' => 'controls',
                'title' => esc_html__( 'Controls', 'meks-audio-player' ),
                'sanitize' => 'checkbox',
                'default' => array( 'skipback', 'playpause', 'jumpforward', 'progress', 'current', 'duration', 'volume', 'speed' )
            ),

            'volume' => array(
                'id' => 'volume',
                'title' => esc_html__( 'Default volume', 'meks-audio-player' ),
                'sanitize' => 'text',
                'default' => '50'
            ),

            'post_type' => array(
                'id' => 'post_type',
                'title' => esc_html__( 'Post Type', 'meks-audio-player' ),
                'sanitize' => 'checkbox',
                'default' => array( 'post' )
            ),

        );

        $fields = apply_filters( 'meks_ap_modify_options_fields', $fields );

        return $fields;

    }


    /**
     * Get options from database
     */
    function get_options() {

        $defaults = array();

        foreach ( $this->fields as $field => $args ) {
            $defaults[$field] = $args['default'];
        }

        $defaults = apply_filters( 'meks_ap_modify_defaults', $defaults );

        $options = get_option( $this->settings_key );

        $options = meks_ap_parse_args( $options, $defaults );

        $options = apply_filters( 'meks_ap_modify_options', $options );

        //print_r( $options );

        return $options;

    }



    /**
     * Enqueue Admin Scripts
     */
    function enqueue_admin_scripts() {
        global $pagenow;
        
        wp_enqueue_script( 'meks_ap_settings', MEKS_AP_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), MEKS_AP_VER, true );

        if ( $pagenow == 'options-general.php' && isset( $_GET['page'] ) && $_GET['page'] == $this->slug ) {
            wp_enqueue_style( 'meks_ap_settings', MEKS_AP_URL . 'assets/css/admin.css', array( 'wp-color-picker' ), MEKS_AP_VER );
        }
    }

    /**
     * Enqueue Frontend Scripts
     */
    function enqueue_frontend_scripts() {

        wp_enqueue_style( 'meks_ap-main', MEKS_AP_URL . 'assets/css/main.css', array(), MEKS_AP_VER );

        wp_enqueue_script( 'meks_ap-player', MEKS_AP_URL . 'assets/js/mediaelement-and-player.js', array( 'jquery' ), MEKS_AP_VER, true );
        wp_enqueue_script( 'meks_ap-player-skip-back', MEKS_AP_URL . 'assets/js/mediaelement-skip-back.js', array( 'jquery'), MEKS_AP_VER, true );
        wp_enqueue_script( 'meks_ap-player-jump-forward', MEKS_AP_URL . 'assets/js/mediaelement-jump-forward.js', array( 'jquery'), MEKS_AP_VER, true );
        wp_enqueue_script( 'meks_ap-player-speed', MEKS_AP_URL . 'assets/js/mediaelement-speed.js', array( 'jquery'), MEKS_AP_VER, true );

        wp_enqueue_script( 'meks_ap-main', MEKS_AP_URL . 'assets/js/main.js', array( 'jquery' ), MEKS_AP_VER, true );

        wp_localize_script( 'meks_ap-main', 'meks_ap_settings', $this->get_js_settings() );

        $inline_styles = $this->get_inline_styles();

        if ( !empty( $inline_styles ) ) {
            wp_add_inline_style( 'meks_ap-main', $inline_styles );
        }

    }

    function script_compatibility() {

        wp_deregister_script( 'mediaelement-core' );
        wp_deregister_script( 'mediaelement' );
        wp_deregister_script( 'wp-mediaelement' );
        wp_deregister_script( 'mediaelement-migrate' );

        wp_deregister_style( 'wp-mediaelement' );
        wp_deregister_style( 'mediaelement' );

    }

    function get_js_settings() {

        $settings['selectors'] = $this->get_allowed_selectors();

        $settings['player'] = array();
        $settings['player']['controls'] =  $this->options['controls'];
        $settings['player']['volume'] =  $this->options['volume']/100;

        return $settings;
    }

    /**
     * Get inline styles (player colors)
     */
    function get_inline_styles() {


        if ( !isset( $this->options['colors'] ) || empty( $this->options['colors'] ) ) {
            return '';
        }

        $styles = '.meks-ap-bg, .mejs-volume-total, .meks-ap-collapsed .meks-ap-toggle {
                    background: ' . $this->options['colors']['bg'] . ';
                 }';

        $styles .= '.meks-ap, .meks-ap a, .mejs-button>button {
                    color: ' . $this->options['colors']['controls'] . ';
                 }';

        $styles .= '.mejs-volume-button>.mejs-volume-slider,.mejs__speed-selector, .mejs-speed-selector, .mejs-playpause-button {
                    background-color: '. $this->options['colors']['controls'] .';
                }';

        $styles .= '.mejs-volume-button:hover > button:before,.mejs__speed-selector,.mejs-speed-selector, .mejs-speed-button:hover button, .mejs-playpause-button button{
                    color: '. $this->options['colors']['bg'] .';
                }';
        $styles .= '.mejs-time-current, .mejs-time-handle-content{
                    background-color: '. $this->options['colors']['controls'] .';
            }';
        $styles .= '.mejs-time-handle-content{
                border-color: '. $this->options['colors']['controls'] .';
        }';
        $styles .= ':root{
            --player-original-bg-color: ' . $this->options['colors']['bg'] . ';
        }';
        return $styles;
    }

    /**
     * Options page callback
     */
    function display_settings_page() {
?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
                <?php
        settings_fields( $this->options_group_id );
        do_settings_sections( $this->slug );
        submit_button();
?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    function settings_page() {

        register_setting(
            $this->options_group_id, // Option group
            $this->settings_key, // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        if ( empty( $this->fields ) ) {
            return false;
        }

        $section_id = 'meks_ap_section';

        add_settings_section( $section_id, '', '', $this->slug );

        foreach ( $this->fields as $field ) {

            if ( empty( $field['id'] ) ) {
                continue;
            }

            $action = 'print_' . $field['id'] . '_field';
            $callback = method_exists( $this, $action ) ? array( $this, $action ) : $field['action'];

            add_settings_field(
                'meks_ap_' . $field['id'] . '_id',
                $field['title'],
                $callback,
                $this->slug,
                $section_id,
                $this->options[$field['id']]
            );
        }

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param unknown $input array $input Contains all settings fields as array keys
     * @return mixed
     */
    function sanitize( $input ) {

        if ( empty( $this->fields ) || empty( $input ) ) {
            return false;
        }

        $new_input = array();
        foreach ( $this->fields as $field ) {
            if ( isset( $input[$field['id']] ) ) {
                $new_input[$field['id']] = $this->sanitize_field( $input[$field['id']], $field['sanitize'] );
            }
        }

        return $new_input;
    }

    /**
     * Dynamically sanitize field values
     *
     * @param unknown $value
     * @param unknown $sensitization_type
     * @return int|string
     */
    function sanitize_field( $value, $sensitization_type ) {
        switch ( $sensitization_type ) {

        case "checkbox":
            $new_input = array();
            foreach ( $value as $key => $val ) {
                $new_input[$key] = ( isset( $value[$key] ) ) ?
                    sanitize_text_field( $val ) :
                    '';
            }
            return $new_input;
            break;

        case "radio":
            return sanitize_text_field( $value );
            break;

        case "text":
            if ( !is_array( $value ) ) {
                return sanitize_text_field( $value );
            }

            $new_value = array();

            foreach ( $value as $key => $val ) {
                $new_value[$key] = sanitize_text_field( $val );
            }

            return $new_value;

            break;

        default:
            break;
        }
    }


    /**
     * Print Controls fields
     */
    function print_controls_field( $args ) {

        $controls = array(
            'skipback' => esc_html__( 'Skip back', 'meks-audio-player' ),
            'playpause' => esc_html__( 'Play (pause)', 'meks-audio-player' ),
            'jumpforward' => esc_html__( 'Jump forward', 'meks-audio-player' ),
            'progress' => esc_html__( 'Progress bar', 'meks-audio-player' ),
            'current' => esc_html__( 'Current time', 'meks-audio-player' ),
            'duration' => esc_html__( 'Duration', 'meks-audio-player' ),
            'volume' => esc_html__( 'Volume', 'meks-audio-player' ),
            'speed' => esc_html__( 'Playback speed', 'meks-audio-player' )
        );

        foreach ( $controls as $key => $title ) {

            $checked =  in_array( $key, $args ) ? $key : '';

            printf(
                '<label><input type="checkbox" id="meks_ap-controls_%s" name="%s[controls][]" value="%s" %s/> %s</label><br>',
                $key,
                $this->settings_key,
                $key,
                checked( $checked, $key, false ),
                $title
            );
        }

        printf( '<p class="description">%s</p>', esc_html__( 'Select which player controls you would like to display', 'meks-audio-player' ) );

    }

    /**
     * Print Volume field
     */
    function print_volume_field( $value ) {

        //print_r( $args );

        printf( '<input type="number" class="meks_ap-volume" min="0" max="100" step="10" name="%s[volume]" value="%s" /> %s',
            $this->settings_key,
            $value,
            '%'
        );


        printf( '<p class="description">%s</p>', esc_html__( 'Specify default player volume (0-100%)', 'meks-audio-player' ) );
    }


    /**
     * Print Style Colors
     */
    function print_colors_field( $args ) {

        //print_r( $args );

        printf( '<label class="meks_ap-colors-label">%s</label><input type="text" class="meks_ap-colors" name="%s[colors][bg]" value="%s" />',
            esc_html__( 'Background', 'meks-audio-player' ),
            $this->settings_key,
            $args['bg']
        );

        printf( '<label class="meks_ap-colors-label">%s</label><input type="text" class="meks_ap-colors" name="%s[colors][controls]" value="%s" />',
            esc_html__( 'Foreground', 'meks-audio-player' ),
            $this->settings_key,
            $args['controls']
        );

        printf( '<p class="description">%s</p>', esc_html__( 'Select your prefered colors', 'meks-audio-player' ) );

    }


    /**
     * Print Post Types fields
     */
    function print_post_type_field( $args ) {

        $post_types = meks_ap_post_types();

        foreach ( $post_types as $key => $type ) {

            $checked =  in_array( $key, $args ) ? $key : '';

            printf(
                '<label><input type="checkbox" id="meks_ap_post_type_%s" name="%s[post_type][]" value="%s" %s/> %s</label><br/>',
                $key,
                $this->settings_key,
                $key,
                checked( $checked, $key, false ),
                $type->label
            );
        }

        printf( '<p class="description">%s</p>', esc_html__( 'Select post types which you are using for audio', 'meks-audio-player' ) );

    }

    function get_allowed_blocks() {

        return apply_filters( 'meks_ap_modify_allowed_blocks', array( 'core/audio' ) );

    }

    function get_allowed_shortcodes() {

        return apply_filters( 'meks_ap_modify_allowed_shortcodes', array( 'audio', 'powerpress', 'ss_player' ) );

    }

    public function get_allowed_selectors() {

        $selectors = array(
            '.wp-block-audio' => array( 'element' => 'audio', 'type' => 'audio'),
            '.wp-audio-shortcode' => array( 'element' => 'self', 'type' => 'audio'),
            '.powerpress_player' => array( 'element' => 'audio', 'type' => 'audio'),
            '.powerpress_links' => array( 'element' => 'audio', 'type' => 'audio')
        );

        return apply_filters( 'meks_ap_modify_allowed_selectors', $selectors );

    }

    /**
     * Function which checks if we should display the player on the current page
     */
    function is_playable() {

        if ( !function_exists( 'parse_blocks' ) ) {
            //WP 5.0+ only
            return false;
        }

        if ( !is_singular( $this->options['post_type'] ) ) {
            return false;
        }

        $content = get_the_content( get_queried_object_id() );

        //print_r( $content );

        $blocks = parse_blocks( $content );

        if ( empty( $blocks ) ) {
            return false;
        }

        $allowed_blocks = $this->get_allowed_blocks();

        foreach ( $blocks as $block ) {

            //print_r( $block );

            if ( in_array( $block['blockName'], $allowed_blocks ) ) {
                return true;
            }
        }

        $allowed_shortcodes = $this->get_allowed_shortcodes();

        foreach ( $allowed_shortcodes as $shortcode ) {

            if ( has_shortcode( $content, $shortcode ) ) {
                return true;
            }

        }


        return false;
    }

    /**
     * Player template
     */
    public function get_player_template_path() {

        return apply_filters( 'meks_ap_modify_player_template_path',  MEKS_AP_DIR . 'inc/player.php' );

    }


    /**
     * Display the player
     */
    function display_player() {

        if ( !$this->is_playable() ) {
            return;
        }

        include_once $this->get_player_template_path();

    }

}
