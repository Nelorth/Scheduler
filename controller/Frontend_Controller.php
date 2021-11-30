<?php

/**
 * Controller for the frontend where a schedule is displayed.
 */
class Frontend_Controller {

    /**
     * Initializes the schedule frontend.
     */
    public function setup() {
        if (isset($_GET['pdf'])) {
            require_once __DIR__ . '/../view/public/PDF_Generator.php';
            $schedule = Schedule::from_id($_GET['pdf']);

            require_once __DIR__ . '/../vendor/autoload.php';
            try {
                $mpdf = new \Mpdf\Mpdf(array('format' => 'A4-P'));

                $pdf_title = $file_name = preg_replace('/[^a-z0-9]+/', '-',
                    strtolower($schedule->get_title()));
                $pdf_generator = new PDF_Generator($schedule);

                $mpdf->WriteHTML($pdf_generator->generate());
                ob_clean();
                $mpdf->Output(strtolower($pdf_title) . '.pdf', 'D');
                exit;
            } catch (\Mpdf\MpdfException $e) {
                echo $e->getMessage();
            }
        }

        add_action('init', array($this, 'enqueue_public_scripts'));
        foreach (Scheduler::SHORTCODE_POOL as $shortcode) {
            add_shortcode($shortcode, array($this, 'resolve_shortcode'));
        }
    }

    /**
     * Registers scripts and stylesheet for frontend display.
     */
    function enqueue_public_scripts() {
        wp_enqueue_style('skd-public-style', plugins_url('../css/public.css', __FILE__));
        wp_enqueue_script('jquery-colorbox', plugins_url('../js/jquery.colorbox-min.js', __FILE__), array('jquery'));
        wp_enqueue_style('jquery-colorbox-style', plugins_url('../css/colorbox/colorbox.css', __FILE__));
    }

    /**
     * Resolves the 'schedule' shortcode and generates the corresponding HTML output.
     *
     * @param array $args The arguments from the shortcode definition.
     * @return string The generated HTML parametrized by the shortcode.
     */
    public function resolve_shortcode($args = array()) {
        // Normalize shortcode string to lower case
        $args = array_change_key_case($args, CASE_LOWER);

        // Match user args with allowed args
        $args = shortcode_atts(array(Schedule::ID_COL_NAME => null), $args);

        $id = $args[Schedule::ID_COL_NAME];
        if (Schedule::schedule_exists($id)) {
            require_once(__DIR__ . '/../view/public/Schedule_View.php');
            $schedule = Schedule::from_id($id);
            $schedule_view = new Schedule_View($schedule);
            return $schedule_view->render();
        } else {
            return sprintf(__('No schedule exists with the specified ID %s. Check your shortcode!', Scheduler::TEXTDOMAIN), $id);
        }
    }
}