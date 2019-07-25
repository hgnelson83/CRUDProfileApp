<?php
require_once "pdo.php";
session_start();

//Before you do the delete
//Make sure the user is logged in 
if (!isset($_SESSION['user_id'])) {
    die("ACCESS DENIED");    
}

if (isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql = "DELETE FROM profile WHERE profile_id = :pid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':pid' => $_POST['profile_id']));
    $_SESSION['success'] = 'Profile deleted';
    header('Location: index.php');
    return;
    
}

if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare("SELECT first_name, last_name, profile_id FROM profile WHERE profile_id = :pid2");
$stmt->execute(array(":pid2" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);


// Making sure that the entry actually exists
if( $row == false) {
    $_SESSION['error'] = 'Could not load profile';
    header('Location: index.php');
    return;
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Nelson Herrero - Delete profile</title>
<?php require_once "head.php" ?>
</head>
<body>
<div class="container">
    <h1>Deleteing Profile</h1>

    <form method="post" action="delete.php">
        <p>First Name: <?= $row['first_name'] ?></p>
        <p>Last Name: <?= $row['last_name'] ?></p>
        <input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>" class="btn btn-primary">
        <input type="submit" name="delete" value="Delete"  class="btn btn-info">
        <input type="submit" name="cancel" value="Cancel" class="btn btn-warning">
    </form>
    
</div>
</body>
</html>