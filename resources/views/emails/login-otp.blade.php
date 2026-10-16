<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login Verification Code</title>
</head>

<body
    style="
        margin:0;
        padding:0;
        background:#f8fafc;
        font-family:Arial,Helvetica,sans-serif;
        color:#0f172a;
    "
>
    <div style="padding:40px 15px;">

        <div
            style="
                max-width:520px;
                margin:0 auto;
                background:#ffffff;
                border:1px solid #e2e8f0;
                border-radius:20px;
                overflow:hidden;
                box-shadow:0 10px 30px rgba(15,23,42,.08);
            "
        >

            <!-- HEADER -->

            <div
                style="
                    padding:28px 30px;
                    background:#ecfdf5;
                    border-bottom:1px solid #d1fae5;
                    text-align:center;
                "
            >
                <div
                    style="
                        display:inline-block;
                        width:52px;
                        height:52px;
                        line-height:52px;
                        border-radius:15px;
                        background:#d1fae5;
                        color:#059669;
                        font-size:24px;
                        font-weight:bold;
                    "
                >
                    🔐
                </div>

                <h1
                    style="
                        margin:15px 0 0;
                        font-size:22px;
                        color:#0f172a;
                    "
                >
                    Login Verification
                </h1>

                <p
                    style="
                        margin:6px 0 0;
                        font-size:14px;
                        color:#64748b;
                    "
                >
                    {{ $systemName }}
                </p>
            </div>

            <!-- BODY -->

            <div style="padding:30px;">

                <p
                    style="
                        margin:0 0 15px;
                        font-size:15px;
                        line-height:1.7;
                        color:#334155;
                    "
                >
                    A login attempt requires additional verification.
                    Use the verification code below to continue.
                </p>

                <!-- OTP -->

                <div
                    style="
                        margin:25px 0;
                        padding:22px;
                        text-align:center;
                        border-radius:15px;
                        background:#f0fdf4;
                        border:1px solid #bbf7d0;
                    "
                >
                    <div
                        style="
                            font-size:11px;
                            font-weight:bold;
                            letter-spacing:2px;
                            text-transform:uppercase;
                            color:#047857;
                            margin-bottom:10px;
                        "
                    >
                        Verification Code
                    </div>

                    <div
                        style="
                            font-size:34px;
                            font-weight:800;
                            letter-spacing:9px;
                            color:#065f46;
                        "
                    >
                        {{ $otp }}
                    </div>
                </div>

                <p
                    style="
                        margin:0 0 10px;
                        font-size:14px;
                        line-height:1.7;
                        color:#475569;
                    "
                >
                    This verification code will expire in
                    <strong>5 minutes</strong>.
                </p>

                <p
                    style="
                        margin:0;
                        font-size:13px;
                        line-height:1.7;
                        color:#94a3b8;
                    "
                >
                    If you did not attempt to sign in, you can safely
                    ignore this email.
                </p>

            </div>

            <!-- FOOTER -->

            <div
                style="
                    padding:20px 30px;
                    background:#f8fafc;
                    border-top:1px solid #e2e8f0;
                    text-align:center;
                "
            >
                <p
                    style="
                        margin:0;
                        font-size:11px;
                        color:#94a3b8;
                    "
                >
                    This is an automated message.
                    Please do not reply to this email.
                </p>

                <p
                    style="
                        margin:6px 0 0;
                        font-size:10px;
                        color:#cbd5e1;
                    "
                >
                    &copy; {{ date('Y') }} {{ $systemName }}.
                    All rights reserved.
                </p>
            </div>

        </div>

    </div>
</body>
</html>