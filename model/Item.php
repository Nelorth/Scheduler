<?php

/**
 * Representation of a schedule item in the database.
 */
class Item {
    const TABLE_NAME = 'item';
    const ID_COL_NAME = 'id';
    const WEEKDAY_COL_NAME = 'weekday';
    const START_TIME_COL_NAME = 'start_time';
    const END_TIME_COL_NAME = 'end_time';
    const EVENT_COL_NAME = 'event';
    const SCHEDULE_COL_NAME = 'schedule';

    private $id;
    private $weekday;
    private $start_time;
    private $end_time;
    private $event;
    private $schedule;

    /**
     * Constructs a new item from the given information.
     *
     * @param int $id The ID of the item.
     * @param string $weekday The weekday of the item.
     * @param string $start_time The start time of the item.
     * @param string $end_time The end time of the item.
     * @param int $event The ID of the event the item is associated with.
     * @param int $schedule The ID of the schedule the item is associated with.
     */
    public function __construct($id, $weekday, $start_time, $end_time, $event, $schedule) {
        $this->id = $id;
        $this->weekday = $weekday;
        $this->start_time = strtotime($start_time);
        $this->end_time = strtotime($end_time);
        $this->event = Event::from_id($event);
        $this->schedule = Schedule::from_id($schedule);
    }

    /**
     * Factory method for constructing an item from a database record.
     *
     * @param Object $result A database row.
     * @return Item The item object defined by the row.
     */
    private static function from_record($result) {
        return new Item(
            $result->id,
            $result->weekday,
            $result->start_time,
            $result->end_time,
            $result->event,
            $result->schedule
        );
    }

    /**
     * Turns a collection of database records into a list of items.
     *
     * @param Object $results A collection of database records resembling schedule items.
     * @return array The corresponding list of item objects.
     */
    private static function to_items($results) {
        $items = array();
        foreach ($results as $result) {
            $items[] = self::from_record($result);
        }
        return $items;
    }

