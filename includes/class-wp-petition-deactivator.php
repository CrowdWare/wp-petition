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
    }
}
