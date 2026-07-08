// assets/js/firebase-init.js

// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-analytics.js";

// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyBwIqGjfaGpdccxg2AyhpmLXDG6A9oC_yI",
  authDomain: "nikahnama-181b3.firebaseapp.com",
  projectId: "nikahnama-181b3",
  storageBucket: "nikahnama-181b3.firebasestorage.app",
  messagingSenderId: "144089904412",
  appId: "1:144089904412:web:57c4505b5d10e53ca3f6d9",
  measurementId: "G-LPJH1KBZQQ"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);

// Export for other scripts if needed
export { app, analytics };
