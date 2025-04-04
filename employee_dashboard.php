<?php
$mysqli = new mysqli("localhost", "root", "", "ehotel");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch all bookings (including completed)
$bookings_sql = "SELECT booking_id, sin, room_id, start_date, end_date, status
                 FROM booking
                 ORDER BY booking_id ASC";
$bookings_result = $mysqli->query($bookings_sql);
if (!$bookings_result) {
    die("Error fetching bookings: " . $mysqli->error);
}

// Fetch all rentings
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
    <title>Employee Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #007BFF;
            color: white;
            padding: 1rem;
            text-align: center;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
        }
        h2 {
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .checkin-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .payment-btn {
            background-color: #ffc107;
            color: #000;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .logout-btn, .manage-btn {
            margin-top: 20px;
            padding: 10px 15px;
            background: #343a40;
            color: white;
            border: none;
            cursor: pointer;
        }
        .success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

<header>
    <h1>Employee Dashboard</h1>
</header>

<div class="container">
    <?php if (isset($_GET['checkedin'])): ?>
        <p class="success">✔️ Booking successfully checked in.</p>
    <?php elseif (isset($_GET['paid'])): ?>
        <p class="success">✔️ Payment marked as paid.</p>
    <?php endif; ?>

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
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while($b = $bookings_result->fetch_assoc()): ?>
            <tr>
                <td><?= $b['booking_id'] ?></td>
                <td><?= htmlspecialchars($b['sin']) ?></td>
                <td><?= $b['room_id'] ?></td>
                <td><?= $b['start_date'] ?></td>
                <td><?= $b['end_date'] ?></td>
                <td><?= $b['status'] ?></td>
                <td>
                    <?php if ($b['status'] !== 'Completed'): ?>
                        <form method="POST" action="check_in_booking.php" style="margin:0;">
                            <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                            <input type="hidden" name="check_in_date" value="<?= date('Y-m-d') ?>">
                            <button type="submit" class="checkin-btn">Check-In</button>
                        </form>
                    <?php else: ?>
                        ✔️
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Current Rentings</h2>
    <table>
        <thead>
            <tr>
                <th>Renting ID</th>
                <th>Customer SIN</th>
                <th>Room ID</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th>Payment</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while($r = $rentings_result->fetch_assoc()): ?>
            <tr>
                <td><?= $r['renting_id'] ?></td>
                <td><?= htmlspecialchars($r['sin']) ?></td>
                <td><?= $r['room_id'] ?></td>
                <td><?= $r['check_in_date'] ?></td>
                <td><?= $r['check_out_date'] ?></td>
                <td><?= $r['payment_status'] ?></td>
                <td>
                    <?php if ($r['payment_status'] !== 'Paid'): ?>
                        <form method="POST" action="mark_paid.php" style="margin:0;">
                            <input type="hidden" name="renting_id" value="<?= $r['renting_id'] ?>">
                            <button type="submit" class="payment-btn">Mark as Paid</button>
                        </form>
                    <?php else: ?>
                        ✔️
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Walk-In Renting</h2>
    <form method="POST" action="create_renting.php">
        <label>SIN: <input type="text" name="sin" required></label><br>
        <label>Room ID: <input type="number" name="room_id" required></label><br>
        <label>Check-Out Date: <input type="date" name="check_out_date" required></label><br>
        <input type="hidden" name="check_in_date" value="<?= date('Y-m-d') ?>">
        <button type="submit" class="checkin-btn">Create Renting</button>
    </form>

    <h2>Process Payment</h2>
    <form method="POST" action="process_payment.php">
        <label>Renting ID: <input type="number" name="renting_id" required></label><br>
        <label>Amount: <input type="number" step="0.01" name="amount" required></label><br>
        <label>Payment Method:
            <select name="payment_method">
                <option value="Credit Card">Credit Card</option>
                <option value="Cash">Cash</option>
                <option value="Online Transfer">Online Transfer</option>
            </select>
        </label><br>
        <button type="submit" class="payment-btn">Record Payment</button>
    </form>

    <button class="manage-btn" onclick="window.location.href='manage_hotel.html'">Manage Hotel</button>
    <button class="logout-btn" onclick="window.location.href='index.html'">Logout</button>
</div>

</body>
</html>
<?php
$bookings_result->free();
$rentings_result->free();
$mysqli->close();
?>
