<?php

/**
 * Class for generating a PDF schedule.
 */
class PDF_Generator {
    private $schedule;
    private $skd_options;
    private $fragmentation;
    private $column_count;
    private $td_width;

    /**
     * Constructs a new PDF generator.
     *
     * @param Schedule The schedule to generate a PDF for.
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
        $this->column_count = $visible_days + 1;
        $this->td_width = 100 / $this->column_count;

        function fac($n) {
            $fac = 1;
            for ($i = 1; $i <= $n; $i++) {
                $fac *= $i;
            }
            return $fac;
        }

        $this->fragmentation = fac(Item::count_max_total_concurrencies($this->schedule->get_id()));
    }

    /**
     * Generates PDF-ready version of the schedule
     *
     * @return string A HTML representation of the schedule optimized for PDF conversion
     */
    public function generate() {
        ob_start();
        ?>
        <style type="text/css">
            table {
                border-collapse: collapse;
                table-layout: fixed;
                border: none;
                page-break-inside: auto;
            }

            tbody tr:nth-child(even) td,
            tbody tr:nth-child(even) th {
                border-top: none;
            }

            tbody tr:nth-child(odd):not(:last-child) td,
            tbody tr:nth-child(odd):not(:last-child) th {
                border-bottom: none;
            }

            tbody tr:nth-child(4n+3),
            tbody tr:nth-child(4n+4) {
                background: rgba(232, 232, 232, 0.5);
            }

            td, th {
                font-size: 10px;
                line-height: 1.0;
            }

            tbody th, tbody td {
                border: 1px solid black;
                height: 15px;
            }

            .skd-schedule-cell {
                background: url("../../images/schedule-item-background.png") repeat-x;
            }

            .skd-item-date {
                font-size: 8px;
            }
        </style>
        <h1 style="text-align: center"><?php echo $this->schedule->get_title(); ?></h1>
        <table width="100%" border="1" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <?php
                    for ($i = 0; $i < $this->column_count * $this->fragmentation; $i++) {
                        ?>
                        <th width="<?php echo $this->td_width / $this->fragmentation; ?>%"></th>
                        <?php
                    }
                    ?>
                </tr>
                <tr>
                    <th align="center" colspan="<?php echo $this->fragmentation; ?>"
                        width="<?php echo $this->td_width; ?>%">
                        <?php _e('Time', Scheduler::TEXTDOMAIN); ?>
                    </th>
                    <?php
                    foreach (Scheduler::WEEKDAYS as $key => $val) {
                        if ($this->skd_options[$key][$this->schedule->get_id()]) {
                            ?>
                            <th align="center" colspan="<?php echo $this->fragmentation; ?>"
                                width="<?php echo $this->td_width; ?>%">
                                <?php echo Scheduler::translate_weekday($key); ?>
                            </th>
                            <?php
                        }
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $scope_start = strtotime($this->skd_options['scope_start'][$this->schedule->get_id()]);
                $scope_end = strtotime($this->skd_options['scope_end'][$this->schedule->get_id()]);

                for ($t = $scope_start; $t < $scope_end; $t += 900) {
                    $time = date('H:i', $t);

                    ?>
                    <tr>
                        <th align="center" class="skd-schedule-time"
                            colspan="<?php echo $this->fragmentation; ?>">
                            <?php if ($t % 1800 == 0) echo $time; ?>
                        </th>
                        <?php foreach (Scheduler::WEEKDAYS as $key => $val) {
                            if (!$this->skd_options[$key][$this->schedule->get_id()]) {
                                continue; // day won't be displayed
                            }
                            $items = Item::get_items_at_weekday_and_start_time($this->schedule->get_id(), $key, $t);
                            foreach ($items as $item) {
                                $concurrencies = $item->count_max_concurrencies();
                                $colspan = $this->fragmentation / $concurrencies;
                                $duration_in_quarters = $item->get_duration_in_quarters();
                                ?>
                                <td align="center" class="skd-schedule-cell" colspan="<?php echo $colspan; ?>"
                                    rowspan="<?php echo $duration_in_quarters; ?>"
                                    height="40px"
                                    style="background-color: <?php echo $item->get_event()->get_color(); ?>;
                                            border: 1px solid #000;">
                                        <span class="skd-item-date">
                                            <?php echo date('H:i', $item->get_start_time()); ?> -
                                            <?php echo date('H:i', $item->get_end_time()); ?><br/>
                                        </span>
                                    <span class="skd-event-name"><?php echo $item->get_event()->get_title(); ?></span>
                                </td>
                                <?php
                            }
                            $missing_span = $this->fragmentation;
                            $local_concurrencies = Item::get_local_concurrencies($this->schedule->get_id(), $key, $t);
                            foreach ($local_concurrencies as $local_concurrency) {
                                $missing_span -= $this->fragmentation / $local_concurrency->count_max_concurrencies();
                            }
                            if ($missing_span) {
                                echo '<td colspan="' . $missing_span . '"></td>';
                            }
                        }
                        ?>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }
}