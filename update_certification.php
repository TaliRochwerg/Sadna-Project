<?php
session_start();
require_once('./config/db.php');

if (!isset($_SESSION['user'])) {
    header('Location: ./login.php');
    exit();
}

if ($_SESSION['role'] === "Commander") {  
    header('Location: ./certification.php');  
    exit(); // Ensure to exit after redirection  
} 

$certification_id = $_GET['certification_id'];

// Fetch certification details
$certification_sql = "SELECT * FROM certifications WHERE id = '$certification_id'";
$certification_result = $conn->query($certification_sql);
$certification = $certification_result->fetch_assoc();

// Fetch commanders
$commander_sql = "SELECT id, username FROM users";
$commander_result = $conn->query($commander_sql);

// Fetch soldiers for the main form
$soldier_sql = "SELECT id, soldier_name FROM soldiers";
$soldier_result = $conn->query($soldier_sql);

// Fetch assigned soldiers for the certification
$certification_soldiers_sql = "SELECT soldier_id FROM certification_soldiers WHERE certification_id = '$certification_id'";
$certification_soldiers_result = $conn->query($certification_soldiers_sql);
$assigned_soldiers = array();
while ($row = $certification_soldiers_result->fetch_assoc()) {
    $assigned_soldiers[] = $row;
}

?>

<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>מערכת ניהול צבאית | Update Certification</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/toast/toast.min.css">
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/toast/toast.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
</head>

