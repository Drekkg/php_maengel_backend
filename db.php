<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');


// Allow cross-origin requests
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json"); 
<?php
// Load DB_PASSWORD from .env file
$envFile = __DIR__ . '/.env'; // Define the path to the .env file
if (file_exists($envFile)) { // Check if the .env file exists
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); 
    // Read the .env file into an array, each line becomes an array element
    foreach ($lines as $line) { // Loop through each line
        if (strpos(trim($line), 'DB_PASSWORD=') === 0) { 
            // Check if the line starts with 'DB_PASSWORD='
            $password = trim(explode('=', $line, 2)[1]); 
            // Split the line at the '=' sign and take the second part (the password)
            break; // Exit the loop once the password is found
        }
    }
} else {
    error_log("Environment file (.env) not found."); 
    // Log an error if the .env file is missing
    die("Environment configuration missing."); 
    // Stop execution if the .env file is not found
}

// Define database connection variables
$servername = localhost;
$username = getenv('DB_USERNAME');
//$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');


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
$sql = "CREATE DATABASE IF NOT EXISTS myDB4";
if ($conn->query($sql) === TRUE) {
    $response["database"] = "Database created successfully";
   
} else {
    $response["error"] = "Error creating database: " . $conn->error;
}

  // Select the database
$conn->select_db("myDB4");

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



// Extract data from the input
$kunde = $data['kunde'] ?? null;
$standort = $data['standort'] ?? null;
$aufstellung = $data['aufstellung'] ?? null;
$anlage = $data['anlage'] ?? null;
$kaeltemittel = $data['kaeltemittel'] ?? null;
$techniker = $data['techniker'] ?? null;
$currentDate = $data['currentDate'] ?? null;
$maengel1 = $data['maengel1'] ?? null;
$maengel2 = $data['maengel2'] ?? null;
$maengel3 = $data['maengel3'] ?? null;
$maengel4 = $data['maengel4'] ?? null;
$maengel5 = $data['maengel5'] ?? null;
$maengel6 = $data['maengel6'] ?? null;

$sql = "INSERT INTO maengel (kunde, standort, aufstellung, anlage, kaeltemittel, techniker, currentDate, maengel1, maengel2, maengel3, maengel4, maengel5, maengel6) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(["error" => "Failed to prepare statement: " . $conn->error]);
    exit();
}

// Bind parameters to the prepared statement
$stmt->bind_param(
    "sssssssssssss", 
    $kunde, 
    $standort, 
    $aufstellung, 
    $anlage, 
    $kaeltemittel, 
    $techniker, 
    $currentDate, 
    $maengel1, 
    $maengel2, 
    $maengel3, 
    $maengel4, 
    $maengel5, 
    $maengel6
);
$stmt->execute();
};

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

            

