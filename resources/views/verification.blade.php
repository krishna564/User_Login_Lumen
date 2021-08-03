<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Verification Mail</title>
</head>
<body>
	<h3>This Mail Contains your email token</h3>
	<p>Your email token for verification is {{$email_token}} </p>
	<p>Please Click on the link to verify <a href="{{url('/users/register/verification/'.$email_token)}}">Link</a></p>
</body>
</html>