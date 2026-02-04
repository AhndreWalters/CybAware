<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

// Initialize game variables
$score = isset($_SESSION['phishing_score']) ? $_SESSION['phishing_score'] : 0;
$current_question = isset($_SESSION['phishing_question']) ? $_SESSION['phishing_question'] : 1;
$total_questions = 10;
$feedback = "";
$feedback_type = "";
$game_completed = false;

// Game data - Realistic looking emails
// Game data - Realistic looking emails
$emails = [
    1 => [
        'sender' => 'security@paypal-support.com',
        'sender_name' => 'PayPal Security',
        'subject' => 'Urgent: Verify Your Account Now',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #202124; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                    <div style="border-bottom: 1px solid #dadce0; padding-bottom: 20px; margin-bottom: 20px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                            <tr>
                                <td style="padding: 0;">
                                    <img src="https://www.paypalobjects.com/webstatic/icon/pp258.png" alt="PayPal" style="height: 32px; margin-bottom: 15px;">
                                </td>
                            </tr>
                        </table>
                        <h1 style="font-size: 20px; font-weight: 400; color: #001c64; margin: 0 0 20px 0;">Account Verification Required</h1>
                    </div>
                    
                    <p style="margin: 0 0 20px 0;">Dear PayPal Member,</p>
                    
                    <p style="margin: 0 0 20px 0;">Our security system has detected unusual activity on your account. To protect your account from unauthorized access, we require immediate verification of your identity.</p>
                    
                    <div style="background-color: #f8f9fa; border: 1px solid #dadce0; border-radius: 4px; padding: 20px; margin: 0 0 20px 0;">
                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #d93025;">Action Required Within 24 Hours:</p>
                        <p style="margin: 0 0 15px 0;">Please click the link below to verify your PayPal account:</p>
                        <p style="margin: 0;">
                            <a href="http://secure-paypal-verify.com/login" style="color: #1a73e8; text-decoration: none; word-break: break-all; display: inline-block; padding: 10px 15px; background-color: #e8f0fe; border-radius: 4px; border: 1px solid #d2e3fc;">http://secure-paypal-verify.com/login</a>
                        </p>
                    </div>
                    
                    <p style="margin: 0 0 20px 0;">If you do not complete this verification process, your account will be temporarily suspended until we can confirm your identity.</p>
                    
                    <p style="margin: 0 0 20px 0;">Thank you for your prompt attention to this security matter.</p>
                    
                    <p style="margin: 0 0 20px 0;">
                        Sincerely,<br>
                        <strong>PayPal Security Team</strong>
                    </p>
                    
                    <div style="border-top: 1px solid #dadce0; padding-top: 20px; margin-top: 30px; font-size: 12px; color: #5f6368;">
                        <p style="margin: 0 0 10px 0;">This is an automated message from PayPal Security. Please do not reply to this email.</p>
                        <p style="margin: 0;">© 2025 PayPal. All rights reserved.</p>
                    </div>
                </div>',
        'answer' => 'phishing',
        'hint' => 'Check the domain: "paypal-support.com" is not PayPal\'s official domain (paypal.com)'
    ],
    2 => [
        'sender' => 'no-reply@amazon.com',
        'sender_name' => 'Amazon Orders',
        'subject' => 'Your order #113-6920517-7262665 has shipped',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #111; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                    <div style="border-bottom: 1px solid #ddd; padding-bottom: 15px; margin-bottom: 20px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                            <tr>
                                <td style="padding: 0;">
                                    <span style="color: #ff9900; font-size: 24px; font-weight: bold; display: inline-block; margin-bottom: 10px;">amazon</span>
                                </td>
                            </tr>
                        </table>
                        <h1 style="font-size: 18px; font-weight: 400; color: #111; margin: 0;">Your order has shipped</h1>
                    </div>
                    
                    <p style="margin: 0 0 20px 0;">Hello,</p>
                    
                    <p style="margin: 0 0 20px 0;">Good news! Your Amazon order has shipped.</p>
                    
                    <div style="background-color: #f3f3f3; border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin: 0 0 20px 0;">
                        <p style="margin: 0 0 10px 0; font-weight: 600; color: #111;">Order Details:</p>
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 13px;">
                            <tr>
                                <td style="padding: 5px 0; color: #555;">Order #:</td>
                                <td style="padding: 5px 0; font-weight: 600;">113-6920517-7262665</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0; color: #555;">Items:</td>
                                <td style="padding: 5px 0;">1 of "Wireless Bluetooth Headphones"</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0; color: #555;">Shipping Method:</td>
                                <td style="padding: 5px 0;">Standard Shipping</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0; color: #555;">Estimated Delivery:</td>
                                <td style="padding: 5px 0;">3-5 business days</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p style="margin: 0 0 20px 0;">
                        <a href="https://www.amazon.com/track-package" style="color: #0066c0; text-decoration: none; font-weight: 600;">Track your package</a>
                    </p>
                    
                    <p style="margin: 0 0 20px 0;">If you have any questions about your order, visit our <a href="https://www.amazon.com/contact-us" style="color: #0066c0; text-decoration: none;">Customer Service</a> page.</p>
                    
                    <p style="margin: 0 0 20px 0;">
                        Thank you for shopping with Amazon!<br>
                        <strong>The Amazon Team</strong>
                    </p>
                    
                    <div style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 30px; font-size: 12px; color: #555;">
                        <p style="margin: 0 0 10px 0;">Please note: This email was sent from a notification-only address that cannot accept incoming email. Please do not reply to this message.</p>
                        <p style="margin: 0;">© 1996-2025, Amazon.com, Inc. or its affiliates</p>
                    </div>
                </div>',
        'answer' => 'legitimate',
        'hint' => 'This uses Amazon\'s official domain and has realistic order details without urgent demands.'
    ],
    3 => [
        'sender' => 'netflix@account-update.com',
        'sender_name' => 'Netflix Billing Department',
        'subject' => 'Payment Failed - Update Your Payment Method',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #333; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                    <div style="border-bottom: 1px solid #e50914; padding-bottom: 15px; margin-bottom: 20px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                            <tr>
                                <td style="padding: 0;">
                                    <span style="color: #e50914; font-size: 28px; font-weight: bold; display: inline-block; margin-bottom: 10px;">NETFLIX</span>
                                </td>
                            </tr>
                        </table>
                        <h1 style="font-size: 18px; font-weight: 400; color: #333; margin: 0;">Payment Update Required</h1>
                    </div>
                    
                    <p style="margin: 0 0 20px 0;">Dear Netflix Subscriber,</p>
                    
                    <p style="margin: 0 0 20px 0;">We were unable to process your most recent payment of <strong>$15.99</strong> for your Netflix subscription.</p>
                    
                    <div style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 20px; margin: 0 0 20px 0;">
                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #856404;">Important Notice:</p>
                        <p style="margin: 0 0 15px 0;">Your account will be suspended in <strong>48 hours</strong> if we do not receive payment.</p>
                        <p style="margin: 0;">
                            To update your payment information, please click here:<br>
                            <a href="http://netflix-billing-update.com/payment" style="color: #e50914; text-decoration: none; font-weight: 600; display: inline-block; margin-top: 10px; padding: 10px 20px; background-color: #e50914; color: white; border-radius: 4px;">Update Payment Method</a>
                        </p>
                    </div>
                    
                    <p style="margin: 0 0 20px 0;">If you believe this is an error, please contact our billing department immediately.</p>
                    
                    <p style="margin: 0 0 20px 0;">
                        Thank you for being a Netflix member.<br>
                        <strong>Netflix Billing Team</strong>
                    </p>
                    
                    <div style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 30px; font-size: 12px; color: #666;">
                        <p style="margin: 0 0 10px 0;">Netflix, Inc.<br>
                        100 Winchester Circle<br>
                        Los Gatos, CA 95032</p>
                        <p style="margin: 0;">This email was sent from an unmonitored mailbox.</p>
                    </div>
                </div>',
        'answer' => 'phishing',
        'hint' => 'Urgent language with threats, and the domain is "account-update.com" not "netflix.com"'
    ],
    4 => [
        'sender' => 'security@twitter.com',
        'sender_name' => 'Twitter Security',
        'subject' => 'New login to your account from Chrome on Windows',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #0f1419; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                    <div style="border-bottom: 1px solid #cfd9de; padding-bottom: 15px; margin-bottom: 20px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                            <tr>
                                <td style="padding: 0;">
                                    <span style="color: #1da1f2; font-size: 24px; font-weight: bold; display: inline-block; margin-bottom: 10px;">Twitter</span>
                                </td>
                            </tr>
                        </table>
                        <h1 style="font-size: 18px; font-weight: 400; color: #0f1419; margin: 0;">New login detected</h1>
                    </div>
                    
                    <p style="margin: 0 0 20px 0;">Hi there,</p>
                    
                    <p style="margin: 0 0 20px 0;">We noticed a new login to your Twitter account. If this was you, you can ignore this message. No further action is required.</p>
                    
                    <div style="background-color: #f7f9f9; border: 1px solid #cfd9de; border-radius: 4px; padding: 15px; margin: 0 0 20px 0;">
                        <p style="margin: 0 0 10px 0; font-weight: 600; color: #0f1419;">Login Details:</p>
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 13px;">
                            <tr>
                                <td style="padding: 5px 0; color: #536471; width: 120px;">Date:</td>
                                <td style="padding: 5px 0;">' . date('F j, Y') . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0; color: #536471;">Time:</td>
                                <td style="padding: 5px 0;">' . date('g:i A') . ' PST</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0; color: #536471;">Browser:</td>
                                <td style="padding: 5px 0;">Chrome</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0; color: #536471;">Operating System:</td>
                                <td style="padding: 5px 0;">Windows</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0; color: #536471;">Location:</td>
                                <td style="padding: 5px 0;">New York, USA (Approximate)</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p style="margin: 0 0 20px 0;">If you don\'t recognize this activity, please review your account immediately:</p>
                    
                    <p style="margin: 0 0 20px 0;">
                        <a href="https://twitter.com/settings/security" style="color: #1da1f2; text-decoration: none; font-weight: 600; display: inline-block; padding: 10px 20px; border: 1px solid #1da1f2; border-radius: 9999px;">Review Your Account Security</a>
                    </p>
                    
                    <p style="margin: 0 0 20px 0;">For your security, this email was sent to all email addresses associated with your Twitter account.</p>
                    
                    <p style="margin: 0 0 20px 0;">
                        Thanks,<br>
                        <strong>The Twitter Team</strong>
                    </p>
                    
                    <div style="border-top: 1px solid #cfd9de; padding-top: 15px; margin-top: 30px; font-size: 12px; color: #536471;">
                        <p style="margin: 0 0 10px 0;">This is an automated message. Please do not reply to this email.</p>
                        <p style="margin: 0;">© 2025 Twitter, Inc.</p>
                    </div>
                </div>',
        'answer' => 'legitimate',
        'hint' => 'Official Twitter domain, provides specific login details without urgent demands'
    ],
    5 => [
        'sender' => 'service@microsoft-security.net',
        'sender_name' => 'Microsoft Windows Support',
        'subject' => 'URGENT: Your Windows License is About to Expire',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #323130; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                    <div style="border-bottom: 1px solid #d83b01; padding-bottom: 15px; margin-bottom: 20px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                            <tr>
                                <td style="padding: 0;">
                                    <span style="color: #0078d4; font-size: 24px; font-weight: bold; display: inline-block; margin-bottom: 10px;">Microsoft</span>
                                </td>
                            </tr>
                        </table>
                        <h1 style="font-size: 20px; font-weight: 600; color: #d83b01; margin: 0;">URGENT: Windows License Expiration Notice</h1>
                    </div>
                    
                    <p style="margin: 0 0 20px 0; font-weight: 600;">ATTENTION: Windows User,</p>
                    
                    <p style="margin: 0 0 20px 0;">Our records indicate that your Microsoft Windows license will <strong>expire in 3 days</strong>.</p>
                    
                    <div style="background-color: #fde7e9; border: 1px solid #d83b01; border-radius: 4px; padding: 20px; margin: 0 0 20px 0;">
                        <p style="margin: 0 0 15px 0; font-weight: 700; color: #a4262c;">IMMEDIATE ACTION REQUIRED:</p>
                        
                        <p style="margin: 0 0 15px 0; font-weight: 600;">Failure to renew your license will result in:</p>
                        
                        <ul style="margin: 0 0 15px 0; padding-left: 20px;">
                            <li style="margin: 0 0 8px 0;">System lockout after expiration</li>
                            <li style="margin: 0 0 8px 0;">Data encryption for security purposes</li>
                            <li style="margin: 0 0 8px 0;">Permanent loss of access to your files</li>
                            <li style="margin: 0 0 8px 0;">Inability to receive critical security updates</li>
                        </ul>
                        
                        <p style="margin: 0;">
                            <a href="http://microsoft-license-renewal.com/activate" style="color: #ffffff; text-decoration: none; font-weight: 700; display: inline-block; padding: 12px 24px; background-color: #d83b01; border-radius: 4px; text-transform: uppercase;">CLICK HERE TO RENEW YOUR LICENSE NOW</a>
                        </p>
                    </div>
                    
                    <p style="margin: 0 0 20px 0; font-weight: 600; color: #a4262c;">This is your FINAL NOTICE before system restrictions are applied.</p>
                    
                    <p style="margin: 0 0 20px 0;">
                        <strong>Microsoft Windows Activation Team</strong><br>
                        One Microsoft Way<br>
                        Redmond, WA 98052
                    </p>
                    
                    <div style="border-top: 1px solid #edebe9; padding-top: 15px; margin-top: 30px; font-size: 11px; color: #605e5c;">
                        <p style="margin: 0 0 10px 0;">This email was sent from an unmonitored mailbox. Please do not reply.</p>
                        <p style="margin: 0;">© 2025 Microsoft Corporation. All rights reserved.</p>
                    </div>
                </div>',
        'answer' => 'phishing',
        'hint' => 'Excessive urgency, threats of data loss, and suspicious domain "microsoft-security.net"'
    ],
    6 => [
    'sender' => 'appleid@id-apple.com',
    'sender_name' => 'Apple Support',
    'subject' => 'Your Apple ID has been locked',
    'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #1d1d1f; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                <div style="border-bottom: 1px solid #d2d2d7; padding-bottom: 20px; margin-bottom: 20px;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                        <tr>
                            <td style="padding: 0;">
                                <span style="color: #000; font-size: 24px; font-weight: 600; display: inline-block; margin-bottom: 10px;">Apple</span>
                            </td>
                        </tr>
                    </table>
                    <h1 style="font-size: 20px; font-weight: 400; color: #1d1d1f; margin: 0 0 10px 0;">Apple ID Locked</h1>
                    <p style="font-size: 13px; color: #86868b; margin: 0;">Account Security Alert</p>
                </div>
                
                <p style="margin: 0 0 20px 0;">Dear Apple User,</p>
                
                <p style="margin: 0 0 20px 0;">We detected multiple failed login attempts to your Apple ID from an unrecognized device. To protect your account, we have temporarily locked your Apple ID.</p>
                
                <div style="background-color: #f5f5f7; border: 1px solid #d2d2d7; border-radius: 8px; padding: 20px; margin: 0 0 20px 0;">
                    <p style="margin: 0 0 15px 0; font-weight: 600; color: #1d1d1f;">To unlock your account:</p>
                    <ol style="margin: 0 0 15px 0; padding-left: 20px;">
                        <li style="margin: 0 0 8px 0;">Click the link below</li>
                        <li style="margin: 0 0 8px 0;">Verify your identity with security questions</li>
                        <li style="margin: 0 0 8px 0;">Reset your password</li>
                    </ol>
                    <p style="margin: 0;">
                        <a href="http://appleid-verify-security.com/unlock" style="color: #06c; text-decoration: none; font-weight: 500; display: inline-block; padding: 10px 20px; background-color: #0071e3; color: white; border-radius: 980px;">Unlock Apple ID Now</a>
                    </p>
                </div>
                
                <p style="margin: 0 0 20px 0; font-size: 13px; color: #86868b;">If you did not attempt to access your account, please secure it immediately.</p>
                
                <p style="margin: 0 0 20px 0;">
                    Sincerely,<br>
                    <strong>Apple Support</strong>
                </p>
                
                <div style="border-top: 1px solid #d2d2d7; padding-top: 20px; margin-top: 30px; font-size: 12px; color: #86868b;">
                    <p style="margin: 0 0 10px 0;">Apple Inc. | One Apple Park Way, Cupertino, CA 95014</p>
                    <p style="margin: 0;">This is an automated message. Please do not reply.</p>
                </div>
            </div>',
    'answer' => 'phishing',
    'hint' => 'Apple uses "apple.com" domains, not "id-apple.com". Legitimate Apple emails never ask you to click links to unlock accounts.'
],

7 => [
    'sender' => 'accounts.google.com',
    'sender_name' => 'Google',
    'subject' => 'Security checkup required',
    'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #202124; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                <div style="border-bottom: 1px solid #dadce0; padding-bottom: 20px; margin-bottom: 20px;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                        <tr>
                            <td style="padding: 0;">
                                <span style="color: #4285f4; font-size: 24px; font-weight: 500; display: inline-block; margin-bottom: 10px;">G</span>
                                <span style="color: #ea4335; font-size: 24px; font-weight: 500;">o</span>
                                <span style="color: #fbbc05; font-size: 24px; font-weight: 500;">o</span>
                                <span style="color: #4285f4; font-size: 24px; font-weight: 500;">g</span>
                                <span style="color: #34a853; font-size: 24px; font-weight: 500;">l</span>
                                <span style="color: #ea4335; font-size: 24px; font-weight: 500;">e</span>
                            </td>
                        </tr>
                    </table>
                    <h1 style="font-size: 20px; font-weight: 400; color: #202124; margin: 0;">Security checkup</h1>
                </div>
                
                <p style="margin: 0 0 20px 0;">Hi,</p>
                
                <p style="margin: 0 0 20px 0;">We noticed some unusual activity in your Google Account. To keep your account secure, please review your recent security events.</p>
                
                <div style="background-color: #f8f9fa; border: 1px solid #dadce0; border-radius: 8px; padding: 20px; margin: 0 0 20px 0;">
                    <p style="margin: 0 0 15px 0; font-weight: 500; color: #202124;">Recent activity to review:</p>
                    <ul style="margin: 0 0 15px 0; padding-left: 20px;">
                        <li style="margin: 0 0 8px 0;">New sign-in from Windows device</li>
                        <li style="margin: 0 0 8px 0;">Password change requested</li>
                        <li style="margin: 0 0 8px 0;">Recovery email updated</li>
                    </ul>
                </div>
                
                <p style="margin: 0 0 20px 0;">
                    <a href="https://myaccount.google.com/security-checkup" style="color: #1a73e8; text-decoration: none; font-weight: 500;">Review your security settings</a>
                </p>
                
                <p style="margin: 0 0 20px 0;">If this wasn\'t you, your account may have been compromised. You should change your password immediately.</p>
                
                <p style="margin: 0 0 20px 0;">
                    Thanks,<br>
                    <strong>The Google Accounts team</strong>
                </p>
                
                <div style="border-top: 1px solid #dadce0; padding-top: 20px; margin-top: 30px; font-size: 12px; color: #5f6368;">
                    <p style="margin: 0 0 10px 0;">You received this email to let you know about important changes to your Google Account and services.</p>
                    <p style="margin: 0;">© 2025 Google LLC, 1600 Amphitheatre Parkway, Mountain View, CA 94043</p>
                </div>
            </div>',
    'answer' => 'legitimate',
    'hint' => 'Legitimate Google email with proper domain, links to official Google site, and no urgent demands for immediate action.'
],

8 => [
    'sender' => 'support@linkedin-professional.com',
    'sender_name' => 'LinkedIn Member Support',
    'subject' => 'Someone viewed your profile',
    'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #000000; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                <div style="border-bottom: 1px solid #0077b5; padding-bottom: 20px; margin-bottom: 20px;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                        <tr>
                            <td style="padding: 0;">
                                <span style="color: #0077b5; font-size: 24px; font-weight: bold; display: inline-block; margin-bottom: 10px;">in</span>
                            </td>
                        </tr>
                    </table>
                    <h1 style="font-size: 20px; font-weight: 600; color: #000000; margin: 0;">See who\'s viewed your profile</h1>
                </div>
                
                <p style="margin: 0 0 20px 0;">Hi Member,</p>
                
                <p style="margin: 0 0 20px 0;">Your profile was viewed 15 times in the last 7 days. Upgrade to LinkedIn Premium to see everyone who\'s viewed your profile and get insights that can help you grow your network.</p>
                
                <div style="background-color: #f3f6f8; border: 1px solid #0077b5; border-radius: 4px; padding: 20px; margin: 0 0 20px 0; text-align: center;">
                    <p style="margin: 0 0 15px 0; font-weight: 600; color: #0077b5;">Limited Time Offer: 50% OFF Premium</p>
                    <p style="margin: 0 0 15px 0; font-size: 18px; font-weight: bold;">Only $14.99/month</p>
                    <p style="margin: 0;">
                        <a href="http://linkedin-premium-offer.com/upgrade" style="color: #ffffff; text-decoration: none; font-weight: 600; display: inline-block; padding: 12px 30px; background-color: #0077b5; border-radius: 24px; text-transform: uppercase;">Claim Your Discount Now</a>
                    </p>
                    <p style="margin: 15px 0 0 0; font-size: 12px; color: #666;">Offer expires in 48 hours</p>
                </div>
                
                <p style="margin: 0 0 20px 0;">Premium members get 5x more profile views and are 40% more likely to receive opportunities.</p>
                
                <p style="margin: 0 0 20px 0;">
                    Best,<br>
                    <strong>The LinkedIn Team</strong>
                </p>
                
                <div style="border-top: 1px solid #e0e0e0; padding-top: 20px; margin-top: 30px; font-size: 12px; color: #666;">
                    <p style="margin: 0 0 10px 0;">This message was sent to LinkedIn member</p>
                    <p style="margin: 0;">© 2025 LinkedIn Corporation, 1000 W Maude Ave, Sunnyvale, CA 94085</p>
                </div>
            </div>',
    'answer' => 'phishing',
    'hint' => 'LinkedIn uses "linkedin.com" domains. Fake domains like "linkedin-professional.com" with urgent limited-time offers are classic phishing tactics.'
],

9 => [
    'sender' => 'noreply@github.com',
    'sender_name' => 'GitHub',
    'subject' => 'New sign-in to your GitHub account',
    'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #24292e; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                <div style="border-bottom: 1px solid #e1e4e8; padding-bottom: 20px; margin-bottom: 20px;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                        <tr>
                            <td style="padding: 0;">
                                <span style="color: #24292e; font-size: 24px; font-weight: 600; display: inline-block; margin-bottom: 10px;">GitHub</span>
                            </td>
                        </tr>
                    </table>
                    <h1 style="font-size: 20px; font-weight: 400; color: #24292e; margin: 0;">New sign-in to your account</h1>
                </div>
                
                <p style="margin: 0 0 20px 0;">Hello,</p>
                
                <p style="margin: 0 0 20px 0;">We noticed a new sign-in to your GitHub account.</p>
                
                <div style="background-color: #f6f8fa; border: 1px solid #e1e4e8; border-radius: 6px; padding: 20px; margin: 0 0 20px 0;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 13px;">
                        <tr>
                            <td style="padding: 8px 0; color: #586069; width: 100px;">Date:</td>
                            <td style="padding: 8px 0; font-weight: 600;">' . date('F j, Y') . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #586069;">Time:</td>
                            <td style="padding: 8px 0;">' . date('g:i A') . ' UTC</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #586069;">IP Address:</td>
                            <td style="padding: 8px 0;">192.168.1.105</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #586069;">Location:</td>
                            <td style="padding: 8px 0;">San Francisco, CA, US</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #586069;">Device:</td>
                            <td style="padding: 8px 0;">Chrome on Windows</td>
                        </tr>
                    </table>
                </div>
                
                <p style="margin: 0 0 20px 0;">If this was you, you can disregard this email. There\'s no need to take any action.</p>
                
                <p style="margin: 0 0 20px 0;">If you don\'t recognize this activity, please secure your account:</p>
                
                <p style="margin: 0 0 20px 0;">
                    <a href="https://github.com/settings/security" style="color: #0366d6; text-decoration: none; font-weight: 500;">Review account security</a>
                </p>
                
                <p style="margin: 0 0 20px 0;">
                    Thanks,<br>
                    <strong>GitHub Security</strong>
                </p>
                
                <div style="border-top: 1px solid #e1e4e8; padding-top: 20px; margin-top: 30px; font-size: 12px; color: #586069;">
                    <p style="margin: 0 0 10px 0;">This email was automatically sent by GitHub Security to keep you informed about your account.</p>
                    <p style="margin: 0;">© 2025 GitHub, Inc. | 88 Colin P Kelly Jr St, San Francisco, CA 94107</p>
                </div>
            </div>',
    'answer' => 'legitimate',
    'hint' => 'Proper GitHub domain, detailed sign-in information without urgent demands, and links to official GitHub security settings.'
],

10 => [
    'sender' => 'banking@wellsfargo-security.com',
    'sender_name' => 'Wells Fargo Security',
    'subject' => 'SUSPICIOUS TRANSACTION ALERT - Immediate Action Required',
    'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #333; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                <div style="background-color: #c00; color: white; padding: 20px; border-radius: 4px 4px 0 0; margin-bottom: 20px;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                        <tr>
                            <td style="padding: 0;">
                                <span style="font-size: 24px; font-weight: bold; display: inline-block; margin-bottom: 10px;">WELLS FARGO</span>
                            </td>
                        </tr>
                    </table>
                    <h1 style="font-size: 22px; font-weight: 700; color: white; margin: 0;">URGENT: Fraud Alert</h1>
                </div>
                
                <p style="margin: 0 0 20px 0; font-weight: 600;">ATTENTION VALUED CUSTOMER,</p>
                
                <p style="margin: 0 0 20px 0;">Our fraud detection system has identified a suspicious transaction on your Wells Fargo account ending in ••••4321.</p>
                
                <div style="background-color: #fff3f3; border: 2px solid #c00; border-radius: 4px; padding: 20px; margin: 0 0 20px 0;">
                    <p style="margin: 0 0 15px 0; font-weight: 700; color: #c00; text-transform: uppercase;">Transaction Details:</p>
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 13px;">
                        <tr>
                            <td style="padding: 8px 0; color: #333; width: 120px;">Amount:</td>
                            <td style="padding: 8px 0; font-weight: 700; color: #c00;">$2,450.00</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #333;">Merchant:</td>
                            <td style="padding: 8px 0;">Electronics Plus Inc.</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #333;">Location:</td>
                            <td style="padding: 8px 0;">Miami, FL</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #333;">Time:</td>
                            <td style="padding: 8px 0;">' . date('g:i A') . ' EST</td>
                        </tr>
                    </table>
                    
                    <p style="margin: 20px 0 15px 0; font-weight: 700; color: #c00;">THIS TRANSACTION WILL BE PROCESSED IN 1 HOUR UNLESS CONFIRMED BY YOU.</p>
                    
                    <div style="text-align: center; margin: 20px 0 0 0;">
                        <a href="http://wellsfargo-security-verify.com/confirm" style="color: white; text-decoration: none; font-weight: 700; display: inline-block; padding: 15px 40px; background-color: #c00; border-radius: 4px; font-size: 16px; text-transform: uppercase;">VERIFY TRANSACTION NOW</a>
                    </div>
                </div>
                
                <p style="margin: 0 0 20px 0; font-weight: 600;">If you did not authorize this transaction, your account will be immediately locked to prevent further unauthorized activity.</p>
                
                <p style="margin: 0 0 20px 0;">
                    Sincerely,<br>
                    <strong>Wells Fargo Fraud Prevention Department</strong>
                </p>
                
                <div style="border-top: 1px solid #ccc; padding-top: 20px; margin-top: 30px; font-size: 11px; color: #666;">
                    <p style="margin: 0 0 10px 0;">Wells Fargo Bank, N.A. | 420 Montgomery Street, San Francisco, CA 94104</p>
                    <p style="margin: 0;">This is an automated security alert. Do not reply to this email.</p>
                </div>
            </div>',
    'answer' => 'phishing',
    'hint' => 'Extreme urgency, threats of account lockout, fake domain "wellsfargo-security.com" instead of official "wellsfargo.com", and pressure to click links immediately.'
],
];

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['answer']) && isset($_POST['question_id'])) {
        $user_answer = $_POST['answer'];
        $question_id = (int)$_POST['question_id'];
        
        if(isset($emails[$question_id])) {
            $correct_answer = $emails[$question_id]['answer'];
            
            if($user_answer === $correct_answer) {
                $score++;
                $_SESSION['phishing_score'] = $score;
                $feedback = "Correct! " . $emails[$question_id]['hint'];
                $feedback_type = "correct";
            } else {
                $feedback = "Incorrect. " . $emails[$question_id]['hint'];
                $feedback_type = "incorrect";
            }
            
            $current_question = $question_id + 1;
            $_SESSION['phishing_question'] = $current_question;
            
            // Check if game is completed
            if($current_question > $total_questions) {
                $game_completed = true;
                
                // Save score to database
                $user_id = $_SESSION['id'];
                $sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at) 
                        VALUES (?, 'phishing_detective', ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = NOW()";
                
                if($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param($stmt, "iii", $user_id, $score, $total_questions);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
                
                // Clear session data
                unset($_SESSION['phishing_score']);
                unset($_SESSION['phishing_question']);
            }
        }
    }
}

