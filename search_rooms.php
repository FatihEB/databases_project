<?php
$mysqli = new mysqli("localhost", "root", "", "ehotel");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$capacity = $_GET['capacity'] ?? '';
$area = $_GET['area'] ?? '';
$chain = $_GET['chain'] ?? '';
$category = $_GET['category'] ?? '';
$min_price = is_numeric($_GET['min_price']) ? $_GET['min_price'] : 0;
$max_price = is_numeric($_GET['max_price']) ? $_GET['max_price'] : 10000;


$query = "
SELECT h.name AS hotel_name, h.rating, r.*
FROM room r
JOIN hotel h ON h.hotel_id = r.hotel_id
JOIN hotel_chain hc ON hc.chain_id = h.chain_id
WHERE r.price BETWEEN ? AND ?
";

$params = [$min_price, $max_price];
$types = "dd";

if (!empty($capacity)) {
    $query .= " AND r.capacity = ?";
    $types .= "i";
    $params[] = $capacity;
}
if (!empty($area)) {
    $query .= " AND h.address LIKE ?";
    $types .= "s";
    $params[] = "%$area%";
}
if (!empty($chain)) {
    $query .= " AND hc.name = ?";
    $types .= "s";
    $params[] = $chain;
}
if (!empty($category)) {
    $query .= " AND h.rating = ?";
    $types .= "i";
    $params[] = $category;
}
if (!empty($checkin) && !empty($checkout)) {
    $query .= " AND NOT EXISTS (
        SELECT 1 FROM booking b
        WHERE b.room_id = r.room_id
        AND b.status != 'Cancelled'
        AND (
            b.start_date < ? AND b.end_date > ?
        )
    )";
    $types .= "ss";
    $params[] = $checkin; 
    $params[] = $checkout;  
}


$stmt = $mysqli->prepare($query);
$stmt->bind_param($types, ...$params);

$stmt->execute();


$result = $stmt->get_result();

echo "<h2>Available Rooms</h2>";
echo "<table border='1' cellpadding='10'>
<tr>
  <th>Hotel Name</th>
  <th>Category</th>
  <th>Room Number</th>
  <th>Capacity</th>
  <th>Price</th>
  <th>Amenities</th>
  <th>View</th>
  <th>Extendable</th>
  <th>Damages</th>
  <th>Action</th>
</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['hotel_name']}</td>
        <td>{$row['rating']} Stars</td>
        <td>{$row['room_number']}</td>
        <td>{$row['capacity']}</td>
        <td>\${$row['price']}</td>
        <td>{$row['amenities']}</td>
        <td>{$row['view_type']}</td>
        <td>" . ($row['extendable'] ? 'Yes' : 'No') . "</td>
        <td>" . ($row['damages'] ?? 'No damages') . "</td>
        <td><a href='book_room.php?room_id={$row['room_id']}&checkin={$checkin}&checkout={$checkout}'>Book Now</a></td>
    </tr>";
}

echo "</table>";

$stmt->close();
$mysqli->close();
?>
