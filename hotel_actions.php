<?php
// hotel_actions.php
// Provides CREATE, READ, UPDATE, and DELETE operations for hotels, rooms, and employees

$mysqli = new mysqli("localhost", "root", "", "ehotel");
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// Determine the action
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'insertHotel':
        insertHotel($mysqli);
        break;
    case 'listHotels':
        listHotels($mysqli);
        break;
    case 'insertRoom':
        insertRoom($mysqli);
        break;
    case 'listRooms':
        listRooms($mysqli);
        break;
    case 'insertEmployee':
        insertEmployee($mysqli);
        break;
    case 'listEmployees':
        listEmployees($mysqli);
        break;
    case 'updateHotel':
        updateHotel($mysqli);
        break;
    case 'deleteHotel':
        deleteHotel($mysqli);
        break;
    case 'updateRoom':
        updateRoom($mysqli);
        break;
    case 'deleteRoom':
        deleteRoom($mysqli);
        break;
    case 'updateEmployee':
        updateEmployee($mysqli);
        break;
    case 'deleteEmployee':
        deleteEmployee($mysqli);
        break;
    default:
        echo "<p style='color:red;'>Unknown action: $action</p>";
        break;
}

$mysqli->close();

function insertHotel($mysqli) {
    $name            = $_POST['name'] ?? '';
    $address         = $_POST['address'] ?? '';
    $rating          = $_POST['rating'] ?? '';
    $numRooms        = $_POST['number_of_rooms'] ?? '';
    $email           = $_POST['email'] ?? '';
    $phone           = $_POST['phone_number'] ?? '';
    $chain_id        = $_POST['chain_id'] ?? '';

    if (!$name || !$address || !$rating || !$numRooms || !$email || !$phone || !$chain_id) {
        die("<p style='color:red;'>Please fill in all required fields for hotel.</p>");
    }

    $stmt = $mysqli->prepare("
        INSERT INTO hotel (name, address, rating, number_of_rooms, email, phone_number, chain_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssiissi", $name, $address, $rating, $numRooms, $email, $phone, $chain_id);

    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error inserting hotel: " . $stmt->error . "</p>");
    }
    $stmt->close();

    echo "<p style='color:green;'>Hotel inserted successfully!</p>";
    echo "<a href='manage_hotel.html'>Back to Manage Hotel</a>";
}

function listHotels($mysqli) {
    $result = $mysqli->query("SELECT * FROM hotel ORDER BY hotel_id ASC");
    if (!$result) {
        die("<p style='color:red;'>Error fetching hotels: " . $mysqli->error . "</p>");
    }

    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>
            <th>Hotel ID</th>
            <th>Name</th>
            <th>Address</th>
            <th>Rating</th>
            <th>Rooms</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Chain ID</th>
          </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['hotel_id']}</td>
                <td>{$row['name']}</td>
                <td>{$row['address']}</td>
                <td>{$row['rating']}</td>
                <td>{$row['number_of_rooms']}</td>
                <td>{$row['email']}</td>
                <td>{$row['phone_number']}</td>
                <td>{$row['chain_id']}</td>
              </tr>";
    }
    echo "</table>";
    $result->free();
}

function insertRoom($mysqli) {
    $hotel_id   = $_POST['hotel_id'] ?? '';
    $room_num   = $_POST['room_number'] ?? '';
    $price      = $_POST['price'] ?? '';
    $capacity   = $_POST['capacity'] ?? '';
    $view_type  = $_POST['view_type'] ?? '';
    $amenities  = $_POST['amenities'] ?? '';
    $extendable = $_POST['extendable'] ?? '0';
    $damages    = $_POST['damages'] ?? NULL; // If empty, can be NULL

    if (!$hotel_id || !$room_num || !$price || !$capacity || !$view_type || !$amenities) {
        die("<p style='color:red;'>Missing required room fields.</p>");
    }

    $stmt = $mysqli->prepare("
        INSERT INTO room (hotel_id, room_number, price, capacity, view_type, amenities, extendable, damages)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isdisiss", $hotel_id, $room_num, $price, $capacity, $view_type, $amenities, $extendable, $damages);

    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error inserting room: " . $stmt->error . "</p>");
    }
    $stmt->close();

    echo "<p style='color:green;'>Room inserted successfully!</p>";
    echo "<a href='manage_hotel.html'>Back to Manage Hotel</a>";
}

