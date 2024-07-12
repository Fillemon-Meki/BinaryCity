<?php
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['contactId'])) {
        die('Contact ID is required.');
    }
    $contactId = $_POST['contactId'];

    if (isset($_POST['clients']) && is_array($_POST['clients'])) {
        foreach ($_POST['clients'] as $clientId) {
            $clientId = intval($clientId);
            $checkQuery = "SELECT * FROM client_contacts WHERE contact_id = '$contactId' AND client_id = '$clientId'";
            $checkResult = $conn->query($checkQuery);
            if ($checkResult->num_rows == 0) {
                $linkQuery = "INSERT INTO client_contacts (contact_id, client_id) VALUES ('$contactId', '$clientId')";
                $conn->query($linkQuery);
            }
        }
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
