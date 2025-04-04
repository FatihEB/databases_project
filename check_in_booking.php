<?php
// check_in_booking.php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/checkin_errors.log');

// DB connection
$mysqli = new mysqli("localhost", "root", "", "ehotel");
if ($mysqli->connect_errno) {
    error_log("❌ DB connection failed: " . $mysqli->connect_error);
    header("Location: employee_dashboard.php?error=db");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? '';
    
    if (!$booking_id) {
        error_log("❌ Missing booking_id.");
        header("Location: employee_dashboard.php?error=missing");
        exit;
    }
    
    // ✅ Step 1: Fetch booking
    $stmt = $mysqli->prepare("SELECT * FROM booking WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking_res = $stmt->get_result();
    $stmt->close();

    if ($booking_res->num_rows === 0) {
        error_log("❌ Booking ID $booking_id not found.");
        header("Location: employee_dashboard.php?error=notfound");
        exit;
    }
    
    $booking = $booking_res->fetch_assoc();
    if ($booking['status'] === 'Completed') {
        error_log("⚠️ Booking $booking_id is already marked as Completed.");
        header("Location: employee_dashboard.php?error=alreadycompleted");
        exit;
    }
    
    $sin = $booking['sin'];
    $room_id = $booking['room_id'];
    $employee_id = $booking['employee_id'] ?: 1;
    $check_out_date = $booking['end_date'];

    // ✅ Step 2: Check if a renting record already exists for this booking
    $check_rent = $mysqli->prepare("SELECT renting_id FROM renting WHERE booking_id = ?");
    $check_rent->bind_param("i", $booking_id);
    $check_rent->execute();
    $check_rent->store_result();
    if ($check_rent->num_rows > 0) {
        // Renting already exists; update booking status to Completed.
        $check_rent->close();
        $update_booking = $mysqli->prepare("UPDATE booking SET status = 'Completed' WHERE booking_id = ?");
        $update_booking->bind_param("i", $booking_id);
        if (!$update_booking->execute()) {
            error_log("❌ Failed to update booking status for ID $booking_id: " . $update_booking->error);
            header("Location: employee_dashboard.php?error=update_fail");
            exit;
        }
        $update_booking->close();
        header("Location: employee_dashboard.php?checkedin=1");
        exit;
    }
    $check_rent->close();

    // ✅ Step 3: Insert renting record
    // We use CURDATE() for check_in_date, similar to create_renting.php.
    $insert_rent = $mysqli->prepare("
        INSERT INTO renting (check_in_date, check_out_date, sin, room_id, employee_id, booking_id)
        VALUES (CURDATE(), ?, ?, ?, ?, ?)
    ");
    $insert_rent->bind_param("ssiii", $check_out_date, $sin, $room_id, $employee_id, $booking_id);
    if (!$insert_rent->execute()) {
        error_log("❌ Insert renting failed: " . $insert_rent->error);
        header("Location: employee_dashboard.php?error=renting_fail");
        exit;
    }
    $renting_id = $insert_rent->insert_id;
    $insert_rent->close();

    // ✅ Step 4: Insert into registers (logging the employee action)
    $insert_reg = $mysqli->prepare("INSERT INTO registers (employee_id, booking_id, renting_id) VALUES (?, ?, ?)");
    $insert_reg->bind_param("iii", $employee_id, $booking_id, $renting_id);
    $insert_reg->execute();
    $insert_reg->close();

    // ✅ Step 5: Insert into rents (linking the customer and room to the renting)
    $insert_rents = $mysqli->prepare("INSERT INTO rents (sin, renting_id, room_id) VALUES (?, ?, ?)");
    $insert_rents->bind_param("sii", $sin, $renting_id, $room_id);
    $insert_rents->execute();
    $insert_rents->close();

    // ✅ Step 6: Update booking status to Completed
    $update_booking = $mysqli->prepare("UPDATE booking SET status = 'Completed' WHERE booking_id = ?");
    $update_booking->bind_param("i", $booking_id);
    if (!$update_booking->execute()) {
        error_log("❌ Failed to update booking status for ID $booking_id: " . $update_booking->error);
    } else {
        error_log("✅ Booking $booking_id marked as Completed.");
    }
    $update_booking->close();

    header("Location: employee_dashboard.php?checkedin=1");
    exit;
} else {
    header("Location: employee_dashboard.php");
    exit;
}
?>