function listRooms($mysqli) {
    $result = $mysqli->query("
        SELECT r.room_id, r.hotel_id, r.room_number, r.price, r.capacity, r.view_type, r.amenities, r.extendable, r.damages
        FROM room r
        ORDER BY r.room_id ASC
    ");
    if (!$result) {
        die("<p style='color:red;'>Error fetching rooms: " . $mysqli->error . "</p>");
    }

    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>
            <th>Room ID</th>
            <th>Hotel ID</th>
            <th>Room #</th>
            <th>Price</th>
            <th>Capacity</th>
            <th>View</th>
            <th>Amenities</th>
            <th>Extendable</th>
            <th>Damages</th>
          </tr>";
    while ($row = $result->fetch_assoc()) {
        $extend = $row['extendable'] ? 'Yes' : 'No';
        $damages = $row['damages'] ?: 'None';
        echo "<tr>
                <td>{$row['room_id']}</td>
                <td>{$row['hotel_id']}</td>
                <td>{$row['room_number']}</td>
                <td>{$row['price']}</td>
                <td>{$row['capacity']}</td>
                <td>{$row['view_type']}</td>
                <td>{$row['amenities']}</td>
                <td>$extend</td>
                <td>$damages</td>
              </tr>";
    }
    echo "</table>";
    $result->free();
}

function insertEmployee($mysqli) {
    $name    = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $sin     = $_POST['sin'] ?? '';
    $role    = $_POST['role'] ?? '';
    $email   = $_POST['email'] ?? '';

    if (!$name || !$address || !$sin || !$role || !$email) {
        die("<p style='color:red;'>Missing required employee fields.</p>");
    }

    $stmt = $mysqli->prepare("
        INSERT INTO employee (name, address, sin, role, email)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssss", $name, $address, $sin, $role, $email);

    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error inserting employee: " . $stmt->error . "</p>");
    }
    $stmt->close();

    echo "<p style='color:green;'>Employee inserted successfully!</p>";
    echo "<a href='manage_hotel.html'>Back to Manage Hotel</a>";
}

function listEmployees($mysqli) {
    $result = $mysqli->query("SELECT * FROM employee ORDER BY employee_id ASC");
    if (!$result) {
        die("<p style='color:red;'>Error fetching employees: " . $mysqli->error . "</p>");
    }

    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>
            <th>Employee ID</th>
            <th>Name</th>
            <th>Address</th>
            <th>SIN</th>
            <th>Role</th>
            <th>Email</th>
          </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['employee_id']}</td>
                <td>{$row['name']}</td>
                <td>{$row['address']}</td>
                <td>{$row['sin']}</td>
                <td>{$row['role']}</td>
                <td>{$row['email']}</td>
              </tr>";
    }
    echo "</table>";
    $result->free();
}

function updateHotel($mysqli) {
    $hotel_id = $_POST['hotel_id'] ?? '';
    $rating = $_POST['rating'] ?? null;
    $num_rooms = $_POST['number_of_rooms'] ?? null;

    if (!$hotel_id || (!$rating && !$num_rooms)) {
        die("<p style='color:red;'>Please provide hotel ID and at least one field to update.</p>");
    }

    $sql = "UPDATE hotel SET ";
    $fields = [];
    $params = [];
    $types = "";

    if ($rating !== null) {
        $fields[] = "rating = ?";
        $params[] = $rating;
        $types .= "i";
    }
    if ($num_rooms !== null) {
        $fields[] = "number_of_rooms = ?";
        $params[] = $num_rooms;
        $types .= "i";
    }

    $sql .= implode(", ", $fields) . " WHERE hotel_id = ?";
    $params[] = $hotel_id;
    $types .= "i";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    echo "<p style='color:green;'>Hotel updated successfully!</p>";
    echo "<a href='manage_hotel.html'>Back</a>";
}

function deleteHotel($mysqli) {
    $hotel_id = $_POST['hotel_id'] ?? '';
    if (!$hotel_id) {
        die("Missing Hotel ID");
    }

    // First, delete all rooms associated with this hotel
    $stmt_rooms = $mysqli->prepare("DELETE FROM room WHERE hotel_id = ?");
    $stmt_rooms->bind_param("i", $hotel_id);
    if (!$stmt_rooms->execute()) {
        die("<p style='color:red;'>Error deleting rooms: " . $stmt_rooms->error . "</p>");
    }
    $stmt_rooms->close();

    // Then, delete the hotel record
    $stmt = $mysqli->prepare("DELETE FROM hotel WHERE hotel_id = ?");
    $stmt->bind_param("i", $hotel_id);
    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error deleting hotel: " . $stmt->error . "</p>");
    }
    $stmt->close();

    echo "<p style='color:green;'>Hotel and its associated rooms have been deleted.</p><a href='manage_hotel.html'>Back</a>";
}

