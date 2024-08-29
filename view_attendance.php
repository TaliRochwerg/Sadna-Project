<?php
session_start();
require_once('./config/db.php');

if (!isset($_SESSION['user'])) {
    header('Location: ./login.php');
    exit();
}

$userID = $_SESSION['user_id'];

// Fetch certifications
$certification_sql = "SELECT id, training_name FROM certifications";
$certification_result = $conn->query($certification_sql);
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>מערכת ניהול צבאית | צפייה בנוכחות</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="/assets/js/bootstrap.bundle.min.js"></script>

    <script src="/assets/js/jquery.min.js"></script>
    <link rel="stylesheet" href="/assets/toast/toast.min.css">
    <script src="/assets/toast/toast.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/DataTables/datatables.min.css">
    <link rel="stylesheet" href="/assets/DataTables/DataTables-1.10.18/css/dataTables.bootstrap4.min.css">
    <script src="/assets/DataTables/datatables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
</head>

<body>
    <?php include './header.php'; ?>
    <div class="main-content container">
        <div class="row mb-3">
            <div class="col-md-12">
                <h3>צפייה בנוכחות</h3>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <select class="form-control" id="certification_id" name="certification_id" required>
                    <option value="">-- בחר הדרכה --</option>
                    <?php
                    if ($certification_result->num_rows > 0) {
                        while ($certification = $certification_result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($certification['id']) . "' >" . htmlspecialchars($certification['training_name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>

        <div class="card mt-4" id="attendanceCard" style="display: none;">
            <div class="card-body">
                <h5 class="card-title">נוכחות לתאריך <span id="selectedDate"></span></h5>
                <div class="table-responsive">
                    <table id="attendanceTable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>מזהה חייל</th>
                                <th>שם חייל</th>
                                <th>הגיע</th>
                                <th>הערה</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceList">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            contentHeight: '55vh',
            expandRows: true,
            direction: 'rtl',
            locale: 'he', // Sets the locale to Hebrew for RTL support
            headerToolbar: {
                left: 'prevYearHeb,prevHeb,nextHeb,nextYearHeb todayHeb',
                center: '',
                right: 'title'
            },
            customButtons: {
                prevYearHeb: {
                    text: 'שנה קודמת',
                    click: function() {
                        calendar.prevYear();
                    }
                },
                prevHeb: {
                    text: 'הקודם',
                    click: function() {
                        calendar.prev();
                    }
                },
                nextHeb: {
                    text: 'הבא',
                    click: function() {
                        calendar.next();
                    }
                },
                nextYearHeb: {
                    text: 'שנה הבאה',
                    click: function() {
                        calendar.nextYear();
                    }
                },
                todayHeb: {
                    text: 'היום',
                    click: function() {
                        calendar.today();
                    }
                },
            },
            initialView: 'dayGridMonth',
            navLinks: true,
            editable: true,
            selectable: true,
            nowIndicator: true,
            dayMaxEventRows: true,
            droppable: true,
            views: {
                dayGridMonth: {
                    dayMaxEventRows: 4
                }
            },
            events: './controllers/attendance/get_all_attendance.php',
            dateClick: function(info) {
                var certificationId = $('#certification_id').val();
                if (certificationId) {
                    fetchAttendance(certificationId, info.dateStr);
                } else {
                    $.toast({
                        heading: 'אזהרה',
                        text: 'אנא בחר הדרכה.',
                        showHideTransition: 'slide',
                        icon: 'warning',
                        position: 'top-right',
                    });
                }
            }
        });
        calendar.render();
    });

    function fetchAttendance(certificationId, date) {
        $.ajax({
            url: './controllers/attendance/get_attendance.php',
            type: 'GET',
            data: {
                certification_id: certificationId,
                date: date
            },
            success: function(result) {
                var data = JSON.parse(result);
                var attendanceList = $('#attendanceList');
                attendanceList.empty(); // Clear the table body

                if (data.status == "success" && data.attendance.length > 0) {
                    data.attendance.forEach(function(record) {
                        attendanceList.append(`
                            <tr>
                                <td class="align-middle">${record.soldier_id}</td>
                                <td class="align-middle">${record.soldier_name}</td>
                                <td class="align-middle">${record.attended == 1 ? 'כן' : 'לא'}</td>
                                <td class="align-middle">${record.note}</td>
                            </tr>
                        `);
                    });
                    $('#selectedDate').text(date);
                    $('#attendanceCard').show();
                } else {
                    $('#attendanceCard').hide();
                    $.toast({
                        heading: 'Warning',
                        text: 'אין נתוני נוכחות להצגה לתאריך זה ולהדרכה זו.',
                        showHideTransition: 'slide',
                        icon: 'warning',
                        position: 'top-right',
                    });
                }
            }
        });
    }
    </script>
</body>

</html>