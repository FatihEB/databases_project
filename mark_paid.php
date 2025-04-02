<?php
$mysqli = new mysqli("localhost", "root", "", "ehotel");
if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $renting_id = $_POST['renting_id'] ?? null;

    if (!$renting_id) {
        die("<p style='color:red;'>No Renting ID provided.</p>");
    }

    $stmt = $mysqli->prepare("UPDATE renting SET payment_status = 'Paid' WHERE renting_id = ?");
    $stmt->bind_param("i", $renting_id);
    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error marking as paid: " . $stmt->error . "</p>");
    }

    $stmt->close();
    header("Location: employee_dashboard.php?paid=1");
    exit;
} else {
    header("Location: employee_dashboard.php");
    exit;
}
?>