function updateRoom($mysqli) {
    $room_id = $_POST['room_id'] ?? '';
    // Allow updating of price, capacity, view type, amenities, extendable flag, and damages
    $price = $_POST['price'] ?? null;
    $capacity = $_POST['capacity'] ?? null;
    $view_type = $_POST['view_type'] ?? null;
    $amenities = $_POST['amenities'] ?? null;
    $extendable = $_POST['extendable'] ?? null;
    $damages = $_POST['damages'] ?? null;

    if (!$room_id) {
        die("<p style='color:red;'>Room ID is required.</p>");
    }
    
    $sql = "UPDATE room SET ";
    $fields = [];
    $params = [];
    $types = "";

    if ($price !== null && $price !== '') {
        $fields[] = "price = ?";
        $params[] = $price;
        $types .= "d";
    }
    if ($capacity !== null && $capacity !== '') {
        $fields[] = "capacity = ?";
        $params[] = $capacity;
        $types .= "i";
    }
    if ($view_type !== null && $view_type !== '') {
        $fields[] = "view_type = ?";
        $params[] = $view_type;
        $types .= "s";
    }
    if ($amenities !== null && $amenities !== '') {
        $fields[] = "amenities = ?";
        $params[] = $amenities;
        $types .= "s";
    }
    if ($extendable !== null && $extendable !== '') {
        $fields[] = "extendable = ?";
        $params[] = $extendable;
        $types .= "i";
    }
    if ($damages !== null) {
        // damages can be set to NULL explicitly
        $fields[] = "damages = ?";
        $params[] = $damages;
        $types .= "s";
    }
    
    if (empty($fields)) {
        die("<p style='color:red;'>At least one field must be provided to update the room.</p>");
    }

    $sql .= implode(", ", $fields) . " WHERE room_id = ?";
    $params[] = $room_id;
    $types .= "i";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("<p style='color:red;'>Prepare failed: " . $mysqli->error . "</p>");
    }
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error updating room: " . $stmt->error . "</p>");
    }
    $stmt->close();

    echo "<p style='color:green;'>Room updated successfully.</p><a href='manage_hotel.html'>Back</a>";
}

function deleteRoom($mysqli) {
    $room_id = $_POST['room_id'] ?? '';
    if (!$room_id) {
        die("<p style='color:red;'>Missing Room ID</p>");
    }
    
    // 1. Delete from registers (child of renting)
    $stmt = $mysqli->prepare("
        DELETE FROM registers
        WHERE renting_id IN (
            SELECT renting_id FROM (SELECT renting_id FROM renting WHERE booking_id IN (
                SELECT booking_id FROM booking WHERE room_id = ?
            )) AS t
        )
    ");
    $stmt->bind_param("i", $room_id);
    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error deleting registers: " . $stmt->error . "</p>");
    }
    $stmt->close();
    
    // 2. Delete from books (child of booking)
    $stmt = $mysqli->prepare("
        DELETE FROM books 
        WHERE booking_id IN (
            SELECT booking_id FROM (SELECT booking_id FROM booking WHERE room_id = ?) AS t
        )
    ");
    $stmt->bind_param("i", $room_id);
    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error deleting from books: " . $stmt->error . "</p>");
    }
    $stmt->close();
    
    // 3. Delete renting records for bookings referencing this room
    $stmt = $mysqli->prepare("
        DELETE FROM renting 
        WHERE booking_id IN (
            SELECT booking_id FROM (SELECT booking_id FROM booking WHERE room_id = ?) AS t
        )
    ");
    $stmt->bind_param("i", $room_id);
    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error deleting renting records: " . $stmt->error . "</p>");
    }
    $stmt->close();
    
    // 4. Delete bookings that reference this room
    $stmt = $mysqli->prepare("DELETE FROM booking WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error deleting bookings: " . $stmt->error . "</p>");
    }
    $stmt->close();
    
    // 5. Finally, delete the room record
    $stmt = $mysqli->prepare("DELETE FROM room WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error deleting room: " . $stmt->error . "</p>");
    }
    $stmt->close();
    
    echo "<p style='color:green;'>Room and all associated records have been deleted.</p><a href='manage_hotel.html'>Back</a>";
}




function updateEmployee($mysqli) {
    $emp_id = $_POST['employee_id'] ?? '';
    // Allow updating of role and email for an employee
    $role = $_POST['role'] ?? null;
    $email = $_POST['email'] ?? null;

    if (!$emp_id) {
        die("<p style='color:red;'>Employee ID is required.</p>");
    }
    
    $sql = "UPDATE employee SET ";
    $fields = [];
    $params = [];
    $types = "";

    if ($role !== null && $role !== '') {
        $fields[] = "role = ?";
        $params[] = $role;
        $types .= "s";
    }
    if ($email !== null && $email !== '') {
        $fields[] = "email = ?";
        $params[] = $email;
        $types .= "s";
    }
    
    if (empty($fields)) {
        die("<p style='color:red;'>At least one field must be provided to update the employee.</p>");
    }

    $sql .= implode(", ", $fields) . " WHERE employee_id = ?";
    $params[] = $emp_id;
    $types .= "i";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("<p style='color:red;'>Prepare failed: " . $mysqli->error . "</p>");
    }
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error updating employee: " . $stmt->error . "</p>");
    }
    $stmt->close();

    echo "<p style='color:green;'>Employee updated successfully.</p><a href='manage_hotel.html'>Back</a>";
}

function deleteEmployee($mysqli) {
    $emp_id = $_POST['employee_id'] ?? '';
    if (!$emp_id) {
        die("<p style='color:red;'>Missing Employee ID</p>");
    }

    $stmt = $mysqli->prepare("DELETE FROM employee WHERE employee_id = ?");
    $stmt->bind_param("i", $emp_id);
    if (!$stmt->execute()) {
        die("<p style='color:red;'>Error deleting employee: " . $stmt->error . "</p>");
    }
    $stmt->close();

    echo "<p style='color:green;'>Employee deleted.</p><a href='manage_hotel.html'>Back</a>";
}
?>
