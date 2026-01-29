<!DOCTYPE html>
<html>
<head>
    <title>Welcome to the Invoice Portal</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #4f46e5; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .credentials { background-color: #f9fafb; padding: 15px; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, {{ $name }}!</h2>
        <p>Your client portal account has been created. You can now log in to view your invoices.</p>
        
        <div class="credentials">
            <p><strong>Login URL:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
            <p><strong>Email:</strong> {{ $email }}</p>
            <p><strong>Password:</strong> {{ $password }}</p>
        </div>
        
        <p>Please change your password after logging in for the first time.</p>
        
        <a href="{{ $loginUrl }}" class="btn">Login to Portal</a>
    </div>
</body>
</html>
