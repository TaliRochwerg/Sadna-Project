<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: ./certification.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>מערכת ניהול צבאית | הרשמה</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.6.7/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-auth.js"></script>
    <link rel="stylesheet" type="text/css" href="/assets/css/styles.css">
</head>

<body>
    <?php include './header.php'; ?>
    <div class="page-content background-page">
        <div class="container">
            <div class="auth-wrapper auth-v1 px-2">
                <div class="auth-inner py-2">
                    <div class="brand-logo">
                        <img src="/assets/img/logo.png" class="army-logo" alt="logo">
                    </div>
                    <div class="mb-0 card">
                        <div class="card-body">
                            <h4 class="mb-1 card-title">הרשמה</h4>
                            <form novalidate id="registerForm" class="needs-validation auth-login-form mt-2">
                                <div id="errorMsg" class="form-group col-md-12"></div>
                                <div class="form-group">
                                    <label for="username" class="form-label">Username</label>
                                    <input id="username" name="username" placeholder="Username" type="text" class="form-control"
                                        required />
                                </div>
                                <div class="form-group">
                                    <label for="email" class="form-label">אימייל</label>
                                    <input id="email" name="email" placeholder="אימייל" type="email" class="form-control"
                                        required />
                                </div>
                                <div class="form-group">
                                    <label for="role" class="form-label">Role</label>
                                    <select id="role" name="role" class="form-control" required>
                                        <option value="">Please select a Role</option>
                                        <option value="Commander">Commander</option>
                                        <option value="Class Commander">Class Commander</option>
                                        <option value="Sergeant">Sergeant</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="d-flex justify-content-between">
                                        <label for="password" class="form-label">סיסמא</label>
                                    </div>
                                    <div class="input-group-merge input-group">
                                        <input placeholder="············" id="password" name="password" type="password"
                                            class="form-control" required />
                                    </div>
                                </div>
                                <button type="button" id="register_btn"
                                    class="waves-effect btn btn-outline-primary w-100 btn-block my-3">הרשמה</button>
                            </form>
                            <p class="text-center mt-2">
                                <span class="mr-25">כבר יש לך משתמש?</span>
                                <a href="./login.php">
                                    <span>לחץ להתחברות</span>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/assets/js/form-validate.js"></script>
    <script>
    const firebaseConfig = {
        apiKey: "AIzaSyAt-v0ZgXDFT9XFwv3iH9sV5NLumNLtgjI",
        authDomain: "army-management-b8d8a.firebaseapp.com",
        projectId: "army-management-b8d8a",
        storageBucket: "army-management-b8d8a.appspot.com",
        messagingSenderId: "1010176047891",
        appId: "1:1010176047891:web:60cfc3c1c2e3cb00fbf8e6",
        measurementId: "G-FLP1QFN5TZ"
    };

    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);

    // Reference to auth service
    const auth = firebase.auth();

    $("#register_btn").on("click", function(e) {
        var form = $("#registerForm");
        if (form[0].checkValidity() === false) {
            e.preventDefault();
            e.stopPropagation();
            form[0].classList.add("was-validated");
            return;
        }

        const username = $("#username").val();
        const email = $("#email").val();
        const role = $("#role").val();
        const password = $("#password").val();

        auth.createUserWithEmailAndPassword(email, password)
            .then(userCredential => {
                const user = userCredential.user;
                $.ajax({
                    url: './controllers/auth/register.php',
                    type: 'POST',
                    data: {
                        username: username,
                        email: email,
                        role: role,
                        password: password,
                    },
                    success: function(result) {
                        const data = JSON.parse(result);
                        if (data.status == "success") {
                            window.location.href = './login.php';
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
            })
            .catch(error => {
                let errorMessage;
                console.log(error.code)
                switch (error.code) {
                    case 'auth/email-already-in-use':
                        errorMessage = 'כתובת האימייל כבר נמצאת בשימוש. אנא השתמש בכתובת אימייל אחרת או התחל התחברות.';
                        break;
                    case 'auth/invalid-email':
                        errorMessage = 'כתובת האימייל אינה חוקית. אנא בדוק את הכתובת ונסה שנית.';
                        break;
                    case 'auth/operation-not-allowed':
                        errorMessage = 'הרשמות חדשות אינן מותרות כרגע. אנא נסה שוב מאוחר יותר.';
                        break;
                    case 'auth/weak-password':
                        errorMessage = 'הסיסמא חלשה מדי. אנא הזן סיסמא חזקה יותר.';
                        break;
                    default:
                        errorMessage = error.message;
                }
                $("#errorMsg").html(
                    `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>${errorMessage}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`
                );
            });

    })
    </script>
</body>

</html>
