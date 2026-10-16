
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Password Reset - {{ config('app.name') }}</title>

    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            min-width: 100%;
            width: 100% !important;
            height: 100% !important;
            background-color: #f1f5f9;
        }

        a {
            color: #059669;
            text-decoration: underline;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                max-width: 100% !important;
            }

            .content-padding {
                padding: 24px 16px !important;
            }

            .mobile-stack {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .mobile-align-left {
                text-align: left !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">

    <!-- Email Outer Wrapper -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%"
        style="background-color: #f1f5f9; table-layout: fixed;">
        <tr>
            <td align="center" style="padding: 32px 12px;">

                <!-- Main Email Card -->
                <table border="0" cellpadding="0" cellspacing="0"
                    width="600" class="email-container"
                    style="background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; width: 100%; max-width: 600px;">

                    <!-- Header Banner -->
                    <tr>
                        <td align="center"
                            style="background-color: #0f172a; padding: 24px; text-align: center;">

                            <h1 style="color: #ffffff; font-size: 20px; font-weight: 700; margin: 0; letter-spacing: 0.5px; line-height: 1.2;">
                                {{ config('app.name') }}
                            </h1>

                            <p style="color: #cbd5e1; font-size: 13px; margin: 8px 0 0;">
                                Account Security Notification
                            </p>

                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="content-padding"
                            style="padding: 32px 32px 24px 32px;">

                            <!-- Greeting -->
                            <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin: 0 0 12px 0; line-height: 1.3;">
                                Hello {{ $name }}! 👋
                            </h2>

                            <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 16px 0;">
                                Your account password has been reset by your
                                administrator. You can now sign in using your
                                updated credentials below.
                            </p>

                            <p style="color: #475569; font-size: 14px; line-height: 1.6; margin: 0 0 24px 0;">
                                Please keep your credentials private and follow
                                the security steps below to protect your account.
                            </p>

                            <!-- Account Details Box -->
                            <table border="0" cellpadding="0" cellspacing="0"
                                width="100%"
                                style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 24px;">

                                <tr>
                                    <td style="padding: 16px 20px;">

                                        <div style="color: #94a3b8; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 12px;">
                                            Your Account Details
                                        </div>

                                        <!-- Email Row -->
                                        <table border="0" cellpadding="0"
                                            cellspacing="0" width="100%"
                                            style="border-bottom: 1px solid #e2e8f0;">

                                            <tr>
                                                <td class="mobile-stack"
                                                    style="padding: 8px 0; color: #64748b; font-size: 14px; font-weight: 500;">
                                                    Email Address
                                                </td>

                                                <td class="mobile-stack mobile-align-left"
                                                    align="right"
                                                    style="padding: 8px 0; color: #0f172a; font-size: 14px; font-weight: 600; word-break: break-all;">
                                                    {{ $email }}
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Password Row -->
                                        <table border="0" cellpadding="0"
                                            cellspacing="0" width="100%">

                                            <tr>
                                                <td class="mobile-stack"
                                                    style="padding: 12px 0 4px 0; color: #64748b; font-size: 14px; font-weight: 500;">
                                                    Temporary Password
                                                </td>

                                                <td class="mobile-stack mobile-align-left"
                                                    align="right"
                                                    style="padding: 12px 0 4px 0;">

                                                    <span style="background-color: #fef3c7; color: #78350f; border: 1px solid #fde68a; font-family: 'Courier New', Courier, monospace; font-size: 14px; font-weight: 700; padding: 6px 10px; border-radius: 4px; display: inline-block; word-break: break-all;">
                                                        {{ $password }}
                                                    </span>

                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            <!-- Security Notice -->
                            <table border="0" cellpadding="0" cellspacing="0"
                                width="100%"
                                style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; margin-bottom: 24px;">

                                <tr>
                                    <td width="36" valign="top"
                                        style="padding: 16px 0 16px 16px; color: #92400e; font-size: 18px;">
                                        &#9888;
                                    </td>

                                    <td style="padding: 16px 16px 16px 8px;">

                                        <p style="color: #92400e; font-size: 13px; font-weight: 700; margin: 0 0 6px 0;">
                                            Important Security Reminder
                                        </p>

                                        <p style="color: #78350f; font-size: 13px; line-height: 1.6; margin: 0;">
                                            Change your temporary password as soon
                                            as possible after signing in. Never
                                            share your password with anyone.
                                        </p>

                                    </td>
                                </tr>
                            </table>

                            <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 24px 0;" />

                            <!-- What's Next Section -->
                            <div style="color: #94a3b8; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px;">
                                What's Next?
                            </div>

                            <table border="0" cellpadding="0" cellspacing="0"
                                width="100%" style="margin-bottom: 24px;">

                                <!-- Step 1 -->
                                <tr>
                                    <td valign="top" width="32"
                                        style="padding-bottom: 14px;">
                                        <div style="background-color: #d1fae5; color: #065f46; font-size: 12px; font-weight: 700; border-radius: 50%; width: 22px; height: 22px; line-height: 22px; text-align: center;">
                                            1
                                        </div>
                                    </td>

                                    <td valign="top"
                                        style="padding-bottom: 14px; color: #475569; font-size: 14px; line-height: 1.5;">
                                        Open the {{ config('app.name') }} sign-in
                                        page and enter your email address.
                                    </td>
                                </tr>

                                <!-- Step 2 -->
                                <tr>
                                    <td valign="top" width="32"
                                        style="padding-bottom: 14px;">
                                        <div style="background-color: #d1fae5; color: #065f46; font-size: 12px; font-weight: 700; border-radius: 50%; width: 22px; height: 22px; line-height: 22px; text-align: center;">
                                            2
                                        </div>
                                    </td>

                                    <td valign="top"
                                        style="padding-bottom: 14px; color: #475569; font-size: 14px; line-height: 1.5;">
                                        Enter the temporary password provided
                                        in the account details above.
                                    </td>
                                </tr>

                                <!-- Step 3 -->
                                <tr>
                                    <td valign="top" width="32">
                                        <div style="background-color: #fef3c7; color: #92400e; font-size: 12px; font-weight: 700; border-radius: 50%; width: 22px; height: 22px; line-height: 22px; text-align: center;">
                                            3
                                        </div>
                                    </td>

                                    <td valign="top"
                                        style="color: #78350f; font-size: 14px; line-height: 1.5;">
                                        <strong>Change your password immediately</strong>
                                        after signing in to keep your account secure.
                                    </td>
                                </tr>

                            </table>

                            <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 24px 0;" />

                            <!-- Security Disclaimer -->
                            <p style="color: #64748b; font-size: 12px; line-height: 1.6; margin: 0 0 12px 0;">
                                <strong style="color: #475569;">
                                    Didn't expect this change?
                                </strong>
                            </p>

                            <p style="color: #64748b; font-size: 12px; line-height: 1.6; margin: 0;">
                                If you did not request or expect this password
                                reset, please contact your system administrator
                                immediately.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center"
                            style="background-color: #f8fafc; padding: 20px; border-top: 1px solid #f1f5f9; color: #94a3b8; font-size: 12px; line-height: 1.5;">

                            Best regards,<br />

                            <strong style="color: #475569;">
                                {{ config('app.name') }} Team
                            </strong>

                            <p style="color: #94a3b8; font-size: 11px; margin: 12px 0 0;">
                                This is an automated notification.
                                Please do not reply directly to this email.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
