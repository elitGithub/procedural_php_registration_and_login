<?php include 'includes/header.php'?>
<?php include 'includes/nav.php'?>

<?php if (!isLoggedIn()) redirect('login.php'); ?>
    <div class="jumbotron">
        <h1 class="text-center">
            ABOUT
        </h1>
    </div>


<?php include "includes/footer.php";?>