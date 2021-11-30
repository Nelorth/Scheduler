<?php

/**
 * Schedule edit menu in the admin area.
 */
class Schedule_Menu {
    const ACTION_DELETE = 'delete';
    const ACTION_BULK_DELETE = 'bulk-delete';
    const ACTION_EDIT = 'edit';
    const EDIT_TABS = array(
        'general' => 'General',
        'items' => 'Items',
        'settings' => 'Settings'
    );

    public function display() {
        echo '<div class="wrap">';
        if (isset($_GET['action']))
            switch ($_GET['action']) {
                case 'add' :
                    $this->display_add();
                    break;
                case 'edit':
                    $this->display_edit();
                    break;
                default:
                    $this->display_summary();
            }
        else {
            $this->display_summary();
        }
        echo '</div>';
    }

    private function display_summary() {
        require_once 'schedule/Schedule_Summary_View.php';
        $schedule_summary = new Schedule_Summary_View();
        $schedule_summary->render();
    }

    private function display_add() {
        require_once 'schedule/Schedule_Edit_View.php';
        $schedule_edit = new Schedule_Edit_View();
        $schedule_edit->render();
    }

    private function display_edit() {
        if (isset($_GET['tab'])) {
            $tab = $_GET['tab'];
        } else {
            $tab = 'general';
        }
        $this->display_edit_tabs($tab);
        switch ($tab) {
            case 'general':
                $this->display_edit_general();
                break;
            case 'items':
                $this->display_edit_items();
                break;
            case 'settings':
                $this->display_edit_settings();
                break;
            default:
                wp_die(__('Unspecified edit tab!', Scheduler::TEXTDOMAIN));
        }
    }

    private function display_edit_general() {
        require_once 'schedule/Schedule_Edit_View.php';
        $schedules_edit = new Schedule_Edit_View();
        $schedules_edit->render();
    }

    private function display_edit_items() {
        if (isset($_GET['id'])) {
            require_once 'schedule/Schedule_Items_View.php';
            $schedule = Schedule::from_id($_GET['id']);
            $schedule_items = new Schedule_Items_View($schedule);
            $schedule_items->render();
        } else {
            wp_die('Unspecified schedule id!', Scheduler::TEXTDOMAIN);
        }
    }

    private function display_edit_settings() {
        if (isset($_GET['id'])) {
            require_once 'schedule/Schedule_Settings_View.php';
            $schedule_settings = new Schedule_Settings_View(Schedule::from_id($_GET['id']));
            $schedule_settings->render();
        } else {
            wp_die('Unspecified schedule id!', Scheduler::TEXTDOMAIN);
        }
    }

    private function display_edit_tabs($tab) {
        ?>
        <nav class="nav-tab-wrapper">
            <?php foreach (self::EDIT_TABS as $key => $val) : ?>
                <?php
                $active = $key === $tab ? ' nav-tab-active' : '';
                $href = '?' . $_SERVER['QUERY_STRING'] . '&tab=' . $key;
                ?>
                <a class="nav-tab <?php echo $active; ?>" href="<?php echo $href; ?>">
                    <?php
                    switch ($key) {
                        case 'general':
                            _e('General', Scheduler::TEXTDOMAIN);
                            break;
                        case 'items':
                            _e('Items', Scheduler::TEXTDOMAIN);
                            break;
                        case 'settings':
                            _e('Settings', Scheduler::TEXTDOMAIN);
                            break;
                    }
                    ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php
    }

    public static function handle_schedule_add_response() {
        if (isset(
            $_POST['schedule-title'],
            $_POST['schedule-description']
        )) {
            Schedule::add_schedule($_POST['schedule-title'],
                $_POST['schedule-description']);
        }
        wp_redirect(admin_url('admin.php?page=') . Admin_Controller::SCHEDULES_PAGE);
    }

    public static function handle_schedule_edit_response() {
        if (isset(
            $_POST['schedule-id'],
            $_POST['schedule-title'],
            $_POST['schedule-description']
        )) {
            Schedule::update_schedule(
                $_POST['schedule-id'],
                $_POST['schedule-title'],
                $_POST['schedule-description']
            );
        }
        wp_redirect(admin_url('admin.php?page=') . Admin_Controller::SCHEDULES_PAGE);
    }
}