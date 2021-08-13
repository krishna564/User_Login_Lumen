<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Reminder</title>
	<style>
		table, th, td {
		  border: 1px solid black;
		  border-collapse: collapse;
		}
	</style>
</head>
<body>
	<h2>Reminder</h2>
	<p>You have the following tasks still pending</p>
	<table>
		<thead>
			<th>Title</th>
			<th>Description</th>
			<th>Due Date</th>
		</thead>
		<tbody>
			<?php foreach ($values as $value) { ?>
				<tr>
					<td><?php echo $value->title;  ?>  </td>
					<td><?php echo $value->description;  ?>  </td>
					<td><?php echo $value->due_date;  ?>  </td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</body>
</html>