import { initializeApp } from "https://www.gstatic.com/firebasejs/9.1.3/firebase-app.js";
import { getAuth, createUserWithEmailAndPassword, signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/9.1.3/firebase-auth.js";

// Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyCon4xFq9nHdLR6wdo2HhqQgP_j9AWRUtQ",
    authDomain: "army-management-63ffe.firebaseapp.com",
    projectId: "army-management-63ffe",
    storageBucket: "army-management-63ffe.appspot.com",
    messagingSenderId: "339028541638",
    appId: "G-D2VNS0C86Y"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

// Register event
document.getElementById('register-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;

    try {
        const userCredential = await createUserWithEmailAndPassword(auth, email, password);
        const user = userCredential.user;

        // Save user to MySQL DB via PHP
        const response = await fetch('../../controllers/auth/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({email: user.email})
        });

        const data = await response.text();
        document.getElementById('register-message').innerHTML = data;
    } catch (error) {
        document.getElementById('register-message').innerHTML = error.message;
    }
});

// Login event (Assuming you have a login form similarly structured)
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;

    try {
        const userCredential = await signInWithEmailAndPassword(auth, email, password);
        const user = userCredential.user;

        // Verify user in MySQL DB via PHP
        const response = await fetch('../../controllers/auth/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({email: user.email})
        });

        const data = await response.text();
        document.getElementById('login-message').innerHTML = data;
    } catch (error) {
        document.getElementById('login-message').innerHTML = error.message;
    }
});
