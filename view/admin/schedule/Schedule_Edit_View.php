<?php

/**
 * Subview for editing a schedule
 */
class Schedule_Edit_View {
    private $page_title;
    private $response_name;
    private $submit_button_text;
    private $schedule;

    public function __construct() {
        $this->schedule = Schedule::construct_default();
        if ($_GET['action'] === Schedule_Menu::ACTION_EDIT) {
            if (isset($_REQUEST[Schedule::ID_COL_NAME])) {
                $this->schedule = Schedule::from_id($_REQUEST[Schedule::ID_COL_NAME]);
            }
            $this->page_title = $this->schedule->get_title() . ' » ' . __('Edit schedule', Scheduler::TEXTDOMAIN);
            $this->response_name = 'schedule_edit_response';
            $this->submit_button_text = __('Save schedule', Scheduler::TEXTDOMAIN);
        } else {
            $this->page_title = __('Add schedule', Scheduler::TEXTDOMAIN);
            $this->response_name = 'schedule_add_response';
            $this->submit_button_text = __('Add schedule', Scheduler::TEXTDOMAIN);
        }
    }

    public function render() {
        ?>
        <h1 class="wp-heading-inline"><?php echo $this->page_title; ?></h1>
        <hr class="wp-header-end"/>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
            <input type="hidden" name="action" value="<?php echo $this->response_name; ?>">
            <input type="hidden" name="schedule-id" value="<?php echo $this->schedule->get_id(); ?>">
            <h2>
                <label for="schedule-title"><?php _e('Title', Scheduler::TEXTDOMAIN); ?></label>
            </h2>
            <input type="text" id="schedule-title" name="schedule-title"
                   value="<?php echo $this->schedule->get_title(); ?>"/>
            <h2>
                <label for="schedule-description"><?php _e('Description', Scheduler::TEXTDOMAIN); ?></label>
            </h2>
            <?php wp_editor($this->schedule->get_description(), 'schedule-description') ?>

            <?php submit_button($this->submit_button_text); ?>
        </form>
        <?php
    }
}