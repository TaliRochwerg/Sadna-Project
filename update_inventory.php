<?php
session_start();
require_once('./config/db.php');

if (!isset($_SESSION['user'])) {
    header('Location: ./login.php');
    exit();
}

// Check if the user role is set and if the user is a Commander or Class Commander
if (isset($_SESSION['role']) && ($_SESSION['role'] === "Commander" || $_SESSION['role'] === "Class Commander")) {  
    header('Location: ./certification.php');  
    exit();
}

// Fetch soldiers
$soldier_sql = "SELECT id, soldier_name, personal_number FROM soldiers";
$soldier_result = $conn->query($soldier_sql);

// Fetch weapons
$weapon_sql = "SELECT id, name FROM weapons";
$weapon_result = $conn->query($weapon_sql);

// Fetch inventory item if 'id' is given in GET params
$inventory_item = null;
if (isset($_GET['inventory_id'])) {
    $inventory_id = $_GET['inventory_id'];
    $inventory_sql = "SELECT * FROM inventory WHERE id = '$inventory_id'";
    $inventory_result = $conn->query($inventory_sql);
    
    if ($inventory_result->num_rows > 0) {
        $inventory_item = $inventory_result->fetch_assoc();
    } else {
        echo "<p>Item not found.</p>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>מערכת ניהול צבא | עדכון מלאי</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/assets/css/styles.css">
</head>

<body>
    <?php include './header.php'; ?>
    <div class="main-content container">
        <div class="row">
            <div class="col-md-12">
                <h3>עדכון מלאי</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form novalidate id="updateInventoryForm" method="POST" class="needs-validation mt-2">
                    <input type="hidden" id="inventory_id" name="inventory_id"
                        value="<?php echo isset($inventory_item) ? htmlspecialchars($inventory_item['id']) : ''; ?>">
                    <div id="errorMsg" class="form-group col-md-12"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="soldier_id">חייל</label>
                                <select class="form-control" id="soldier_id" name="soldier_id" required>
                                    <option selected disabled value="">-- בחר חייל --</option>
                                    <?php
                                    if ($soldier_result->num_rows > 0) {
                                        while ($soldier = $soldier_result->fetch_assoc()) {
                                            $selected = (isset($inventory_item) && $inventory_item['soldier_id'] == $soldier['id']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($soldier['id']) . "' data-personal-number='" . htmlspecialchars($soldier['personal_number']) . "' $selected>" . htmlspecialchars($soldier['soldier_name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">
                                    שדה חייל חובה.
                                </div>
                            </div>
                        </div>
                        <!-- Personal Number (Read Only) -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="personal_number">מספר אישי</label>
                                <input type="text" class="form-control" id="personal_number" name="personal_number"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="class">כיתה</label>
                                <input type="text" class="form-control" id="class" name="class"
                                    value="<?php echo isset($inventory_item) ? htmlspecialchars($inventory_item['class']) : ''; ?>"
                                    required>
                                <div class="invalid-feedback">
                                    שדה כיתה חובה.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="weapon_id">נשק</label>
                                <select class="form-control" id="weapon_id" name="weapon_id" required>
                                    <option selected disabled value="">-- בחר נשק --</option>
                                    <?php
                                    if ($weapon_result->num_rows > 0) {
                                        while ($weapon = $weapon_result->fetch_assoc()) {
                                            $selected = (isset($inventory_item) && $inventory_item['weapon_id'] == $weapon['id']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($weapon['id']) . "' $selected>" . htmlspecialchars($weapon['name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">
                                    שדה נשק חובה.
                                </div>
                            </div>
                        </div>

                        <!-- SKU Number -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sku_number">מספר מק"ט</label>
                                <input type="text" class="form-control" id="sku_number" name="sku_number" 
                                    value="<?php echo isset($inventory_item) ? htmlspecialchars($inventory_item['sku_number']) : ''; ?>" required>
                                <div class="invalid-feedback">
                                    שדה מק"ט חובה.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="weapon_type">סוג הנשק</label>
                                <select class="form-control" id="weapon_type" name="weapon_type" required>
                                    <option selected disabled value="">-- בחר סוג נשק --</option>
                                    <option value="M4"
                                        <?php echo (isset($inventory_item) && $inventory_item['weapon_type'] == 'M4') ? 'selected' : ''; ?>>
                                        M4</option>
                                    <option value="נגב"
                                        <?php echo (isset($inventory_item) && $inventory_item['weapon_type'] == 'נגב') ? 'selected' : ''; ?>>
                                        נגב</option>
                                    <option value="מטול"
                                        <?php echo (isset($inventory_item) && $inventory_item['weapon_type'] == 'מטול') ? 'selected' : ''; ?>>
                                        מטול</option>
                                    <option value="כוונת"
                                        <?php echo (isset($inventory_item) && $inventory_item['weapon_type'] == 'כוונת') ? 'selected' : ''; ?>>
                                        כוונת</option>
                                </select>
                                <div class="invalid-feedback">
                                    שדה סוג נשק חובה.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="received_date">תאריך קבלה</label>
                                <input type="date" class="form-control" id="received_date" name="received_date"
                                    value="<?php echo isset($inventory_item) ? htmlspecialchars($inventory_item['received_date']) : ''; ?>"
                                    required>
                                <div class="invalid-feedback">
                                    תאריך קבלה חובה.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_test_date">תאריך בדיקה אחרונה</label>
                                <input type="date" class="form-control" id="last_test_date" name="last_test_date"
                                    value="<?php echo isset($inventory_item) ? htmlspecialchars($inventory_item['last_test_date']) : ''; ?>"
                                    required>
                                <div class="invalid-feedback">
                                    תאריך בדיקה אחרונה חובה.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item_condition">מצב הפריט</label>
                                <input class="form-control" id="item_condition" name="item_condition"
                                    value="<?php echo isset($inventory_item) ? htmlspecialchars($inventory_item['item_condition']) : ''; ?>"
                                    required>
                                <div class="invalid-feedback">
                                    מצב הפריט חובה.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" id="submit_btn" class="btn btn-primary">עדכן מלאי</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/assets/js/form-validate.js"></script>
    <script>
    // Handle soldier selection and auto-populate personal number
    $("#soldier_id").on("change", function() {
        var personalNumber = $(this).find('option:selected').data('personal-number');
        $("#personal_number").val(personalNumber);
    });

    // Handle form submission
    $("#submit_btn").on("click", function(e) {
        var form = $("#updateInventoryForm");
        if (form[0].checkValidity() === false) {
            e.preventDefault();
            e.stopPropagation();
            form[0].classList.add("was-validated");
            return;
        }

        $.ajax({
            url: './controllers/inventory/update_inventory.php',
            type: 'POST',
            data: {
                inventory_id: $("#inventory_id").val(),
                soldier_id: $("#soldier_id").val(),
                class: $("#class").val(),
                weapon_id: $("#weapon_id").val(),
                sku_number: $("#sku_number").val(),
                weapon_type: $("#weapon_type").val(),
                item_condition: $("#item_condition").val(),
                received_date: $("#received_date").val(),
                last_test_date: $("#last_test_date").val(),
            },
            success: function(result) {
                console.log(result);
                if (result.status == "success") {
                    window.location.href = './inventory_list.php';
                } else {
                    $("#errorMsg").html(
                        `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>${result.message}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                         </div>`
                    );
                }
            },
        });
    });

    $(document).ready(function() {
        // Trigger change event to populate the personal number field if a soldier is already selected
        if ($("#soldier_id").val()) {
            $("#soldier_id").trigger("change");
        }

        // Trigger change event to populate the SKU number field if a weapon is already selected
        if ($("#weapon_id").val()) {
            $("#weapon_id").trigger("change");
        }
    });
    </script>
</body>

</html>