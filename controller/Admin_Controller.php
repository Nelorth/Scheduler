<?php

/**
 * Controller for the administration backend menus.
 */
class Admin_Controller {
    const SCHEDULES_PAGE = 'skd-schedules';
    const EVENTS_PAGE = 'skd-events';
    const SETTINGS_PAGE = 'skd-settings';
    const IMPORT_EXPORT_PAGE = 'skd-import-export';
    const HELP_PAGE = 'skd-help';

    /**
     * Initializes the admin backend.
     */
    function setup() {
        add_action('admin_menu', array($this, 'register_plugin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        $this->setup_request_handlers();
    }

    /**
     * Initializes event handlers for the admin backend.
     */
    private function setup_request_handlers() {
        require_once dirname(__FILE__) . '/../view/admin/Schedule_Menu.php';
        require_once dirname(__FILE__) . '/../view/admin/Event_Menu.php';
        require_once dirname(__FILE__) . '/../view/admin/Import_Export_Menu.php';
        add_action('admin_post_schedule_add_response', array(Schedule_Menu::class, 'handle_schedule_add_response'));
        add_action('admin_post_schedule_edit_response', array(Schedule_Menu::class, 'handle_schedule_edit_response'));
        add_action('admin_post_event_add_response', array(Event_Menu::class, 'handle_event_add_response'));
        add_action('admin_post_event_edit_response', array(Event_Menu::class, 'handle_event_edit_response'));
    }

    /**
     * Registers scripts and stylesheets for the admin backend.
     */
    function enqueue_admin_scripts() {
        wp_enqueue_style('skd-admin-style', plugins_url('../css/admin.css', __FILE__));
        wp_enqueue_script('skd-admin-js', plugins_url('../js/admin.js', __FILE__), array('jquery'),
            null, false);

        // Localize Scripts
        $translations = array(
            'confirmDelete' => __('Do you want to delete the selected schedule?', Scheduler::TEXTDOMAIN),
        );
        wp_localize_script('skd-admin-js', 'translator', $translations);
    }

    /**
     * Adds the plugin menu to the admin backend.
     */
    function register_plugin_menu() {
        $schedules_submenu = new Schedule_Menu();
        $events_submenu = new Event_Menu();
        $import_export_submenu = new Import_Export_Menu();

        add_menu_page(__('Scheduler', Scheduler::TEXTDOMAIN),
            __('Scheduler', Scheduler::TEXTDOMAIN), 'manage_options', 'scheduler',
            array($schedules_submenu, 'display'), 'dashicons-schedule');
        add_submenu_page('scheduler', __('Schedules', Scheduler::TEXTDOMAIN),
            __('Schedules', Scheduler::TEXTDOMAIN), 'manage_options',
            self::SCHEDULES_PAGE, array($schedules_submenu, 'display'));

        add_submenu_page('scheduler', __('Events', Scheduler::TEXTDOMAIN),
            __('Events', Scheduler::TEXTDOMAIN), 'manage_options',
            self::EVENTS_PAGE, array($events_submenu, 'display'));

        add_submenu_page('scheduler', __('Import/Export', Scheduler::TEXTDOMAIN),
            __('Import/Export', Scheduler::TEXTDOMAIN), 'manage_options',
            self::IMPORT_EXPORT_PAGE, array($import_export_submenu, 'display'));

        remove_submenu_page('scheduler', 'scheduler');
    }
}