<?php

/**
 * This class manages the database infrastructure.
 */
class DatabaseManager {
    const QUERY_SEPARATOR = "\n--- end of Scheduler SQL Query ---\n";

    /**
     * Exports the entire plugin database tables as SQL script.
     *
     * @return string The SQL backup of the plugin tables.
     */
    public static function export() {
        global $wpdb;
        $prefix = $wpdb->prefix . Scheduler::DB_PREFIX;
        return self::backup_tables(array(
            $prefix . Schedule::TABLE_NAME,
            $prefix . Event::TABLE_NAME,
            $prefix . Item::TABLE_NAME
        ));
    }

    /**
     * Generates an SQL backup script for the plugin tables.
     *
     * @param array $tables The tables to backup.
     * @return string The SQL backup script.
     */
    private static function backup_tables($tables = array()) {
        global $wpdb;
        $sql = '';

        foreach ($tables as $key => $table) {
            $result = $wpdb->get_results('SELECT * FROM ' . $table, ARRAY_N);
            $num_rows = $wpdb->num_rows;
            $num_fields = 0;
            if ($num_rows > 0) {
                $num_fields = count($result[0]);
            }

            $sql .= 'DROP TABLE IF EXISTS ' . $table . ';';
            $sql .= DatabaseManager::QUERY_SEPARATOR;

            $row2 = $wpdb->get_results('SHOW CREATE TABLE ' . $table, ARRAY_N);
            $row2 = $row2[0];
            $sql .= "\n\n" . $row2[1] . ";\n\n";
            $sql .= DatabaseManager::QUERY_SEPARATOR;

            for ($i = 0; $i < $num_rows; $i++) {
                $row = $result[$i];
                $sql .= 'INSERT INTO ' . $table . ' VALUES(';
                for ($j = 0; $j < $num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = preg_replace("/\n/", "\\n", $row[$j]);
                    if (isset($row[$j])) {
                        $sql .= '"' . $row[$j] . '"';
                    } else {
                        $sql .= '""';
                    }
                    if ($j < ($num_fields - 1)) {
                        $sql .= ',';
                    }
                }
                $sql .= ");\n";
                if ($i < $num_rows - 1 || $key < count($tables) - 1) {
                    $sql .= DatabaseManager::QUERY_SEPARATOR;
                }
            }
            $sql .= "\n\n\n";
        }

        return $sql;
    }

    /**
     * Applies an SQL backup script to import table data.
     *
     * @param string $sql The SQL script to use for importing.
     * @return bool|WP_Error Whether the import was successful.
     */
    public static function import($sql) {
        global $wpdb;
        $queries = explode(DatabaseManager::QUERY_SEPARATOR, $sql);
        foreach ($queries as $query) {
            $wpdb->query($query);
            if ($wpdb->last_error != '') {
                $error = new WP_Error("dberror", __("Database query error"), $wpdb->last_error);
                return $error;
            }
        }
        return true;
    }
}