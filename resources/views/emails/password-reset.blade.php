<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Password Reset</title>
</head>

<body style="
        margin:0;
        padding:0;
        background:#f8fafc;
        font-family:Arial,Helvetica,sans-serif;
        color:#334155;
    ">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8fafc;padding:40px 15px;">
        <tr>
            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="
                    max-width:560px;
                    background:#ffffff;
                    border:1px solid #e2e8f0;
                    border-radius:20px;
                    overflow:hidden;
                ">

                    {{-- HEADER --}}
                    <tr>
                        <td style="
                            padding:30px 30px 20px;
                            text-align:center;
                        ">

                            <div style="
                                width:56px;
                                height:56px;
                                margin:0 auto 15px;
                                background:#ecfdf5;
                                border-radius:16px;
                                line-height:56px;
                                color:#059669;
                                font-size:26px;
                                font-weight:bold;
                            ">
                                🔐
                            </div>

                            <h1 style="
                                margin:0;
                                color:#0f172a;
                                font-size:24px;
                            ">
                                Password Reset
                            </h1>

                            <p style="
                                margin:8px 0 0;
                                color:#64748b;
                                font-size:14px;
                            ">
                                Reset your account password
                            </p>

                        </td>
                    </tr>

                    {{-- CONTENT --}}
                    <tr>
                        <td style="
                            padding:10px 30px 30px;
                        ">

                            <p style="
                                margin:0 0 15px;
                                font-size:15px;
                                line-height:1.7;
                            ">
                                Hello,
                            </p>

                            <p style="
                                margin:0 0 20px;
                                font-size:15px;
                                line-height:1.7;
                            ">
                                We received a request to reset the password
                                associated with your account.
                            </p>

                            <div style="
                                text-align:center;
                                margin:25px 0;
                            ">
                                <a href="{{ $resetUrl }}" style="
                                    display:inline-block;
                                    padding:13px 24px;
                                    background:#059669;
                                    color:#ffffff;
                                    text-decoration:none;
                                    border-radius:12px;
                                    font-size:14px;
                                    font-weight:bold;
                                ">
                                    Reset Password
                                </a>
                            </div>

                            <p style="
                                margin:0 0 10px;
                                font-size:13px;
                                line-height:1.7;
                                color:#64748b;
                            ">
                                This password reset link will expire in
                                <strong>
                                    {{ $expiresIn }} minutes
                                </strong>.
                            </p>

                            <p style="
                                margin:15px 0 0;
                                font-size:13px;
                                line-height:1.7;
                                color:#64748b;
                            ">
                                If you did not request a password reset,
                                you can safely ignore this email.
                            </p>

                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td style="
                            padding:18px 30px;
                            border-top:1px solid #f1f5f9;
                            text-align:center;
                        ">

                            <p style="
                                margin:0;
                                font-size:11px;
                                color:#94a3b8;
                            ">
                                This is an automated message.
                                Please do not reply to this email.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>