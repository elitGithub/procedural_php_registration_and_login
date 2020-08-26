<?php include 'includes/header.php'?>
<?php include 'includes/nav.php'?>

	<div class="jumbotron">
		<h1 class="text-center"> Home Page</h1>
	</div>


<?php
$query = "SELECT * FROM users;";
$result = query($query);
confirm($result);
$row = fetchAssoc($result);
echo "<pre>";
print_r($row);
echo "</pre>";
?>

<?php include "includes/footer.php";?>