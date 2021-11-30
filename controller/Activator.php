<?php

/**
 * This class is responsible for necessary initialization upon plugin activation.
 */
class Activator {
    /**
     * Prefix for tables in the database.
     */
    private $table_prefix;

    /**
     * Charset to use in database tables.
     */
    private $charset_collate;

    /**
     * Constructs a new instance.
     */
    public function __construct() {
        global $wpdb;
        $this->table_prefix = $wpdb->prefix . Scheduler::DB_PREFIX;
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    /**
     * Activates the plugin.
     */
    public function activate() {
        require_once(__DIR__ . '/../model/Schedule.php');
        require_once(__DIR__ . '/../model/Event.php');
        require_once(__DIR__ . '/../model/Item.php');
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $this->init_schedule_table();
        $this->init_event_table();
        $this->init_item_table();

        add_option('scheduler_db_version', Scheduler::DB_VERSION);
    }

    /**
     * Updates schedule table or creates if not exists.
     */
    private function init_schedule_table() {
        $schedule = $this->table_prefix . Schedule::TABLE_NAME;
        $schedule_id = Schedule::ID_COL_NAME;
        $schedule_title = Schedule::TITLE_COL_NAME;
        $schedule_description = Schedule::DESCRIPTION_COL_NAME;

        $sql = "CREATE TABLE IF NOT EXISTS $schedule (
            $schedule_id int(10) NOT NULL AUTO_INCREMENT,
            $schedule_title varchar(127) NOT NULL,
            $schedule_description text NOT NULL,
            PRIMARY KEY ($schedule_id)
        ) $this->charset_collate;";

        dbDelta($sql);  // see https://developer.wordpress.org/reference/functions/dbdelta
    }

    /**
     * Updates event table or creates if not exists.
     */
    private function init_event_table() {
        $event = $this->table_prefix . Event::TABLE_NAME;
        $event_id = Event::ID_COL_NAME;
        $event_title = Event::TITLE_COL_NAME;
        $event_description = Event::DESCRIPTION_COL_NAME;
        $event_thumbnail = Event::THUMBNAIL_COL_NAME;
        $event_color = Event::COLOR_COL_NAME;
        $event_page = Event::PAGE_COL_NAME;


        $sql = "CREATE TABLE IF NOT EXISTS $event (
            $event_id int(10) NOT NULL AUTO_INCREMENT,
            $event_title varchar(127) NOT NULL,
            $event_description text NOT NULL,
            $event_thumbnail varchar(127) NOT NULL,
            $event_color varchar(7) NOT NULL,
            $event_page bigint(20) NOT NULL,
            PRIMARY KEY ($event_id)
        ) $this->charset_collate;";

        dbDelta($sql);  // see https://developer.wordpress.org/reference/functions/dbdelta
    }

    /**
     * Updates item table or creates if not exists.
     */
    private function init_item_table() {
        $item = $this->table_prefix . Item::TABLE_NAME;
        $item_id = Item::ID_COL_NAME;
        $item_weekday = Item::WEEKDAY_COL_NAME;
        $item_start_time = Item::START_TIME_COL_NAME;
        $item_end_time = Item::END_TIME_COL_NAME;
        $item_event = Item::EVENT_COL_NAME;
        $item_schedule = Item::SCHEDULE_COL_NAME;
        $weekdays = "'" . implode("', '", array_keys(Scheduler::WEEKDAYS)) . "'";

        $sql = "CREATE TABLE IF NOT EXISTS $item (
            $item_id int(10) NOT NULL AUTO_INCREMENT,
            $item_weekday enum($weekdays) NOT NULL,
            $item_start_time time NOT NULL,
            $item_end_time time NOT NULL,
            $item_event int(10) NOT NULL,
            $item_schedule int(10) NOT NULL,
            PRIMARY KEY ($item_id)
        ) $this->charset_collate;";

        dbDelta($sql);  // see https://developer.wordpress.org/reference/functions/dbdelta
    }
}