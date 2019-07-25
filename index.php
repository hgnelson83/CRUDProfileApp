<?php
require_once "pdo.php";
session_start();

?>
<!DOCTYPE html>
<html>
<head>
<title>Nelson Herrero's Registry</title>
<?php require_once "head.php" ?>
</head>
<body>
	<div class="container">
        <div class="row">
            <div class="col-sm-8">
                <h1>Nelson Herrero's Registry</h1>
                <?php    
                // line added to turn on color syntax highlight
                if ( isset($_SESSION['success']) ) {
                  echo('<p class="alert alert-success">'.htmlentities($_SESSION['success'])."</p>\n");
                  unset($_SESSION['success']);
                } else if(isset($_SESSION['error'])) {
                    echo('<p class="alert alert-danger">'.htmlentities($_SESSION['error'])."</p>\n");
                    unset($_SESSION['error']);
                }

                ?>
                
                <?php
                if (!isset($_SESSION['user_id']) ) {
                    echo '<p><a href="login.php" class="btn btn-info">Please log in</a></p>';
                    $stmt = $pdo->query("SELECT first_name, last_name, headline, profile_id FROM profile");
                    echo "\n";
                    echo('<table class="table table-striped">'."\n");
                    echo '<tr>' ."\n";
                    echo '<th>Name</th>'."\n";
                    echo '<th>Headline</th>'."\n";
                    echo '</tr>'."\n";

                    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
                        echo("<tr>"."\n");
                        echo '<td><a href="view.php?profile_id='.$row['profile_id'].'">' . htmlentities($row['first_name'] . ' ' . $row['last_name']) . '</a></td>'."\n";
                        echo "<td>" . htmlentities($row['headline']) . "</td>"."\n";
                        echo("</tr>"."\n");
                    }        
                    echo("</table>");

                }  else {
                    echo '<p><a href="logout.php" class="btn btn-warning">Logout</a></p>';
                    $stmt = $pdo->query("SELECT first_name, last_name, headline, profile_id, user_id FROM profile");
                    echo "\n";
                    echo('<table class="table table-striped">'."\n");
                    echo '<tr>' ."\n";
                    echo '<th>Name</th>'."\n";
                    echo '<th>Headline</th>'."\n";
                    echo '<th>Action</th>'."\n";
                    echo '</tr>'."\n";

                    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
                        echo '<tr>'."\n";
                        echo '<td><a href="view.php?profile_id='.$row['profile_id'].'">' . htmlentities($row['first_name'] . ' ' . $row['last_name']) . '</a></td>'."\n";
                        echo '<td>' . htmlentities($row['headline']) . '</td>'."\n";
                        echo '<td>';
                        if ($row['user_id'] == $_SESSION['user_id']) {
                            echo '<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ';
                            echo '<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>';    
                        }
                        echo '</td>'."\n";
                        echo '</tr>'."\n";
                    }        
                    echo("</table>");
                    echo '<p><a href="add.php" class="btn btn-primary">Add New Entry</a></p>';
                }       

                ?>
            </div>
        </div>
    </div>
</body>
