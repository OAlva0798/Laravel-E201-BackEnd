<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Notification</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #f1f5f9;
    font-family: Arial, Helvetica, sans-serif;
    color: #1e293b;
    line-height: 1.6;
">

    <table role="presentation"
           width="100%"
           cellspacing="0"
           cellpadding="0"
           style="background-color: #f1f5f9; padding: 32px 12px;">

        <tr>
            <td align="center">

                <table role="presentation"
                       width="100%"
                       cellspacing="0"
                       cellpadding="0"
                       style="
                           max-width: 560px;
                           background-color: #ffffff;
                           border-radius: 12px;
                           overflow: hidden;
                           border: 1px solid #e2e8f0;
                       ">

                    <!-- Header -->
                    <tr>
                        <td style="
                            background-color: #047857;
                            padding: 28px 32px;
                            text-align: center;
                        ">
                            <h1 style="
                                margin: 0;
                                color: #ffffff;
                                font-size: 24px;
                                font-weight: 700;
                            ">
                                Password Reset
                            </h1>

                            <p style="
                                margin: 8px 0 0;
                                color: #d1fae5;
                                font-size: 14px;
                            ">
                                Your account credentials have been updated
                            </p>
                        </td>
                    </tr>

                    <!-- Email Content -->
                    <tr>
                        <td style="padding: 32px;">

                            <h2 style="
                                margin: 0 0 16px;
                                font-size: 21px;
                                color: #0f172a;
                            ">
                                Hello {{ $name }}!
                            </h2>

                            <p style="margin: 0 0 16px; font-size: 15px;">
                                Your account password has been reset by your
                                administrator. You can now sign in using the
                                credentials provided below.
                            </p>

                            <!-- Credentials -->
                            <table role="presentation"
                                   width="100%"
                                   cellspacing="0"
                                   cellpadding="0"
                                   style="
                                       background-color: #f8fafc;
                                       border: 1px solid #e2e8f0;
                                       border-radius: 8px;
                                       margin: 24px 0;
                                   ">

                                <tr>
                                    <td style="padding: 20px;">

                                        <p style="
                                            margin: 0 0 6px;
                                            color: #64748b;
                                            font-size: 12px;
                                            font-weight: 700;
                                            text-transform: uppercase;
                                            letter-spacing: 0.5px;
                                        ">
                                            Email Address
                                        </p>

                                        <p style="
                                            margin: 0 0 20px;
                                            font-size: 15px;
                                            font-weight: 600;
                                            color: #0f172a;
                                            word-break: break-word;
                                        ">
                                            {{ $email }}
                                        </p>

                                        <p style="
                                            margin: 0 0 6px;
                                            color: #64748b;
                                            font-size: 12px;
                                            font-weight: 700;
                                            text-transform: uppercase;
                                            letter-spacing: 0.5px;
                                        ">
                                            Temporary Password
                                        </p>

                                        <p style="
                                            margin: 0;
                                            padding: 12px;
                                            background-color: #ffffff;
                                            border: 1px solid #cbd5e1;
                                            border-radius: 6px;
                                            font-family: monospace;
                                            font-size: 16px;
                                            font-weight: 700;
                                            color: #047857;
                                            word-break: break-all;
                                        ">
                                            {{ $password }}
                                        </p>

                                    </td>
                                </tr>
                            </table>

                            <!-- Security Notice -->
                            <table role="presentation"
                                   width="100%"
                                   cellspacing="0"
                                   cellpadding="0"
                                   style="
                                       background-color: #fffbeb;
                                       border-left: 4px solid #f59e0b;
                                       margin: 24px 0;
                                   ">
                                <tr>
                                    <td style="padding: 14px 16px;">
                                        <p style="
                                            margin: 0 0 6px;
                                            color: #92400e;
                                            font-size: 14px;
                                            font-weight: 700;
                                        ">
                                            Important Security Reminder
                                        </p>

                                        <p style="
                                            margin: 0;
                                            color: #78350f;
                                            font-size: 13px;
                                        ">
                                            For your protection, sign in and
                                            change your temporary password
                                            immediately. Never share your
                                            password with anyone.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 20px 0 0; font-size: 14px;">
                                If you did not request or expect this password
                                reset, please contact your administrator
                                immediately.
                            </p>

                            <p style="margin: 24px 0 0; font-size: 15px;">
                                Thank you,<br>
                                <strong style="color: #047857;">
                                    The Administration Team
                                </strong>
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="
                            padding: 20px 32px;
                            background-color: #f8fafc;
                            border-top: 1px solid #e2e8f0;
                            text-align: center;
                        ">
                            <p style="
                                margin: 0;
                                color: #64748b;
                                font-size: 12px;
                            ">
                                This is an automated email. Please do not
                                reply directly to this message.
                            </p>

                            <p style="
                                margin: 8px 0 0;
                                color: #94a3b8;
                                font-size: 11px;
                            ">
                                &copy; {{ date('Y') }} All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>