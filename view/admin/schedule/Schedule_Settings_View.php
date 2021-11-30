<?php

/**
 * Subview for the settings of a schedule.
 */
class Schedule_Settings_View {
    private $schedule;

    public function __construct(Schedule $schedule) {
        $this->schedule = $schedule;
        $this->handle_requests();
    }

    public function render() {
        $this->print_page_header();
    }

    private function print_page_header() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');

        $skd_options = get_option(Scheduler::OPTIONS);
        ?>
        <script type="text/javascript">
            jQuery(function ($) {
                $(document).ready(function ($) {
                    $('#accent-color').wpColorPicker();
                });
            });
        </script>
        <h1 class="wp-heading-inline">
            <?php echo $this->schedule->get_title() . ' » ' . __('Schedule settings', Scheduler::TEXTDOMAIN); ?>
        </h1>
        <hr class="wp-header-end"/>
        <form method="post">
            <input type="hidden" name="schedule-id" value="<?php echo $this->schedule->get_id(); ?>"/>
            <table class="form-table">
                <tr>
                    <th><label for="accent-color"><?php _e('Accent color', Scheduler::TEXTDOMAIN); ?></label></th>
                    <td>
                        <input type="text" id="accent-color" name="accent-color"
                               value="<?php echo $skd_options[Scheduler::ACCENT_COLOR_OPTION][$this->schedule->get_id()]; ?>"
                               data-default-color="#0086cd"/>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Visible weekdays', Scheduler::TEXTDOMAIN); ?></th>
                    <td>
                        <?php foreach (Scheduler::WEEKDAYS as $key => $val) : ?>
                            <label for="<?php echo $key; ?>">
                                <?php $checked = $skd_options[$key][$this->schedule->get_id()] ? 'checked="checked"' : ''; ?>
                                <input type="checkbox" name="<?php echo $key; ?>" <?php echo $checked; ?> />
                                <span><?php echo Scheduler::translate_weekday($key); ?></span>
                            </label>
                            <br/>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Schedule scope', Scheduler::TEXTDOMAIN); ?></th>
                    <td>
                        <label for="scope-start"><?php _e('From', Scheduler::TEXTDOMAIN); ?></label>
                        <select name="scope-start" id="scope-start">
                            <?php $this->print_time_options($skd_options['scope_start'][$this->schedule->get_id()]); ?>
                        </select>
                        <label for="scope-end"><?php _e('to', Scheduler::TEXTDOMAIN); ?></label>
                        <select name="scope-end" id="scope-end">
                            <?php $this->print_time_options($skd_options['scope_end'][$this->schedule->get_id()]); ?>
                        </select>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save schedule', Scheduler::TEXTDOMAIN)); ?>
        </form>
        <?php
    }

    private function print_time_options($current_time) {
        for ($i = 0; $i < 15 * 4 * 24; $i += 15) : ?>
            <?php $time = sprintf('%02d:%02d', floor($i / 60), $i % 60); ?>
            <?php $selected = $time === $current_time ? 'selected="selected"' : ''; ?>
            <option value="<?php echo $time; ?>" <?php echo $selected; ?>>
                <?php echo $time; ?>
            </option>
        <?php endfor;
    }

    private function handle_requests() {
        if (isset($_POST['schedule-id'])) {
            $skd_options = get_option(Scheduler::OPTIONS);
            foreach (Scheduler::WEEKDAYS as $key => $val) {
                if (isset($_POST[$key])) {
                    $skd_options[$key][strval($this->schedule->get_id())] = 1;
                } else {
                    $skd_options[$key][strval($this->schedule->get_id())] = 0;
                }
            }
            if (isset($_POST['accent-color'])) {
                $skd_options[Scheduler::ACCENT_COLOR_OPTION][$this->schedule->get_id()] = $_POST['accent-color'];
            }
            if (isset($_POST['scope-start'])) {
                $skd_options[Scheduler::SCOPE_START_OPTION][$this->schedule->get_id()] = $_POST['scope-start'];
            }
            if (isset($_POST['scope-end'])) {
                $skd_options[Scheduler::SCOPE_END_OPTION][$this->schedule->get_id()] = $_POST['scope-end'];
            }
            update_option(Scheduler::OPTIONS, $skd_options);
        }
    }
}