// Reset game if needed
if(isset($_GET['reset'])) {
    unset($_SESSION['phishing_score']);
    unset($_SESSION['phishing_question']);
    $score = 0;
    $current_question = 1;
    header("location: phishing-game.php");
    exit;
}

// Get current question display number
$display_question = min($current_question, $total_questions);

// Get current email only if game is not completed and question exists
$current_email = null;
if(!$game_completed && isset($emails[$current_question])) {
    $current_email = $emails[$current_question];
}

// If current question exceeds total but game not marked completed, fix it
if($current_question > $total_questions && !$game_completed) {
    $game_completed = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/ui-icon-social-engineering.png" type="image/x-icon">
    <title>Phishing Detective | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
    .game-interface {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .game-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .game-header h1 {
        color: #1e40af;
        font-size: 2rem;
        margin-bottom: 10px;
    }
    
    .game-header p {
        color: #64748b;
        font-size: 1.1rem;
    }
    
    .progress-container {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        width: 100%;
        box-sizing: border-box;
    }
    
    .progress-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.9rem;
        color: #64748b;
    }
    
    .progress-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        width: 100%;
    }
    
    .progress-fill {
        height: 100%;
        background: #3b82f6;
        width: <?php echo $game_completed ? '100' : (($display_question-1)/$total_questions)*100; ?>%;
        transition: width 0.3s ease;
    }
    
    .score-display {
        text-align: center;
        font-size: 1.1rem;
        color: #1e40af;
        font-weight: 600;
        background: #eff6ff;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .email-container {
        background: white;
        border-radius: 8px;
        padding: 0;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        width: 100%;
        box-sizing: border-box;
    }
    
    .email-header {
        background: #f8fafc;
        padding: 20px;
        border-bottom: 1px solid #e2e8f0;
        width: 100%;
        box-sizing: border-box;
    }
    
    .email-subject {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 15px;
        width: 100%;
    }
    
    .email-from {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        width: 100%;
    }
    
    .from-label {
        color: #64748b;
        min-width: 60px;
        font-size: 0.9rem;
    }
    
    .from-details {
        flex: 1;
        width: calc(100% - 60px);
    }
    
    .sender-name {
        font-weight: 600;
        color: #1e293b;
        width: 100%;
    }
    
    .sender-email {
        color: #64748b;
        font-size: 0.9rem;
        width: 100%;
    }
    
    .email-body {
        padding: 30px;
        min-height: 300px;
        background: white;
        text-align: left;
        font-family: Arial, Helvetica, sans-serif;
        width: 100%;
        box-sizing: border-box;
        overflow-wrap: break-word;
        word-wrap: break-word;
    }
    
    .decision-section {
        background: #f8fafc;
        padding: 25px;
        border-radius: 8px;
        margin-top: 20px;
        border: 1px solid #e2e8f0;
        width: 100%;
        box-sizing: border-box;
    }
    
    .decision-title {
        font-size: 1.1rem;
        color: #1e293b;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 600;
        width: 100%;
    }
    
    .options-container {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        width: 100%;
    }
    
    .option-btn {
        flex: 1;
        min-width: 150px;
        max-width: 250px;
        padding: 16px;
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s ease;
        box-sizing: border-box;
        width: calc(50% - 10px); /* Makes two buttons equal width */
    }
    
    .option-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .phishing-btn {
        color: #dc2626;
        border-color: #fecaca;
    }
    
    .phishing-btn:hover {
        background: #fee2e2;
        border-color: #dc2626;
    }
    
    .phishing-btn.selected {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
    }
    
    .legit-btn {
        color: #059669;
        border-color: #a7f3d0;
    }
    
    .legit-btn:hover {
        background: #d1fae5;
        border-color: #059669;
    }
    
    .legit-btn.selected {
        background: #059669;
        color: white;
        border-color: #059669;
    }
    
    .feedback {
        padding: 16px;
        border-radius: 6px;
        margin: 20px 0;
        font-weight: 500;
        text-align: center;
        animation: fadeIn 0.3s ease;
        width: 100%;
        box-sizing: border-box;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .feedback.correct {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }
    
    .feedback.incorrect {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }
    
    .game-controls {
        text-align: center;
        margin-top: 30px;
        width: 100%;
    }
    
    .submit-btn {
        padding: 14px 40px;
        background: #1e40af;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 200px;
        width: auto;
        display: inline-block;
    }
    
    .submit-btn:hover:not(:disabled) {
        background: #1e3a8a;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(30, 64, 175, 0.2);
    }
    
    .submit-btn:disabled {
        background: #94a3b8;
        cursor: not-allowed;
        transform: none;
    }
    
    .completion-screen {
        text-align: center;
        padding: 40px 30px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        width: 100%;
        box-sizing: border-box;
    }
    
    .completion-screen h2 {
        color: #1e40af;
        font-size: 1.8rem;
        margin-bottom: 15px;
    }
    
    .score-result {
        font-size: 1.2rem;
        color: #334155;
        margin-bottom: 25px;
        width: 100%;
    }
    
    .completion-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 30px;
        flex-wrap: wrap;
        width: 100%;
    }
    
    .action-btn {
        padding: 12px 30px;
        background: #1e40af;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease;
        box-sizing: border-box;
        min-width: 150px;
    }
    
    .action-btn:hover {
        background: #1e3a8a;
        transform: translateY(-2px);
    }
    
    .action-btn.secondary {
        background: white;
        color: #64748b;
        border: 2px solid #e2e8f0;
    }
    
    .action-btn.secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    
    .tip-box {
        background: #e0f2fe;
        padding: 15px;
        border-radius: 6px;
        margin: 20px 0;
        font-size: 0.95rem;
        color: #0369a1;
        border-left: 4px solid #0ea5e9;
        width: 100%;
        box-sizing: border-box;
    }
    
    /* Container alignment fix */
    .game-interface > * {
        width: 100%;
        box-sizing: border-box;
        display: block;
    }
    
    @media (max-width: 768px) {
        .game-interface {
            padding: 15px;
        }
        
        .options-container {
            flex-direction: column;
            align-items: stretch;
        }
        
        .option-btn {
            width: 100%;
            max-width: 100%;
            margin-bottom: 10px;
        }
        
        .email-body {
            padding: 20px;
            font-size: 13px;
        }
        
        .completion-actions {
            flex-direction: column;
            align-items: stretch;
        }
        
        .action-btn {
            width: 100%;
            max-width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .submit-btn {
            width: 100%;
            max-width: 100%;
        }
    }
    
    @media (max-width: 480px) {
        .game-header h1 {
            font-size: 1.5rem;
        }
        
        .email-subject {
            font-size: 1rem;
        }
        
        .option-btn {
            padding: 14px;
            font-size: 0.95rem;
        }
    }
    
    /* Ensure consistent alignment */
    .game-interface {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .game-interface > * {
        align-self: stretch;
    }
</style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-interface">
                <div class="game-header">
                    <h1>Phishing Detective</h1>
                    <p>Analyze emails and identify phishing attempts</p>
                </div>
                
                <?php if($feedback): ?>
                    <div class="feedback <?php echo $feedback_type; ?>">
                        <?php echo $feedback; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($game_completed): ?>
                    <div class="completion-screen">
                        <h2>Assessment Complete</h2>
                        <div class="score-result">
                            You scored <?php echo $score; ?> out of <?php echo $total_questions; ?> correctly.
                        </div>
                        
                        <?php
                        $percentage = ($score / $total_questions) * 100;
                        if($percentage >= 80) {
                            echo '<p style="color: #059669; font-weight: 600;">Excellent! You have strong phishing detection skills.</p>';
                        } elseif($percentage >= 60) {
                            echo '<p style="color: #d97706; font-weight: 600;">Good job! You can identify most phishing attempts.</p>';
                        } else {
                            echo '<p style="color: #dc2626; font-weight: 600;">Practice makes perfect! Review phishing indicators to improve.</p>';
                        }
                        ?>
                        
                        <div class="completion-actions">
                            <a href="game.php" class="action-btn secondary">Back to Games</a>
                            <a href="certificate.php" class="action-btn">View Certificate</a>
                            <a href="phishing-game.php?reset=1" class="action-btn">Play Again</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if($current_email): ?>
                        <form method="POST" action="phishing-game.php" id="gameForm">
                            <input type="hidden" name="question_id" value="<?php echo $current_question; ?>">
                            <input type="hidden" name="answer" id="selectedAnswer" value="">
                            
                            <div class="email-container">
                                <div class="email-header">
                                    <div class="email-subject"><?php echo htmlspecialchars($current_email['subject']); ?></div>
                                    <div class="email-from">
                                        <div class="from-label">From:</div>
                                        <div class="from-details">
                                            <div class="sender-name"><?php echo htmlspecialchars($current_email['sender_name']); ?></div>
                                            <div class="sender-email"><?php echo htmlspecialchars($current_email['sender']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="email-body">
                                    <?php echo $current_email['body']; ?>
                                </div>
                            </div>
                            
                            <div class="options-container">
                                <button type="button" class="option-btn legit-btn" onclick="selectAnswer('legitimate')">
                                    Legitimate Email
                                </button>
                                <button type="button" class="option-btn phishing-btn" onclick="selectAnswer('phishing')">
                                    Phishing Attempt
                                </button>
                            </div>
                            
                            <div class="game-controls">
                                <button type="submit" class="submit-btn" id="submitBtn" disabled>
                                    <?php echo $current_question == $total_questions ? 'Complete Assessment' : 'Next Question'; ?>
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="completion-screen">
                            <p>Loading assessment...</p>
                            <div class="completion-actions">
                                <a href="phishing-game.php?reset=1" class="action-btn">Restart Game</a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
    
    <script>
        let selectedAnswer = null;
        
        function selectAnswer(answer) {
            // Remove selected class from all buttons
            document.querySelectorAll('.option-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // Add selected class to clicked button
            const clickedBtn = event.currentTarget;
            clickedBtn.classList.add('selected');
            
            // Set hidden input value
            document.getElementById('selectedAnswer').value = answer;
            
            // Enable submit button
            document.getElementById('submitBtn').disabled = false;
            
            selectedAnswer = answer;
        }
        
        // Prevent form submission without selection
        document.getElementById('gameForm')?.addEventListener('submit', function(e) {
            if(!selectedAnswer) {
                e.preventDefault();
                alert('Please select an answer before continuing.');
                return false;
            }
            return true;
        });
        
        // Add hover effects
        document.addEventListener('DOMContentLoaded', function() {
            const optionButtons = document.querySelectorAll('.option-btn');
            optionButtons.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    if(!this.classList.contains('selected')) {
                        this.style.transform = 'translateY(-2px)';
                        this.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
                    }
                });
                
                btn.addEventListener('mouseleave', function() {
                    if(!this.classList.contains('selected')) {
                        this.style.transform = 'translateY(0)';
                        this.style.boxShadow = 'none';
                    }
                });
            });
            
            // Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                if(e.key === '1' || e.key === 'l') {
                    const legitBtn = document.querySelector('.legit-btn');
                    if(legitBtn) {
                        selectAnswer('legitimate');
                        legitBtn.click();
                    }
                } else if(e.key === '2' || e.key === 'p') {
                    const phishingBtn = document.querySelector('.phishing-btn');
                    if(phishingBtn) {
                        selectAnswer('phishing');
                        phishingBtn.click();
                    }
                } else if(e.key === 'Enter' && selectedAnswer) {
                    document.getElementById('submitBtn').click();
                }
            });
        });
    </script>
</body>
</html>