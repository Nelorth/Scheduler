<?php

/**
 * Subview for editing an event.
 */
class Event_Edit_View {
    private $response_name;
    private $page_title;
    private $submit_button_text;
    private $event;

    public function __construct() {
        $this->event = Event::construct_default();
        if ($_GET['action'] === Event_Menu::ACTION_EDIT) {
            $this->response_name = 'event_edit_response';
            $this->page_title = __('Edit event', Scheduler::TEXTDOMAIN);
            $this->submit_button_text = __('Save event', Scheduler::TEXTDOMAIN);
            if (isset($_REQUEST[Event::ID_COL_NAME])) {
                $this->event = Event::from_id($_REQUEST[Event::ID_COL_NAME]);
            } else {
                wp_die(__('No event id specified!', Scheduler::TEXTDOMAIN));
            }
        } else {
            $this->response_name = 'event_add_response';
            $this->page_title = __('Add event', Scheduler::TEXTDOMAIN);
            $this->submit_button_text = __('Add event', Scheduler::TEXTDOMAIN);
        }
    }

    public function render() {
        $this->load_scripts_and_styles();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo $this->page_title; ?></h1>
            <hr class="wp-header-end"/>
            <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                <input type="hidden" name="action" value="<?php echo $this->response_name; ?>">
                <input type="hidden" name="event-id" value="<?php echo $this->event->get_id(); ?>">
                <input type="hidden" id="event-thumbnail" name="event-thumbnail" value="<?php echo $this->event->get_thumbnail(); ?>">
                <table class="form-table">
                    <tr>
                        <td>
                            <table width="100%">
                                <tr>
                                    <th>
                                        <h2>
                                            <label for="event-title"><?php _e('Title', Scheduler::TEXTDOMAIN); ?></label>
                                        </h2>
                                    </th>
                                    <td>
                                        <input type="text" id="event-title" name="event-title" required="required"
                                               value="<?php echo $this->event->get_title(); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <h2>
                                            <label for="event-thumbnail"><?php _e('Thumbnail', Scheduler::TEXTDOMAIN); ?></label>
                                        </h2>
                                    </th>
                                    <td>
                                        <div id="event-thumbnail-wrapper" <?php if (!$this->event->get_thumbnail()) echo 'style="display: none";'; ?>>
                                            <img id="event-thumbnail-viewer"
                                                 src="<?php echo $this->event->get_thumbnail(); ?>"/>
                                        </div>
                                        <button type="button" class="button" id="image-upload-button">
                                            <?php _e('Select...', Scheduler::TEXTDOMAIN); ?>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <h2>
                                            <label for="event-page"><?php _e('Associated page', Scheduler::TEXTDOMAIN); ?></label>
                                        </h2>
                                    </th>
                                    <td>
                                        <?php wp_dropdown_pages(array(
                                            'name' => 'event-page',
                                            'id' => 'event-page',
                                            'show_option_none' => __('[None]', Scheduler::TEXTDOMAIN),
                                            'selected' => $this->event->get_page(),
                                            'option_none_value' => -1
                                        )); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <h2>
                                            <label for="event-color"><?php _e('Color', Scheduler::TEXTDOMAIN); ?></label>
                                        </h2>
                                    </th>
                                    <td>
                                        <input type="text" id="event-color" name="event-color"
                                               value="<?php echo $this->event->get_color(); ?>"
                                               data-default-color="#0086cd"/>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <h2>
                                <label for="event-description"><?php _e('Description', Scheduler::TEXTDOMAIN); ?></label>
                            </h2>
                            <?php wp_editor($this->event->get_description(), 'event-description',
                                array('textarea_rows' => 10)); ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button($this->submit_button_text); ?>
            </form>
        </div>
        <?php
    }

    private function load_scripts_and_styles() {
        wp_enqueue_script('jquery');
        wp_enqueue_media(); // enqueue necessary scripts and styles for Wordpress Media Uploader
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');
        $this->print_uploader_script();
    }

    private function print_uploader_script() {
        ?>
        <script type="text/javascript">
            jQuery(function ($) {
                $('#image-upload-button').click(function (e) {
                    e.preventDefault();
                    var image = wp.media({
                        title: '<?php _e("Select event thumbnail", Scheduler::TEXTDOMAIN); ?>',
                        multiple: false
                    }).open().on('select', function (e) {
                        var url = image.state().get('selection').first().toJSON().url;
                        $('#event-thumbnail-wrapper').show();
                        $('#event-thumbnail-viewer').attr('src', url);
                        $('#event-thumbnail').val(url);
                    });
                });
                $(document).ready(function ($) {
                    $('#event-color').wpColorPicker();
                });
            })
        </script>
        <?php
    }
}