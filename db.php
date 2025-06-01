<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');



// Allow cross-origin requests
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, PUT, GET, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json"); 
// Load environment variables from .env file

// Load DB_PASSWORD from .env file
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), 'DB_PASSWORD=') === 0) {
            $password = trim(explode('=', $line, 2)[1]);
            break;
        }
    }
} else {
    error_log("Environment file (.env) not found.");
    die("Environment configuration missing.");
}

// Define database connection variables
$servername = '127.0.0.1:3307';
$username = 'h97690_derek';
$dbname = 'h97690_maengel_list';

//error_log("DB_PASSWORD: FFS " . getenv('DB_PASSWORD'));

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}


// Create the database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS maengel_list";
if ($conn->query($sql) === TRUE) {
    $response["database"] = "Database created successfully";
   
} else {
    $response["error"] = "Error creating database: " . $conn->error;
}

  // Select the database
$conn->select_db("maengel_list");

 $sql = "CREATE TABLE IF NOT EXISTS maengel (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kunde VARCHAR(30),
    standort VARCHAR(50),
    aufstellung VARCHAR(30),
    anlage  VARCHAR(30),
    kaeltemittel VARCHAR(30),
    techniker VARCHAR(25),
    currentDate VARCHAR(15),
    maengel1 VARCHAR(155),
    maengel2 VARCHAR(155),
    maengel3 VARCHAR(155),
    maengel4 VARCHAR(155),
    maengel5 VARCHAR(155),
    maengel6 VARCHAR(155),
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql) === TRUE) {
        $response["Table"] =   "Table maengel created successfully";
      } else {
        $response["Error"] = "Error creating table: " . $conn->error;
      }
 
     



// Prepare the SQL query
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     error_log("POST request received");
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

trigger_error("POST REQ", E_USER_WARNING);

// Extract data from the input
$kunde = $data['kunde'] ?? null;
$standort = $data['standort'] ?? null;
$aufstellung = $data['aufstellung'] ?? null;
$anlage = $data['anlage'] ?? null;
$type = $data['type'] ?? null;
$kaeltemittel = $data['kaeltemittel'] ?? null;
$techniker = $data['techniker'] ?? null;
$currentDate = $data['currentDate'] ?? null;
$maengel1 = $data['maengel1'] ?? null;
$maengel2 = $data['maengel2'] ?? null;
$maengel3 = $data['maengel3'] ?? null;
$maengel4 = $data['maengel4'] ?? null;
$maengel5 = $data['maengel5'] ?? null;
$maengel6 = $data['maengel6'] ?? null;
$archived = $data['archived'] ?? null;

$sql = "INSERT INTO maengel (kunde, standort, aufstellung, anlage, type, kaeltemittel, techniker, currentDate, maengel1, maengel2, maengel3, maengel4, maengel5, maengel6, archived) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(["error" => "Failed to prepare statement: " . $conn->error]);
    exit();
}

// Bind parameters to the prepared statement
$stmt->bind_param(
    "sssssssssssssss", 
    $kunde, 
    $standort, 
    $aufstellung,
    $anlage,
    $type, 
    $kaeltemittel, 
    $techniker, 
    $currentDate, 
    $maengel1, 
    $maengel2, 
    $maengel3, 
    $maengel4, 
    $maengel5, 
    $maengel6,
    $archived
);
$stmt->execute();
};


if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
   
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    $id = $_GET['id'] ?? null;
    $archived = $data['archived'] ?? null;


    if (!$id || !is_numeric($id) || $archived === null || !is_numeric($archived)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid input. ID and archived status are required and must be numeric."]);
        exit();
    }

    $sql = "UPDATE maengel SET archived = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to prepare statement: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("ii", $archived, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Row updated successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "No rows were updated."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to update row: " . $stmt->error]);
    }

    $stmt->close();
    
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Get the ID from the query string
    $id = $_GET['id'] ?? null;

    // Validate the ID
    if (!$id || !is_numeric($id)) {
        http_response_code(400); // Bad Request
        echo json_encode(["success" => false, "message" => "Invalid ID."]);
        exit();
    }

    $sql = "DELETE FROM maengel WHERE maengel.id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to prepare statement: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Row updated successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "No rows were updated."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to update row: " . $stmt->error]);
    }

    $stmt->close();
    
    exit();
}









$sql = "SELECT * FROM maengel";
$result = $conn->query($sql);

if ($result === false) {
    echo json_encode(["error" => "Failed to execute query: " . $conn->error]);
    exit();
}

// Fetch data and return as JSON
if ($result->num_rows > 0) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row; // Add each row to the data array
    }
     echo json_encode(["success" => true, "data" => $data]);
     exit();
} else {
    echo json_encode(["success" => true, "data" => []]); // Return an empty array if no rows are found
    exit();
}



$stmt->close();
$conn->close();
?>

            
