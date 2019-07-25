<?php // line 1 added to enable color highlight
require_once "pdo.php";
require_once "util.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    die("ACCESS DENIED");    
}

if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}

if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) ) {   
    //Validate profile entries
    $msg = validateProfile();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;        
    }
    
    //Validate position entries if present
    $msg = validatePos();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;        
    }
    
    //Validate Education entries if present
    $msg = validateEdu();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;        
    }    
    
    //Add the data
    $stmt = $pdo->prepare('INSERT INTO Profile
    (user_id, first_name, last_name, email, headline, summary)
    VALUES ( :uid, :fn, :ln, :em, :he, :su)');
    $stmt->execute(array(
      ':uid' => $_SESSION['user_id'],
      ':fn' => $_POST['first_name'],
      ':ln' => $_POST['last_name'],
      ':em' => $_POST['email'],
      ':he' => $_POST['headline'],
      ':su' => $_POST['summary'])
    );
    
    $profile_id = $pdo->lastInsertId();
    //Insert the position entries
    insertPositions($pdo, $profile_id);
    
    //Insert the education entries
    insertEducations($pdo, $profile_id);   
        
    $_SESSION['success'] = 'Profile added';
    header("Location: index.php");
    return;
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Nelson Herrero's Profile Add</title>
<?php require_once "head.php" ?>
</head>
<body>    
<div class="container">
    <div class="row">
    <h1>Adding Profile for <?= htmlentities($_SESSION['name']); ?></h1>
        <?php
            if ( isset($_SESSION['error']) ) {
            echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
            unset($_SESSION['error']);
            } 
        ?>
        
        <form method="post">
            <div class="form-group">
                <p>First Name:
                <input type="text" name="first_name" size="60" class="form-control"/></p>
            </div>
            <div class="form-group">
                <p>Last Name:
                <input type="text" name="last_name" size="60" class="form-control"/></p>
            </div>
            <div class="form-group">                
                <p>Email:
                <input type="text" name="email" size="30" class="form-control"/></p>
            </div>
            <div class="form-group">
                <p>Headline:<br/>
                <input type="text" name="headline" size="80" class="form-control"/></p>
            </div>
            <div class="form-group">
                <p>Summary:<br/>
                <textarea name="summary" rows="8" cols="80" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <p>Education: <input type="submit" id="addEdu" value="+" class="btn btn-info"></p>
                <div id="edu_fields"></div>
            </div>
            
            <div class="form-group">
                <p>Position: <input type="submit" id="addPos" value="+" class="btn btn-info"></p>
                <div id="position_fields"></div>
            </div>
            
            <div class="form-group">
                <input type="submit" value="Add" class="btn btn-primary">
                <input type="submit" name="cancel" value="Cancel" class="btn btn-warning">
            </div>
        </form>
    </div>    
</div>
<script>
countPos = 0;  
countEdu = 0;
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
});
</script>
</body>
</html>