<?php


$mysqli = new mysqli("localhost", "root", "", "ehotel");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


$bookings_sql = "SELECT booking_id, sin, room_id, start_date, end_date, status
                 FROM booking
                 ORDER BY booking_id ASC";

$bookings_result = $mysqli->query($bookings_sql);
if (!$bookings_result) {
    die("Error fetching bookings: " . $mysqli->error);
}


$rentings_sql = "SELECT renting_id, sin, room_id, check_in_date, check_out_date, payment_status
                 FROM renting
                 ORDER BY renting_id ASC";
$rentings_result = $mysqli->query($rentings_sql);
if (!$rentings_result) {
    die("Error fetching rentings: " . $mysqli->error);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Employee Dashboard</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f4f8;
        }
        header {
            background-color: #007BFF;
            color: #fff;
            padding: 16px;
            text-align: center;
        }
        h1 { margin: 0; }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .section { margin-bottom: 30px; }
        .section h2 { margin-top: 0; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f8f8f8; }

        .form-control { margin-bottom: 10px; }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .checkin-btn { background-color: #28a745; color: #fff; }
        .renting-btn { background-color: #17a2b8; color: #fff; }
        .payment-btn { background-color: #ffc107; color: #333; }
        .manage-hotel-btn { background-color: #6c757d; color: #fff; }
        .logout-btn {
            background-color: #dc3545;
            color: #fff;
            margin-left: 10px;
        }
        .button-group { margin-top: 20px; }
        .section hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
<?php if (isset($_GET['paid'])): ?>
    <p style="color:green;">✔️ Payment status updated.</p>
<?php endif; ?>
<header>
    <h1>Employee Dashboard</h1>
</header>

<div class="container">
    <?php if (isset($_GET['checkedin'])): ?>
        <p style="color: green; font-weight: bold;">✔️ Booking successfully checked in.</p>
    <?php endif; ?>

    <div class="section" id="bookings-section">
        <h2>Current Bookings</h2>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer SIN</th>
                    <th>Room ID</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($bookings_result->num_rows > 0): ?>
                <?php while($brow = $bookings_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $brow['booking_id']; ?></td>
                    <td><?php echo htmlspecialchars($brow['sin']); ?></td>
                    <td><?php echo $brow['room_id']; ?></td>
                    <td><?php echo $brow['start_date']; ?></td>
                    <td><?php echo $brow['end_date']; ?></td>
                    <td><?php echo $brow['status']; ?></td>
                    <td>
                        <button class="checkin-btn" onclick="checkInBooking(<?php echo $brow['booking_id']; ?>)">Check-In</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No bookings found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <hr>

    <div class="section" id="rentings-section">
        <h2>Current Rentings</h2>
        <table>
            <thead>
                <tr>
                    <th>Renting ID</th>
                    <th>Customer SIN</th>
                    <th>Room ID</th>
                    <th>Check-In Date</th>
                    <th>Check-Out Date</th>
                    <th>Payment Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($rentings_result->num_rows > 0): ?>
                <?php while($rrow = $rentings_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $rrow['renting_id']; ?></td>
                    <td><?php echo htmlspecialchars($rrow['sin']); ?></td>
                    <td><?php echo $rrow['room_id']; ?></td>
                    <td><?php echo $rrow['check_in_date']; ?></td>
                    <td><?php echo $rrow['check_out_date']; ?></td>
                    <td><?php echo $rrow['payment_status']; ?></td>
                    <td>
                        <?php if ($rrow['payment_status'] !== 'Paid'): ?>
                        <form method="POST" action="mark_paid.php" style="display:inline;">
                            <input type="hidden" name="renting_id" value="<?php echo $rrow['renting_id']; ?>">
                            <button type="submit" class="payment-btn">Mark as Paid</button>
                        </form>
                        <?php else: ?>
                            <span style="color: green;">✔ Paid</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No active rentings found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <hr>

    <div class="section" id="checkin-section">
        <h2>Turn Booking into Renting</h2>
        <form action="check_in_booking.php" method="POST">
            <div class="form-control">
                <label for="booking_id">Booking ID</label>
                <input type="number" name="booking_id" id="booking_id" required>
            </div>
            <div class="form-control">
                <label for="check_in_date">Check-In Date</label>
                <input type="date" name="check_in_date" id="check_in_date" 
                       value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <button type="submit" class="checkin-btn">Check-In</button>
        </form>
    </div>

    <hr>

    <div class="section" id="direct-renting-section">
        <h2>Direct Renting (Walk-In)</h2>
        <form action="create_renting.php" method="POST">
            <div class="form-control">
                <label for="sin">Customer SIN</label>
                <input type="text" name="sin" id="sin" placeholder="Existing or New Customer SIN" required>
            </div>
            <div class="form-control">
                <label for="room_id">Room ID</label>
                <input type="number" name="room_id" id="room_id" placeholder="Available room ID" required>
            </div>
            <div class="form-control">
                <label for="check_in_display">Check-In Date</label>
                <input type="text" id="check_in_display" value="<?php echo date('Y-m-d'); ?>" disabled>
                <input type="hidden" name="check_in_date" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-control">
                <label for="check_out_date">Check-Out Date</label>
                <input type="date" name="check_out_date" id="check_out_date" required>
            </div>
            <button type="submit" class="renting-btn">Create Renting</button>
        </form>
    </div>

    <hr>

    <div class="section" id="payment-section">
        <h2>Process Customer Payment</h2>
        <form action="process_payment.php" method="POST">
            <div class="form-control">
                <label for="renting_id">Renting ID</label>
                <input type="number" name="renting_id" id="renting_id" required>
            </div>
            <div class="form-control">
                <label for="amount">Payment Amount</label>
                <input type="number" step="0.01" name="amount" id="amount" placeholder="0.00" required>
            </div>
            <div class="form-control">
                <label for="payment_method">Payment Method</label>
                <select name="payment_method" id="payment_method">
                    <option value="Credit Card">Credit Card</option>
                    <option value="Cash">Cash</option>
                    <option value="Online Transfer">Online Transfer</option>
                </select>
            </div>
            <button type="submit" class="payment-btn">Record Payment</button>
        </form>
    </div>

    <hr>

    <div class="section" id="manage-section">
        <h2>Hotel Management</h2>
        <p>Insert, read, and update hotel, room, and employee data.</p>
        <button class="manage-hotel-btn" onclick="window.location.href='manage_hotel.html'">
            Go to Manage Hotel Page
        </button>
    </div>

    <div class="section button-group">
        <button class="logout-btn" onclick="window.location.href='index.html'">Logout</button>
    </div>
</div>

<script>
function checkInBooking(bookingId) {
    window.location.href = `check_in_booking.php?booking_id=${bookingId}`;
}
</script>
</body>



</html>
<?php
$bookings_result->free();
$rentings_result->free();
$mysqli->close();
?>
<form id="checkinForm" action="check_in_booking.php" method="POST" style="display:none;">
  <input type="hidden" name="booking_id" id="hiddenBookingId" />
  <input type="hidden" name="check_in_date" value="<?php echo date('Y-m-d'); ?>" />
</form>

<td>
  <button class="checkin-btn" onclick="checkInBooking(<?php echo $brow['booking_id']; ?>)">Check-In</button>
</td>

<script>
function checkInBooking(bookingId) {
  document.getElementById("hiddenBookingId").value = bookingId;
  document.getElementById("checkinForm").submit();
}
</script>