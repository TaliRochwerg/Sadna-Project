<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location:./login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>מערכת ניהול צבאית | Certifications</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/assets/css/styles.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="/assets/DataTables/datatables.min.css">
    <link rel="stylesheet" href="/assets/DataTables/DataTables-1.10.18/css/dataTables.bootstrap4.min.css">

    <script src="/assets/DataTables/datatables.min.js"></script>

    <!-- Material Design Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
</head>

<body>
    <?php include './header.php'; ?>
    <div class="main-content container">
        <div class="row">
            <div class="col-md-12">
                <h3>רשימת אימונים והסמכות מלאה</h3>
                <div id="errorMsg" class="alert alert-danger d-none">
                    אין לך הרשאות לבצע פעולה זו.
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm" id="createCertificationBtn">יצירת אימון חדש</a>
                    </div>
                </div>
                <div class="table-responsive mt-4">
                    <table id="certificationTable" class="table" style="width:100%">
                        <thead>
                            <tr>
                                <th>מזהה אימון</th>
                                <th>שם האימון</th>
                                <th>תאריך ושעה</th>
                                <th>מיקום</th>
                                <th>מפקד</th>
                                <th>רשימת חיילים</th>
                                <th>פעולות</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">אישור מחיקת אימון</h5>
                    <button type="button" class="btn-close mx-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    האם אתה בטוח שאתה רוצה למחוק את ההסמכה?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ביטול</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">מחיקה</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    $(document).ready(function() {
        // Determine if user is a "Commander"
        var userRole = <?php echo json_encode($_SESSION['role']); ?>;
        console.log(userRole, '-d-d-d-d-')
        // Check if the user is a "Commander" and display error on "Add Certification" button click
        $('#createCertificationBtn').on('click', function() {
            if (userRole === "Commander") {
                $('#errorMsg').removeClass('d-none');
            } else {
                window.location.href = './create_certification.php';
            }
        });

        var table = $('#certificationTable').DataTable({
            "processing": true,
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Hebrew.json"
            },
            "ajax": {
                "url": "./controllers/certifications/get_certifications.php",
                "type": "GET"
            },
            "columns": [{
                    "data": "training_id"
                },
                {
                    "data": "training_name"
                },
                {
                    "data": "date_time"
                },
                {
                    "data": "location"
                },
                {
                    "data": "commander_username"
                },
                {
                    "data": "soldier_count"
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        // Check if the current user's role is "Commander"
                        let deleteButton = '';
                        let updateButton = `<a href="javascript:void(0);" class="btn btn-sm btn-primary update-certification" data-id="${row.id}">
                            <span data-notify="icon" class="mdi mdi-pencil"></span>
                        </a>`;
                        
                        if (userRole !== "Commander") {
                            deleteButton = `<a href="javascript:void(0);" class="certification-delete btn btn-sm btn-danger mx-1" data-id="${row.id}">
                                <span data-notify="icon" class="mdi mdi-delete"></span>
                            </a>`;
                        }

                        return `
                            <div class="d-flex justify-content-center align-items-center">
                                ${updateButton}
                                ${deleteButton}
                            </div>`;
                    }
                }
            ]
        });

        let deleteCertificationId;

        // Use event delegation to handle dynamic elements
        $('#certificationTable tbody').on('click', '.certification-delete', function() {
            deleteCertificationId = $(this).data('id');
            $('#deleteModal').modal('show');
        });

        $('#confirmDelete').on('click', function() {
            if (deleteCertificationId) {
                $.ajax({
                    url: `./controllers/certifications/delete_certification.php?certification_id=${deleteCertificationId}`,
                    type: 'DELETE',
                    success: function(result) {
                        $('#deleteModal').modal('hide');
                        table.ajax.reload(null, false); // Reload the table data 
                    },
                    error: function(err) {
                        console.error('Error deleting certification:', err);
                    }
                });
            }
        });

        // Handle "Update Certification" click
        $('#certificationTable tbody').on('click', '.update-certification', function() {
            var certificationId = $(this).data('id');
            if (userRole === "Commander") {
                $('#errorMsg').removeClass('d-none');
            } else {
                window.location.href = `./update_certification.php?certification_id=${certificationId}`;
            }
        });
    });
    </script>
</body>

</html>
