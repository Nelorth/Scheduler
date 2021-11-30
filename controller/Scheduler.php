<?php

/**
 * The main class defining the plugin.
 */
class Scheduler {
    const NAME = 'Scheduler';
    const VERSION = '1.0.0';
    const DB_PREFIX = 'skd_';
    const DB_VERSION = '1.0';
    const WEEKDAYS = array(
        'mon' => 'Monday',
        'tue' => 'Tuesday',
        'wed' => 'Wednesday',
        'thu' => 'Thursday',
        'fri' => 'Friday',
        'sat' => 'Saturday',
        'sun' => 'Sunday'
    );

    const OPTIONS = 'skd_options';
    const SCOPE_START_OPTION = 'scope_start';
    const SCOPE_END_OPTION = 'scope_end';
    const ACCENT_COLOR_OPTION = 'accent_color';

    const TEXTDOMAIN = 'scheduler';
    const SHORTCODE_POOL = array('schedule', 'stundenplan');

    /**
     * Constructs a new scheduler.
     */
    public function __construct() {
        $this->load_dependencies();
        add_action('after_setup_theme', array($this, 'load_textdomain'));
    }

    /**
     * Executes the plugin.
     */
    public function run() {
        $this->init_plugin_options();
        $this->setup_admin_actions();
        $this->setup_public_actions();
    }

    /**
     * Obtains the translated weekday for a given weekday key.
     *
     * @param string $key weekday key to translate.
     * @return string The weekday name in the current language.
     */
    public static function translate_weekday($key) {
        switch ($key) {
            case 'mon':
                return __('Monday', Scheduler::TEXTDOMAIN);
            case 'tue':
                return __('Tuesday', Scheduler::TEXTDOMAIN);
            case 'wed':
                return __('Wednesday', Scheduler::TEXTDOMAIN);
            case 'thu':
                return __('Thursday', Scheduler::TEXTDOMAIN);
            case 'fri':
                return __('Friday', Scheduler::TEXTDOMAIN);
            case 'sat':
                return __('Saturday', Scheduler::TEXTDOMAIN);
            case 'sun':
                return __('Sunday', Scheduler::TEXTDOMAIN);
            default:
                return __(sprintf('%s is not a valid weekday key.', $key), Scheduler::TEXTDOMAIN);
        }
    }

    /**
     * Initialize the plugin options form.
     */
    private function init_plugin_options() {
        // Make sure the options array exists
        if (!is_array(get_option(Scheduler::OPTIONS))) {
            add_option(Scheduler::OPTIONS, array());
        }

        // Define default values for all keys
        $default_values = array(
            Scheduler::ACCENT_COLOR_OPTION => array(),
            Scheduler::SCOPE_END_OPTION => array(),
            Scheduler::SCOPE_START_OPTION => array(),
        );

        // Add weekday option defaults
        foreach (Scheduler::WEEKDAYS as $option_key => $default_value) {
            $default_values[$option_key] = array();
        }

        $skd_options = get_option(Scheduler::OPTIONS);

        // Make sure all the keys exist and have a valid value.
        foreach ($default_values as $option_key => $default_value) {
            if (!key_exists($option_key, $skd_options)) {
                $skd_options[$option_key] = $default_value;
            }
        }

        if (Schedule::table_exists()) {
            // Define default values for all id specific keys
            $id_specific_default_values = array(
                Scheduler::ACCENT_COLOR_OPTION => '#0086cd',
                Scheduler::SCOPE_START_OPTION => '08:00',
                Scheduler::SCOPE_END_OPTION => '22:00'
            );

            // Add weekday id specific option defaults
            foreach (Scheduler::WEEKDAYS as $option_key => $default_value) {
                $id_specific_default_values[$option_key] = 1;
            }

            $ids = Schedule::get_all_ids();

            // Make sure all the id specific keys exist and have a valid value.
            foreach ($ids as $id) {
                foreach ($id_specific_default_values as $option_key => $default_value) {
                    if (!key_exists($id, $skd_options[$option_key])) {
                        $skd_options[$option_key][$id] = $id_specific_default_values[$option_key];
                    };
                }
            }
        }

        update_option(Scheduler::OPTIONS, $skd_options);
    }

    /**
     * Initializes the admin area.
     */
    private function setup_admin_actions() {
        $admin = new Admin_Controller();
        $admin->setup();
    }

    /**
     * Initializes the frontend.
     */
    private function setup_public_actions() {
        $public = new Frontend_Controller();
        $public->setup();
    }

    /**
     * Loads the translated plugin strings.
     */
    function load_textdomain() {
        load_plugin_textdomain(Scheduler::TEXTDOMAIN, false,
            basename(dirname(__FILE__, 2)) . '/languages');
    }

    /**
     * Loads the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // The class defining all actions that occur in the admin area.
        require_once 'Admin_Controller.php';

        // The class defining all actions that occur in the frontend.
        require_once 'Frontend_Controller.php';

        // The fundamental model classes.
        require_once __DIR__ . '/../model/DatabaseManager.php';
        require_once __DIR__ . '/../model/Schedule.php';
        require_once __DIR__ . '/../model/Event.php';
        require_once __DIR__ . '/../model/Item.php';
    }
}