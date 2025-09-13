<html>
<body>
    <h2>Email Verification</h2>
    <p>Thank you for registering. Please verify your email by clicking the link below:</p>
    <p>
        <a href="{{ url('/verify-email?code=' . $activationCode) }}">Verify Email</a>
    </p>
    <p>If you did not register, please ignore this email.</p>
</body>
</html>