    /**
     * Retrieves all schedule items from the database.
     *
     * @return array List of all items.
     */
    public static function get_all_items($schedule_id) {
        global $wpdb;
        $item = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $item_start_time = self::START_TIME_COL_NAME;
        $schedule = self::SCHEDULE_COL_NAME;
        $results = $wpdb->get_results("SELECT * 
            FROM $item 
            WHERE $schedule = $schedule_id 
            ORDER BY $item_start_time ASC;");
        return self::to_items($results);
    }

    /**
     * Retrieves the items of a certain schedule and weekday from the database.
     *
     * @param int $schedule_id The ID of the desired schedule.
     * @param string $weekday The desired weekday.
     * @return array List of all items taking place on the given weekday.
     *
     */
    public static function get_items_at_weekday($schedule_id, $weekday) {
        global $wpdb;
        $item = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $item_weekday = self::WEEKDAY_COL_NAME;
        $item_start_time = self::START_TIME_COL_NAME;
        $schedule = self::SCHEDULE_COL_NAME;
        $results = $wpdb->get_results("SELECT * 
            FROM $item 
            WHERE $schedule = $schedule_id 
            AND $item_weekday = '$weekday'
            ORDER BY $item_start_time ASC;");
        return self::to_items($results);
    }

    /**
     * Retrieves the items of a certain schedule, weekday and start time from the database.
     *
     * @param int $schedule_id The ID of the desired schedule.
     * @param string $weekday The desired weekday.
     * @param Object $start_time The desired start time.
     * @return array List of all items taking place on the given weekday at the specified starting time.
     */
    public static function get_items_at_weekday_and_start_time($schedule_id, $weekday, $start_time) {
        global $wpdb;
        $item = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $item_weekday = self::WEEKDAY_COL_NAME;
        $item_start_time = self::START_TIME_COL_NAME;
        $schedule = self::SCHEDULE_COL_NAME;
        $start_time_lower_bound = date('H:i', $start_time - 450);
        $start_time_upper_bound = date('H:i', $start_time + 450);

        $sql = "SELECT * 
            FROM $item 
            WHERE $schedule = $schedule_id
            AND $item_weekday = '$weekday'
            AND $item_start_time > '$start_time_lower_bound'
            AND $item_start_time <= '$start_time_upper_bound';";

        $results = $wpdb->get_results($sql);
        return self::to_items($results);
    }

    /**
     * Retrieves all schedule items taking place during the specified time.
     *
     * @param int $schedule_id The ID of the schedule in question.
     * @param string $day The day in question.
     * @param Object $time The critical time in question.
     * @return array List of all concurrent items for the specified time.
     */
    public static function get_local_concurrencies($schedule_id, $day, $time) {
        global $wpdb;
        $item = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $item_start_time = self::START_TIME_COL_NAME;
        $item_end_time = self::END_TIME_COL_NAME;
        $item_weekday = self::WEEKDAY_COL_NAME;
        $item_schedule = self::SCHEDULE_COL_NAME;
        $time = date('H:i', $time + 450);

        $results = $wpdb->get_results("SELECT *
             FROM $item
             WHERE $item_schedule = $schedule_id
             AND $item_weekday = '$day'
             AND $item_start_time <= '$time'
             AND $item_end_time > '$time';");
        return self::to_items($results);
    }

    /**
     * Determines the maximum occurring item concurrencies within a schedule.
     *
     * @param int $schedule_id The ID of the schedule in question.
     * @return int The maximum number of concurrent items.
     */
    public static function count_max_total_concurrencies($schedule_id) {
        $max = 0;
        foreach (self::get_all_items($schedule_id) as $item) {
            $concurrencies = $item->count_max_concurrencies();
            if ($concurrencies > $max) {
                $max = $concurrencies;
            }
        }
        return $max;
    }

    /**
     * Determines the maximum number of concurrencies for THIS item.
     *
     * @return int The maximum number of items concurrent to this one.
     */
    public function count_max_concurrencies() {
        global $wpdb;
        $item = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $item_start_time = self::START_TIME_COL_NAME;
        $item_end_time = self::END_TIME_COL_NAME;
        $item_schedule = self::SCHEDULE_COL_NAME;
        $item_weekday = self::WEEKDAY_COL_NAME;
        $this_weekday = $this->weekday;
        $this_schedule = $this->schedule->get_id();

        $max = 0;
        for ($t = $this->get_quartered_start_time(); $t < $this->get_quartered_end_time(); $t += 900) {
            $start_time = date('H:i', $t + 450);
            $end_time = date('H:i', $t - 450);
            $count = $wpdb->get_var("SELECT COUNT(*)
                    FROM $item
                    WHERE $item_schedule = $this_schedule
                    AND $item_weekday = '$this_weekday'
                    AND $item_start_time <= '$start_time'
                    AND $item_end_time > '$end_time';");
            if ($count > $max) {
                $max = $count;
            }
        }
        return $max;
    }

    /**
     * Inserts a new event into the database.
     *
     * @param string $title The title of the event.
     * @param string $description The description of the event.
     * @param string $thumbnail The thumbnail path for the event.
     * @param string $color The event color in HEX.
     * @param int $page The ID of the page the event is associated with.
     */

    /**
     * Inserts a new event item the database.
     *
     * @param string $weekday The weekday of the item.
     * @param string $start_time The start time of the item.
     * @param string $end_time The end time of the item.
     * @param int $event The ID of the event the item is associated with.
     * @param int $schedule The ID of the schedule the item is associated with.
     */
    public static function insert_item($weekday, $start_time, $end_time, $event, $schedule) {
        global $wpdb;
        $item = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $wpdb->insert($item, array(
            self::WEEKDAY_COL_NAME => $weekday,
            self::START_TIME_COL_NAME => $start_time,
            self::END_TIME_COL_NAME => $end_time,
            self::EVENT_COL_NAME => $event,
            self::SCHEDULE_COL_NAME => $schedule
        ));
    }

    /**
     * Deletes an item entirely from the database.
     *
     * @param int $id The ID of the item to remove.
     */
    public static function delete_item($id) {
        global $wpdb;
        $item = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $wpdb->delete($item, array(self::ID_COL_NAME => $id));
    }

    ////////////////////////////////////////////////////////////////////////////////
    /// getters and setters                                                      ///
    ////////////////////////////////////////////////////////////////////////////////

    public function get_id() {
        return $this->id;
    }

    public function get_weekday() {
        return $this->weekday;
    }

    public function get_start_time() {
        return $this->start_time;
    }

    public function get_quartered_start_time() {
        return round($this->start_time / 900) * 900;
    }

    public function get_end_time() {
        return $this->end_time;
    }

    public function get_quartered_end_time() {
        return round($this->end_time / 900) * 900;
    }

    public function get_duration_in_quarters() {
        return ($this->get_quartered_end_time() - $this->get_quartered_start_time()) / 900;
    }

    public function get_event() {
        return $this->event;
    }

    public function get_schedule() {
        return $this->schedule;
    }
}