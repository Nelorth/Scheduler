<?php

/**
 * Single event menu in the admin area.
 */
class Event_Menu {
    const ACTION_EDIT = 'edit';
    const ACTION_ADD = 'add';
    const ACTION_DELETE = 'delete';
    const ACTION_BULK_DELETE = 'bulk-delete';

    public function display() {
        if (isset($_REQUEST['action'])) {
            switch ($_REQUEST['action']) {
                case self::ACTION_ADD:
                case self::ACTION_EDIT:
                    $this->display_edit();
                    break;
                default:
                    $this->display_overview();
            }
        } else {
            $this->display_overview();
        }
    }

    private function display_overview() {
        require_once 'event/Event_Summary_View.php';
        $events_overview = new Event_Summary_View();
        $events_overview->render();
    }

    private function display_edit() {
        require_once 'event/Event_Edit_View.php';
        $events_edit = new Event_Edit_View();
        $events_edit->render();
    }

    public static function handle_event_add_response() {
        if (isset(
            $_POST['event-title'],
            $_POST['event-description'],
            $_POST['event-thumbnail'],
            $_POST['event-color'],
            $_POST['event-page']
        )) {
            Event::add_event(
                $_POST['event-title'],
                $_POST['event-description'],
                $_POST['event-thumbnail'],
                $_POST['event-color'],
                $_POST['event-page']
            );
        }
        wp_redirect(admin_url('admin.php?page=') . Admin_Controller::EVENTS_PAGE);
    }

    public static function handle_event_edit_response() {
        if (isset(
            $_POST['event-id'],
            $_POST['event-title'],
            $_POST['event-description'],
            $_POST['event-thumbnail'],
            $_POST['event-color'],
            $_POST['event-page']
        )) {
            Event::update_event(
                $_POST['event-id'],
                $_POST['event-title'],
                $_POST['event-description'],
                $_POST['event-thumbnail'],
                $_POST['event-color'],
                $_POST['event-page']
            );
        }
        wp_redirect(admin_url('admin.php?page=') . Admin_Controller::EVENTS_PAGE);
    }
}