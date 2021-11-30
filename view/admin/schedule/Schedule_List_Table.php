<?php

/**
 * Tabular overview of schedules.
 */
class Schedule_List_Table extends WP_List_Table {

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    public function __construct() {
        parent::__construct(array(
            'singular' => __('schedule'), // Singular label
            'plural' => __('schedules'), // Plural label, also this will be one of the table css class
            'ajax' => false // We won't support Ajax for this table
        ));
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {
        return $columns = array(
            'cb' => '<input type="checkbox" />',
            Schedule::TITLE_COL_NAME => __('Title'),
            Schedule::ID_COL_NAME => __('ID'),
            'shortcode' => __('Shortcode')
        );
    }

    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns() {
        return array(
            Schedule::ID_COL_NAME => array(Schedule::ID_COL_NAME, false),
            Schedule::TITLE_COL_NAME => array(Schedule::TITLE_COL_NAME, true)
        );
    }

    /**
     * Define which columns are hidden
     */
    public function get_hidden_columns() {
        return array();
    }

    /** Text displayed when no schedule is available */
    public function no_items() {
        _e('No schedules exist. Add one using the "Create" button!', Scheduler::TEXTDOMAIN);
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
            case Schedule::ID_COL_NAME:
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%s[]" value="%s" />',
            Schedule::ID_COL_NAME,
            $item[Schedule::ID_COL_NAME]);
    }

    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_title($item) {
        $title = sprintf('<strong><a href="?page=%s&action=%s&id=%s">' . $item[Schedule::TITLE_COL_NAME] . '</a></strong>',
            esc_attr($_REQUEST['page']), Schedule_Menu::ACTION_EDIT, absint($item[Schedule::ID_COL_NAME]));
        $actions = array(
            Schedule_Menu::ACTION_EDIT => sprintf('<a href="?page=%s&action=%s&id=%s">' . __('Edit', Scheduler::TEXTDOMAIN) . '</a>',
                esc_attr($_REQUEST['page']), Schedule_Menu::ACTION_EDIT, absint($item[Schedule::ID_COL_NAME])),
            Schedule_Menu::ACTION_DELETE => sprintf('<a href="?page=%s&action=%s&id=%s" class="delete-button">'
                . __('Delete', Scheduler::TEXTDOMAIN) . '</a>',
                esc_attr($_REQUEST['page']), Schedule_Menu::ACTION_DELETE, absint($item[Schedule::ID_COL_NAME]))
        );
        return $title . $this->row_actions($actions);
    }

    /**
     * Method for shortcode column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_shortcode($item) {
        return sprintf('<input type="text" class="shortcode-textbox" value="%s" readonly="readonly" />',
            esc_html('[' . Scheduler::SHORTCODE_POOL[0] . ' id="' . $item[Schedule::ID_COL_NAME] . '"]'));

    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
            Schedule_Menu::ACTION_BULK_DELETE => __('Delete', Scheduler::TEXTDOMAIN)
        );
        return $actions;
    }


    /**
     * Implement all bulk actions.
     */
    public function process_bulk_actions() {
        if ($this->current_action() === Schedule_Menu::ACTION_BULK_DELETE) {
            if (isset($_REQUEST[Schedule::ID_COL_NAME])) {
                foreach ($_REQUEST[Schedule::ID_COL_NAME] as $id) {
                    Schedule::delete_schedule($id);
                }
            }
        } else if ($this->current_action() === Schedule_Menu::ACTION_DELETE) {
            if (isset($_REQUEST[Schedule::ID_COL_NAME])) {
                Schedule::delete_schedule($_REQUEST[Schedule::ID_COL_NAME]);
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
        global $wpdb;
        $schedule_table = $wpdb->prefix . Scheduler::DB_PREFIX . Schedule::TABLE_NAME;
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $schedule_table");
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        $orderby = !empty($_GET["orderby"]) ? esc_sql($_GET["orderby"]) : Schedule::TITLE_COL_NAME;
        $order = !empty($_GET["order"]) ? esc_sql($_GET["order"]) : 'ASC';

        $schedules = Schedule::get_schedules($orderby, $order, $per_page, $offset);
        $items = array();
        foreach ($schedules as $schedule) {
            $items[] = array(
                Schedule::ID_COL_NAME => $schedule->get_id(),
                Schedule::TITLE_COL_NAME => $schedule->get_title(),
                Schedule::DESCRIPTION_COL_NAME => $schedule->get_description()
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
