<?php

/**
 * Summary view of all schedules.
 */
class Schedule_Summary_View {
    private $table;

    public function __construct() {
        require('Schedule_List_Table.php');
        $this->table = new Schedule_List_Table();
        $this->table->prepare_items();
    }

    public function render() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Schedules', Scheduler::TEXTDOMAIN); ?></h1>
            <a class="page-title-action" href="?page=<?php echo Admin_Controller::SCHEDULES_PAGE; ?>&action=add">
                <?php _e('Create', Scheduler::TEXTDOMAIN) ?>
            </a>
            <hr class="wp-header-end"/>
            <h2><?php _e('Click on a schedule to edit it.', Scheduler::TEXTDOMAIN); ?></h2>
            <form method="get">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>"/>
                <?php $this->table->display(); ?>
            </form>
        </div>
        <?php
    }
}