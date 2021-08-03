<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Task Updated</title>
</head>
<body>
	<h3>Your task has been updated</h3>
	<p>Creator has updated your task</p>
	<p>Now your task is as follows:</p>
	<ul>
		<li>Title: {{$task->title}}</li>
		<li>Description: {{$task->description}}</li>
		<li>Due date: {{$task->due_date}}</li>
	</ul>
</body>
</html>