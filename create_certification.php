<?php
session_start();
require_once('./config/db.php');

if (!isset($_SESSION['user'])) {
    header('Location: ./login.php');
    exit();
}

// Check if the logged-in user has the role of "Commander"
if ($_SESSION['role'] === "Commander") {  
    header('Location: ./certification.php');  
    exit(); // Ensure to exit after redirection  
} 

// Fetch commanders
$commander_sql = "SELECT id, username FROM users";
$commander_result = $conn->query($commander_sql);

// Fetch soldiers
$soldier_sql = "
    SELECT id, soldier_name 
    FROM soldiers 
";
$soldier_result = $conn->query($soldier_sql);

?>

<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>מערכת ניהול צבאית | Create Certification</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
</head>

<body>
    <?php include './header.php'; ?>
    <div class="main-content container">
        <div class="row">
            <div class="col-md-12">
                <h3>יצירת אימון חדש</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form novalidate id="createCertificationForm" method="POST" class="needs-validation mt-2">
                    <div id="errorMsg" class="form-group col-md-12"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="training_name">שם האימון</label>
                                <input type="text" class="form-control" id="training_name" name="training_name"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_time">תאריך ושעה</label>
                                <input type="datetime-local" class="form-control" id="date_time" name="date_time"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location">מיקום</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="commander_id">מפקד אחראי</label>
                                <select class="form-control" id="commander_id" name="commander_id" required>
                                    <option selected disabled value="">-- בחר מפקד --</option>
                                    <?php
                                    if ($commander_result->num_rows > 0) {
                                        while ($commander = $commander_result->fetch_assoc()) {
                                            echo "<option value='" . htmlspecialchars($commander['id']) . "' >" . htmlspecialchars($commander['username']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="soldiers">רשימת חיילים משתתפים</label>
                                <select multiple class="form-control" id="soldiers" name="soldiers[]">
                                    <?php
                                    if ($soldier_result->num_rows > 0) {
                                        while ($soldier = $soldier_result->fetch_assoc()) {
                                            echo "<option value='" . htmlspecialchars($soldier['id']) . "'>" . htmlspecialchars($soldier['soldier_name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description">תיאור</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" id="create_btn" class="btn btn-primary">צור אימון</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script src="/assets/js/form-validate.js"></script>
    <script>
    $("#create_btn").on("click", function(e) {
        var form = $("#createCertificationForm");
        if (form[0].checkValidity() === false) {
            e.preventDefault();
            e.stopPropagation();
            form[0].classList.add("was-validated");
            return;
        }
        var isValid = true;

        // Validate Select2 field
        if ($('#soldiers').val() === null || $('#soldiers').val().length === 0) {
            $('#soldiers').siblings('.select2-container').find('.select2-selection').addClass('is-invalid');
            isValid = false;
        } else {
            $('#soldiers').siblings('.select2-container').find('.select2-selection').removeClass('is-invalid');
        }

        // Prevent form submission if invalid
        if (!isValid) {
            $("#errorMsg").html(
                `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>נא למלא את כל השדות הנדרשים.</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`
            );
            return;
        }

        // Send the form data via AJAX to create the new training
        $.ajax({
            url: './controllers/certifications/create_certification.php',
            type: 'POST',
            data: {
                training_name: $("#training_name").val(),
                date_time: $("#date_time").val(),
                location: $("#location").val(),
                commander_id: $("#commander_id").val(),
                soldiers: $("#soldiers").val(),
                description: $("#description").val(),
            },
            success: function(result) {
                const data = JSON.parse(result);
                if (data.status == "success") {
                    window.location.href = './certification_list.php';
                } else {
                    $("#errorMsg").html(
                        `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>${data.message}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>`
                    );
                }
            },
        });
    });

    $(document).ready(function() {
        // Initialize Select2 with checkboxes
        $('#soldiers').select2({
            placeholder: '-- בחר חיילים --',
            width: '100%',
            dropdownCssClass: 'custom-dropdown'
        });

        // Handle "Select All" functionality
        $('#soldiers').on('select2:open', function() {
            var $dropdown = $(this).data('select2').$dropdown;
            console.log($("#selectAll").is(':checked'), '-dddd', $dropdown.find('#selectAll').length)
            // Check if "Select All" checkbox already exists
            if ($dropdown.find('#selectAll').length === 0) {
                var $selectAll = $(
                    '<div class="select-all"><input type="checkbox" id="selectAll" /> <label for="selectAll">בחר הכל</label></div>'
                );

                // Append "Select All" checkbox to the dropdown
                $dropdown.find('.select2-results').prepend($selectAll);

                // Handle the "Select All" checkbox
                $selectAll.find('#selectAll').on('change', function() {
                    var isChecked = $(this).is(':checked');
                    $dropdown.find('.select2-results__option').each(function() {
                        $(this).find('.checkbox').prop('checked', isChecked);
                        if (isChecked) {
                            $(this).addClass('select2-results__option--selected');
                        } else {
                            $(this).removeClass('select2-results__option--selected');
                        }
                    });
                    console.log($('#soldiers').find('option').map(function() {
                        return $(this).val();
                    }).get())
                    $('#soldiers').val(isChecked ? $('#soldiers').find('option').map(
                        function() {
                            return $(this).val();
                        }).get() : []).trigger('change');
                });
            }

            // Update "Select All" checkbox based on current selection
            updateSelectAllCheckbox();
        });

        // Update "Select All" checkbox state based on individual selection
        $('#soldiers').on('change', function() {
            updateSelectAllCheckbox();
        });

        function updateSelectAllCheckbox() {
            var $selectAll = $('#soldiers').data('select2').$dropdown.find('#selectAll');
            var $options = $('#soldiers').find('option');
            var $selectedOptions = $('#soldiers').find('option:selected');

            if ($options.length === $selectedOptions.length && $selectedOptions.length > 0) {
                $selectAll.prop('checked', true);
            } else {
                $selectAll.prop('checked', false);
            }
        }
    });
    </script>
</body>

</html>