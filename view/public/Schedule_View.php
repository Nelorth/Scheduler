<?php

/**
 * Class for displaying a schedule in the frontend.
 */
class Schedule_View {
    private $schedule;
    private $skd_options;
    private $fragmentation;
    private $td_width;

    /**
     * Constructs a new schedule view.
     *
     * @param Schedule The schedule to display.
     */
    public function __construct(Schedule $schedule) {
        $this->schedule = $schedule;
        $this->skd_options = get_option(Scheduler::OPTIONS);

        $visible_days = 0;
        foreach (Scheduler::WEEKDAYS as $key => $val) {
            if ($this->skd_options[$key][$this->schedule->get_id()]) {
                $visible_days++;
            }
        }
        $this->td_width = 100 / ($visible_days + 1);

        $this->fragmentation = $this->fac(Item::count_max_total_concurrencies($this->schedule->get_id()));
    }

    /**
     * Renders the schedule to HTML.
     *
     * @return string HTMl representation of the schedule.
     */
    public function render() {
        ob_start();
        $theme_color = get_option(Scheduler::OPTIONS)[Scheduler::ACCENT_COLOR_OPTION][$this->schedule->get_id()];
        ?>
        <style type="text/css">
            .skd-schedule .skd-schedule-wrapper table thead {
                background-color: <?php echo $theme_color; ?>;
            }

            .skd-schedule .skd-schedule-wrapper table thead th {
                border-color: <?php echo $theme_color; ?>;
            }

            .skd-pdf-link a {
                color: <?php echo $theme_color; ?>;
            }
        </style>
        <div class="skd-schedule">
            <?php
            $this->print_schedule_header();
            $this->print_schedule();
            ?>
            <div class="skd-pdf-link">
                <a href="?pdf=<?php echo $this->schedule->get_id(); ?>">
                    <?php _e('Download PDF', Scheduler::TEXTDOMAIN); ?>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Prints the schedule.
     */
    public function print_schedule() {
        ?>
        <div class="skd-schedule-wrapper">
            <table>
                <thead>
                    <?php $this->print_table_head(); ?>
                </thead>
                <tbody>
                    <?php $this->print_table_body(); ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Prints the schedule header.
     */
    private function print_schedule_header() {
        ?>
        <h2><?php echo $this->schedule->get_title(); ?></h2>
        <div class="skd-schedule-description"><?php echo $this->schedule->get_description(); ?></div>
        <?php
    }

    /**
     * Prints the schedule table header.
     */
    private function print_table_head() {
        ?>
        <th colspan="<?php echo $this->fragmentation; ?>" width="<?php echo $this->td_width; ?>%">
            <?php _e('Time', Scheduler::TEXTDOMAIN); ?>
        </th>
        <?php
        foreach (Scheduler::WEEKDAYS as $key => $val) {
            if ($this->skd_options[$key][$this->schedule->get_id()]) {
                ?>
                <th colspan="<?php echo $this->fragmentation; ?>"
                    width="<?php echo $this->td_width; ?>%">
                    <?php echo Scheduler::translate_weekday($key); ?>
                </th>
                <?php
            }
        }
    }

    /**
     * Prints the schedule table content.
     */
    private function print_table_body() {
        $scope_start = strtotime($this->skd_options['scope_start'][$this->schedule->get_id()]);
        $scope_end = strtotime($this->skd_options['scope_end'][$this->schedule->get_id()]);

        for ($t = $scope_start; $t < $scope_end; $t += 900) {
            $this->print_row($t);
        }
    }

    /**
     * Prints a single schedule table cell.
     *
     * @param Item $item The item to display in the cell.
     */
    private function print_cell(Item $item) {
        $concurrencies = $item->count_max_concurrencies();
        $colspan = $this->fragmentation / $concurrencies;
        $duration_in_quarters = $item->get_duration_in_quarters();
        ?>
        <td class="skd-schedule-cell" colspan="<?php echo $colspan; ?>" rowspan="<?php echo $duration_in_quarters; ?>"
            width="<?php echo $this->td_width * ($colspan / $this->fragmentation); ?>%">
            <a class="skd-schedule-item" id="skd-schedule-open-<?php echo $item->get_id(); ?>"
               href="<?php echo get_the_permalink($item->get_event()->get_page()); ?>"
               style="background-color: <?php echo $item->get_event()->get_color(); ?>;">
                <dl class="skd-schedule-item-info">
                    <dt class="skd-schedule-item-time">
                        <?php echo date('H:i', $item->get_start_time()); ?> -
                        <?php echo date('H:i', $item->get_end_time()); ?><br/>
                    </dt>
                    <dd class="skd-schedule-item-title"><?php echo $item->get_event()->get_title(); ?></dd>
                </dl>
            </a>
            <?php $this->print_colorbox_content($item); ?>
            <script language="javascript" type="text/javascript">
                jQuery(function ($) {
                    $(window).ready(function () {
                        $("#skd-schedule-open-<?php echo $item->get_id(); ?>").colorbox({
                            html: $("#skd-schedule-overlay-<?php echo $item->get_id(); ?>").html(),
                            className: 'skd-schedule-colorbox',
                            onComplete: function () {
                                $(this).colorbox.resize();
                            }
                        });
                    });
                });
            </script>
        </td>
        <?php
    }

    /**
     * Prints the colorbox overlay for the given item.
     *
     * @param Item The item to print an overlay for.
     */
    private function print_colorbox_content(Item $item) {
        ?>
        <div id="skd-schedule-overlay-<?php echo $item->get_id(); ?>" class="skd-schedule-overlay">
            <div class="skd-schedule-event-thumbnail">
                <img src="<?php echo $item->get_event()->get_thumbnail(); ?>"/>
            </div>
            <div class="skd-schedule-event-content">
                <div class="skd-schedule-event-title">
                    <?php echo $item->get_event()->get_title(); ?>
                </div>
                <div class="skd-schedule-event-description">
                    <?php echo $item->get_event()->get_description(); ?>
                </div>
            </div>

            <?php if (!empty($item->get_event()->get_page())) : ?>
                <a href="<?php echo get_the_permalink($item->get_event()->get_page()); ?>"
                   class="skd-schedule-link" target="_blank">
                    <?php _e('More Info', Scheduler::TEXTDOMAIN); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Prints a schedule table row for a given time slot.
     *
     * @param Object $time The time of the row to print.
     */
    private function print_row($time) {
        ?>
        <tr valign="top">
            <th class="skd-schedule-time" colspan="<?php echo $this->fragmentation; ?>">
                <?php if ($time % 1800 == 0) echo date('H:i', $time); ?>
            </th>
            <?php foreach (Scheduler::WEEKDAYS as $key => $val) {
                if (!$this->skd_options[$key][$this->schedule->get_id()]) {
                    continue; // day won't be displayed
                }
                $items = Item::get_items_at_weekday_and_start_time($this->schedule->get_id(), $key, $time);
                foreach ($items as $item) {
                    $this->print_cell($item);
                }
                $this->fill_cells($key, $time);
            }
            ?>
        </tr>
        <?php
    }

    /**
     * Pads remaining space in a time slot with dummy cells.
     *
     * @param string $day The weekday in question.
     * @param Object $time The time in question.
     */
    private function fill_cells($day, $time) {
        $missing_span = $this->fragmentation;
        $local_concurrencies = Item::get_local_concurrencies($this->schedule->get_id(), $day, $time);
        foreach ($local_concurrencies as $local_concurrency) {
            $missing_span -= $this->fragmentation / $local_concurrency->count_max_concurrencies();
        }
        if ($missing_span) {
            echo '<td colspan="' . $missing_span . '"></td>';
        }
    }

    /**
     * Computes the factorial of an integer.
     *
     * @param int $n The number to calculate the factorial of.
     * @return int The factorial n! of n.
     */
  	private function fac($n) {
  	    $fac = 1;
  	    for ($i = 1; $i <= $n; $i++) {
  	        $fac *= $i;
  	    }
  	    return $fac;
  	}
}