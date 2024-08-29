<?php
session_start();
require_once('./config/db.php');

if (!isset($_SESSION['user'])) {
    header('Location: ./login.php');
    exit();
}

// Check user role and pass it to the front-end
$user_role = $_SESSION['role'];

if ($_SESSION['role'] === "Commander") {  
    // No redirection; they will view the page, but without processing functionality
}

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch all soldiers with left join to include certification and attendance info for the selected date
$soldiers_sql = "
    SELECT s.id AS soldier_id, s.soldier_name, 
           GROUP_CONCAT(DISTINCT c.training_name) AS training_name,
           MAX(a.attended) AS attended, 
           MAX(a.note) AS note
    FROM soldiers s
    LEFT JOIN certification_soldiers cs ON s.id = cs.soldier_id
    LEFT JOIN certifications c ON cs.certification_id = c.id
    LEFT JOIN attendance a ON s.id = a.soldier_id AND a.attendance_date = '" . $conn->real_escape_string($date) . "'
    GROUP BY s.id
";

$soldiers_result = $conn->query($soldiers_sql);
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compass | סימון נוכחות</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/DataTables/datatables.min.css">
    <link rel="stylesheet" href="/assets/DataTables/DataTables-1.10.18/css/dataTables.bootstrap4.min.css">
    <script src="/assets/DataTables/datatables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="/assets/toast/toast.min.css">
    <script src="/assets/toast/toast.min.js"></script>
</head>

<body>
    <?php include './header.php'; ?>
    <div class="main-content container">
        <div class="row mb-3">
            <div class="col-md-12">
            <h3>נוכחות עבור תאריך <?php echo date('d/m/Y', strtotime($date)); ?></h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form method="POST" id="attendanceForm" <?php if ($user_role === 'Commander') echo 'disabled'; ?>>
                <input type="hidden" name="date" value="<?php echo $date; ?>">
                    <div class="table-responsive mt-4">
                        <table id="attendanceMarkingTable" class="table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>מזהה חייל</th>
                                    <th>שם חייל</th>
                                    <th>הגיע/לא הגיע</th>
                                    <th>הערה</th>
                                </tr>
                            </thead>
                            <tbody id="soldiersList">
                                <?php
                                if ($soldiers_result->num_rows > 0) {
                                    while ($soldier = $soldiers_result->fetch_assoc()) {
                                        $checked = $soldier['attended'] == 1 ? 'checked' : '';
                                        $note = htmlspecialchars($soldier['note'] ?? '');
                                        echo "<tr>";
                                        echo "<td class='align-middle'>" . htmlspecialchars($soldier['soldier_id']) . "</td>";
                                        echo "<td class='align-middle'>" . htmlspecialchars($soldier['soldier_name']) . "</td>";
                                        echo "<td class='align-middle'>
                                                <input type='hidden' name='arrived[" . htmlspecialchars($soldier['soldier_id']) . "]' value='0'>
                                                <input type='checkbox' name='arrived[" . htmlspecialchars($soldier['soldier_id']) . "]' value='1' $checked>
                                              </td>";
                                        echo "<td class='align-middle'><input type='text' name='note[" . htmlspecialchars($soldier['soldier_id']) . "]' class='form-control' value='$note'></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>אין חיילים להצגה.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <!-- Disable the submit button for Commander role -->
                            <button type="button" id="mark_attendance" class="btn btn-primary" <?php if ($user_role === 'Commander') echo 'disabled'; ?>>שלח נוכחות</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    $(document).ready(function() {
        // Disable form submission for Commander role
        var role = "<?php echo $user_role; ?>";
        if (role === 'Commander') {
            $('#attendanceForm input, #attendanceForm textarea, #attendanceForm checkbox').prop('disabled', true);
        }

        // Handle form submission
        $("#mark_attendance").on('click', function(e) {
            e.preventDefault();
            var formData = $('#attendanceForm').serialize();
            $.ajax({
                url: './controllers/attendance/mark_attendance.php',
                type: 'POST',
                data: formData,
                success: function(result) {
                    const data = JSON.parse(result);
                    if (data.status == "success") {
                        window.location.href = './attendance.php';
                    } else {
                        $.toast({
                            heading: 'Error',
                            text: data.message,
                            showHideTransition: 'slide',
                            icon: 'error',
                            position: 'top-left',
                        });
                    }
                },
            });
        });
    });
    </script>
</body>

</html>
