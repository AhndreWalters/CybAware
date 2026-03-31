<?php
// Start the session so we can access the logged in user's data
session_start();

// If the user is not logged in, redirect them to the login page and stop the script
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Load the database connection
require_once "config/database.php";

// Load the current score and question number from the session, defaulting to 0 and 1 if not set
$score = isset($_SESSION['phishing_score']) ? $_SESSION['phishing_score'] : 0;
$current_question = isset($_SESSION['phishing_question']) ? $_SESSION['phishing_question'] : 1;
$total_questions = 10;
$feedback = "";
$game_completed = false;

// Array of ten emails - each one has sender details, an HTML body, the correct answer and a hint explaining why
$emails = [
    1 => [
        'sender' => 'security@paypal-support.com',
        'sender_name' => 'PayPal Security',
        'subject' => 'Urgent: Verify Your Account Now',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #202124; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                    <div style="border-bottom: 1px solid #dadce0; padding-bottom: 20px; margin-bottom: 20px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                            <tr><td style="padding: 0;"><img src="https://www.paypalobjects.com/webstatic/icon/pp258.png" alt="PayPal" style="height: 32px; margin-bottom: 15px;"></td></tr>
                        </table>
                        <h1 style="font-size: 20px; font-weight: 400; color: #001c64; margin: 0 0 20px 0;">Account Verification Required</h1>
                    </div>
                    <p style="margin: 0 0 20px 0;">Dear PayPal Member,</p>
                    <p style="margin: 0 0 20px 0;">Our security system has detected unusual activity on your account. To protect your account from unauthorized access, we require immediate verification of your identity.</p>
                    <div style="background-color: #f8f9fa; border: 1px solid #dadce0; border-radius: 4px; padding: 20px; margin: 0 0 20px 0;">
                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #d93025;">Action Required Within 24 Hours:</p>
                        <p style="margin: 0 0 15px 0;">Please click the link below to verify your PayPal account:</p>
                        <p style="margin: 0;"><a href="#" style="color: #1a73e8; text-decoration: none; word-break: break-all; display: inline-block; padding: 10px 15px; background-color: #e8f0fe; border-radius: 4px; border: 1px solid #d2e3fc;">http://secure-paypal-verify.com/login</a></p>
                    </div>
                    <p style="margin: 0 0 20px 0;">If you do not complete this verification process, your account will be temporarily suspended until we can confirm your identity.</p>
                    <p style="margin: 0 0 20px 0;">Sincerely,<br><strong>PayPal Security Team</strong></p>
                    <div style="border-top: 1px solid #dadce0; padding-top: 20px; margin-top: 30px; font-size: 12px; color: #5f6368;">
                        <p style="margin: 0 0 10px 0;">This is an automated message from PayPal Security. Please do not reply to this email.</p>
                        <p style="margin: 0;">© 2025 PayPal. All rights reserved.</p>
                    </div>
                </div>',
        'answer' => 'phishing',
        'hint' => 'Check the sender domain: "paypal-support.com" is NOT PayPal\'s official domain (paypal.com). Legitimate PayPal emails always come from @paypal.com.'
    ],
    2 => [
        'sender' => 'no-reply@amazon.com',
        'sender_name' => 'Amazon Orders',
        'subject' => 'Your order #113-6920517-7262665 has shipped',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #111; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                    <div style="border-bottom: 1px solid #ddd; padding-bottom: 15px; margin-bottom: 20px;">
                        <span style="color: #ff9900; font-size: 24px; font-weight: bold; display: inline-block; margin-bottom: 10px;">amazon</span>
                        <h1 style="font-size: 18px; font-weight: 400; color: #111; margin: 0;">Your order has shipped</h1>
                    </div>
                    <p style="margin: 0 0 20px 0;">Hello,</p>
                    <p style="margin: 0 0 20px 0;">Good news! Your Amazon order has shipped.</p>
                    <div style="background-color: #f3f3f3; border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin: 0 0 20px 0;">
                        <p style="margin: 0 0 10px 0; font-weight: 600; color: #111;">Order Details:</p>
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 13px;">
                            <tr><td style="padding: 5px 0; color: #555;">Order #:</td><td style="padding: 5px 0; font-weight: 600;">113-6920517-7262665</td></tr>
                            <tr><td style="padding: 5px 0; color: #555;">Items:</td><td style="padding: 5px 0;">1 of "Wireless Bluetooth Headphones"</td></tr>
                            <tr><td style="padding: 5px 0; color: #555;">Shipping Method:</td><td style="padding: 5px 0;">Standard Shipping</td></tr>
                            <tr><td style="padding: 5px 0; color: #555;">Estimated Delivery:</td><td style="padding: 5px 0;">3-5 business days</td></tr>
                        </table>
                    </div>
                    <p style="margin: 0 0 20px 0;"><a href="#" style="color: #0066c0; text-decoration: none; font-weight: 600;">Track your package</a></p>
                    <p style="margin: 0 0 20px 0;">If you have any questions about your order, visit our <a href="#" style="color: #0066c0; text-decoration: none;">Customer Service</a> page.</p>
                    <p style="margin: 0 0 20px 0;">Thank you for shopping with Amazon!<br><strong>The Amazon Team</strong></p>
                    <div style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 30px; font-size: 12px; color: #555;">
                        <p style="margin: 0 0 10px 0;">Please note: This email was sent from a notification-only address that cannot accept incoming email. Please do not reply to this message.</p>
                        <p style="margin: 0;">© 1996-2025, Amazon.com, Inc. or its affiliates</p>
                    </div>
                </div>',
        'answer' => 'legitimate',
        'hint' => 'This is a legitimate email. It comes from Amazon\'s official domain (@amazon.com), includes realistic order details, has no urgent threats, and links only to amazon.com.'
    ],
    3 => [
        'sender' => 'netflix@account-update.com',
        'sender_name' => 'Netflix Billing Department',
        'subject' => 'Payment Failed - Update Your Payment Method',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #333; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                    <div style="border-bottom: 1px solid #e50914; padding-bottom: 15px; margin-bottom: 20px;">
                        <span style="color: #e50914; font-size: 28px; font-weight: bold; display: inline-block; margin-bottom: 10px;">NETFLIX</span>
                        <h1 style="font-size: 18px; font-weight: 400; color: #333; margin: 0;">Payment Update Required</h1>
                    </div>
                    <p style="margin: 0 0 20px 0;">Dear Netflix Subscriber,</p>
                    <p style="margin: 0 0 20px 0;">We were unable to process your most recent payment of <strong>$15.99</strong> for your Netflix subscription.</p>
                    <div style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 20px; margin: 0 0 20px 0;">
                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #856404;">Important Notice:</p>
                        <p style="margin: 0 0 15px 0;">Your account will be suspended in <strong>48 hours</strong> if we do not receive payment.</p>
                        <p style="margin: 0;">To update your payment information, please click here:<br>
                            <a href="#" style="color: white; text-decoration: none; font-weight: 600; display: inline-block; margin-top: 10px; padding: 10px 20px; background-color: #e50914; color: white; border-radius: 4px;">Update Payment Method</a>
                        </p>
                    </div>
                    <p style="margin: 0 0 20px 0;">If you believe this is an error, please contact our billing department immediately.</p>
                    <p style="margin: 0 0 20px 0;">Thank you for being a Netflix member.<br><strong>Netflix Billing Team</strong></p>
                    <div style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 30px; font-size: 12px; color: #666;">
                        <p style="margin: 0 0 10px 0;">Netflix, Inc.<br>100 Winchester Circle<br>Los Gatos, CA 95032</p>
                        <p style="margin: 0;">This email was sent from an unmonitored mailbox.</p>
                    </div>
                </div>',
        'answer' => 'phishing',
        'hint' => 'Two red flags: the sender domain is "account-update.com", NOT "netflix.com". Real Netflix emails always come from @netflix.com. The 48-hour suspension threat is also a classic pressure tactic.'
    ],
    4 => [
        'sender' => 'security@twitter.com',
        'sender_name' => 'Twitter Security',
        'subject' => 'New login to your account from Chrome on Windows',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #0f1419; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                    <div style="border-bottom: 1px solid #cfd9de; padding-bottom: 15px; margin-bottom: 20px;">
                        <span style="color: #1da1f2; font-size: 24px; font-weight: bold; display: inline-block; margin-bottom: 10px;">Twitter</span>
                        <h1 style="font-size: 18px; font-weight: 400; color: #0f1419; margin: 0;">New login detected</h1>
                    </div>
                    <p style="margin: 0 0 20px 0;">Hi there,</p>
                    <p style="margin: 0 0 20px 0;">We noticed a new login to your Twitter account. If this was you, you can ignore this message. No further action is required.</p>
                    <div style="background-color: #f7f9f9; border: 1px solid #cfd9de; border-radius: 4px; padding: 15px; margin: 0 0 20px 0;">
                        <p style="margin: 0 0 10px 0; font-weight: 600; color: #0f1419;">Login Details:</p>
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 13px;">
                            <tr><td style="padding: 5px 0; color: #536471; width: 120px;">Date:</td><td style="padding: 5px 0;">' . date('F j, Y') . '</td></tr>
                            <tr><td style="padding: 5px 0; color: #536471;">Time:</td><td style="padding: 5px 0;">' . date('g:i A') . ' PST</td></tr>
                            <tr><td style="padding: 5px 0; color: #536471;">Browser:</td><td style="padding: 5px 0;">Chrome</td></tr>
                            <tr><td style="padding: 5px 0; color: #536471;">Operating System:</td><td style="padding: 5px 0;">Windows</td></tr>
                            <tr><td style="padding: 5px 0; color: #536471;">Location:</td><td style="padding: 5px 0;">New York, USA (Approximate)</td></tr>
                        </table>
                    </div>
                    <p style="margin: 0 0 20px 0;">If you don\'t recognize this activity, please review your account immediately:</p>
                    <p style="margin: 0 0 20px 0;"><a href="#" style="color: #1da1f2; text-decoration: none; font-weight: 600; display: inline-block; padding: 10px 20px; border: 1px solid #1da1f2; border-radius: 9999px;">Review Your Account Security</a></p>
                    <p style="margin: 0 0 20px 0;">For your security, this email was sent to all email addresses associated with your Twitter account.</p>
                    <p style="margin: 0 0 20px 0;">Thanks,<br><strong>The Twitter Team</strong></p>
                    <div style="border-top: 1px solid #cfd9de; padding-top: 15px; margin-top: 30px; font-size: 12px; color: #536471;">
                        <p style="margin: 0 0 10px 0;">This is an automated message. Please do not reply to this email.</p>
                        <p style="margin: 0;">© 2025 Twitter, Inc.</p>
                    </div>
                </div>',
        'answer' => 'legitimate',
        'hint' => 'This is a legitimate email. It comes from Twitter\'s official @twitter.com domain, provides specific login details, and doesn\'t pressure you into clicking anything urgently.'
    ],
    5 => [
        'sender' => 'service@microsoft-security.net',
        'sender_name' => 'Microsoft Windows Support',
        'subject' => 'URGENT: Your Windows License is About to Expire',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #323130; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                    <div style="border-bottom: 1px solid #d83b01; padding-bottom: 15px; margin-bottom: 20px;">
                        <span style="color: #0078d4; font-size: 24px; font-weight: bold; display: inline-block; margin-bottom: 10px;">Microsoft</span>
                        <h1 style="font-size: 20px; font-weight: 600; color: #d83b01; margin: 0;">URGENT: Windows License Expiration Notice</h1>
                    </div>
                    <p style="margin: 0 0 20px 0; font-weight: 600;">ATTENTION: Windows User,</p>
                    <p style="margin: 0 0 20px 0;">Our records indicate that your Microsoft Windows license will <strong>expire in 3 days</strong>.</p>
                    <div style="background-color: #fde7e9; border: 1px solid #d83b01; border-radius: 4px; padding: 20px; margin: 0 0 20px 0;">
                        <p style="margin: 0 0 15px 0; font-weight: 700; color: #a4262c; text-transform: uppercase;">Immediate Action Required:</p>
                        <p style="margin: 0 0 15px 0; font-weight: 600;">Failure to renew your license will result in:</p>
                        <ul style="margin: 0 0 15px 0; padding-left: 20px;">
                            <li style="margin: 0 0 8px 0;">System lockout after expiration</li>
                            <li style="margin: 0 0 8px 0;">Data encryption for security purposes</li>
                            <li style="margin: 0 0 8px 0;">Permanent loss of access to your files</li>
                            <li style="margin: 0 0 8px 0;">Inability to receive critical security updates</li>
                        </ul>
                        <p style="margin: 0;"><a href="#" style="color: white; text-decoration: none; font-weight: 700; display: inline-block; padding: 12px 24px; background-color: #d83b01; border-radius: 4px; text-transform: uppercase;">CLICK HERE TO RENEW YOUR LICENSE NOW</a></p>
                    </div>
                    <p style="margin: 0 0 20px 0; font-weight: 600; color: #a4262c;">This is your FINAL NOTICE before system restrictions are applied.</p>
                    <p style="margin: 0 0 20px 0;"><strong>Microsoft Windows Activation Team</strong><br>One Microsoft Way<br>Redmond, WA 98052</p>
                    <div style="border-top: 1px solid #edebe9; padding-top: 15px; margin-top: 30px; font-size: 11px; color: #605e5c;">
                        <p style="margin: 0 0 10px 0;">This email was sent from an unmonitored mailbox. Please do not reply.</p>
                        <p style="margin: 0;">© 2025 Microsoft Corporation. All rights reserved.</p>
                    </div>
                </div>',
        'answer' => 'phishing',
        'hint' => 'Multiple red flags: the domain is "microsoft-security.net", Microsoft always uses @microsoft.com. Windows licenses don\'t expire this way, and threats of "permanent data loss" are classic scare tactics.'
    ],
    6 => [
        'sender' => 'appleid@id-apple.com',
        'sender_name' => 'Apple Support',
        'subject' => 'Your Apple ID has been locked',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #1d1d1f; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                <div style="border-bottom: 1px solid #d2d2d7; padding-bottom: 20px; margin-bottom: 20px;">
                    <span style="color: #000; font-size: 24px; font-weight: 600; display: inline-block; margin-bottom: 10px;">Apple</span>
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
                    <p style="margin: 0;"><a href="#" style="color: white; text-decoration: none; font-weight: 500; display: inline-block; padding: 10px 20px; background-color: #0071e3; color: white; border-radius: 980px;">Unlock Apple ID Now</a></p>
                </div>
                <p style="margin: 0 0 20px 0; font-size: 13px; color: #86868b;">If you did not attempt to access your account, please secure it immediately.</p>
                <p style="margin: 0 0 20px 0;">Sincerely,<br><strong>Apple Support</strong></p>
                <div style="border-top: 1px solid #d2d2d7; padding-top: 20px; margin-top: 30px; font-size: 12px; color: #86868b;">
                    <p style="margin: 0 0 10px 0;">Apple Inc. | One Apple Park Way, Cupertino, CA 95014</p>
                    <p style="margin: 0;">This is an automated message. Please do not reply.</p>
                </div>
            </div>',
        'answer' => 'phishing',
        'hint' => 'The sender domain is "id-apple.com", Apple only ever emails from @apple.com. The reversed domain order (id-apple instead of apple-id) is a classic phishing trick to fool people at a glance.'
    ],
    7 => [
        'sender' => 'accounts.google.com',
        'sender_name' => 'Google',
        'subject' => 'Security checkup required',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #202124; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                <div style="border-bottom: 1px solid #dadce0; padding-bottom: 20px; margin-bottom: 20px;">
                    <span style="color: #4285f4; font-size: 24px; font-weight: 500; display: inline-block; margin-bottom: 10px;">G</span><span style="color: #ea4335; font-size: 24px; font-weight: 500;">o</span><span style="color: #fbbc05; font-size: 24px; font-weight: 500;">o</span><span style="color: #4285f4; font-size: 24px; font-weight: 500;">g</span><span style="color: #34a853; font-size: 24px; font-weight: 500;">l</span><span style="color: #ea4335; font-size: 24px; font-weight: 500;">e</span>
                    <h1 style="font-size: 20px; font-weight: 400; color: #202124; margin: 10px 0 0 0;">Security checkup</h1>
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
                <p style="margin: 0 0 20px 0;"><a href="#" style="color: #1a73e8; text-decoration: none; font-weight: 500;">Review your security settings</a></p>
                <p style="margin: 0 0 20px 0;">If this wasn\'t you, your account may have been compromised. You should change your password immediately.</p>
                <p style="margin: 0 0 20px 0;">Thanks,<br><strong>The Google Accounts team</strong></p>
                <div style="border-top: 1px solid #dadce0; padding-top: 20px; margin-top: 30px; font-size: 12px; color: #5f6368;">
                    <p style="margin: 0 0 10px 0;">You received this email to let you know about important changes to your Google Account and services.</p>
                    <p style="margin: 0;">© 2025 Google LLC, 1600 Amphitheatre Parkway, Mountain View, CA 94043</p>
                </div>
            </div>',
        'answer' => 'legitimate',
        'hint' => 'This is a legitimate email. It comes from Google\'s official domain, links only to myaccount.google.com, and doesn\'t demand immediate action or threaten account deletion.'
    ],
    8 => [
        'sender' => 'support@linkedin-professional.com',
        'sender_name' => 'LinkedIn Member Support',
        'subject' => 'Someone viewed your profile',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #000000; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                <div style="border-bottom: 1px solid #0077b5; padding-bottom: 20px; margin-bottom: 20px;">
                    <span style="color: #0077b5; font-size: 24px; font-weight: bold; display: inline-block; margin-bottom: 10px;">in</span>
                    <h1 style="font-size: 20px; font-weight: 600; color: #000000; margin: 0;">See who\'s viewed your profile</h1>
                </div>
                <p style="margin: 0 0 20px 0;">Hi Member,</p>
                <p style="margin: 0 0 20px 0;">Your profile was viewed 15 times in the last 7 days. Upgrade to LinkedIn Premium to see everyone who\'s viewed your profile and get insights that can help you grow your network.</p>
                <div style="background-color: #f3f6f8; border: 1px solid #0077b5; border-radius: 4px; padding: 20px; margin: 0 0 20px 0; text-align: center;">
                    <p style="margin: 0 0 15px 0; font-weight: 600; color: #0077b5;">Limited Time Offer: 50% OFF Premium</p>
                    <p style="margin: 0 0 15px 0; font-size: 18px; font-weight: bold;">Only $14.99/month</p>
                    <p style="margin: 0;"><a href="#" style="color: white; text-decoration: none; font-weight: 600; display: inline-block; padding: 12px 30px; background-color: #0077b5; border-radius: 24px; text-transform: uppercase;">Claim Your Discount Now</a></p>
                    <p style="margin: 15px 0 0 0; font-size: 12px; color: #666;">Offer expires in 48 hours</p>
                </div>
                <p style="margin: 0 0 20px 0;">Premium members get 5x more profile views and are 40% more likely to receive opportunities.</p>
                <p style="margin: 0 0 20px 0;">Best,<br><strong>The LinkedIn Team</strong></p>
                <div style="border-top: 1px solid #e0e0e0; padding-top: 20px; margin-top: 30px; font-size: 12px; color: #666;">
                    <p style="margin: 0 0 10px 0;">This message was sent to LinkedIn member</p>
                    <p style="margin: 0;">© 2025 LinkedIn Corporation, 1000 W Maude Ave, Sunnyvale, CA 94085</p>
                </div>
            </div>',
        'answer' => 'phishing',
        'hint' => 'The domain is "linkedin-professional.com", LinkedIn only emails from @linkedin.com. The "48 hours only" urgency combined with a fake domain is a textbook phishing combination.'
    ],
    9 => [
        'sender' => 'noreply@github.com',
        'sender_name' => 'GitHub',
        'subject' => 'New sign-in to your GitHub account',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #24292e; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                <div style="border-bottom: 1px solid #e1e4e8; padding-bottom: 20px; margin-bottom: 20px;">
                    <span style="color: #24292e; font-size: 24px; font-weight: 600; display: inline-block; margin-bottom: 10px;">GitHub</span>
                    <h1 style="font-size: 20px; font-weight: 400; color: #24292e; margin: 0;">New sign-in to your account</h1>
                </div>
                <p style="margin: 0 0 20px 0;">Hello,</p>
                <p style="margin: 0 0 20px 0;">We noticed a new sign-in to your GitHub account.</p>
                <div style="background-color: #f6f8fa; border: 1px solid #e1e4e8; border-radius: 6px; padding: 20px; margin: 0 0 20px 0;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 13px;">
                        <tr><td style="padding: 8px 0; color: #586069; width: 100px;">Date:</td><td style="padding: 8px 0; font-weight: 600;">' . date('F j, Y') . '</td></tr>
                        <tr><td style="padding: 8px 0; color: #586069;">Time:</td><td style="padding: 8px 0;">' . date('g:i A') . ' UTC</td></tr>
                        <tr><td style="padding: 8px 0; color: #586069;">IP Address:</td><td style="padding: 8px 0;">192.168.1.105</td></tr>
                        <tr><td style="padding: 8px 0; color: #586069;">Location:</td><td style="padding: 8px 0;">San Francisco, CA, US</td></tr>
                        <tr><td style="padding: 8px 0; color: #586069;">Device:</td><td style="padding: 8px 0;">Chrome on Windows</td></tr>
                    </table>
                </div>
                <p style="margin: 0 0 20px 0;">If this was you, you can disregard this email. There\'s no need to take any action.</p>
                <p style="margin: 0 0 20px 0;">If you don\'t recognize this activity, please secure your account:</p>
                <p style="margin: 0 0 20px 0;"><a href="#" style="color: #0366d6; text-decoration: none; font-weight: 500;">Review account security</a></p>
                <p style="margin: 0 0 20px 0;">Thanks,<br><strong>GitHub Security</strong></p>
                <div style="border-top: 1px solid #e1e4e8; padding-top: 20px; margin-top: 30px; font-size: 12px; color: #586069;">
                    <p style="margin: 0 0 10px 0;">This email was automatically sent by GitHub Security to keep you informed about your account.</p>
                    <p style="margin: 0;">© 2025 GitHub, Inc. | 88 Colin P Kelly Jr St, San Francisco, CA 94107</p>
                </div>
            </div>',
        'answer' => 'legitimate',
        'hint' => 'This is a legitimate email. Sent from GitHub\'s official @github.com domain, it provides detailed sign-in info, gives you the option to ignore it, and links only to github.com/settings.'
    ],
    10 => [
        'sender' => 'banking@wellsfargo-security.com',
        'sender_name' => 'Wells Fargo Security',
        'subject' => 'SUSPICIOUS TRANSACTION ALERT - Immediate Action Required',
        'body' => '<div style="font-family: Arial, Helvetica, sans-serif; color: #333; line-height: 1.5; font-size: 14px; max-width: 600px; margin: 0 auto;">
                <div style="background-color: #c00; color: white; padding: 20px; border-radius: 4px 4px 0 0; margin-bottom: 20px;">
                    <span style="font-size: 24px; font-weight: bold; display: inline-block; margin-bottom: 10px;">WELLS FARGO</span>
                    <h1 style="font-size: 22px; font-weight: 700; color: white; margin: 0;">URGENT: Fraud Alert</h1>
                </div>
                <p style="margin: 0 0 20px 0; font-weight: 600;">ATTENTION VALUED CUSTOMER,</p>
                <p style="margin: 0 0 20px 0;">Our fraud detection system has identified a suspicious transaction on your Wells Fargo account ending in ••••4321.</p>
                <div style="background-color: #fff3f3; border: 2px solid #c00; border-radius: 4px; padding: 20px; margin: 0 0 20px 0;">
                    <p style="margin: 0 0 15px 0; font-weight: 700; color: #c00; text-transform: uppercase;">Transaction Details:</p>
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 13px;">
                        <tr><td style="padding: 8px 0; color: #333; width: 120px;">Amount:</td><td style="padding: 8px 0; font-weight: 700; color: #c00;">$2,450.00</td></tr>
                        <tr><td style="padding: 8px 0; color: #333;">Merchant:</td><td style="padding: 8px 0;">Electronics Plus Inc.</td></tr>
                        <tr><td style="padding: 8px 0; color: #333;">Location:</td><td style="padding: 8px 0;">Miami, FL</td></tr>
                        <tr><td style="padding: 8px 0; color: #333;">Time:</td><td style="padding: 8px 0;">' . date('g:i A') . ' EST</td></tr>
                    </table>
                    <p style="margin: 20px 0 15px 0; font-weight: 700; color: #c00;">THIS TRANSACTION WILL BE PROCESSED IN 1 HOUR UNLESS CONFIRMED BY YOU.</p>
                    <div style="text-align: center; margin: 20px 0 0 0;">
                        <a href="#" style="color: white; text-decoration: none; font-weight: 700; display: inline-block; padding: 15px 40px; background-color: #c00; border-radius: 4px; font-size: 16px; text-transform: uppercase;">VERIFY TRANSACTION NOW</a>
                    </div>
                </div>
                <p style="margin: 0 0 20px 0; font-weight: 600;">If you did not authorize this transaction, your account will be immediately locked to prevent further unauthorized activity.</p>
                <p style="margin: 0 0 20px 0;">Sincerely,<br><strong>Wells Fargo Fraud Prevention Department</strong></p>
                <div style="border-top: 1px solid #ccc; padding-top: 20px; margin-top: 30px; font-size: 11px; color: #666;">
                    <p style="margin: 0 0 10px 0;">Wells Fargo Bank, N.A. | 420 Montgomery Street, San Francisco, CA 94104</p>
                    <p style="margin: 0;">This is an automated security alert. Do not reply to this email.</p>
                </div>
            </div>',
        'answer' => 'phishing',
        'hint' => 'The domain "wellsfargo-security.com" is fake, real Wells Fargo emails come from @wellsfargo.com only. The extreme 1-hour countdown pressure and threat of account lockout are designed to stop you thinking clearly.'
    ]
];

