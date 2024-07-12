<?php
include 'includes/db_connect.php';

// Fetch clients
$clientsQuery = "SELECT clients.id, clients.name, clients.client_code, COUNT(client_contacts.contact_id) AS contact_count 
                 FROM clients 
                 LEFT JOIN client_contacts ON clients.id = client_contacts.client_id 
                 GROUP BY clients.id 
                 ORDER BY clients.name ASC";
$clientsResult = $conn->query($clientsQuery);

// Fetch contacts
$contactsQuery = "SELECT contacts.id, contacts.name, contacts.surname, contacts.email, COUNT(client_contacts.client_id) AS client_count 
                  FROM contacts 
                  LEFT JOIN client_contacts ON contacts.id = client_contacts.contact_id 
                  GROUP BY contacts.id 
                  ORDER BY contacts.surname ASC, contacts.name ASC";
$contactsResult = $conn->query($contactsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Views</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <div class="dashboard-container">
        <div class="container">
            <h1>View</h1>
            <div class="nav-tabs">
                <a href="#clients" class="active"><i class="fas fa-users"></i> Clients</a>
                <a href="#contacts"><i class="fas fa-address-book"></i> Contacts</a>
            </div>
            <div id="clients" class="tab-content active">
                <h2>Clients</h2>
                <button onclick="showClientForm()"><i class="fas fa-plus"></i> Add Client</button>
                <div id="clientForm" style="display: none;">
                    <h3>New Client</h3>
                    <form id="addClientForm">
                        <div class="form-group">
                            <label for="clientName">Name</label>
                            <input type="text" id="clientName" name="clientName" required>
                        </div>
                        <button type="submit"><i class="fas fa-save"></i> Save Client</button>
                    </form>
                </div>
                <div class="table-container">
                    <table id="clientsTable" class="display">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Client Code</th>
                                <th>No. of Linked Contacts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($clientsResult->num_rows > 0) {
                                while ($client = $clientsResult->fetch_assoc()) { ?>
                                    <tr data-client-code="<?php echo $client['client_code']; ?>">
                                        <td><?php echo $client['name']; ?></td>
                                        <td><?php echo $client['client_code']; ?></td>
                                        <td style="text-align: center;"><?php echo $client['contact_count']; ?></td>
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
            </div>
            <div id="contacts" class="tab-content">
                <h2>Contacts</h2>
                <button onclick="showContactForm()"><i class="fas fa-plus"></i> Add Contact</button>
                <div id="contactForm" style="display: none;">
                    <h3>New Contact</h3>
                    <form id="addContactForm">
                        <div class="form-group">
                            <label for="contactName">Name</label>
                            <input type="text" id="contactName" name="contactName" required>
                        </div>
                        <div class="form-group">
                            <label for="contactSurname">Surname</label>
                            <input type="text" id="contactSurname" name="contactSurname" required>
                        </div>
                        <div class="form-group">
                            <label for="contactEmail">Email</label>
                            <input type="email" id="contactEmail" name="contactEmail" required>
                        </div>
                        <button type="submit"><i class="fas fa-save"></i> Save Contact</button>
                    </form>
                </div>
                <div class="table-container">
                    <table id="contactsTable" class="display">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Surname</th>
                                <th>Email Address</th>
                                <th>No. of Linked Clients</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($contactsResult->num_rows > 0) {
                                while ($contact = $contactsResult->fetch_assoc()) { ?>
                                    <tr data-contact-id="<?php echo $contact['id']; ?>">
                                        <td><?php echo $contact['name']; ?></td>
                                        <td><?php echo $contact['surname']; ?></td>
                                        <td><?php echo $contact['email']; ?></td>
                                        <td style="text-align: center;"><?php echo $contact['client_count']; ?></td>
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
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#clientsTable').DataTable();
            $('#contactsTable').DataTable();
        });

        function showClientForm() {
            document.getElementById('clientForm').style.display = 'block';
        }

        function showContactForm() {
            document.getElementById('contactForm').style.display = 'block';
        }

        document.querySelectorAll('.nav-tabs a').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.nav-tabs a').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                document.querySelector(this.getAttribute('href')).classList.add('active');
            });
        });

        document.getElementById('addClientForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('add_client.php', {
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

        document.getElementById('addContactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('add_contact.php', {
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


        document.addEventListener('DOMContentLoaded', function() {
            // Make client rows clickable
            document.querySelectorAll('#clientsTable tbody tr').forEach(row => {
                row.addEventListener('click', function() {
                    window.location.href = 'client_form.php?client_code=' + this.dataset.clientCode;
                });
            });

            // Make contact rows clickable
            document.querySelectorAll('#contactsTable tbody tr').forEach(row => {
                row.addEventListener('click', function() {
                    window.location.href = 'contact_form.php?contact_id=' + this.dataset.contactId;
                });
            });
        });
    </script>
</body>

</html>