<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>New User</title>
</head>
<body>
	<h3>Hello {{$user->username}}</h3>
	<p>Admin has created new profile for you with this email. Your temporary password is {{$password}}. Please change your password by clicking forgot password</p>
</body>
</html>