// Handle the "Next Question" action - advances to the next question
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'next') {
    $next_question = $current_question + 1;

    if($next_question > $total_questions) {
        // All questions answered - save score to DB and mark game complete
        $game_completed = true;
        $user_id = $_SESSION['id'];
        $sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at)
                VALUES (?, 'phishing_detective_lvl1', ?, ?, NOW())
                ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = NOW()";

        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $user_id, $score, $total_questions);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        // Clear all phishing game session data
        unset($_SESSION['phishing_score']);
        unset($_SESSION['phishing_question']);
        unset($_SESSION['phishing_feedback']);
        unset($_SESSION['phishing_answered']);
    } else {
        // Advance to the next question and clear the answered flag
        $_SESSION['phishing_question'] = $next_question;
        unset($_SESSION['phishing_answered']);
        unset($_SESSION['phishing_feedback']);
    }

    header("location: phishing-game-1.php");
    exit;
}

// Handle an answer submission - stay on the same question, show feedback
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['answer']) && isset($_POST['question_id'])) {
    $user_answer = $_POST['answer'];
    $question_id = (int)$_POST['question_id'];

    // Only process if we haven't already answered this question
    if(isset($emails[$question_id]) && !isset($_SESSION['phishing_answered'])) {
        $correct_answer = $emails[$question_id]['answer'];
        $hint = $emails[$question_id]['hint'];

        if($user_answer === $correct_answer) {
            $score++;
            $_SESSION['phishing_score'] = $score;
            $feedback = "<div class='feedback correct'><span style='color: #10b981;'>Correct!</span></div>";
        } else {
            $feedback = "<div class='feedback incorrect'><span style='color: #dc2626;'>Incorrect</span></div>";
        }

        $feedback .= "<div class='hint-box'><strong>Hint:</strong> " . htmlspecialchars($hint) . "</div>";

        // Store feedback and mark this question as answered in the session
        $_SESSION['phishing_feedback'] = $feedback;
        $_SESSION['phishing_answered'] = true;
    }
}