<body>
    <?php include './header.php'; ?>
    <div class="main-content container">
        <div class="row">
            <div class="col-md-12">
                <h3>עדכן אימון</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form novalidate id="updateCertificationForm" method="POST" class="needs-validation mt-2">
                    <div id="errorMsg" class="form-group col-md-12"></div>
                    <input type="hidden" id="certification_id" name="certification_id"
                        value="<?php echo htmlspecialchars($certification_id); ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="training_name">שם האימון</label>
                                <input type="text" class="form-control" id="training_name" name="training_name"
                                    value="<?php echo htmlspecialchars($certification['training_name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_time">תאריך ושעה</label>
                                <input type="datetime-local" class="form-control" id="date_time" name="date_time"
                                    value="<?php echo date('Y-m-d\TH:i', strtotime($certification['date_time'])); ?>"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location">מיקום</label>
                                <input type="text" class="form-control" id="location" name="location"
                                    value="<?php echo htmlspecialchars($certification['location']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="commander_id">מפקד אחראי</label>
                                <select class="form-control" id="commander_id" name="commander_id" required>
                                    <option disabled>-- בחר מפקד --</option>
                                    <?php
                                    if ($commander_result->num_rows > 0) {
                                        while ($commander = $commander_result->fetch_assoc()) {
                                            $selected = ($commander['id'] == $certification['commander_id']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($commander['id']) . "' $selected>" . htmlspecialchars($commander['username']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Soldier Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="soldiers">רשימת חיילים משתתפים</label>
                                <select id="soldiers" name="soldiers[]" class="form-control" multiple="multiple"
                                    >
                                    <?php
                                    if ($soldier_result->num_rows > 0) {
                                        while ($soldier = $soldier_result->fetch_assoc()) {
                                            $selected = in_array($soldier['id'], array_column($assigned_soldiers, 'soldier_id')) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($soldier['id']) . "' $selected>" . htmlspecialchars($soldier['soldier_name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description">תיאור</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    required><?php echo htmlspecialchars($certification['description']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Update Soldier Button -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary" id="updateSoldierBtn">עדכן ציון של
                                חייל</button>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" id="update_btn" class="btn btn-primary">עדכן אימון</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Updating Soldier Grade -->
    <div class="modal fade" id="updateSoldierModal" tabindex="-1" aria-labelledby="updateSoldierModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateSoldierModalLabel">עדכן ציון חייל</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form novalidate id="updateSoldierForm" class="needs-validation" method="POST">
                        <div id="errorMsg1" class="form-group col-md-12"></div>
                        <div class="form-group">
                            <label for="soldier_select">בחר חייל לעדכן ציון</label>
                            <select class="form-control" id="soldier_select" required>
                                <option value="">-- בחר חייל --</option>
                                <?php
                                // Re-fetch the soldiers for the modal
                                $soldier_result_modal = $conn->query($soldier_sql); // Re-run the query

                                if ($soldier_result_modal->num_rows > 0) {
                                    while ($soldier = $soldier_result_modal->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($soldier['id']) . "'>" . htmlspecialchars($soldier['soldier_name']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Soldier Details and Grade Update -->
                        <div class="form-group">
                            <label for="soldier_grade">ציון</label>
                            <input type="number" class="form-control" id="soldier_grade" name="soldier_grade" value=""
                                required>
                        </div>
                        <div class="form-group">
                            <label for="soldier_grades">ציונים נוכחיים</label>
                            <div id="soldier_grades"></div>
                        </div>
                        <button type="button" class="btn btn-primary" id="saveSoldierGradeBtn">שמור ציון</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/form-validate.js"></script>
    <script>
    // Open modal when clicking the update soldier button
    $("#updateSoldierBtn").on("click", function() {
        // Clear the select and input fields in the modal
        $("#soldier_select").val(''); // Clear the soldier selection
        $("#soldier_grade").val(''); // Clear the grade input

        // Clear any dynamic content inside the modal (if you have more fields)
        $("#soldier_grades").html(''); // Clear previous grade data

        // Show the modal
        $('#updateSoldierModal').modal('show');
    });

    // Fetch soldier details and show grade update form
    $("#soldier_select").on("change", function() {
        var soldier_id = $(this).val();
        if (soldier_id !== "") {
            $.ajax({
                url: './controllers/certifications/get_soldier_details.php',
                type: 'POST',
                data: {
                    certification_id: $("#certification_id").val(),
                    soldier_id: soldier_id
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.status == "success") {
                        $("#soldier_grade").val(data.grades[0].grade);
                        $("#soldier_grades").html('');

                        // Iterate through the grades and display them
                        data.grades.forEach(function(grade) {
                            $("#soldier_grades").append(
                                "<span class='badge bg-primary me-2'>" + grade
                                .grade + "</span>"
                            );
                        });
                    } else {
                        $("#soldier_grades").html("<p>לא נמצאו ציונים עבור החייל הזה</p>");
                    }
                }
            });
        }
    });

    // Save soldier grade
    $("#saveSoldierGradeBtn").on("click", function() {
        var soldier_id = $("#soldier_select").val();
        var soldier_grade = $("#soldier_grade").val();
        if (soldier_grade === "" || !soldier_id) {
            $.toast({
                heading: 'Error',
                text: "נא להזין ציון חוקית",
                showHideTransition: 'slide',
                icon: 'error',
                position: 'top-left',
            });
            return;
        }

        $.ajax({
            url: './controllers/certifications/update_soldier_grade.php',
            type: 'POST',
            data: {
                certification_id: $("#certification_id").val(),
                soldier_id: soldier_id,
                soldier_grade: parseInt(soldier_grade, 10)
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.status == "success") {
                    $.toast({
                        heading: 'Success',
                        text: "ציון החייל עודכנה בהצלחה",
                        showHideTransition: 'slide',
                        icon: 'success',
                        position: 'top-left',
                    });
                    $('#updateSoldierModal').modal('hide');
                } else {
                    $.toast({
                        heading: 'Error',
                        text: data.message || "אירעה שגיאה בעדכון הציון",
                        showHideTransition: 'slide',
                        icon: 'error',
                        position: 'top-left',
                    });
                }
            }
        });
    });

    // Update certification form
    $("#update_btn").on("click", function(e) {
        var form = $("#updateCertificationForm");
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

        $.ajax({
            url: './controllers/certifications/update_certification.php',
            type: 'POST',
            data: {
                certification_id: $("#certification_id").val(),
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
            }
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
                $('#soldiers').val(isChecked ? $('#soldiers').find('option').map(function() {
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