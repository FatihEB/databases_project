<?php
$mysqli = new mysqli("localhost", "root", "", "ehotel");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$query = "
SELECT h.name AS hotel_name, h.address, SUM(r.capacity) AS total_capacity
FROM hotel h
JOIN room r ON h.hotel_id = r.hotel_id
GROUP BY h.hotel_id, h.name, h.address
ORDER BY total_capacity DESC;
";

$result = $mysqli->query($query);

if (!$result) {
    die("Query failed: " . $mysqli->error);
}

echo "<h2>Total Room Capacity per Hotel</h2>";
echo "<table border='1' cellpadding='10'>
<tr>
  <th>Hotel Name</th>
  <th>Address</th>
  <th>Total Capacity (Beds)</th>
</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
      <td>" . htmlspecialchars($row['hotel_name']) . "</td>
      <td>" . htmlspecialchars($row['address']) . "</td>
      <td>" . $row['total_capacity'] . "</td>
    </tr>";
}

echo "</table>";

$mysqli->close();
?>
