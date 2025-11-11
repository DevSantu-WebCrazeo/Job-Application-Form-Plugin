<?php
/**
 * Applicant confirmation email template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Received</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f4f4;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700;">Application Received!</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin: 0 0 20px 0; color: #374151; font-size: 16px; line-height: 1.6;">
                                Dear <strong><?php echo esc_html($data['name']); ?></strong>,
                            </p>
                            
                            <p style="margin: 0 0 20px 0; color: #374151; font-size: 16px; line-height: 1.6;">
                                Thank you for your interest in joining our team at <strong><?php echo esc_html(get_bloginfo('name')); ?></strong>. We have successfully received your application for the position of <strong><?php echo esc_html($data['position']); ?></strong>.
                            </p>
                            
                            <div style="background-color: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; margin: 20px 0; border-radius: 4px;">
                                <h2 style="margin: 0 0 15px 0; color: #1f2937; font-size: 18px; font-weight: 600;">Your Application Details</h2>
                                
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; width: 140px; font-weight: 600;">Application ID:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px;">
                                            <code style="background-color: #d1fae5; padding: 4px 8px; border-radius: 4px; font-family: 'Courier New', monospace; color: #059669; font-weight: 600;"><?php echo esc_html($data['unique_id']); ?></code>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; font-weight: 600;">Position:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px; font-weight: 500;"><?php echo esc_html($data['position']); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; font-weight: 600;">Submitted:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px;"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'))); ?></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <p style="margin: 20px 0; color: #374151; font-size: 16px; line-height: 1.6;">
                                Our team will carefully review your application and qualifications. We appreciate the time and effort you've invested in applying with us.
                            </p>
                            
                            <p style="margin: 20px 0; color: #374151; font-size: 16px; line-height: 1.6;">
                                If your profile matches our requirements, we will contact you via email or phone to schedule the next steps in our hiring process.
                            </p>
                            
                            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.5;">
                                    <strong>Note:</strong> Please keep your Application ID (<code style="background-color: #fde68a; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; color: #92400e;"><?php echo esc_html($data['unique_id']); ?></code>) for your records. You may need it for future reference.
                                </p>
                            </div>
                            
                            <p style="margin: 30px 0 20px 0; color: #374151; font-size: 16px; line-height: 1.6;">
                                We look forward to the possibility of working together!
                            </p>
                            
                            <p style="margin: 0; color: #374151; font-size: 16px; line-height: 1.6;">
                                Best regards,<br>
                                <strong style="color: #1f2937;"><?php echo esc_html(get_bloginfo('name')); ?></strong><br>
                                <span style="color: #6b7280; font-size: 14px;">Hiring Team</span>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 12px; line-height: 1.5;">
                                This is an automated confirmation email. Please do not reply to this message.
                            </p>
                            <p style="margin: 0; color: #6b7280; font-size: 12px; line-height: 1.5;">
                                <a href="<?php echo esc_url(home_url()); ?>" style="color: #10b981; text-decoration: none;"><?php echo esc_html(get_bloginfo('name')); ?></a> | 
                                <a href="<?php echo esc_url(home_url()); ?>" style="color: #10b981; text-decoration: none;">Visit Our Website</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

