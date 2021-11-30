<?php

/*
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load necessary dependencies
require('controller/Scheduler.php');
require('model/Schedule.php');
require('model/Event.php');
require('model/Item.php');

// Fetch database arguments
global $wpdb;
$table_prefix = $wpdb->prefix . Scheduler::DB_PREFIX;
$schedule = $table_prefix . Schedule::TABLE_NAME;
$event = $table_prefix . Event::TABLE_NAME;
$item = $table_prefix . Item::TABLE_NAME;

// Remove database tables
$wpdb->query("DROP TABLE IF EXISTS $item;");
$wpdb->query("DROP TABLE IF EXISTS $event;");
$wpdb->query("DROP TABLE IF EXISTS $schedule;");

// Remove admin menu entry
delete_option(Scheduler::OPTIONS);