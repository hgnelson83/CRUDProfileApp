<?php
require_once "pdo.php";
require_once "util.php";

session_start();

//Prepare the SQL statement to display the view values accordingly
$stmt = $pdo->prepare("SELECT first_name, last_name, email, headline, summary 
FROM profile
WHERE profile_id = :pid");

$stmt->execute(array(":pid" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Making sure that the entry actually exists
if( $row == false) {
    $_SESSION['error'] = 'Could not load profile';
    header('Location: index.php');
    return;
}

$positions = loadPos($pdo, $_REQUEST['profile_id']);
$educations = loadEdu($pdo, $_REQUEST['profile_id']);

?>
<!DOCTYPE html>
<html>
<head>
<title>Nelson Herrero's Profile View</title>
<?php require_once "head.php" ?>
</head>
<body>
	<div class="container">
        <div class="row">
            <h1>Profile information</h1>
            <p><strong>First Name: </strong><?= htmlentities($row['first_name'])?></p>
            <p><strong>Last Name: </strong><?= htmlentities($row['last_name'])?></p>
            <p><strong>Email: </strong><?= htmlentities($row['email'])?></p>
            <p><strong>Headline: </strong><br/>
            <?= htmlentities($row['headline'])?></p>
            <p><strong>Summary: </strong><br/>
            <?= htmlentities($row['summary'])?><p>
            </p>
            
            <?php
            if ($educations != null) {
                $edu = 0;
                echo("<p><strong>Education: </strong></p>" . "\n");
                echo("<ul>" . "\n");
                foreach ($educations as $education) {
                    $edu++;
                    echo("<li>");
                    echo(htmlentities($education['year']) . ": " . htmlentities($education['name']) . "\n");
                    echo("</li>");
                }
                echo('</ul>' . "\n");
            } 
            ?>
            
            <?php
            if ($positions != null) {
                $pos = 0;
                echo("<p><strong>Position: </strong></p>" . "\n");
                echo("<ul>" . "\n");
                foreach ($positions as $position) {
                    $pos++;
                    echo("<li>");
                    echo(htmlentities($position['year']) . ": " . htmlentities($position['description']) . "\n");
                    echo("</li>");
                }
                echo('</ul>' . "\n");
            } 
            ?>  
            
            <a href="index.php" class="btn btn-info">Done</a>
         </div>
    </div>
</body>
</html>