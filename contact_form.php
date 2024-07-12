<?php
include 'includes/db_connect.php';

// Check if contact_id is provided
if (!isset($_GET['contact_id'])) {
    die('Contact ID is required.');
}

$contactId = $_GET['contact_id'];

// Fetch contact details
$contactQuery = "SELECT * FROM contacts WHERE id = '$contactId'";
$contactResult = $conn->query($contactQuery);

if ($contactResult->num_rows == 0) {
    die('Contact not found.');
}

$contact = $contactResult->fetch_assoc();

// Fetch all unlinked clients
$unlinkedClientsQuery = "
    SELECT * FROM clients 
    WHERE id NOT IN (SELECT client_id FROM client_contacts WHERE contact_id = '$contactId') 
    ORDER BY name ASC";
$unlinkedClientsResult = $conn->query($unlinkedClientsQuery);

// Fetch linked clients
$linkedClientsQuery = "
    SELECT clients.id, clients.name, clients.client_code 
    FROM clients 
    JOIN client_contacts ON clients.id = client_contacts.client_id 
    WHERE client_contacts.contact_id = '$contactId' 
    ORDER BY clients.name ASC";
$linkedClientsResult = $conn->query($linkedClientsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="dashboard-container">
    <div class="container">
        <h1>Contact Form</h1>
        <div class="nav-tabs">
            <a href="#general" class="active"><i class="fas fa-info-circle"></i> General</a>
            <a href="#linkClients"><i class="fas fa-link"></i> Link Clients</a>
            <a href="#linkedClients"><i class="fas fa-users"></i> Linked Clients</a>
        </div>
        
        <div id="general" class="tab-content active">
            <h2>General</h2>
            <form id="updateContactForm">
                <div class="form-group">
                    <label for="contactName">Name</label>
                    <input type="text" id="contactName" name="contactName" value="<?php echo htmlspecialchars($contact['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="contactSurname">Surname</label>
                    <input type="text" id="contactSurname" name="contactSurname" value="<?php echo htmlspecialchars($contact['surname']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="contactEmail">Email</label>
                    <input type="email" id="contactEmail" name="contactEmail" value="<?php echo htmlspecialchars($contact['email']); ?>" required>
                </div>
                <button type="submit"><i class="fas fa-save"></i> Save Contact</button>
            </form>
        </div>

        <div id="linkClients" class="tab-content">
            <h2>Link Clients</h2>
            <form id="linkClientsForm">
                <div class="table-container">
                    <table id="unlinkedClientsTable" class="display">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Client Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($unlinkedClientsResult->num_rows > 0) {
                                while ($client = $unlinkedClientsResult->fetch_assoc()) { ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="clients[]" value="<?php echo $client['id']; ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($client['name']); ?></td>
                                        <td><?php echo htmlspecialchars($client['client_code']); ?></td>
                                    </tr>
                                <?php } 
                            } else { ?>
                                <tr>
                                    <td colspan="3">No client(s) found.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit"><i class="fas fa-save"></i> Save Changes</button>
            </form>
        </div>

        <div id="linkedClients" class="tab-content">
            <h2>Linked Clients</h2>
            <div class="table-container">
                <table id="linkedClientsTable" class="display">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Client Code</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($linkedClientsResult->num_rows > 0) {
                            while ($client = $linkedClientsResult->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($client['name']); ?></td>
                                    <td><?php echo htmlspecialchars($client['client_code']); ?></td>
                                    <td>
                                        <a href="#" onclick="confirmUnlink('<?php echo $contactId; ?>', '<?php echo $client['id']; ?>')">Unlink</a>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="3">No clients found.</td>
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
        $('#linkedClientsTable').DataTable();
        $('#unlinkedClientsTable').DataTable();
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

    document.getElementById('updateContactForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const email = document.getElementById('contactEmail').value;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            alert('Please enter a valid email address.');
            return;
        }
        const formData = new FormData(this);
        formData.append('contactId', '<?php echo $contactId; ?>');
        fetch('update_contact.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(data => {
            if (data.success) {
                alert('Contact updated successfully!');
            } else {
                alert(data.message);
            }
        });
    });

    document.getElementById('linkClientsForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('contactId', '<?php echo $contactId; ?>');
        fetch('link_clients.php', {
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

    function confirmUnlink(contactId, clientId) {
        if (confirm("Are you sure you want to unlink this client?")) {
            window.location.href = "unlink_client.php?contact_id=" + contactId + "&client_id=" + clientId;
        }
    }
</script>
</body>
</html>
