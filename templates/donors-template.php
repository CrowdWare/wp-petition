<?php
/**
 * Template for the donors list.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/templates
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get the campaign and donations
if (!isset($campaign) || !$campaign || !isset($donations)) {
    return;
}
?>

<div class="wp-petition-donors-container" data-campaign-id="<?php echo esc_attr($campaign->campaign_id); ?>">
    <h3><?php echo esc_html__('Zeit-Spenden', 'wp-petition'); ?></h3>
    
    <?php if (empty($donations)) : ?>
        <p><?php echo esc_html__('Noch keine Spenden bis jetzt. Sei der Erste, der etwas spendet!', 'wp-petition'); ?></p>
    <?php else : ?>
        <table class="wp-petition-donors-table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Name', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Stunden', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Social Media', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Anderer Support', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Datum', 'wp-petition'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($donations as $donation) : ?>
                    <tr>
                        <td><?php echo esc_html($donation->name); ?></td>
                        <td><?php echo esc_html($donation->hours); ?></td>
                        <td class="social-icons">
                            <?php if ($donation->facebook_post) : ?>
                                <span class="social-icon facebook-icon" title="<?php echo esc_attr__('Facebook Posts', 'wp-petition'); ?>"></span>
                            <?php endif; ?>
                            
                            <?php if ($donation->x_post) : ?>
                                <span class="social-icon x-icon" title="<?php echo esc_attr__('X Post', 'wp-petition'); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($donation->other_support); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($donation->created_at))); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
