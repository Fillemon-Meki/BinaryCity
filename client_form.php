<?php
include 'includes/db_connect.php';

// Check if client_code is provided
if (!isset($_GET['client_code'])) {
    die('Client code is required.');
}

$clientCode = $_GET['client_code'];

// Fetch client details
$clientQuery = "SELECT * FROM clients WHERE client_code = '$clientCode'";
$clientResult = $conn->query($clientQuery);

if ($clientResult->num_rows == 0) {
    die('Client not found.');
}

$client = $clientResult->fetch_assoc();
$clientId = $client['id'];  // Using client ID internally

// Fetch all unlinked contacts
$unlinkedContactsQuery = "
    SELECT * FROM contacts 
    WHERE id NOT IN (SELECT contact_id FROM client_contacts WHERE client_id = '$clientId') 
    ORDER BY surname ASC, name ASC";
$unlinkedContactsResult = $conn->query($unlinkedContactsQuery);

// Fetch linked contacts
$linkedContactsQuery = "
    SELECT contacts.id, contacts.name, contacts.surname, contacts.email 
    FROM contacts 
    JOIN client_contacts ON contacts.id = client_contacts.contact_id 
    WHERE client_contacts.client_id = '$clientId' 
    ORDER BY contacts.surname ASC, contacts.name ASC";
$linkedContactsResult = $conn->query($linkedContactsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Form</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="dashboard-container">
    <div class="container">
        <h1>Client Form</h1>
        <div class="nav-tabs">
            <a href="#general" class="active"><i class="fas fa-info-circle"></i> General</a>
            <a href="#linkContacts"><i class="fas fa-link"></i> Link Contacts</a>
            <a href="#linkedContacts"><i class="fas fa-users"></i> Linked Contacts</a>
        </div>
        
        <div id="general" class="tab-content active">
            <h2>General</h2>
            <form id="updateClientForm">
                <div class="form-group">
                    <label for="clientName">Name</label>
                    <input type="text" id="clientName" name="clientName" value="<?php echo htmlspecialchars($client['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="clientCode">Client Code</label>
                    <input type="text" id="clientCode" name="clientCode" value="<?php echo htmlspecialchars($client['client_code']); ?>" readonly>
                </div>
                <button type="submit"><i class="fas fa-save"></i> Save Client</button>
            </form>
        </div>

        <div id="linkContacts" class="tab-content">
            <h2>Link Contacts</h2>
            <form id="linkContactsForm">
                <div class="table-container">
                    <table id="unlinkedContactsTable" class="display">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Surname</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($unlinkedContactsResult->num_rows > 0) {
                                while ($contact = $unlinkedContactsResult->fetch_assoc()) { ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="contacts[]" value="<?php echo $contact['id']; ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['surname']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                    </tr>
                                <?php } 
                            } else { ?>
                                <tr>
                                    <td colspan="4">No contact(s) found.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit"><i class="fas fa-save"></i> Save Changes</button>
            </form>
        </div>

        <div id="linkedContacts" class="tab-content">
            <h2>Linked Contacts</h2>
            <div class="table-container">
                <table id="linkedContactsTable" class="display">
                    <thead>
                        <tr>
                            <th>Contact Full Name</th>
                            <th>Email Address</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($linkedContactsResult->num_rows > 0) {
                            while ($contact = $linkedContactsResult->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($contact['surname'] . ' ' . $contact['name']); ?></td>
                                    <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                    <td>
                                        <a href="#" onclick="confirmUnlink('<?php echo $clientCode; ?>', '<?php echo $contact['id']; ?>')">Unlink</a>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="3">No contacts found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#linkedContactsTable').DataTable();
        $('#unlinkedContactsTable').DataTable();
    });

    document.querySelectorAll('.nav-tabs a').forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('.nav-tabs a').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            document.querySelector(this.getAttribute('href')).classList.add('active');
        });
    });

    document.getElementById('updateClientForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('clientId', '<?php echo $clientId; ?>');
        fetch('update_client.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(data => {
            if (data.success) {
                alert('Client updated successfully!');
                window.location.href = 'client_form.php?client_code=' + data.client_code;
            } else {
                alert(data.message);
            }
        });
    });

    document.getElementById('linkContactsForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('clientCode', '<?php echo $clientCode; ?>');
        fetch('link_contacts.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    });

    function confirmUnlink(clientCode, contactId) {
        if (confirm("Are you sure you want to unlink this contact?")) {
            window.location.href = "unlink_contact.php?client_code=" + clientCode + "&contact_id=" + contactId;
        }
    }
</script>
</body>
</html>
