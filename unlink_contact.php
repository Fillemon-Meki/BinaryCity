<?php
include 'includes/db_connect.php';

$clientCode = $_GET['client_code'];
$contactId = $_GET['contact_id'];

// Fetch client ID using client code
$clientQuery = "SELECT id FROM clients WHERE client_code = '$clientCode'";
$clientResult = $conn->query($clientQuery);

if ($clientResult->num_rows == 0) {
    die("Client not found.");
}

$client = $clientResult->fetch_assoc();
$clientId = $client['id'];

// Perform unlink operation
$unlinkQuery = "DELETE FROM client_contacts WHERE client_id = '$clientId' AND contact_id = '$contactId'";
if ($conn->query($unlinkQuery)) {
    header("Location: client_form.php?client_code=" . $clientCode);
} else {
    echo "Error unlinking contact: " . $conn->error;
}

$conn->close();
?>

