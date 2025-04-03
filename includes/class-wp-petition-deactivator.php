<?php
/**
 * Fired during plugin deactivation.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WP_Petition
 * @subpackage WP_Petition/includes
 * @author     Your Name <email@example.com>
 */
class WP_Petition_Deactivator {

    /**
     * Plugin deactivation functionality.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // We don't want to delete the tables on deactivation
        // This ensures user data is preserved if the plugin is reactivated
        
        // If you want to clean up all data on deactivation, uncomment the following code:
        /*
        global $wpdb;
        $campaigns_table = $wpdb->prefix . 'petition_campaigns';
        $donations_table = $wpdb->prefix . 'petition_donations';
        
        $wpdb->query("DROP TABLE IF EXISTS $donations_table");
        $wpdb->query("DROP TABLE IF EXISTS $campaigns_table");
        
        delete_option('wp_petition_db_version');
        */
    }
}
