<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ./login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>מערכת ניהול צבאית | ניהול נוכחות</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/toast/toast.min.css">
    <script src="/assets/toast/toast.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">

    <link href="/assets/fullcalendar/main.css" rel="stylesheet" type="text/css" />
    <link href="/assets/moment/min/moment.min.js" rel="stylesheet" type="text/css" />
</head>

<body>
    <?php include './header.php'; ?>
    <div class="main-content container">
        <div class="row mb-3">
            <div class="col-md-12">
                <h3>ניהול נוכחות</h3>
            </div>
        </div>
        <div class="row mb-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/assets/fullcalendar/main.js"></script>
    <script>
    function getLocalDateString(date) {
        var year = date.getFullYear();
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var day = date.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
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

        dateClick: function(info) {
            window.location.href = `./attendance_marking.php?date=${info.dateStr}`;
        }
    });

    calendar.render();
    </script>
</body>

</html>