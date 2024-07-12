<?php
include 'includes/db_connect.php';

if (!isset($_POST['clientCode'])) {
    die(json_encode(['success' => false, 'message' => 'Client code is required.']));
}

$clientCode = $_POST['clientCode'];
$contacts = isset($_POST['contacts']) ? $_POST['contacts'] : [];

// Fetch client ID using client code
$clientQuery = "SELECT id FROM clients WHERE client_code = '$clientCode'";
$clientResult = $conn->query($clientQuery);

if ($clientResult->num_rows == 0) {
    die(json_encode(['success' => false, 'message' => 'Client not found.']));
}

$client = $clientResult->fetch_assoc();
$clientId = $client['id'];

// Link each contact to the client
foreach ($contacts as $contactId) {
    // Check if the contact is already linked to prevent duplicates
    $checkQuery = "SELECT * FROM client_contacts WHERE client_id = '$clientId' AND contact_id = '$contactId'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult->num_rows == 0) {
        $linkQuery = "INSERT INTO client_contacts (client_id, contact_id) VALUES ('$clientId', '$contactId')";
        if (!$conn->query($linkQuery)) {
            die(json_encode(['success' => false, 'message' => 'Error linking contact: ' . $conn->error]));
        }
    }
}

echo json_encode(['success' => true]);
?>
