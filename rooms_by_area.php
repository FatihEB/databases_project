<?php
$mysqli = new mysqli("localhost", "root", "", "ehotel");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$query = "
SELECT 
    SUBSTRING_INDEX(h.address, ',', -2) AS area, 
    COUNT(*) AS available_rooms
FROM room r
JOIN hotel h ON h.hotel_id = r.hotel_id
WHERE r.room_id NOT IN (
    SELECT room_id FROM booking
    WHERE CURDATE() BETWEEN start_date AND end_date
)
GROUP BY area
ORDER BY available_rooms DESC;
";

$result = $mysqli->query($query);

if (!$result) {
    die("Query failed: " . $mysqli->error);
}

echo "<h2>Available Rooms by Area</h2>";
echo "<table border='1' cellpadding='10'>
<tr>
  <th>Area (City, State)</th>
  <th>Number of Available Rooms</th>
</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
      <td>" . htmlspecialchars($row['area']) . "</td>
      <td>" . $row['available_rooms'] . "</td>
    </tr>";
}

echo "</table>";

$mysqli->close();
?>