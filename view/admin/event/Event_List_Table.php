<?php

/**
 * Tabular overview of events.
 */
class Event_List_Table extends WP_List_Table {

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    public function __construct() {
        parent::__construct(array(
            'singular' => __('event'), // Singular label
            'plural' => __('events'), // Plural label, also this will be one of the table css class
            'ajax' => false // We won't support Ajax for this table
        ));
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array of columns to use with the table
     */
    function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            Event::TITLE_COL_NAME => __('Title'),
            Event::COLOR_COL_NAME => __('Color'),
            Event::PAGE_COL_NAME => __('Page'),
            Event::ID_COL_NAME => '' // hidden column
        );
    }

    /**
     * Decide which columns to activate the sorting functionality on
     * @return array of columns that can be sorted by the user
     */
    public function get_sortable_columns() {
        return array(
            Event::TITLE_COL_NAME => array(Event::TITLE_COL_NAME, true),
            Event::PAGE_COL_NAME => array(get_the_title(Event::PAGE_COL_NAME), false)
        );
    }

    /**
     * Define which columns are hidden
     */
    public function get_hidden_columns() {
        return array(Event::ID_COL_NAME);
    }

    /** Text displayed when no event is available */
    public function no_items() {
        _e('No events exist. Add one using the "Create" button!', Scheduler::TEXTDOMAIN);
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case Event::ID_COL_NAME:
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox.
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%s[]" value="%s" />',
            Event::ID_COL_NAME,
            $item[Event::ID_COL_NAME]);
    }

    /**
     * Method for thumbnail column.
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_thumbnail($item) {
        return sprintf('<div class="event-thumbnail"><a href="?page=%s&action=%s&%s=%s"><img src="%s" /></a></div>',
            esc_attr($_REQUEST['page']), Event_Menu::ACTION_EDIT, Event::ID_COL_NAME,
            absint($item[Event::ID_COL_NAME]), $item[Event::THUMBNAIL_COL_NAME]);
    }

    /**
     * Method for name column.
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_title($item) {
        $title = sprintf('<div class="skd-event-thumbnail"><img src="%s" /></div><strong><a href="?page=%s&action=%s&%s=%s">' . $item[Event::TITLE_COL_NAME] . '</a></strong>',
            $item[Event::THUMBNAIL_COL_NAME],
            esc_attr($_REQUEST['page']), Event_Menu::ACTION_EDIT, Event::ID_COL_NAME, absint($item[Event::ID_COL_NAME]));
        $actions = array(
            Event_Menu::ACTION_EDIT => sprintf('<a href="?page=%s&action=%s&%s=%s">' . __('Edit', Scheduler::TEXTDOMAIN) . '</a>',
                esc_attr($_REQUEST['page']), Event_Menu::ACTION_EDIT, Event::ID_COL_NAME, absint($item[Event::ID_COL_NAME])),
            Event_Menu::ACTION_DELETE => sprintf('<a href="?page=%s&action=%s&%s=%s" class="delete-button">'
                . __('Delete', Scheduler::TEXTDOMAIN) . '</a>',
                esc_attr($_REQUEST['page']), Event_Menu::ACTION_DELETE, Event::ID_COL_NAME, absint($item[Event::ID_COL_NAME]))
        );
        return $title . $this->row_actions($actions);
    }

    /**
     * Method for color column.
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_color($item) {
        return sprintf('<div class="color-box" style="background: %s;"></div>',
            esc_html($item[Event::COLOR_COL_NAME]));
    }

    function column_page($item) {
        return sprintf('<a href="%s" target="_blank">%s</a>',
            get_permalink($item[Event::PAGE_COL_NAME]),
            get_the_title($item[Event::PAGE_COL_NAME]));
    }

    /**
     * Returns an associative array containing the bulk action.
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
            Event_Menu::ACTION_BULK_DELETE => __('Delete', Scheduler::TEXTDOMAIN)
        );
        return $actions;
    }

    /**
     * Implement all bulk actions.
     */
    public function process_bulk_actions() {
        if ($this->current_action() === Event_Menu::ACTION_BULK_DELETE) {
            if (isset($_REQUEST[Event::ID_COL_NAME])) {
                foreach ($_REQUEST[Event::ID_COL_NAME] as $id) {
                    Event::delete_event($id);
                }
            }
        } else if ($this->current_action() === Event_Menu::ACTION_DELETE) {
            if (isset($_REQUEST[Event::ID_COL_NAME])) {
                Event::delete_event($_REQUEST[Event::ID_COL_NAME]);
            }
        }
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items() {
        /**
         * Define column headers.
         */
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        /**
         * Handle potential bulk actions.
         */
        $this->process_bulk_actions();

        /**
         * Fetch table contents from database.
         */
        $total_items = Event::count_events();
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        $orderby = !empty($_GET["orderby"]) ? esc_sql($_GET["orderby"]) : null;
        $order = !empty($_GET["order"]) ? esc_sql($_GET["order"]) : null;

        $items = array();
        $events = Event::get_events($orderby, $order, $per_page, $offset);
        foreach ($events as $event) {
            $items[] = array(
                Event::ID_COL_NAME => $event->get_id(),
                Event::TITLE_COL_NAME => $event->get_title(),
                Event::DESCRIPTION_COL_NAME => $event->get_description(),
                Event::THUMBNAIL_COL_NAME => $event->get_thumbnail(),
                Event::COLOR_COL_NAME => $event->get_color(),
                Event::PAGE_COL_NAME => $event->get_page()
            );
        }
        $this->items = $items;

        /**
         * Set pagination arguments.
         */
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
}
