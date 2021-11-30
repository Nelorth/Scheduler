<?php

/**
 * Representation of a recurring event in the database.
 */
class Event {
    const TABLE_NAME = 'event';
    const ID_COL_NAME = 'id';
    const TITLE_COL_NAME = 'title';
    const DESCRIPTION_COL_NAME = 'description';
    const THUMBNAIL_COL_NAME = 'thumbnail';
    const COLOR_COL_NAME = 'color';
    const PAGE_COL_NAME = 'page';

    private $id;
    private $title;
    private $description;
    private $thumbnail;
    private $color;
    private $page;

    /**
     * Constructs a new event from the given information.
     *
     * @param int $id The ID of the event.
     * @param string $title The title of the event.
     * @param string $description The description of the event.
     * @param string $thumbnail The thumbnail path for the event.
     * @param string $color The event color in HEX.
     * @param int $page The ID of the page the event is associated with.
     */
    public function __construct($id, $title, $description, $thumbnail, $color, $page) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->thumbnail = $thumbnail;
        $this->color = $color;
        $this->page = $page;
    }

    /**
     * Factory method for constructing an empty event.
     *
     * @return Event An empty event.
     */
    public static function construct_default() {
        return new self('', '', '', '', '#0086cd', '');
    }

    /**
     * Factory method for constructing an event from a database ID.
     *
     * @param int $id The ID of the desired event in the database.
     * @return Event A fully initialized event.
     */
    public static function from_id($id) {
        global $wpdb;
        $event = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $event_id = self::ID_COL_NAME;
        $row = $wpdb->get_row("SELECT * FROM $event WHERE $event_id = $id;");
        return new Event($row->id, $row->title, $row->description, $row->thumbnail, $row->color, $row->page);
    }

    /**
     * Factory method for constructing an event from a database record.
     *
     * @param Object $result A database row.
     * @return Event The event object defined by the row.
     */
    private static function from_record($result) {
        return new Event(
            $result->id,
            $result->title,
            $result->description,
            $result->thumbnail,
            $result->color,
            $result->page
        );
    }

    /**
     * Turns a collection of database records into a list of events.
     *
     * @param Object $results A collection of database records resembling events.
     * @return array The corresponding list of event objects.
     */
    private static function to_events($results) {
        $events = array();
        foreach ($results as $result) {
            $events[] = self::from_record($result);
        }
        return $events;
    }


    /**
     * Retrieves all events from the database.
     *
     * @return array List of all events.
     */
    public static function get_all_events() {
        global $wpdb;
        $event = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $results = $wpdb->get_results("SELECT * FROM $event;");
        return self::to_events($results);
    }

    /**
     * Retrieves events in a paginated fashion with sorting.
     *
     * @param string $orderby The attribute to order the results by.
     * @param string $order ASC or DESC.
     * @param $per_page Number of items per page.
     * @param int $offset The current page number.
     * @return array The events corresponding to the selection.
     */
    public static function get_events($orderby, $order, $per_page, $offset) {
        global $wpdb;
        $event = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $orderby = !empty($orderby) ? esc_sql($orderby) : Event::TITLE_COL_NAME;
        $order = !empty($order) ? esc_sql($order) : 'ASC';
        $results = $wpdb->get_results("SELECT * FROM $event
            ORDER BY $orderby $order
            LIMIT $per_page
            OFFSET $offset;"
        );
        return self::to_events($results);
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
    public static function add_event($title, $description, $thumbnail, $color, $page) {
        global $wpdb;
        $event = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $wpdb->insert($event, array(
            self::TITLE_COL_NAME => $title,
            self::DESCRIPTION_COL_NAME => $description,
            self::THUMBNAIL_COL_NAME => $thumbnail,
            self::COLOR_COL_NAME => $color,
            self::PAGE_COL_NAME => $page
        ));
    }

    /**
     * Updates an existing event in the database.
     *
     * @param int $id The ID of the event.
     * @param string $title The title of the event.
     * @param string $description The description of the event.
     * @param string $thumbnail The thumbnail path for the event.
     * @param string $color The event color in HEX.
     * @param int $page The ID of the page the event is associated with.
     */
    public static function update_event($id, $title, $description, $thumbnail, $color, $page) {
        global $wpdb;
        $event = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $wpdb->update($event, array(
            self::TITLE_COL_NAME => $title,
            self::DESCRIPTION_COL_NAME => $description,
            self::THUMBNAIL_COL_NAME => $thumbnail,
            self::COLOR_COL_NAME => $color,
            self::PAGE_COL_NAME => $page
        ), array(self::ID_COL_NAME => $id));
    }

    /**
     * Deletes an event entirely from the database.
     *
     * @param int $id The ID of the event to remove.
     */
    public static function delete_event($id) {
        global $wpdb;
        $event = $wpdb->prefix . Scheduler::DB_PREFIX . self::TABLE_NAME;
        $wpdb->delete(
            $event, array(self::ID_COL_NAME => $id)
        );
    }

    /**
     * Determines the total number of events in the database.
     *
     * @return int The number of events.
     */
    public static function count_events() {
        global $wpdb;
        $event = $wpdb->prefix . Scheduler::DB_PREFIX . Event::TABLE_NAME;
        return $wpdb->get_var("SELECT COUNT(*) FROM $event");
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

    public function get_thumbnail() {
        return $this->thumbnail;
    }

    public function get_color() {
        return $this->color;
    }

    public function get_page() {
        return $this->page;
    }
}