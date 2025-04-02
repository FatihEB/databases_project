<?php
$mysqli = new mysqli("localhost", "root", "", "ehotel");
if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sin = $_POST['sin'] ?? '';
    $room_id = $_POST['room_id'] ?? '';
    $check_out_date = $_POST['check_out_date'] ?? '';
    $employee_id = 1;

    if (!$sin || !$room_id || !$check_out_date) {
        die("<p style='color:red;'>Missing required fields.</p>");
    }

    $check_customer = $mysqli->prepare("SELECT sin FROM customer WHERE sin = ?");
    $check_customer->bind_param("s", $sin);
    $check_customer->execute();
    $check_customer->store_result();

    if ($check_customer->num_rows === 0) {
        $check_customer->close();

        $insert_customer = $mysqli->prepare("INSERT INTO customer (sin, name, address, date_of_registration) VALUES (?, ?, ?, CURDATE())");
        $dummy_name = "Walk-In Customer";
        $dummy_address = "Unknown Address";
        $insert_customer->bind_param("sss", $sin, $dummy_name, $dummy_address);

        if (!$insert_customer->execute()) {
            die("<p style='color:red;'>Error creating new customer: " . $insert_customer->error . "</p>");
        }

        $insert_customer->close();
    } else {
        $check_customer->close();
    }

    $insert_rent = $mysqli->prepare("
        INSERT INTO renting (check_in_date, check_out_date, sin, room_id, employee_id)
        VALUES (CURDATE(), ?, ?, ?, ?)
    ");
    $insert_rent->bind_param("ssii", $check_out_date, $sin, $room_id, $employee_id);

    if (!$insert_rent->execute()) {
        die("<p style='color:red;'>Error inserting renting: " . $insert_rent->error . "</p>");
    }

    $renting_id = $insert_rent->insert_id;
    $insert_rent->close();

    $insert_rents = $mysqli->prepare("INSERT INTO rents (sin, renting_id, room_id) VALUES (?, ?, ?)");
    $insert_rents->bind_param("sii", $sin, $renting_id, $room_id);
    if (!$insert_rents->execute()) {
        die("<p style='color:red;'>Error inserting into rents: " . $insert_rents->error . "</p>");
    }
    $insert_rents->close();

    echo "<h2>✅ Renting Created for Walk-In Customer</h2>";
    echo "<p>Renting ID: $renting_id</p>";
    echo "<a href='employee_dashboard.php'>← Back to Dashboard</a>";
} else {
    header("Location: employee_dashboard.php");
    exit;
}
?>
