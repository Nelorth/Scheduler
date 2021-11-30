<?php

/**
 * Representation of an entire schedule in the database.
 */
class Schedule {
    const TABLE_NAME = 'schedule';
    const ID_COL_NAME = 'id';
    const TITLE_COL_NAME = 'title';
    const DESCRIPTION_COL_NAME = 'description';

    private $id;
    private $title;
    private $description;

    /**
     * Constructs a new schedule from the given information.
     *
     * @param int $id The ID of the schedule.
     * @param string $title The title of the schedule.
     * @param string $description The description of the schedule.
     */
    public function __construct($id, $title, $description) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * Factory method for constructing an empty schedule.
     *
     * @return Schedule An empty schedule.
     */
    public static function construct_default() {
        return new self('', '', '');
    }

    /**
     * Factory method for constructing a schedule from a database ID.
     *
     * @param int $id The ID of the desired schedule in the database.
     * @return Schedule A fully initialized schedule.
     */
    public static function from_id($id) {
        global $wpdb;
        $schedule = $wpdb->prefix . Scheduler::DB_PREFIX . Schedule::TABLE_NAME;
        $schedule_id = self::ID_COL_NAME;
        $row = $wpdb->get_row("SELECT * FROM $schedule WHERE $schedule_id = $id;");
        return new Schedule($row->id, $row->title, $row->description);
    }

    /**
     * Retrieves schedules in a paginated fashion with sorting.
     *
     * @param string $orderby The attribute to order the results by.
     * @param string $order ASC or DESC.
     * @param $per_page Number of items per page.
     * @param int $offset The current page number.
     * @return array The events corresponding to the selection.
     */
    public static function get_schedules($orderby, $order, $per_page, $offset) {
        global $wpdb;
        $schedule = $wpdb->prefix . Scheduler::DB_PREFIX . Schedule::TABLE_NAME;
        $results = $wpdb->get_results("SELECT * FROM $schedule
            ORDER BY $orderby $order
            LIMIT $per_page
            OFFSET $offset;"
        );
        $schedules = array();
        foreach ($results as $result) {
            $schedules[] = new Schedule($result->id, $result->title, $result->description);
        }
        return $schedules;
    }

    /**
     * Retrieves all existing schedule IDs from the database.
     *
     * @return array List of all schedule IDs.
     */
    public static function get_all_ids() {
        global $wpdb;
        $schedule = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $schedule_id = self::ID_COL_NAME;
        $col = $wpdb->get_col("SELECT $schedule_id FROM $schedule;");
        $ids = array();
        foreach ($col as $id) {
            $ids[] = $id;
        }
        return $ids;
    }

    /**
     * Inserts a new schedule into the database.
     *
     * @param string $title The title of the schedule.
     * @param string $description The description of the schedule.
     */
    public static function add_schedule($title, $description) {
        global $wpdb;
        $schedule = $wpdb->prefix . Scheduler::DB_PREFIX . Schedule::TABLE_NAME;
        $wpdb->insert($schedule, array(
            self::TITLE_COL_NAME => $title,
            self::DESCRIPTION_COL_NAME => $description
        ));
    }

    /**
     * Updates an existing schedule in the database.
     *
     * @param int $id The ID of the schedule.
     * @param string $title The title of the schedule.
     * @param string $description The description of the schedule.
     */
    public static function update_schedule($id, $title, $description) {
        global $wpdb;
        $schedule = $wpdb->prefix . Scheduler::DB_PREFIX . Schedule::TABLE_NAME;
        $wpdb->update($schedule, array(
            self::TITLE_COL_NAME => $title,
            self::DESCRIPTION_COL_NAME => $description
        ), array(self::ID_COL_NAME => $id));
    }

    /**
     * Deletes a schedule entirely from the database.
     *
     * @param int $id The ID of the schedule to remove.
     */
    public static function delete_schedule($id) {
        global $wpdb;
        $schedule = $wpdb->prefix . Scheduler::DB_PREFIX . Schedule::TABLE_NAME;
        $wpdb->delete($schedule, array(
            self::ID_COL_NAME => $id
        ));
    }

    /**
     * Checks whether the schedule table exists.
     *
     * @return bool True iff the schedule table is present.
     */
    public static function table_exists() {
        global $wpdb;
        $schedule = $wpdb->prefix . Scheduler::DB_PREFIX . Schedule::TABLE_NAME;
        return $wpdb->get_var("SHOW TABLES LIKE '$schedule'") == $schedule;
    }

    /**
     * Checks whether a specific schedule exists.
     *
     * @param int $id The id of the schedule.
     * @return bool True iff the requested schedule is present.
     */
    public static function schedule_exists($id) {
        global $wpdb;
        $schedule = $wpdb->prefix . Scheduler::DB_PREFIX . Schedule::TABLE_NAME;
        $schedule_id = Schedule::ID_COL_NAME;
        return $wpdb->get_var("SELECT $schedule_id FROM $schedule WHERE $schedule_id = $id;");
    }

    ////////////////////////////////////////////////////////////////////////////////
    /// getters and setters                                                      ///
    ////////////////////////////////////////////////////////////////////////////////

    public function get_id() {
        return $this->id;
    }

    public function get_title() {
        return $this->title;
    }

    public function get_description() {
        return $this->description;
    }
}