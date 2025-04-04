<?php
// Connect to database
$mysqli = new mysqli("localhost", "root", "", "ehotel");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// If form submitted, process booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sin = $_POST['sin'] ?? '';
    $room_id = $_POST['room_id'] ?? '';
    $employee_id = $_POST['employee_id'] ?? 1; 
    $checkin = $_POST['checkin'] ?? '';
    $checkout = $_POST['checkout'] ?? '';
    $status = 'Pending';

    if ($sin && $room_id && $checkin && $checkout) {
        $check_customer = $mysqli->prepare("SELECT sin FROM customer WHERE sin = ?");
        $check_customer->bind_param("s", $sin);
        $check_customer->execute();
        $check_customer->store_result();

        if ($check_customer->num_rows === 0) {
            echo "<p style='color: red;'>Error: No customer found with SIN $sin. Please enter an existing customer SIN.</p>";
        } else {
            $overlap_check = $mysqli->prepare("SELECT * FROM booking WHERE room_id = ? AND NOT (end_date <= ? OR start_date >= ?)");
            $overlap_check->bind_param("iss", $room_id, $checkin, $checkout);
            $overlap_check->execute();
            $overlap_result = $overlap_check->get_result();

            if ($overlap_result->num_rows > 0) {
                echo "<p style='color: red;'>Error: This room is already booked during the selected dates. Please choose a different room or date range.</p>";
            } else {
                $stmt = $mysqli->prepare("INSERT INTO booking (start_date, end_date, status, sin, room_id, employee_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssii", $checkin, $checkout, $status, $sin, $room_id, $employee_id);
                $stmt->execute();
                $booking_id = $stmt->insert_id;
                $stmt->close();

                $stmt = $mysqli->prepare("INSERT INTO books (sin, booking_id, room_id) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $sin, $booking_id, $room_id);
                $stmt->execute();
                $stmt->close();

                echo "<h2>Booking Confirmed</h2>";
                echo "<p>Booking ID: $booking_id</p>";
                echo "<p>Customer SIN: $sin</p>";
                echo "<p>Room ID: $room_id</p>";
                echo "<p>Check-in: $checkin</p>";
                echo "<p>Check-out: $checkout</p>";
                echo "<a href='booking.html'>Back to Search</a>";
                exit;
            }
            $overlap_check->close();
        }
        $check_customer->close();
    } else {
        echo "<p>Error: Please fill in all required fields.</p>";
    }
}

$room_id = $_GET['room_id'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
?>

<h2>Book Room</h2>
<form method="POST">
  <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room_id); ?>">
  <input type="hidden" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
  <input type="hidden" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>">

  <label for="sin">Customer SIN:</label>
  <input type="text" name="sin" id="sin" required>

  <label for="employee_id">Handled By Employee ID:</label>
  <input type="number" name="employee_id" id="employee_id" value="1" required>

  <button type="submit">Confirm Booking</button>
</form>

<a href="booking.html">Cancel and Return</a>