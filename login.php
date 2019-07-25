<?php
require_once "pdo.php";
session_start();

// Server Side Validation
if ( isset($_POST['email']) && isset($_POST['pass'])  ) {
    $salt = 'XyZzy12*_';
    $check = hash('md5', $salt.$_POST['pass']);

    $stmt = $pdo->prepare('SELECT user_id, name FROM users WHERE email = :em AND password = :pw');
    $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ( $row !== false ) {
        error_log("Login success ".$_POST['email']);
        $_SESSION['name'] = $row['name'];
        $_SESSION['user_id'] = $row['user_id'];
        // Redirect the browser to index.php
        header("Location: index.php");
        return;
    } else {
        error_log("Login fail ".$_POST['email']." $check");
        $_SESSION['error'] = "Incorrect password";
        header("Location: login.php");
        return;
    }

} 

?>

<!DOCTYPE html>
<html>
<head>
    <title>Nelson Herrero's Login Page</title>
    <?php require_once "head.php" ?>
</head>
<body>
<div class="container">
    <h1 class="alert alert-info">Please Log In</h1>
    <?php    
    // line added to turn on color syntax highlight
    if ( isset($_SESSION['error']) ) {
      echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
      unset($_SESSION['error']);
    }

    ?>

    <form method="post" action="login.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" name="email" id="email" class="form-control">
        </div>

        <div class="form-group">
            <label for="id_1723">Password</label>
            <input type="password" name="pass" id="id_1723" class="form-control"><br/>
        </div>

        <div class="form-group">
            <input type="submit" onclick="return doValidate();" name="submit" value="Log In"  class="btn btn-primary">
            <input type="submit" name="cancel" value="Cancel" class="btn btn-secondary">
        </div>
    </form>

    <script>
        function doValidate() {
            console.log('Validating...');
            try {
                addr = document.getElementById('email').value;
                pw = document.getElementById('id_1723').value;
                console.log("Validating addr="+addr+" pw="+pw);
                if (addr == null || addr == "" || pw == null || pw == "") {
                    alert("Both fields must be filled out");
                    return false;
                }
                if ( addr.indexOf('@') == -1 ) {
                    alert("Email address must contain @");
                    return false;
                }                
                return true;
            } catch(e) {
                return false;
            }
            return false;
        }
    </script>
</div>
</body>
