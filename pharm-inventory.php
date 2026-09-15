<?php
include "config.php";
session_start();
$ename = "Unknown";
if (isset($_SESSION['user'])) {
    $sql1 = "SELECT E_FNAME FROM EMPLOYEE WHERE E_ID='{$_SESSION['user']}'";
    $result1 = $conn->query($sql1);
    if ($result1 && $result1->num_rows > 0) {
        $row1 = $result1->fetch_row();
        $ename = $row1[0];
    }
}
if (isset($_POST['search']) && !empty(trim($_POST['valuetosearch']))) {
    $search = mysqli_real_escape_string($conn, trim($_POST['valuetosearch']));
    
    if (is_numeric($search)) {
        // If input is numeric, search by med_id
        $query = "SELECT med_id as medid, med_name as medname, med_qty as medqty, category as medcategory, med_price as medprice, location_rack as medlocation 
                  FROM meds 
                  WHERE med_id = '$search'";
    } else {
        // Otherwise, search by medicine name
        $query = "SELECT med_id as medid, med_name as medname, med_qty as medqty, category as medcategory, med_price as medprice, location_rack as medlocation 
                  FROM meds 
                  WHERE med_name LIKE '%$search%'";
    }
    $search_result = mysqli_query($conn, $query) or die(mysqli_error($conn));
} else {
    $query = "SELECT med_id as medid, med_name as medname, med_qty as medqty, category as medcategory, med_price as medprice, location_rack as medlocation FROM meds";
    $search_result = mysqli_query($conn, $query) or die(mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Medicine Inventory</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --primary: #4f46e5;
    --primary-dark: #4338ca;
    --secondary: #06b6d4;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --card-bg: #ffffff;
    --text-color: #1f2937;
    --navbar-bg: #f9fafb;
    --sidebar-bg: #1e293b;
    --light-bg: #f3f4f6;
    --border-color: #e5e7eb;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--light-bg);
    color: var(--text-color);
    min-height: 100vh;
    line-height: 1.5;
}

/* Navbar */
.navbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    background: var(--navbar-bg);
    color: var(--text-color);
    padding: 15px 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    z-index: 200;
    box-shadow: var(--shadow-sm);
    border-bottom: 1px solid var(--border-color);
}

.navbar .menu-icon {
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: var(--text-color);
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.navbar .menu-icon:hover {
    background: var(--light-bg);
    transform: scale(1.05);
}

.navbar a {
    color: var(--text-color);
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.navbar a:hover {
    background: var(--light-bg);
}

/* Sidebar */
.sidenav {
    height: 100%;
    width: 0;
    position: fixed;
    z-index: 250;
    top: 0; left: 0;
    background: var(--sidebar-bg);
    overflow-x: hidden;
    transition: 0.4s;
    padding-top: 80px;
    color: #fff;
    box-shadow: var(--shadow-lg);
}

.sidenav a, .sidenav button.dropdown-btn {
    padding: 14px 24px;
    font-size: 16px;
    color: #e2e8f0;
    display: block;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    position: relative;
}

.sidenav a i, .sidenav button i {
    margin-right: 12px;
    width: 20px;
    text-align: center;
}

.sidenav a:hover, .sidenav button:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.sidenav a.active, .sidenav button.active {
    background: rgba(79, 70, 229, 0.2);
    color: #fff;
    border-left: 3px solid var(--primary);
}

.dropdown-container {
    display: none;
    background: rgba(0,0,0,0.1);
    padding-left: 0;
}

.dropdown-container a {
    padding-left: 60px;
    color: #cbd5e1;
    font-size: 15px;
}

/* Overlay */
#overlay {
    position: fixed;
    display: none;
    width: 100%; height: 100%;
    top: 0; left: 0;
    background: rgba(0,0,0,0.5);
    z-index: 100;
}

/* Main content */
.main {
    margin-top: 80px;
    padding: 24px;
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
}

/* Headline */
.head {
    margin-bottom: 32px;
}

.head h2 {
    font-size: 32px;
    font-weight: 700;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 12px;
}

.head h2::before {
    content: "";
    display: inline-block;
    width: 5px;
    height: 32px;
    background: var(--primary);
    border-radius: 4px;
}

/* Card container */
.inventory-card {
    background: var(--card-bg);
    border-radius: 12px;
    box-shadow: var(--shadow-md);
    padding: 24px;
    overflow: hidden;
}

/* Search form */
.search-container {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
}

.search-input {
    flex: 1;
    position: relative;
}

.search-input input {
    width: 100%;
    padding: 12px 16px 12px 44px;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.2s ease;
    background: var(--light-bg);
}

.search-input i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
}

