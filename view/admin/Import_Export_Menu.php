<?php

/**
 * Data import and export menu in the admin area.
 */
class Import_Export_Menu {


    public function __construct() {
        $this->handle_requests();
    }

    public function display() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Import/Export schedule data', Scheduler::TEXTDOMAIN); ?></h1>
            <hr class="wp-header-end"/>
            <h2><?php _e('Export schedules', Scheduler::TEXTDOMAIN); ?></h2>
            <p><?php _e('Export all schedule data into a single .sql file which can be used for backup.', Scheduler::TEXTDOMAIN); ?></p>
            <p><?php _e('Note that all event thumbnails will have to be transferred manually and set again.', Scheduler::TEXTDOMAIN); ?></p>
            <form method="post">
                <input type="hidden" name="import-export-action" value="export"/>
                <?php submit_button(__('Export', Scheduler::TEXTDOMAIN)); ?>
            </form>
            <h2><?php _e('Import schedules', Scheduler::TEXTDOMAIN); ?></h2>
            <p><?php _e('Import all schedule data from previously exported .sql file. Attention: All current data will be overridden!', Scheduler::TEXTDOMAIN); ?></p>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="import-export-action" value="import"/>
                <input type="hidden" name="MAX_FILE_SIZE" value="104857600"/>
                <input type="file" name="sql-file" required="required"/>
                <?php submit_button(__('Import', Scheduler::TEXTDOMAIN)); ?>
            </form>
        </div>
        <?php
    }

    private function handle_requests() {
        if (isset($_POST['import-export-action'])) {
            switch ($_POST['import-export-action']) {
                case 'export' :
                    $this->handle_import_request();
                    break;
                case 'import':
                    $this->handle_export_request();
                    break;
            }
        }
    }

    private function handle_import_request() {
        $sql = DatabaseManager::export();

        header('Content-Disposition: attachment; filename="scheduler.sql"');
        header('Content-Type: text/sql');
        header('Content-Length: ' . strlen($sql));
        header('Connection: close');

        echo $sql;
        exit;
    }

    private function handle_export_request() {
        if (isset($_FILES['sql-file'])) {
            $file_path = $_FILES['sql-file']['tmp_name'];
            if (!$file_path) {
                add_action('admin_notices', array($this, 'show_invalid_file_notice'));
            } else {
                $backup = DatabaseManager::export();
                $sql = file_get_contents($file_path);
                $import_result = DatabaseManager::import($sql);
                if ($import_result !== true) {
                    $backup_result = DatabaseManager::import($backup);
                    add_action('admin_notices', array($this, 'show_import_error_notice'), $import_result);
                    if ($backup_result !== true) {
                        add_action('admin_notices', array($this, 'show_backup_error_notice'), $backup_result);
                    }
                } else {
                    add_action('admin_notices', array($this, 'show_import_success_notice'));
                }
            }
        }
    }

    public function show_import_error_notice($import_result) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Error while importing! No changes have been made.', Scheduler::TEXTDOMAIN); ?></p>
            <br/>
            <strong>Fehlermeldung: </strong> <br/>
            <code>
                <?php
                echo $import_result->error_data['dberror'];
                ?>
            </code>
        </div>
        <?php
    }

    public function show_backup_error_notice($backup_result) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Error while restoring! Contact one of the plugin authors for further help.'); ?></p>
            <strong><?php _e('Error message:', Scheduler::TEXTDOMAIN); ?> </strong> <br/>
            <code>
                <?php
                echo $backup_result->error_data['dberror'];
                ?>
            </code>
        </div>
        <?php
    }

    public function show_import_success_notice() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Schedule data successfully imported.', Scheduler::TEXTDOMAIN); ?></p>
        </div>
        <?php
    }

    public function show_invalid_file_notice() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('A .sql file must be selected for import!', Scheduler::TEXTDOMAIN); ?></p>
        </div>
        <?php
    }
}