// If the reset parameter is in the URL, clear all game session data
if(isset($_GET['reset'])) {
    unset($_SESSION['phishing_score']);
    unset($_SESSION['phishing_question']);
    unset($_SESSION['phishing_feedback']);
    unset($_SESSION['phishing_answered']);
    header("location: phishing-game-1.php");
    exit;
}

// Load state for rendering

// Re-read score and question after any session updates above
$score = isset($_SESSION['phishing_score']) ? $_SESSION['phishing_score'] : 0;
$current_question = isset($_SESSION['phishing_question']) ? $_SESSION['phishing_question'] : 1;

// Has the user already answered the current question this round?
$already_answered = isset($_SESSION['phishing_answered']) && $_SESSION['phishing_answered'] === true;

// Load feedback from session if it wasn't set during this request
if(empty($feedback) && isset($_SESSION['phishing_feedback'])) {
    $feedback = $_SESSION['phishing_feedback'];
}

// Cap the displayed question number at the total
$display_question = min($current_question, $total_questions);

// Load the email data for the current question if the game is still in progress
$current_email = null;
if(!$game_completed && isset($emails[$current_question])) {
    $current_email = $emails[$current_question];
}

// Safety check - if the question counter has gone past the total outside of POST, mark complete
if($current_question > $total_questions && !$game_completed) {
    $game_completed = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/phishing.png" type="image/x-icon">
    <title>Phishing Detective - Read Emails | CybAware</title>

    <?php // Load the main site stylesheet ?>
    <link rel="stylesheet" href="css/styles.css">

    <style>
        <?php // Centres the game content and stacks everything vertically ?>
        .game-interface {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            width: 100%;
        }

        <?php // Centres the game title and subtitle above the email card ?>
        .game-header {
            text-align: center;
            margin-bottom: 30px;
            width: 100%;
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

        <?php // Wrapper for the progress bar and labels above it ?>
        .progress-container {
            margin-bottom: 25px;
            width: 100%;
            box-sizing: border-box;
        }

        <?php // Row with the current question number on the left and the score on the right ?>
        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
            color: #6b7280;
        }

        <?php // Grey track that the blue progress fill sits inside ?>
        .progress-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        <?php // Blue fill that grows as the user progresses through the emails ?>
        .progress-fill {
            height: 100%;
            background: #1e40af;
            transition: width 0.3s ease;
        }

        <?php // Base styles for the correct and incorrect feedback banners ?>
        .feedback {
            padding: 16px;
            border-radius: 6px;
            margin: 0 0 16px 0;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
            border: 1px solid transparent;
        }

        <?php // Green banner shown when the user correctly identifies the email ?>
        .feedback.correct {
            background: #f0fdf4;
            color: #065f46;
            border-color: #10b981;
        }

        <?php // Red banner shown when the user gets the answer wrong ?>
        .feedback.incorrect {
            background: #fef2f2;
            color: #991b1b;
            border-color: #ef4444;
        }

        <?php // Yellow hint box shown below the email body explaining the correct answer ?>
        .hint-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 14px;
            margin: 0 0 20px 0;
            font-size: 14px;
            color: #92400e;
        }

        <?php // White card that displays the email the user needs to analyse ?>
        .email-container {
            background: white;
            border-radius: 8px;
            padding: 0;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
        }

        <?php // Light grey header area showing the subject, sender and recipient details ?>
        .email-header {
            background: #f8fafc;
            padding: 25px;
            border-bottom: 1px solid #e2e8f0;
            width: 100%;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        <?php // Row containing the Subject label and the email subject text ?>
        .email-subject-row {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .email-subject-label {
            color: #374151;
            font-weight: 600;
            font-size: 0.9rem;
            min-width: 70px;
            margin-right: 15px;
        }

        .email-subject-value {
            flex: 1;
            font-weight: 600;
            font-size: 1.2rem;
            color: #1f2937;
        }

        <?php // Row containing the sender avatar, name, email address and timestamp ?>
        .email-sender-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        <?php // Container grouping the avatar and sender details together ?>
        .sender-info-container {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
            min-width: 300px;
        }

        <?php // Circular blue avatar showing the first letter of the sender name ?>
        .sender-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .sender-details { flex: 1; }

        <?php // Row showing the sender display name and their email address side by side ?>
        .sender-name-email {
            display: flex;
            align-items: baseline;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 3px;
        }

        .sender-display-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 1rem;
        }

        <?php // The sender email address shown in grey next to the display name - key for spotting phishing ?>
        .sender-email-address {
            color: #6b7280;
            font-size: 0.9rem;
        }

        <?php // Timestamp shown on the right side of the sender row ?>
        .email-time {
            color: #6b7280;
            font-size: 0.85rem;
            white-space: nowrap;
            margin-left: 20px;
            min-width: 180px;
            text-align: right;
        }

        <?php // Row at the bottom of the header showing who the email was sent to ?>
        .email-to-row {
            display: flex;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        .email-to-label {
            color: #374151;
            font-weight: 600;
            font-size: 0.9rem;
            min-width: 70px;
            margin-right: 15px;
        }

        .email-to-value {
            color: #6b7280;
            font-size: 0.9rem;
        }

        <?php // White padded area below the header containing the full email body content ?>
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

        <?php // Row containing the Legitimate and Phishing answer buttons side by side ?>
        .options-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            width: 100%;
            margin-bottom: 20px;
        }

        <?php // Base styles shared by both answer option buttons ?>
        .option-btn {
            flex: 1;
            min-width: 150px;
            max-width: 300px;
            padding: 18px 30px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .option-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        <?php // Red styled button for the Phishing Attempt answer option ?>
        .phishing-btn { color: #dc2626; border-color: #fecaca; }
        .phishing-btn:hover { background: #fee2e2; border-color: #dc2626; }
        .phishing-btn.selected { background: #dc2626; color: white; border-color: #dc2626; }

        <?php // Green styled button for the Legitimate Email answer option ?>
        .legit-btn { color: #059669; border-color: #a7f3d0; }
        .legit-btn:hover { background: #d1fae5; border-color: #059669; }
        .legit-btn.selected { background: #059669; color: white; border-color: #059669; }

        <?php // Centres the submit / next button below the answer options ?>
        .game-controls {
            text-align: center;
            margin-top: 20px;
            width: 100%;
        }

        <?php // Blue submit button used to confirm the selected answer ?>
        .submit-btn {
            padding: 16px 50px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 250px;
            display: inline-block;
            box-shadow: 0 4px 6px rgba(30, 64, 175, 0.2);
        }

        .submit-btn:hover:not(:disabled) {
            background: #1e3a8a;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(30, 64, 175, 0.3);
        }

        <?php // Greyed out disabled state shown before the user has selected an answer ?>
        .submit-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        <?php // Centred white card shown when all ten emails have been answered ?>
        .completion-screen {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #e2e8f0;
        }

        .completion-screen h2 { color: #1e40af; font-size: 2rem; margin-bottom: 15px; }

        <?php // Bold text showing the final score on the completion screen ?>
        .score-result {
            font-size: 1.3rem;
            color: #334155;
            margin-bottom: 25px;
            font-weight: 600;
        }

        <?php // Row of action buttons at the bottom of the completion screen ?>
        .completion-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
            width: 100%;
        }

        <?php // Primary blue action button on the completion screen ?>
        .action-btn {
            padding: 14px 35px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
            box-sizing: border-box;
            min-width: 180px;
            text-align: center;
        }

        .action-btn:hover {
            background: #1e3a8a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(30, 64, 175, 0.2);
        }

        <?php // Secondary outlined button variant on the completion screen ?>
        .action-btn.secondary {
            background: white;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }

        .action-btn.secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        <?php // Light blue note at the bottom of the completion screen ?>
        .certificate-note {
            margin-top: 20px;
            padding: 15px;
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 6px;
            color: #0369a1;
            font-size: 14px;
            text-align: center;
        }

        <?php // Makes every direct child of the game interface take the full available width ?>
        .game-interface {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .game-interface > * {
            width: 100%;
            box-sizing: border-box;
        }

        <?php // On small screens the layout adjusts so all elements stack vertically ?>
        @media (max-width: 768px) {
            .game-interface { padding: 15px; }
            .email-sender-row { flex-direction: column; align-items: flex-start; gap: 15px; }
            .sender-info-container { min-width: 100%; margin-bottom: 5px; }
            .email-time { margin-left: 0; text-align: left; min-width: auto; }
            .sender-name-email { flex-direction: column; gap: 5px; }
            .email-subject-row { flex-direction: column; align-items: flex-start; gap: 5px; }
            .email-subject-label { min-width: auto; }
            .email-to-row { flex-direction: column; align-items: flex-start; gap: 5px; }
            .email-to-label { min-width: auto; }
            .options-container { flex-direction: column; align-items: center; }
            .option-btn { width: 100%; max-width: 100%; margin-bottom: 10px; }
            .email-body { padding: 20px; font-size: 13px; }
            .completion-actions { flex-direction: column; align-items: center; }
            .action-btn { width: 100%; max-width: 300px; text-align: center; margin-bottom: 10px; }
            .submit-btn { width: 100%; max-width: 100%; }
            .game-header h1 { font-size: 1.6rem; }
            .email-header { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php // Load the shared navigation bar at the top of the page ?>
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-interface">

                <?php // Game title and subtitle shown above the progress bar ?>
                <div class="game-header">
                    <h1>Phishing Detective | Read Emails</h1>
                    <p>Analyze emails and identify phishing attempts</p>
                </div>

                <?php // Progress bar showing how far through the ten emails the user is ?>
                <div class="progress-container">
                    <div class="progress-info">
                        <span>Question <?php echo $display_question; ?> of <?php echo $total_questions; ?></span>
                        <span>Score: <?php echo $score; ?>/<?php echo $total_questions; ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $game_completed ? '100' : (($current_question - 1) / $total_questions * 100); ?>%;"></div>
                    </div>
                </div>

                <?php // Show the completion screen if all emails have been judged, otherwise show the current email ?>
                <?php if($game_completed): ?>
                    <?php // Completion card with the final score, a performance message and action buttons ?>
                    <div class="completion-screen">
                        <h2>Assessment Complete</h2>
                        <div class="score-result">
                            You scored <?php echo $score; ?> out of <?php echo $total_questions; ?> correctly.
                        </div>

                        <?php
                        // Show a different performance message depending on the percentage scored
                        $percentage = ($score / $total_questions) * 100;
                        if($percentage >= 80) {
                            echo '<p style="color: #059669; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Excellent! You have strong phishing detection skills.</p>';
                        } elseif($percentage >= 60) {
                            echo '<p style="color: #d97706; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Good job! You can identify most phishing attempts.</p>';
                        } else {
                            echo '<p style="color: #dc2626; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Practice makes perfect! Review phishing indicators to improve.</p>';
                        }
                        ?>

                        <?php // Buttons to go back to the games list, view the certificate, or replay this game ?>
                        <div class="completion-actions">
                            <a href="game.php" class="action-btn secondary">Back to Games</a>
                            <a href="certificate.php" class="action-btn">View Certificate</a>
                            <a href="phishing-game-1.php?reset=1" class="action-btn">Play Again</a>
                        </div>

                        <?php // Reminder telling the user which games still need to be completed for the certificate ?>
                        <div class="certificate-note">
                            <strong>Progress:</strong> You've completed Phishing Detective - Read Emails. Complete Hunt Errors and Password Fortress to unlock your cybersecurity awareness certificate.
                        </div>
                    </div>

                <?php else: ?>
                    <?php if($current_email): ?>

                        <?php // Show the correct/incorrect feedback banner above the email if already answered ?>
                        <?php if(!empty($feedback)): ?>
                            <?php
                            preg_match('/<div class=\'feedback[^\']*\'.*?<\/div>/s', $feedback, $topFeedback);
                            if(!empty($topFeedback)) echo $topFeedback[0];
                            ?>
                        <?php endif; ?>

                        <?php // The email card showing the subject, sender details and full email body ?>
                        <div class="email-container">
                            <div class="email-header">

                                <?php // Subject line row at the top of the email header ?>
                                <div class="email-subject-row">
                                    <div class="email-subject-label">Subject:</div>
                                    <div class="email-subject-value"><?php echo htmlspecialchars($current_email['subject']); ?></div>
                                </div>

                                <?php // Sender row showing the avatar, display name, email address and timestamp ?>
                                <div class="email-sender-row">
                                    <div class="sender-info-container">
                                        <div class="sender-avatar">
                                            <?php echo strtoupper(substr($current_email['sender_name'], 0, 1)); ?>
                                        </div>
                                        <div class="sender-details">
                                            <div class="sender-name-email">
                                                <div class="sender-display-name"><?php echo htmlspecialchars($current_email['sender_name']); ?></div>
                                                <?php // The sender email address is the key clue for spotting phishing emails ?>
                                                <div class="sender-email-address"><?php echo htmlspecialchars($current_email['sender']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="email-time">
                                        <?php echo date('l, F j, Y') . ' at ' . date('g:i A'); ?>
                                    </div>
                                </div>

                                <?php // To row showing the logged in user's email address as the recipient ?>
                                <div class="email-to-row">
                                    <div class="email-to-label">To:</div>
                                    <div class="email-to-value">
                                        Me (<?php echo htmlspecialchars($_SESSION['email'] ?? 'you@example.com'); ?>)
                                    </div>
                                </div>
                            </div>

                            <?php // The full HTML body of the email rendered below the header ?>
                            <div class="email-body">
                                <?php echo $current_email['body']; ?>
                            </div>
                        </div>

                        <?php // Show the hint below the email body after the user has answered ?>
                        <?php if(!empty($feedback) && $already_answered): ?>
                            <?php
                            preg_match('/<div class=\'hint-box\'.*?<\/div>/s', $feedback, $hintMatch);
                            if(!empty($hintMatch)) echo $hintMatch[0];
                            ?>
                        <?php endif; ?>

                        <?php if($already_answered): ?>
                            <?php // User has answered - show the Next Question button ?>
                            <form method="POST" action="phishing-game-1.php">
                                <input type="hidden" name="action" value="next">
                                <div class="game-controls">
                                    <button type="submit" class="submit-btn">
                                        <?php echo $current_question == $total_questions ? 'See Results &#8594;' : 'Next Question'; ?>
                                    </button>
                                </div>
                            </form>

                        <?php else: ?>
                            <?php // User hasn't answered yet - show the answer form ?>
                            <form method="POST" action="phishing-game-1.php" id="gameForm">
                                <?php // Hidden fields that carry the current question number and selected answer on submission ?>
                                <input type="hidden" name="question_id" value="<?php echo $current_question; ?>">
                                <input type="hidden" name="answer" id="selectedAnswer" value="">

                                <?php // Two answer buttons letting the user judge the email ?>
                                <div class="options-container">
                                    <button type="button" class="option-btn legit-btn" onclick="selectAnswer('legitimate', this)">
                                        Legitimate Email
                                    </button>
                                    <button type="button" class="option-btn phishing-btn" onclick="selectAnswer('phishing', this)">
                                        Phishing Attempt
                                    </button>
                                </div>

                                <?php // Submit button - says Complete Assessment on the last email ?>
                                <div class="game-controls">
                                    <button type="submit" class="submit-btn" id="submitBtn" disabled>
                                        <?php echo $current_question == $total_questions ? 'Complete Assessment' : 'Submit Answer'; ?>
                                    </button>
                                </div>
                            </form>

                        <?php endif; ?>

                    <?php else: ?>
                        <?php // Fallback shown if the email data fails to load for any reason ?>
                        <div class="completion-screen">
                            <p>Loading assessment...</p>
                            <div class="completion-actions">
                                <a href="phishing-game-1.php?reset=1" class="action-btn">Restart Game</a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>

        <?php // Load the shared footer at the bottom of the page ?>
        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
        // Track which answer the user has currently selected
        let selectedAnswer = null;

        // Highlight the clicked button, store the answer value and enable the submit button
        function selectAnswer(answer, btn) {
            document.querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            document.getElementById('selectedAnswer').value = answer;
            document.getElementById('submitBtn').disabled = false;
            selectedAnswer = answer;
        }

        // Block form submission if the user tries to submit without selecting an answer
        document.getElementById('gameForm')?.addEventListener('submit', function(e) {
            if(!selectedAnswer) {
                e.preventDefault();
                alert('Please select an answer before continuing.');
                return false;
            }
            return true;
        });

        // Keyboard shortcuts - press 1 or L for Legitimate, 2 or P for Phishing, Enter to submit
        document.addEventListener('keydown', function(e) {
            const legitBtn = document.querySelector('.legit-btn');
            const phishingBtn = document.querySelector('.phishing-btn');
            if((e.key === '1' || e.key === 'l') && legitBtn) {
                selectAnswer('legitimate', legitBtn);
            } else if((e.key === '2' || e.key === 'p') && phishingBtn) {
                selectAnswer('phishing', phishingBtn);
            } else if(e.key === 'Enter' && selectedAnswer) {
                document.getElementById('submitBtn')?.click();
            }
        });
    </script>
</body>
</html>