<?php
require_once 'autoload.php';

/********** create database **********/

try{
    $db = new DbConnector();
} catch(Exception $e) {
    $header_str = 'Location: login.php?success=false&err=' . $e->getMessage();
    header($header_str);
}

$user = new User($db->getDB());

session_start();


/********** validate session data **********/

if(!empty($_SESSION['userAuth'])) {

    try {
        $user->validateToken($_SESSION['userAuth'], $_SESSION['id']);
    } catch(Exception $e) {
        session_destroy();
        $header_str = 'Location: login.php?success=false&err=' . $e->getMessage();
        header($header_str);
    }

} elseif(!empty($_POST['email']) && !empty($_POST['password'])) {

/********** validate / login **********/

    try{
        $user->login($_POST['email'], $_POST['password']);
    } catch(Exception $e) {
        $header_str = 'Location: login.php?success=false&err=' . $e->getMessage();
        header($header_str);
    }
}
else {
    header('Location: login.php?success=false');
}

$bookings = $user->getBookings();

//change spec errors to if success=false echo some generic err (do validation in form?)
/********** handle ajax **********/

if($_POST['newEmail']){
    $user->changeEmail($_POST['newEmail']);
}
if($_POST['newPassword']){
    $user->changePassword($_POST['newPassword']);
}

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="style/style.css">
    <script src="js/vendor/jquery-1.12.4.min.js"></script>
    <script src="js/account-page.js"></script>
    <title>Space Book</title>
</head>
<body>
<header>
    <div class="container">
        <img class="brand center-block" src="images/spacebook.png" alt="space book">
    </div>
</header>
<div class="logo-bar">
    <div class="container center-block">
        <h1>Account Page</h1>
        <a class="btn othr-btn" href="index.php">Home</a>
        <a class="btn log-btn" href="logout.php">Logout</a>
    </div>
</div>
<main>
    <div class="container center-block">
        <div id="user-details">
            <div id="details" class="user-account-content">
                <h2>User Details</h2>
                <h4 id="email-field">Email: <span><?php echo $_SESSION['email'] ?></span></h4>
                <button type="submit" id="edit" class="btn">edit</button>
            </div>
            <div id="update-form" class="user-account-content">
                <h2>Change Details</h2>
                <form method="post" class="form-horizontal">
                    <div class="form-group">
                        <label for="email" class="col-md-3 control-label">New Email:</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="email" id="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password" class="col-md-3 control-label">New Password:</label>
                        <div  class="col-md-6">
                            <input type="password" class="form-control" name="password" id="password">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3">
                            <input type="submit" id="save-user-details" class="btn" value="Save">
                            <input type="submit" class="btn" value="Cancel">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="bookings">
            <h2>Your Bookings</h2>
            <?php
            if (is_array($bookings) && count($bookings) > 0) {
                echo '<div class="table-responsive">';
                echo '<table class="table"><tr><th>Carpark Name</th><th>Date From</th><th>Time From</th><th>Date To</th><th>Time To</th></tr>';
                foreach ($bookings as $row) {
                    echo "<tr><td>" . $row['Carpark Name'] . "</td>";
                    echo "<td>" . $row['Date From'] . "</td>";
                    echo "<td>" . $row['Time From'] . "</td>";
                    echo "<td>" . $row['Date To'] . "</td>";
                    echo "<td>" . $row['Time To'] . "</td></tr>";
                }
                echo "</table></div>";
            } else {
                echo "<h4>You have no bookings</h4>";
            }
            ?>
        </div>
    </div>
</main>
<footer>
</footer>
</body>
</html>