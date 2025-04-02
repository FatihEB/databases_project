<?php
error_log("✅ create_renting.php was accessed");

$mysqli = new mysqli("localhost", "root", "", "ehotel");
if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? '';
    $check_in_date = $_POST['check_in_date'] ?? '';

    if (empty($booking_id) || empty($check_in_date)) {
        die("<p style='color:red;'>Error: Missing booking ID or check-in date.</p>");
    }

    $stmt = $mysqli->prepare("SELECT * FROM booking WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking_res = $stmt->get_result();
    $stmt->close();

    if ($booking_res->num_rows === 0) {
        die("<p style='color:red;'>Booking ID $booking_id not found.</p>");
    }

    $booking = $booking_res->fetch_assoc();

    if (in_array($booking['status'], ['Completed', 'Cancelled'])) {
        die("<p style='color:red;'>Booking already marked as '{$booking['status']}'.</p>");
    }

    $check_renting = $mysqli->prepare("SELECT renting_id FROM renting WHERE booking_id = ?");
    $check_renting->bind_param("i", $booking_id);
    $check_renting->execute();
    $check_renting->store_result();
    if ($check_renting->num_rows > 0) {
        $check_renting->close();
        die("<p style='color:red;'>This booking has already been checked in.</p>");
    }
    $check_renting->close();

    $sin = $booking['sin'];
    $room_id = $booking['room_id'];
    $employee_id = $booking['employee_id'] ?? 1;
    $check_out_date = $booking['end_date'];

    $check_cust = $mysqli->prepare("SELECT sin FROM customer WHERE sin = ? LIMIT 1");
    $check_cust->bind_param("s", $sin);
    $check_cust->execute();
    $check_cust->store_result();
    if ($check_cust->num_rows === 0) {
        die("<p style='color:red;'>Customer SIN $sin not found.</p>");
    }
    $check_cust->close();

    $insert_rent = $mysqli->prepare("
        INSERT INTO renting (check_in_date, check_out_date, sin, room_id, employee_id, booking_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    if (!$insert_rent) {
        die("<p style='color:red;'>Prepare failed: " . $mysqli->error . "</p>");
    }

    $insert_rent->bind_param("sssiii", $check_in_date, $check_out_date, $sin, $room_id, $employee_id, $booking_id);

    if (!$insert_rent->execute()) {
        die("<p style='color:red;'>Insert renting failed: " . $insert_rent->error . "</p>");
    }

    $renting_id = $insert_rent->insert_id;
    $insert_rent->close();

    if (!$renting_id) {
        die("<p style='color:red;'>Insert ID not returned. Check triggers or constraints.</p>");
    }

    $insert_reg = $mysqli->prepare("INSERT INTO registers (employee_id, booking_id, renting_id) VALUES (?, ?, ?)");
    $insert_reg->bind_param("iii", $employee_id, $booking_id, $renting_id);
    if (!$insert_reg->execute()) {
        die("<p style='color:red;'>Error inserting into registers: " . $insert_reg->error . "</p>");
    }
    $insert_reg->close();
    $insert_rents = $mysqli->prepare("INSERT INTO rents (sin, renting_id, room_id) VALUES (?, ?, ?)");
    $insert_rents->bind_param("sii", $sin, $renting_id, $room_id);
    if (!$insert_rents->execute()) {
        die("<p style='color:red;'>Error inserting into rents: " . $insert_rents->error . "</p>");
    }
    $insert_rents->close();

    $update_booking = $mysqli->prepare("UPDATE booking SET status = 'Completed' WHERE booking_id = ?");
    $update_booking->bind_param("i", $booking_id);
    $update_booking->execute();
    $update_booking->close();

    header("Location: employee_dashboard.php?checkedin=1");
    exit;

} else {
    header("Location: employee_dashboard.php");
    exit;
}
?>
