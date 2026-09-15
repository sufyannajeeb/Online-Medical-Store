<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pharmacy";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set header for JSON response
header('Content-Type: application/json');

// Get current date
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$lastMonth = date('Y-m-d', strtotime('-1 month'));

// Initialize response array
$response = [
    'totalProducts' => 0,
    'productsChange' => '0% from last month',
    'ordersToday' => 0,
    'ordersChange' => '0% from yesterday',
    'lowStockItems' => 0,
    'lowStockChange' => '0 new items',
    'revenueToday' => 0,
    'revenueChange' => '0% from yesterday'
];

// 1. Get total products count (using 'meds' table)
$sql = "SELECT COUNT(*) as count FROM meds";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response['totalProducts'] = (int)$row['count'];
}

// 2. Get products change from last month
// Since there's no date_added column, we'll use a default message
$response['productsChange'] = 'Data not available';

// 3. Get orders today (using 'sales' table)
$sql = "SELECT COUNT(*) as count FROM sales WHERE S_DATE = '$today'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response['ordersToday'] = (int)$row['count'];
}

// 4. Get orders change from yesterday
$sql = "SELECT COUNT(*) as count FROM sales WHERE S_DATE = '$yesterday'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $yesterdayOrders = (int)$row['count'];
    $change = $yesterdayOrders > 0 ? round((($response['ordersToday'] - $yesterdayOrders) / $yesterdayOrders) * 100, 1) : 0;
    $direction = $change >= 0 ? '+' : '';
    $response['ordersChange'] = "$direction$change% from yesterday";
}

// 5. Get low stock items (using 'meds' table, assuming threshold is 10)
$threshold = 10;
$sql = "SELECT COUNT(*) as count FROM meds WHERE MED_QTY < $threshold";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response['lowStockItems'] = (int)$row['count'];
}

// 6. Get new low stock items
// Since there's no date_added column, we'll use a default message
$response['lowStockChange'] = 'Data not available';

// 7. Get revenue today
$sql = "SELECT SUM(TOTAL_AMT) as total FROM sales WHERE S_DATE = '$today'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response['revenueToday'] = (float)$row['total'];
}

// 8. Get revenue change from yesterday
$sql = "SELECT SUM(TOTAL_AMT) as total FROM sales WHERE S_DATE = '$yesterday'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $yesterdayRevenue = (float)$row['total'];
    $change = $yesterdayRevenue > 0 ? round((($response['revenueToday'] - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1) : 0;
    $direction = $change >= 0 ? '+' : '';
    $response['revenueChange'] = "$direction$change% from yesterday";
}

// Close connection
$conn->close();

// Return JSON response
echo json_encode($response);
?>