<?php

/**
 * Subview for editing items of a schedule.
 */
class Schedule_Items_View {
    private $schedule;

    public function __construct(Schedule $schedule) {
        $this->schedule = $schedule;
        $this->handle_requests();
    }

    public function render() {
        $this->print_page_header();
        $this->print_add_section();
        $this->print_edit_section();
    }

    private function print_page_header() {
        ?>
        <h1 class="wp-heading-inline">
            <?php echo $this->schedule->get_title() . ' » ' . __('Edit items', Scheduler::TEXTDOMAIN); ?>
        </h1>
        <hr class="wp-header-end"/>
        <?php
    }

    private function print_add_section() {
        ?>
        <h2><?php _e('Add item'); ?></h2>
        <form method="post" id="add-item-form">
            <input type="hidden" name="edit-action" value="add-item"/>
            <input type="hidden" name="item-schedule" value="<?php echo $_GET['id']; ?>"/>
            <label for="item-event"><?php _e('Event', Scheduler::TEXTDOMAIN); ?></label>
            <select name="item-event" id="item-event">
                <?php foreach (Event::get_all_events() as $event) : ?>
                    <option value="<?php echo $event->get_id(); ?>">
                        <?php echo $event->get_title(); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="item-weekday"><?php _e('on', Scheduler::TEXTDOMAIN); ?></label>
            <select name="item-weekday" id="item-weekday">
                <?php foreach (Scheduler::WEEKDAYS as $key => $val) : ?>
                    <option value="<?php echo $key; ?>">
                        <?php echo Scheduler::translate_weekday($key); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="item-start-time"><?php _e('from', Scheduler::TEXTDOMAIN); ?></label>
            <input type="time" value="08:00" name="item-start-time" id="item-start-time" min="00:00" max="23:59"
                   required="required"/>
            <label for="item-end-time"><?php _e('to', Scheduler::TEXTDOMAIN); ?></label>
            <input type="time" value="10:00" name="item-end-time" id="item-end-time" min="00:00" max="23:59"
                   required="required"/>
            <?php submit_button(__('Add', Scheduler::TEXTDOMAIN), 'primary', 'submit', false); ?>
        </form>
        <?php
    }

    private function print_edit_section() {
        ?>
        <h2><?php _e('Registered items'); ?></h2>
        <div id="weekday-wrapper">
            <?php
            foreach (Scheduler::WEEKDAYS as $key => $day) {
                $this->print_weekday($key, $day);
            }
            ?>
        </div>
        <?php
    }

    private function print_weekday($key, $day) {
        $items = Item::get_items_at_weekday($this->schedule->get_id(), $key);
        ?>
        <div class="weekday">
            <h3><?php echo __($day, Scheduler::TEXTDOMAIN); ?></h3>
            <hr/>
            <?php
            if (!count($items)) {
                _e('No items.', Scheduler::TEXTDOMAIN);
            }
            foreach ($items as $item) {
                $this->print_weekday_item($item);
            }
            ?>
        </div>
        <?php
    }

    private function print_weekday_item(Item $item) {
        ?>
        <form method="post">
            <input type="hidden" name="edit-action" value="delete-item"/>
            <input type="hidden" name="item-schedule" value="<?php echo $item->get_id(); ?>"/>
            <strong><?php echo $item->get_event()->get_title(); ?></strong>
            <p>
                <strong><?php echo date('H:i', $item->get_start_time()); ?></strong>
                <?php _e('to', Scheduler::TEXTDOMAIN); ?>
                <strong><?php echo date('H:i', $item->get_end_time()); ?></strong>
            </p>
            <div>
                <input type="submit" class="button button-primary"
                       value="<?php _e('Delete', Scheduler::TEXTDOMAIN); ?>"/>
            </div>
        </form>
        <hr/>
        <?php
    }

    private function handle_requests() {
        if (isset($_POST['edit-action'])) {
            switch ($_POST['edit-action']) {
                case 'add-item':
                    if (isset(
                        $_POST['item-schedule'],
                        $_POST['item-weekday'],
                        $_POST['item-start-time'],
                        $_POST['item-end-time'],
                        $_POST['item-event']
                    )) {
                        Item::insert_item(
                            $_POST['item-weekday'],
                            $_POST['item-start-time'],
                            $_POST['item-end-time'],
                            $_POST['item-event'],
                            $_POST['item-schedule']
                        );
                    }
                    break;
                case 'delete-item':
                    if (isset($_POST['item-schedule'])) {
                        Item::delete_item($_POST['item-schedule']);
                    }
                    break;
                default:
                    wp_die(__('No valid edit action!', Scheduler::TEXTDOMAIN));
            }
        }
    }
}