<?php
include 'includes/db_connect.php';

if (!isset($_GET['contact_id']) || !isset($_GET['client_id'])) {
    die(json_encode(['success' => false, 'message' => 'Contact ID and Client ID are required.']));
}

$contactId = $_GET['contact_id'];
$clientId = $_GET['client_id'];

$unlinkQuery = "DELETE FROM client_contacts WHERE contact_id = '$contactId' AND client_id = '$clientId'";
if ($conn->query($unlinkQuery) === TRUE) {
    header("Location: client_form.php?client_code=" . $clientCode);
} else {
    echo json_encode(['success' => false, 'message' => 'Error unlinking client.']);
}
?>
