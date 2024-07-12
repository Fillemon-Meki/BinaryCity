<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

include 'includes/db_connect.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $clientId = intval($_POST['clientId']);
    $clientName = trim($_POST['clientName']);

    if (empty($clientName)) {
        $response['success'] = false;
        $response['message'] = 'Client Name cannot be empty.';
    } else {
        // Generate new client code
        $clientCode = generateClientCode($clientName);

        // Update the client in the database
        $updateQuery = "UPDATE clients SET name = ?, client_code = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssi", $clientName, $clientCode, $clientId);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Client updated successfully.';
            $response['client_code'] = $clientCode;
        } else {
            $response['success'] = false;
            $response['message'] = 'Error updating client: ' . $stmt->error;
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

// Function to generate client code
function generateClientCode($clientName) {
    // Strip non-alphanumeric characters except spaces
    $clientName = preg_replace("/[^a-zA-Z0-9 ]+/", "", $clientName);

    // Split name into words
    $words = explode(' ', $clientName);

    if (count($words) >= 3) {
        // Take the first letter of each of the first three words
        $alphaPart = strtoupper($words[0][0] . $words[1][0] . $words[2][0]);
    } elseif (count($words) == 2) {
        // Take the first letter of the first word and the first two letters of the second word
        $alphaPart = strtoupper($words[0][0] . substr($words[1], 0, 2));
    } else {
        // Take the first three letters of the single word
        $alphaPart = strtoupper(substr($words[0], 0, 3));
    }

    // Ensure the alpha part is at least 3 characters long
    while (strlen($alphaPart) < 3) {
        $alphaPart .= 'A'; // Append 'A' to fill up to 3 characters
    }

    // Generate the unique numeric part
    $numericPart = generateUniqueNumericPart($alphaPart);

    // Format the client code
    $clientCode = $alphaPart . str_pad($numericPart, 3, '0', STR_PAD_LEFT);

    return $clientCode;
}

// Function to generate unique numeric part for client code
function generateUniqueNumericPart($alphaPart) {
    global $conn;

    $query = "SELECT MAX(CAST(SUBSTRING(client_code, 4, 3) AS UNSIGNED)) AS max_numeric_part 
              FROM clients 
              WHERE LEFT(client_code, 3) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $alphaPart);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $maxNumericPart = $row['max_numeric_part'];

    if ($maxNumericPart === null) {
        // No existing codes for this alpha part
        return 1;
    } else {
        // Increment numeric part
        return intval($maxNumericPart) + 1;
    }
}
?>
