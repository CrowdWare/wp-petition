<?php
/**
 * Template for the Minutos donors list.
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
if (!isset($campaign) || !$campaign || !isset($minutos_donations)) {
    return;
}
?>

<div class="wp-petition-donors-container" data-campaign-id="<?php echo esc_attr($campaign->campaign_id); ?>">
    <h3><?php echo esc_html__('Minutos-Spenden', 'wp-petition'); ?></h3>
    
    <?php if (empty($minutos_donations)) : ?>
        <p><?php echo esc_html__('Noch keine Minuto-Spenden bis jetzt. Sei der Erste der Minutos spendet!', 'wp-petition'); ?></p>
    <?php else : ?>
        <table class="wp-petition-donors-table minutos-donors-table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Name', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Minutos', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Wert (€)', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Status', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Social Media', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Anderer Support', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Datum', 'wp-petition'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($minutos_donations as $donation) : ?>
                    <tr class="<?php echo $donation->minutos_received ? 'minutos-received' : 'minutos-pending'; ?>">
                        <td><?php echo esc_html($donation->name); ?></td>
                        <td><?php echo esc_html($donation->minutos); ?></td>
                        <td><?php echo esc_html(number_format($donation->minutos / 2, 2)); ?> €</td>
                        <td>
                            <?php if ($donation->minutos_received) : ?>
                                <span class="minutos-status received" title="<?php echo esc_attr__('Minutos Erhalten', 'wp-petition'); ?>"><?php echo esc_html__('Erhalten', 'wp-petition'); ?></span>
                            <?php else : ?>
                                <span class="minutos-status pending" title="<?php echo esc_attr__('Minutos Ausstehend', 'wp-petition'); ?>"><?php echo esc_html__('Ausstehend', 'wp-petition'); ?></span>
                            <?php endif; ?>
                        </td>
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
