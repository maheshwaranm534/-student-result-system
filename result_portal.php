<?php
$host = "localhost";
$user = "root";
$pass = "maheshwaranmanigandan";
$db = "student_result";
$conn = new mysqli($host, $user, $pass, $db);
session_start();

$error = "";
$resultData = null;
$adminPassword = "admin123"; // CHANGE this to your secret password

// Admin Login
if (isset($_POST['admin_login'])) {
    if ($_POST['admin_pass'] === $adminPassword) {
        $_SESSION['admin'] = true;
    } else {
        $error = "Invalid admin password!";
    }
}

// Student Login
if (isset($_POST['student_login'])) {
    $reg = $_POST['regno'];
    $pwd = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM students WHERE regno=? AND password=?");
    $stmt->bind_param("ss", $reg, $pwd);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $_SESSION['student'] = $reg;
        $stmt = $conn->prepare("SELECT * FROM results WHERE regno=?");
        $stmt->bind_param("s", $reg);
        $stmt->execute();
        $resultData = $stmt->get_result()->fetch_assoc();
    } else {
        $error = "Invalid student credentials!";
    }
}

// Save Data (admin)
if (isset($_POST['save_data']) && isset($_SESSION['admin'])) {
    $reg = $_POST['reg'];
    $name = $_POST['name'];
    $pwd = $_POST['pwd'];
    $rdbms = $_POST['rdbms'] ?? 0;
    $c = $_POST['c'] ?? 0;
    $dld = $_POST['dld'] ?? 0;
    $dldprac = $_POST['dldprac'] ?? 0;
    $os = $_POST['os'] ?? 0;
    $web = $_POST['web'] ?? 0;

    // Insert or Update
    $conn->query("INSERT INTO students (regno, name, password) 
    VALUES ('$reg', '$name', '$pwd') 
    ON DUPLICATE KEY UPDATE name='$name', password='$pwd'");

    $conn->query("INSERT INTO results (regno, rdbms, c, dld, dld_prac, os, webdesign) 
    VALUES ('$reg', $rdbms, $c, $dld, $dldprac, $os, $web) 
    ON DUPLICATE KEY UPDATE rdbms=$rdbms, c=$c, dld=$dld, dld_prac=$dldprac, os=$os, webdesign=$web");

    $error = "✅ Data Saved for $reg!";
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: result_portal.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Result Portal</title>
    <style>
        body {
            font-family: Arial;
            background: linear-gradient(to right, #141e30, #243b55);
            color: white;
            text-align: center;
            padding-top: 50px;
        }
        .box {
            width: 450px;
            margin: auto;
            background: rgba(255,255,255,0.1);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 0 20px #fff;
        }
        input, select {
            width: 90%;
            padding: 10px;
            margin: 10px;
            border-radius: 8px;
            border: none;
        }
        input[type="submit"] {
            background: #28a745;
            color: white;
            cursor: pointer;
        }
        table {
            width: 100%;
            margin-top: 20px;
            color: white;
        }
        td, th {
            padding: 8px;
        }
        a {
            color: yellow;
        }
    </style>
</head>
<body>
<div class="box">
    <h2>📚 Student Result Portal</h2>

    <?php if (!isset($_SESSION['admin']) && !isset($_SESSION['student'])): ?>
        <form method="post">
            <h3>👨‍🎓 Student Login</h3>
            <input type="text" name="regno" placeholder="Register Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" name="student_login" value="Check Result">
        </form>

        <hr>

        <form method="post">
            <h3>🛠 Admin Login</h3>
            <input type="password" name="admin_pass" placeholder="Admin Password" required>
            <input type="submit" name="admin_login" value="Go to Entry Page">
        </form>

        <p style="color: yellow;"><?= $error ?></p>

    <?php elseif (isset($_SESSION['student'])): ?>
        <h3>📄 Result for <?= $_SESSION['student'] ?></h3>
        <table border="1">
            <tr><th>Subject</th><th>Marks</th></tr>
            <tr><td>RDBMS</td><td><?= $resultData['rdbms'] ?></td></tr>
            <tr><td>C</td><td><?= $resultData['c'] ?></td></tr>
            <tr><td>DLD</td><td><?= $resultData['dld'] ?></td></tr>
            <tr><td>DLD Practical</td><td><?= $resultData['dld_prac'] ?></td></tr>
            <tr><td>OS</td><td><?= $resultData['os'] ?></td></tr>
            <tr><td>Web Design</td><td><?= $resultData['webdesign'] ?></td></tr>
        </table>
        <br>
        <a href="?logout=true">🔙 Logout</a>

    <?php elseif (isset($_SESSION['admin'])): ?>
        <h3>➕ Add / Update Student Result</h3>
        <form method="post">
            <input type="text" name="reg" placeholder="Register Number" required>
            <input type="text" name="name" placeholder="Student Name" required>
            <input type="text" name="pwd" placeholder="Student Password" required>
            <input type="number" name="rdbms" placeholder="RDBMS Marks">
            <input type="number" name="c" placeholder="C Marks">
            <input type="number" name="dld" placeholder="DLD Marks">
            <input type="number" name="dldprac" placeholder="DLD Practical Marks">
            <input type="number" name="os" placeholder="OS Marks">
            <input type="number" name="web" placeholder="Web Design Marks">
            <input type="submit" name="save_data" value="💾 Save Data">
        </form>
        <p style="color: lightgreen;"><?= $error ?></p>
        <br>
        <a href="?logout=true">🔙 Logout</a>
    <?php endif; ?>
</div>
</body>
</html>
