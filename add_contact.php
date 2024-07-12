<?php
include 'includes/db_connect.php';

// Function to sanitize input data
function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $name = sanitize_input($_POST["contactName"]);
    $surname = sanitize_input($_POST["contactSurname"]);
    $email = filter_var($_POST["contactEmail"], FILTER_VALIDATE_EMAIL) ? $_POST["contactEmail"] : '';

    // Validate email format
    if (!$email) {
        $response = array(
            'success' => false,
            'message' => 'Invalid email format.'
        );
    } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO contacts (name, surname, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $surname, $email);

        if ($stmt->execute()) {
            $response = array(
                'success' => true,
                'message' => 'Contact added successfully.'
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Error: ' . $conn->error
            );
        }
    }

    echo json_encode($response);
}
?>
