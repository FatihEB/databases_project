<?php
// hotel_actions.php
// Provides CREATE and READ operations for hotels, rooms, employees
// Expand as needed for UPDATE/DELETE

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

    echo "<table>";
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

    echo "<table>";
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

    echo "<table>";
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
    if (!$hotel_id) die("Missing Hotel ID");

    $stmt = $mysqli->prepare("DELETE FROM hotel WHERE hotel_id = ?");
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();

    echo "<p style='color:green;'>Hotel deleted.</p><a href='manage_hotel.html'>Back</a>";
}

function updateRoom($mysqli) {
    $room_id = $_POST['room_id'] ?? '';
    $price = $_POST['price'] ?? null;
    $capacity = $_POST['capacity'] ?? null;

    if (!$room_id || (!$price && !$capacity)) {
        die("<p style='color:red;'>Room ID and at least one field required.</p>");
    }

    $sql = "UPDATE room SET ";
    $fields = [];
    $params = [];
    $types = "";

    if ($price !== null) {
        $fields[] = "price = ?";
        $params[] = $price;
        $types .= "d";
    }
    if ($capacity !== null) {
        $fields[] = "capacity = ?";
        $params[] = $capacity;
        $types .= "i";
    }

    $sql .= implode(", ", $fields) . " WHERE room_id = ?";
    $params[] = $room_id;
    $types .= "i";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    echo "<p style='color:green;'>Room updated successfully.</p><a href='manage_hotel.html'>Back</a>";
}

function deleteRoom($mysqli) {
    $room_id = $_POST['room_id'] ?? '';
    if (!$room_id) die("Missing Room ID");

    $stmt = $mysqli->prepare("DELETE FROM room WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();

    echo "<p style='color:green;'>Room deleted.</p><a href='manage_hotel.html'>Back</a>";
}

function updateEmployee($mysqli) {
    $emp_id = $_POST['employee_id'] ?? '';
    $role = $_POST['role'] ?? null;
    $email = $_POST['email'] ?? null;

    if (!$emp_id || (!$role && !$email)) {
        die("<p style='color:red;'>Employee ID and at least one field required.</p>");
    }

    $sql = "UPDATE employee SET ";
    $fields = [];
    $params = [];
    $types = "";

    if ($role !== null) {
        $fields[] = "role = ?";
        $params[] = $role;
        $types .= "s";
    }
    if ($email !== null) {
        $fields[] = "email = ?";
        $params[] = $email;
        $types .= "s";
    }

    $sql .= implode(", ", $fields) . " WHERE employee_id = ?";
    $params[] = $emp_id;
    $types .= "i";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    echo "<p style='color:green;'>Employee updated.</p><a href='manage_hotel.html'>Back</a>";
}

function deleteEmployee($mysqli) {
    $emp_id = $_POST['employee_id'] ?? '';
    if (!$emp_id) die("Missing Employee ID");

    $stmt = $mysqli->prepare("DELETE FROM employee WHERE employee_id = ?");
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();

    echo "<p style='color:green;'>Employee deleted.</p><a href='manage_hotel.html'>Back</a>";
}

?>
