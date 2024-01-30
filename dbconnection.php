<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Replace these with your actual MongoDB connection details
$host = "mongodb://localhost:27017";
$databaseName = "basicDetails";
$collectionName = "users";

// Set up MongoDB connection
$manager = new MongoDB\Driver\Manager($host);

// Define the command to list collections
$command = new MongoDB\Driver\Command(['listCollections' => 1]);

// Execute the command
$collections = $manager->executeCommand($databaseName, $command);

// Check if the desired collection exists
$collectionExists = false;
foreach ($collections as $collectionInfo) {
    if ($collectionInfo->name === $collectionName) {
        $collectionExists = true;
        break;
    }
}

// If the collection does not exist, create it
if (!$collectionExists) {
    $createCommand = new MongoDB\Driver\Command(['create' => $collectionName]);
    $manager->executeCommand($databaseName, $createCommand);
}
// Fetch data from the POST request
$data = json_decode(file_get_contents('php://input'), true);

$name =$data['name'];
$surname = $data['surname'];
$dob = $data['dob'];
$idNumber = $data['idNumber'];

// Query to find documents with the specified value in the column
$filter = ['idNumber' => $idNumber]; // Replace 'column_name' with the actual column name

$query = new MongoDB\Driver\Query($filter);

// Execute the query
$result = $manager->executeQuery("$databaseName.$collectionName", $query);

// Check if any documents were found
if (iterator_count($result) > 0) {
    echo json_encode(["status" => "error", "message" => "ID Number already exists in the collection."]);
    exit();
    
} 


if (empty($name) || empty($surname)) {
    echo json_encode(["status" => "error", "message" => "Please provide valid data for both name and surname fields."]);
    exit();
}

// Insert a document into the collection
$insertCommand = new MongoDB\Driver\Command([
    'insert' => $collectionName,
    'documents' => [
        ['name' => $name, 'surname' => $surname,'dob' => $dob,'idNumber'=>$idNumber]
    ]
]);
// Initialize message variable
$message = "";

try {
    // Execute the insert command
    $result = $manager->executeCommand($databaseName, $insertCommand);

    // Check if the operation was successful
    $response = current($result->toArray());

    if ($response->ok) {
        echo json_encode(["status" => "success", "message" => "Data inserted successfully."]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Error inserting data. Details: " . json_encode($response, JSON_PRETTY_PRINT)]);
        exit();
    }
} catch (MongoDB\Driver\Exception\Exception $e) {
    // Handle MongoDB driver exceptions
    echo json_encode(["status" => "error", "message" => "MongoDB driver error: " . $e->getMessage()]);
    exit();
}

