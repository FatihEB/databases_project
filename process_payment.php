<?php

$mysqli = new mysqli("localhost", "root", "", "ehotel");
if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $renting_id = $_POST['renting_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $method = $_POST['payment_method'] ?? '';

    if (!$renting_id || !$amount || !$method) {
        die("<p style='color:red;'>Missing payment data.</p>");
    }


    $stmt = $mysqli->prepare("UPDATE renting SET payment_status = 'Paid' WHERE renting_id = ?");
    if (!$stmt) {
        die("<p style='color:red;'>Prepare failed: " . $mysqli->error . "</p>");
    }

    $stmt->bind_param("i", $renting_id);

    if (!$stmt->execute()) {
        die("<p style='color:red;'>Execute failed: " . $stmt->error . "</p>");
    }

    $stmt->close();

    header("Location: employee_dashboard.php?paid=1");
    exit;
} else {
    header("Location: employee_dashboard.php");
    exit;
}
?>
