<?php

//Functions for code organization and to avoid DRY 

//Validate profiles
function validateProfile() {
    if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1) { 
        return "All fields are required";
    }
    
    if ( strpos($_POST['email'],'@') === false ) {
        return "Email address must contain @";
    }
    return true;
}

//Validate Education fields
function validateEdu() {
    for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['edu_year'.$i]) ) continue;
        if ( ! isset($_POST['edu_school'.$i]) ) continue;
        $year = $_POST['edu_year'.$i];
        $name = $_POST['edu_school'.$i];
        if ( strlen($year) == 0 || strlen($name) == 0 ) {
            return "All fields are required";
        }

        if ( ! is_numeric($year) ) {
            return "Education: Year must be numeric";
        }
    }
    return true;
}

// Validate positions
function validatePos() {
    for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['year'.$i]) ) continue;
        if ( ! isset($_POST['desc'.$i]) ) continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];
        if ( strlen($year) == 0 || strlen($desc) == 0 ) {
            return "All fields are required";
        }

        if ( ! is_numeric($year) ) {
            return "Position: Year must be numeric";
        }
    }
    return true;    
}

//Load positions entries
function loadPos($pdo, $profile_id) {
    $stmt = $pdo->prepare('SELECT * FROM Position
        WHERE profile_id = :prof ORDER BY rank');
    $stmt->execute(array(':prof' => $profile_id));
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $positions;
}

//Load Education entries
function loadEdu($pdo, $profile_id) {
    $stmt = $pdo->prepare('SELECT year, name FROM Education
        JOIN Institution
            ON Education.institution_id = Institution.institution_id
        WHERE profile_id = :prof ORDER BY rank');
    $stmt->execute(array(':prof' => $profile_id));
    $educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $educations;
}

//Add education to the database after validation
function insertEducations($pdo, $profile_id) {
    $rank = 1;
    for ($i=1 ; $i<=9; $i++) {
        if (!isset($_POST['edu_year'.$i]) ) continue;
        if (!isset($_POST['edu_school'.$i]) ) continue;
        $year = $_POST['edu_year'.$i];
        $school = $_POST['edu_school'.$i];
        
        //Lookup school
        $institution_id = false;
        $stmt = $pdo->prepare('SELECT institution_id 
        FROM Institution 
        WHERE name = :name');
        $stmt->execute(array(':name' => $school));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ( $row !== false ) $institution_id = $row['institution_id'];
        
        // Insert if there was no institution
        if ($institution_id === false ) {
            $stmt = $pdo->prepare('INSERT INTO Institution
                (name) VALUES (:name)');
            $stmt = $pdo->prepare('INSERT INTO Institution
            (name) VALUES (:name)');
            $stmt->execute(array(':name' => $school));
            $institution_id = $pdo->lastInsertId();
        }
        
        $stmt = $pdo->prepare('INSERT INTO Education
            (profile_id, rank, year, institution_id)
            VALUES (:pid, :rank, :year, :instid)');
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':instid' => $institution_id
        ));
        
        $rank++;
        
    }    
}


//Add Position to the database after validation
function insertPositions($pdo, $profile_id) {
    $rank = 1;
    for ($i=1 ; $i<=9; $i++) {
        if (!isset($_POST['year'.$i]) ) continue;
        if (!isset($_POST['desc'.$i]) ) continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];
        
        $stmt = $pdo->prepare('INSERT INTO Position
            (profile_id, rank, year, description)
             VALUES (:pid, :rank, :year, :desc)');
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc)
        );
        $rank++;
    }
}
