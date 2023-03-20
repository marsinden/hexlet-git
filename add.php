<?php
    require_once "pdo.php";
    require_once "util.php";
    session_start();

    if ( ! isset($_SESSION["name"]) ) {
        die("ACCESS DENIED");
    }
    
    // If the user requested logout go back to view.php
    if ( isset($_POST['cancel']) ) {
        header('Location: index.php');
        return;
    }

    //Sending data to table 'Profile'
    if ( isset($_POST['first_name']) && isset($_POST['last_name']) 
    && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {

        // Data validation: Checking if form is not empty/if email adress right (util.php)
        $msg = validateProfile();
        if ( is_string($msg) ) {
            $_SESSION['error'] = $msg;
            header("Location: add.php");
            return;
        }

        // data validation for positions (util.php)
        $msg = validatePos();
        if ( is_string($msg) ) {
            $_SESSION['error'] = $msg;
            header("Location: add.php");
            return;
        }

        // validate Education
        $msg = validateEducation();
        if (is_string($msg)) {
            $_SESSION['error'] = $msg;
            header("Location: add.php");
            return;
        }

        $sql = "INSERT INTO profile (user_id, first_name, last_name, email, headline, summary)
                VALUES (:uid, :fn, :ln, :em, :he, :su)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':uid' => $_SESSION['user_id'],
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary']));

        $profile_id = $pdo->lastInsertId();
        
        // add positions (if filds "Position" added and checking is ok) util.php
        insertPosition($pdo, $profile_id);

        //add education (util.php)
        insertEducations($pdo, $profile_id);

        $_SESSION['success'] = 'Profile added';
        header( 'Location: index.php' ) ;
        return;
    }
?>

<!DOCTYPE html>
<html>
<head>
 <?php require_once "head.php"; ?>
<title>Synytsia Denys Profile Add 093c07ef</title>
</head>
<body>
<div class="container">
<h1>Adding Profile for <?php echo htmlentities($_SESSION['name']) ?></h1>

<?php
    //Message of input validation
    flashMessages();
?>

<form method="post">
<p>First Name:
<input type="text" name="first_name" size="60"/></p>
<p>Last Name:
<input type="text" name="last_name" size="60"/></p>
<p>Email:
<input type="text" name="email" size="30"/></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80"/></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80"></textarea></p>

<p>
Education: <input type="submit" id="addEdu" value="+">
<div id="edu_fields"></div>
</p>
<p>
Position: <input type="submit" id="addPos" value="+">
<div id="position_fields"></div>
</p>
<p>
<input type="submit" value="Add">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>

<script>
    countPos = 0;
    countEdu = 0;

    // http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
    $(document).ready(function(){
        window.console && console.log('Document ready called');

        $('#addPos').click(function(event){
            // http://api.jquery.com/event.preventdefault/
            event.preventDefault();
            if ( countPos >= 9 ) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countPos++;
            window.console && console.log("Adding position "+countPos);
            $('#position_fields').append(
                '<div id="position'+countPos+'"> \
                <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
                <input type="button" value="-" onclick="$(\'#position'+countPos+'\').remove();return false;"><br><br>\
                <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
                </div>'
            );
        });

        $('#addEdu').click(function(event){
            event.preventDefault();
            if ( countEdu >= 9 ) {
                alert("Maximum of nine education entries exceeded");
                return;
            }
            countEdu++;
            window.console && console.log("Adding education "+countEdu);

            $('#edu_fields').append(
                '<div id="edu'+countEdu+'"> \
                <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
                <input type="button" value="-" onclick="$(\'#edu'+countEdu+'\').remove();return false;"><br>\
                <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" class="school" value="" />\
                </p></div>'
            );

            $('.school').autocomplete({
                source: "school.php"
            });

        });

    });
</script>
</div>
</body>
</html>
