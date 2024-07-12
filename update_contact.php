<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

include 'includes/db_connect.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contactId = $_POST['contactId'];
    $contactName = $_POST['contactName'];
    $contactSurname = $_POST['contactSurname'];
    $contactEmail = $_POST['contactEmail'];

    // Validate email format
    if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        $response['success'] = false;
        $response['message'] = 'Invalid email format.';
    } else {
        // Update contact details in the database
        $updateQuery = "UPDATE contacts SET name = ?, surname = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssi", $contactName, $contactSurname, $contactEmail, $contactId);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Contact updated successfully.';
        } else {
            $response['success'] = false;
            $response['message'] = 'Error updating contact: ' . $stmt->error;
        }

        $stmt->close();
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Invalid request.';
}

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
