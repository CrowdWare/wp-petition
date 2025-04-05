<?php
/**
 * Database updater for the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/includes
 */

/**
 * Database updater for the plugin.
 *
 * This class handles all database updates for the plugin.
 *
 * @since      1.0.0
 * @package    WP_Petition
 * @subpackage WP_Petition/includes
 * @author     Your Name <email@example.com>
 */
class WP_Petition_Updater {

    /**
     * The current database version.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $db_version    The current database version.
     */
    private $db_version = '1.0.3';

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_action('plugins_loaded', array($this, 'check_db_version'));
    }

    /**
     * Check the current database version and run updates if necessary.
     *
     * @since    1.0.0
     */
    public function check_db_version() {
        $installed_db_version = get_option('wp_petition_db_version');

        if ($installed_db_version !== $this->db_version) {
            // Run updates based on the installed version
            if (version_compare($installed_db_version, '1.0.3', '<')) {
                $this->update_to_1_0_3();
            }
            
            // Update the database version
            update_option('wp_petition_db_version', $this->db_version);
        }
    }
    
    /**
     * Update to version 1.0.3.
     * 
     * Adds the admin_notes field to the votes table.
     *
     * @since    1.0.3
     */
    private function update_to_1_0_3() {
        global $wpdb;
        
        $votes_table = $wpdb->prefix . 'petition_votes';
        
        // Check if the admin_notes column already exists
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$votes_table} LIKE 'admin_notes'");
        
        if (empty($column_exists)) {
            // Add the admin_notes column
            $wpdb->query("ALTER TABLE {$votes_table} ADD COLUMN admin_notes TEXT AFTER notes");
        }
    }
}

// Initialize the updater
new WP_Petition_Updater();
