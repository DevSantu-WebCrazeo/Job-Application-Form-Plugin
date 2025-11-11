<?php
/**
 * Admin settings page template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap jaf-settings-wrap">
    <h1>Job Application Form - Settings</h1>
    
    <form method="post" action="" class="jaf-settings-form">
        <?php wp_nonce_field('jaf_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="notification_email">Notification Email</label>
                </th>
                <td>
                    <input type="email" name="notification_email" id="notification_email" value="<?php echo esc_attr($notification_email); ?>" class="regular-text">
                    <p class="description">Email address where new application notifications will be sent.</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="allowed_file_types">Allowed File Types</label>
                </th>
                <td>
                    <input type="text" name="allowed_file_types" id="allowed_file_types" value="<?php echo esc_attr($allowed_file_types); ?>" class="regular-text">
                    <p class="description">Comma-separated list of allowed file extensions (e.g., pdf,doc,docx,txt,rtf).</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="max_file_size">Max File Size (MB)</label>
                </th>
                <td>
                    <input type="number" name="max_file_size" id="max_file_size" value="<?php echo esc_attr($max_file_size); ?>" class="small-text" min="1" max="50">
                    <p class="description">Maximum file size allowed for resume uploads in megabytes.</p>
                </td>
            </tr>
        </table>
        
        <h2 class="title">Thank You Page Settings</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="thank_you_page">Thank You Page URL</label>
                </th>
                <td>
                    <input type="url" name="thank_you_page" id="thank_you_page" value="<?php echo esc_attr($thank_you_page); ?>" class="regular-text" placeholder="<?php echo esc_attr(home_url('/thank-you/')); ?>">
                    <p class="description">URL of the thank you page where users will be redirected after submitting the form. Leave empty to use default: <code><?php echo esc_html(home_url('/thank-you/')); ?></code></p>
                    <p class="description"><strong>Note:</strong> The application ID will be automatically added as a query parameter (<code>?app_id=APP-123456</code>) to the URL.</p>
                    <p class="description"><strong>Shortcode:</strong> Use <code>[application_id]</code> on your thank you page to display the application ID.</p>
                </td>
            </tr>
        </table>
        
        <h2 class="title">Application ID Settings</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="app_id_prefix">Application ID Prefix</label>
                </th>
                <td>
                    <input type="text" name="app_id_prefix" id="app_id_prefix" value="<?php echo esc_attr($app_id_prefix); ?>" class="regular-text" maxlength="10">
                    <p class="description">Prefix for application IDs (e.g., JAF, APP, JOB). Maximum 10 characters.</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="app_id_length">Application ID Length</label>
                </th>
                <td>
                    <input type="number" name="app_id_length" id="app_id_length" value="<?php echo esc_attr($app_id_length); ?>" class="small-text" min="4" max="12">
                    <p class="description">Length of the random part of the Application ID (excluding prefix and separator). Recommended: 6-8 characters.</p>
                    <p class="description"><strong>Example:</strong> If prefix is "JAF" and length is 8, IDs will look like: JAF-12345678</p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="jaf_settings_submit" class="button button-primary" value="Save Settings">
        </p>
    </form>
</div>

