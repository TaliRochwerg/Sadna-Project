<?php
session_start();
require_once('./config/db.php');

if (!isset($_SESSION['user'])) {
    header('Location: ./login.php');
    exit();
}

// Fetch class
$class_sql = "SELECT DISTINCT class FROM inventory";
$class_result = $conn->query($class_sql);

// Fetch weapon_type
$weapon_type_sql = "SELECT DISTINCT weapon_type FROM inventory";
$weapon_type_result = $conn->query($weapon_type_sql);

?>

<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>מערכת ניהול צבאית | ניהול מלאי</title>
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
        <div class="row mb-3">
            <div class="col-md-12">
                <h3>ניהול מלאי</h3>
                <div id="errorMsg" class="alert alert-danger d-none">
                    אין לך הרשאות לבצע פעולה זו.
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <a href="javascript:void(0);" id="createInventoryBtn" class="btn btn-outline-primary btn-sm">הוספת מלאי חדש </a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row my-3">
                    <div class="col-md-4">
                        <select class="form-control" id="classFilter" name="classFilter" required>
                            <option value="">-- בחירת מחלקה --</option>
                            <?php
                                if ($class_result->num_rows > 0) {
                                    while ($class = $class_result->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($class['class']) . "' >" . htmlspecialchars($class['class']) . "</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="weaponTypeFilter" name="weaponTypeFilter" required>
                            <option value="">-- בחירת סוג נשק --</option>
                            <?php
                                if ($weapon_type_result->num_rows > 0) {
                                    while ($weapon_type = $weapon_type_result->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($weapon_type['weapon_type']) . "' >" . htmlspecialchars($weapon_type['weapon_type']) . "</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="table-responsive mt-4">
                    <table id="inventoryTable" class="table" style="width:100%">
                        <thead>
                            <tr>
                                <th>חייל</th>
                                <th>מספר אישי</th>
                                <th>מחלקה</th>
                                <th>נשק</th>
                                <th>מק"ט</th>
                                <th>סוג נשק</th>
                                <th>מצב הפריט</th>
                                <th>תאריך קבלה</th>
                                <th>תאריך בדיקה אחרון</th>
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
                    <h5 class="modal-title" id="deleteModalLabel">אישור מחיקה</h5>
                    <button type="button" class="btn-close mx-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    אתה בטוח שאתה רוצה למחוק את הפריט הזה?
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
        // Determine if user is a "Commander" or "Class Commander"
        var userRole = <?php echo json_encode($_SESSION['role']); ?>;

        // Check if the user is a "Commander" or "Class Commander" and display error on "Add Inventory" button click
        $('#createInventoryBtn').on('click', function() {
            if (userRole === "Commander" || userRole === "Class Commander") {
                $('#errorMsg').removeClass('d-none');
            } else {
                window.location.href = './create_inventory.php';
            }
        });

        var table = $('#inventoryTable').DataTable({
            "processing": true,
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Hebrew.json"
            },
            "ajax": {
                "url": "./controllers/inventory/get_inventory.php",
                "type": "GET",
                "data": function(d) {
                    d.classFilter = $('#classFilter').val();
                    d.weaponTypeFilter = $('#weaponTypeFilter').val();
                }
            },
            "columns": [{
                    "data": "soldier_name"
                },
                {
                    "data": "personal_number"
                },
                {
                    "data": "class"
                },
                {
                    "data": "weapon_name"
                },
                {
                    "data": "sku_number"
                },
                {
                    "data": "weapon_type"
                },
                {
                    "data": "item_condition"
                },
                {
                    "data": "received_date"
                },
                {
                    "data": "last_test_date"
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        let deleteButton = '';
                        let updateButton = `<a href="javascript:void(0);" class="btn btn-sm btn-primary update-inventory" data-id="${row.id}"><span data-notify="icon" class="mdi mdi-pencil"></span></a>`;
                        
                        if (userRole !== "Commander" && userRole !== "Class Commander") {
                            deleteButton = `<a href="javascript:void(0);" class="inventory-delete btn btn-sm btn-danger mx-1" data-id="${row.id}">
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

        // Filter by class
        $('#classFilter').on('change', function() {
            table.ajax.reload();
        });

        // Filter by weapon type
        $('#weaponTypeFilter').on('change', function() {
            table.ajax.reload();
        });

        // Use event delegation to handle dynamic elements
        $('#inventoryTable tbody').on('click', '.inventory-delete', function() {
            var deleteInventoryId = $(this).data('id');
            $('#deleteModal').modal('show');

            $('#confirmDelete').on('click', function() {
                if (deleteInventoryId) {
                    $.ajax({
                        url: `./controllers/inventory/delete_inventory.php/?inventory_id=${deleteInventoryId}`,
                        type: 'DELETE',
                        success: function(result) {
                            $('#deleteModal').modal('hide');
                            table.ajax.reload(null, false); // Reload the table data
                        },
                        error: function(err) {
                            console.error('Error deleting order:', err);
                        }
                    });
                }
            });
        });

        // Handle "Update Inventory" click
        $('#inventoryTable tbody').on('click', '.update-inventory', function() {
            var inventoryId = $(this).data('id');
            if (userRole === "Commander" || userRole === "Class Commander") {
                $('#errorMsg').removeClass('d-none');
            } else {
                window.location.href = `./update_inventory.php?inventory_id=${inventoryId}`;
            }
        });
    });
    </script>
</body>

</html>
