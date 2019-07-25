<?php
require_once "pdo.php";
require_once "util.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    die("ACCESS DENIED");
    return;
}

if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}

if (!isset($_REQUEST['profile_id']) ) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;    
}

//Load the profile in question
$stmt = $pdo->prepare('SELECT * FROM Profile
    WHERE profile_id = :prof AND user_id = :uid');
$stmt->execute(array(':prof' => $_REQUEST['profile_id'],
                     ':uid' => $_SESSION['user_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ($profile === false) {
    $_SESSION['error'] = "Could not load profile";
    header("Location: index.php");
    return;
}

//To save the new edited values
if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) ) {
    //Validate profile entries
    $msg = validateProfile();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_REQUEST['profile_id']);
        return;        
    }

    //Validate position entries if present
    $msg = validatePos();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_REQUEST['profile_id']);
        return;        
    }
    
    //Validate Education entries if present
    $msg = validateEdu();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;        
    }  
    
    //Update the data
    $stmt = $pdo->prepare("UPDATE profile SET 
    first_name = :fn, last_name = :ln, 
    email = :em, headline = :hl, summary = :sm
    WHERE profile_id = :profile_id AND user_id=:uid");
            $stmt->execute(array(
                ':fn' => $_POST['first_name'],
                ':ln' => $_POST['last_name'],
                ':em' => $_POST['email'],
                ':hl' => $_POST['headline'],
                ':sm' => $_POST['summary'],
                ':uid' => $_SESSION['user_id'],
                ':profile_id' => $_REQUEST['profile_id']));

    //Clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM Position
        WHERE profile_id=:pid');
    $stmt->execute(array(':pid' => $_REQUEST['profile_id']));
    
    //Insert the position entries
    insertPositions($pdo, $_REQUEST['profile_id']);
    
    //Clear out the old education entries
    $stmt = $pdo->prepare('DELETE FROM Education
        WHERE profile_id=:pid');
    $stmt->execute(array(':pid' => $_REQUEST['profile_id']));
    
    //Insert the education entries
    insertEducations($pdo, $_REQUEST['profile_id']);   
        
    $_SESSION['success'] = "Profile updated";
    header("Location: index.php");
    return;

}

//Load up the position and education rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo,  $_REQUEST['profile_id']);

?>
<!DOCTYPE html>
<html>
<head>
<title>Nelson Herrero's Profile Edit</title>
<?php require_once "head.php" ?>
</head>
<body>
<div class="container">
    <h1>Editing Profile for <?= htmlentities($_SESSION['name']); ?></h1>
    <?php
        if(isset($_SESSION['error'])) {
            echo('<p class="alert alert-danger">'.htmlentities($_SESSION['error'])."</p>\n");
            unset($_SESSION['error']);
        }
    ?>
    
    <form method="post" action="edit.php">
        <div class="form-group">
            <p>First Name:
            <input type="text" name="first_name" size="60" value="<?= htmlentities($profile['first_name']); ?>" class="form-control"/></p>
        </div>
        <div class="form-group">
            <p>Last Name:
            <input type="text" name="last_name" size="60" value="<?= htmlentities($profile['last_name']); ?>" class="form-control"/></p>
        </div>
        <div class="form-group">                
            <p>Email:
            <input type="text" name="email" size="30" value="<?= htmlentities($profile['email']); ?>" class="form-control"/></p>
        </div>
        <div class="form-group">
            <p>Headline:<br/>
            <input type="text" name="headline" size="80" value="<?= htmlentities($profile['headline']); ?>" class="form-control"/></p>
        </div>
        <div class="form-group">
            <p>Summary:<br/>
            <textarea name="summary" rows="8" cols="80" class="form-control"><?= htmlentities($profile['summary']); ?></textarea>
        </div>
        <!-- ********************* -->
        <!-- EDUCATION STARTS HERE -->
        <!-- ********************* -->
        <?php 
            $countEdu = 0;
            echo('<div class="form-group">' . "\n");
                echo('<p>Education: <input type="submit" id="addEdu" value="+" class="btn btn-info"></p>' . "\n");
                echo('<div id="edu_fields">' . "\n");
                    if (count($schools) > 0 ) {
                        foreach ($schools as $school) {
                            $countEdu++;
                            echo('<div id="edu'.$countEdu.'">'."\n");
                                echo('
                                <p>Year: <input type="text" name="edu_year'.$countEdu.'" value="'.$school['year'].'" />
                                <input type="button" class="btn btn-danger" value="-" onclick="$(\'#edu'.$countEdu.'\').remove(); return false;"></p>
                                <p>School: <input type="text" size="80" name="edu_school'.$countEdu.'" class="school"
                                value="'.htmlentities($school['name']).'" />');
                            echo "\n</div>\n";
                        }
                    }
                echo('</div>' . "\n");    
            echo('</div>' . "\n");
        ?>
        <!-- ******************* -->
        <!-- EDUCATION ENDS HERE -->
        <!-- ******************* -->
        
        <!-- POSITIONS STARTS HERE -->
        <?php 
            $countPos = 0;
            echo('<div class="form-group">' . "\n");
            echo('<p>Position:' . "\n");
            echo('<input type="submit" id="addPos" value="+" class="btn btn-info"></p>' . "\n");
            echo('<div id="position_fields">' . "\n");
            if (count($positions) > 0 ) {
                foreach ($positions as $position) {
                    $countPos++;
                    echo('<div id="position'.$countPos.'">'."\n");
                    echo('<p>Year: <input type="text" name="year'.$countPos.'"');
                    echo(' value="'.$position['year'].'" />'."\n");
                    echo('<input type="button" class="btn btn-danger" value="-" ');
                    echo('onclick="$(\'#position'.$countPos.'\').remove(); return false;">'."\n");
                    echo('</p>' . "\n");
                    echo('<textarea name="desc'.$countPos.'" rows="8" cols="80">' . "\n");
                    echo(htmlentities($position['description'])."\n");
                    echo("\n</textarea>\n</div>\n");
                }
            }
            echo('</div>' . "\n");
        echo('</div>' . "\n");
        ?>
        
        <div class="form-group">
            <input type="hidden" name="profile_id" value="<?= htmlentities($_GET['profile_id']); ?>" />
            <input type="submit" value="Save" class="btn btn-primary">
            <input type="submit" name="cancel" value="Cancel" class="btn btn-warning">
        </div>
        
    </form>    
</div>
<script>
countPos = <?= $countPos  ?>;  
countEdu = <?= $countEdu  ?>;
$(document).ready(function() {
    $('#addPos').click(function(event) {
        event.preventDefault();
        if (countPos >= 9) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
    
        countPos++;
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button"  class="btn btn-danger" value="-" \
                onclick="$(\'#position'+countPos+'\').remove(); return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>'
        );
    });
    
    $('#addEdu').click(function(event) {
        event.preventDefault();
        if (countEdu >= 9) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
    
        countEdu++;
        $('#edu_fields').append(
            '<div id="edu'+countEdu+'"> \
            <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
            <input type="button"  class="btn btn-danger" value="-" \
                onclick="$(\'#edu'+countEdu+'\').remove(); return false;"></p> \
            <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" autocomplete="off" class="school" value="" /></p>\
            </div>'
        );
        
        $('.school').autocomplete({
            source: "school.php"
        });
    });
    
    $('.school').autocomplete({
        source: "school.php"
    });
});
</script>
</body>
</html>