<?php
session_start();

$conn = new mysqli("localhost","root","","smart_surplus");
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$username = $_POST['username'];
$password = $_POST['password'];

// Select admin by username or email
$stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
$stmt->bind_param("ss",$username,$username);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 1){
    $row = $result->fetch_assoc();
    echo "Entered password: '$password'<br>";
    echo "Stored hash: '" . $row['password'] . "'<br>";
    if(password_verify($password, $row['password'])){
        $_SESSION['admin'] = $row['username'];
        header("Location: ../adminDash.html");
exit();

    } else {
        $error = "Invalid password.";
    }
}


echo "<h2>$error</h2><a href='../admin.html'>Go back to login</a>";
?>
