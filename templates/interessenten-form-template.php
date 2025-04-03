<?php
/**
 * Template for the interessenten form.
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get the campaign
if (!isset($campaign) || !$campaign) {
    return;
}
?>

<div class="wp-petition-form-container">
    <h3><?php echo esc_html__('Werde ein Interessent', 'wp-petition'); ?></h3>

    <div class="wp-petition-notice-container">
        <?php
        // Display success message if set
        if (isset($_GET['wp_petition_interessent_success'])) {
            echo '<div class="wp-petition-notice success">' . esc_html__('Vielen Dank für dein Interesse!', 'wp-petition') . '</div>';
        }

        // Display error message if set
        if (isset($_GET['wp_petition_interessent_error'])) {
            echo '<div class="wp-petition-notice error">' . esc_html(urldecode($_GET['wp_petition_interessent_error'])) . '</div>';
        }
        ?>
    </div>

    <form class="wp-petition-form" method="post" action="">
        <?php wp_nonce_field('wp_petition_interessent_form', 'wp_petition_interessent_nonce'); ?>
        <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign->campaign_id); ?>">

        <div class="form-field">
            <label for="name"><?php echo esc_html__('Name', 'wp-petition'); ?> *</label>
            <input type="text" name="name" id="name" required>
        </div>

        <div class="form-field">
            <label for="email"><?php echo esc_html__('Email', 'wp-petition'); ?> *</label>
            <input type="email" name="email" id="email" required>
        </div>

        <p><?php echo esc_html__('Ich möchte wie folgt helfen:', 'wp-petition'); ?></p>

        <div class="form-field checkbox-field">
            <input type="checkbox" name="entwicklerhilfe" id="entwicklerhilfe" value="1">
            <label for="entwicklerhilfe"><?php echo esc_html__('Ich würde als Entwickler helfen', 'wp-petition'); ?></label>
        </div>

        <div class="form-field checkbox-field">
            <input type="checkbox" name="mundpropaganda" id="mundpropaganda" value="1">
            <label for="mundpropaganda"><?php echo esc_html__('Ich würde helfen mit Mund-zu-Mund-Propaganda', 'wp-petition'); ?></label>
        </div>

        <div class="form-field checkbox-field">
            <input type="checkbox" name="geldspende" id="geldspende" value="1">
            <label for="geldspende"><?php echo esc_html__('Ich würde einen Betrag an Geld spenden', 'wp-petition'); ?></label>
        </div>

        <div class="form-field checkbox-field">
            <input type="checkbox" name="projektfortschritt" id="projektfortschritt" value="1">
            <label for="projektfortschritt"><?php echo esc_html__('Ich möchte über den Projektfortschritt informiert werden', 'wp-petition'); ?></label>
        </div>

        <div class="form-field">
            <button type="submit" name="wp_petition_interessent_submit" class="submit-button"><?php echo esc_html__('Interesse bekunden', 'wp-petition'); ?></button>
        </div>
    </form>
</div>