.search-input input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    background: #fff;
}

.search-button {
    padding: 12px 24px;
    border-radius: 10px;
    border: none;
    background: var(--primary);
    color: #fff;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-button:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Table */
.table-container {
    overflow-x: auto;
    border-radius: 10px;
    border: 1px solid var(--border-color);
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th, table td {
    padding: 16px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

table th {
    background: var(--light-bg);
    font-weight: 600;
    color: var(--text-color);
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

table tr:last-child td {
    border-bottom: none;
}

table tr:hover td {
    background: rgba(79, 70, 229, 0.03);
}

/* Status badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 500;
}

.status-badge.in-stock {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.status-badge.out-of-stock {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}

.status-badge.low-stock {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning);
}

.status-badge i {
    margin-right: 6px;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    color: #d1d5db;
}

.empty-state p {
    font-size: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .search-container {
        flex-direction: column;
    }
    
    .search-button {
        width: 100%;
        justify-content: center;
    }
    
    .table-container {
        font-size: 14px;
    }
    
    table th, table td {
        padding: 12px 8px;
    }
}
</style>
</head>
<body>
<div class="navbar">
    <span class="menu-icon" onclick="openNav()">
        <i class="fas fa-bars"></i>
    </span>
    <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i>
        Logout (signed in as <?php echo htmlspecialchars($ename); ?>)
    </a>
</div>

<div id="mySidenav" class="sidenav">
    <a href="javascript:void(0)" onclick="closeNav()" style="font-size:24px;position:absolute;top:15px;right:25px;">
        <i class="fas fa-times"></i>
    </a>
    <a href="pharmmainpage.php">
        <i class="fas fa-home"></i>
        Dashboard
    </a>
    <a href="pharm-inventory.php" class="active">
        <i class="fas fa-pills"></i>
        View Inventory
    </a>
    <!-- <a href="pharm-pos1.php">
        <i class="fas fa-plus-circle"></i>
        Add New Sale
    </a>
    <button class="dropdown-btn">
        <i class="fas fa-users"></i>
        Customers
    </button>
    <div class="dropdown-container">
        <a href="pharm-customer.php">
            <i class="fas fa-user-plus"></i>
            Add New Customer
        </a>
        <a href="pharm-customer-view.php">
            <i class="fas fa-list"></i>
            View Customers
        </a>
    </div> -->
</div>

<div id="overlay" onclick="closeNav()"></div>

<div class="main">
    <div class="head">
        <h2>MEDICINE INVENTORY</h2>
    </div>
    
    <div class="inventory-card">
        <form method="post" class="search-container">
            <div class="search-input">
                <i class="fas fa-search"></i>
                <input type="text" name="valuetosearch" placeholder="Search by medicine name or ID...">
            </div>
            <button type="submit" name="search" class="search-button">
                <i class="fas fa-search"></i>
                Search
            </button>
        </form>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Medicine ID</th>
                        <th>Medicine Name</th>
                        <th>Quantity Available</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Location in Store</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($search_result && $search_result->num_rows > 0) {
                        while($row = $search_result->fetch_assoc()) {
                            $quantity = $row["medqty"];
                            $statusClass = "";
                            $statusText = "";
                            $statusIcon = "";
                            
                            if ($quantity <= 0) {
                                $statusClass = "out-of-stock";
                                $statusText = "Out of Stock";
                                $statusIcon = "fas fa-times-circle";
                            } elseif ($quantity < 10) {
                                $statusClass = "low-stock";
                                $statusText = "Low Stock";
                                $statusIcon = "fas fa-exclamation-triangle";
                            } else {
                                $statusClass = "in-stock";
                                $statusText = "In Stock";
                                $statusIcon = "fas fa-check-circle";
                            }
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["medid"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["medname"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["medqty"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["medcategory"]) . "</td>";
                            echo "<td>$" . htmlspecialchars($row["medprice"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["medlocation"]) . "</td>";
                            echo "<td><span class='status-badge " . $statusClass . "'><i class='" . $statusIcon . "'></i>" . $statusText . "</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='empty-state'><i class='fas fa-box-open'></i><p>No medicines found in inventory</p></td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function openNav() {
    document.getElementById("mySidenav").style.width = "280px";
    document.getElementById("overlay").style.display = "block";
}

function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
    document.getElementById("overlay").style.display = "none";
}

var dropdown = document.getElementsByClassName("dropdown-btn");
for (var i = 0; i < dropdown.length; i++) {
    dropdown[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var dropdownContent = this.nextElementSibling;
        dropdownContent.style.display = dropdownContent.style.display === "block" ? "none" : "block";
    });
}
</script>
</body>
</html>