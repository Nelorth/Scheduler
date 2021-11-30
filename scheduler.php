<?php

/**
 * Plugin Name: Scheduler
 * Description: Create multiple schedules for weekly events with Lightbox overlays and PDF generation.
 * Version: 1.0.0
 * Author: Nikolas Kirschstein
 * Author URI: http://www.kirschstein.io
 * Text Domain: scheduler
 * Domain Path: /languages/
 * License: GPL3
 */

// If this file is called directly, abort instantly.
if (!defined('WPINC')) {
    die;
}

////////////////////////////////////////////////////////////////////////////////

/**
 * Plugin activation hook.
 */
function activate_scheduler() {
    require_once('controller/Activator.php');
    $activator = new Activator();
    $activator->activate();
}

register_activation_hook(__FILE__, 'activate_scheduler');

////////////////////////////////////////////////////////////////////////////////

/**
 * Plugin deactivation hook.
 */
function deactivate_scheduler() {
    require_once('controller/Deactivator.php');
    $deactivator = new Deactivator();
    $deactivator->deactivate();
}

register_activation_hook(__FILE__, 'deactivate_scheduler');

////////////////////////////////////////////////////////////////////////////////

require 'controller/Scheduler.php';

/**
 * Runs the plugin.
 */
function run_scheduler() {
    $scheduler = new Scheduler();
    $scheduler->run();
}

run_scheduler();