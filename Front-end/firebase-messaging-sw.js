importScripts('https:// www.gstatic.com/firebasejs/10.10.0/firebase-app-compat.js');
importScripts('https:// www.gstatic.com/firebasejs/10.10.0/firebase-messaging-compat.js');

const firebaseConfig = {
  apiKey: "AIzaSyA19cqbLvyWdFtbd3uM7a0aYgr4BJzt4x8",
  authDomain: "poly-gear.firebaseapp.com",
  projectId: "poly-gear",
  storageBucket: "poly-gear.firebasestorage.app",
  messagingSenderId: "169981413560",
  appId: "1:169981413560:web:687702b5fbb48b52dbcc4d",
  measurementId: "G-SC2LM28K3E"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// bắt push khi browser tắt hoặc đóng tab
messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  const notificationTitle = payload.notification?.title || 'Thông báo mới';
  const notificationOptions = {
    body: payload.notification?.body,
    icon: '/img/logo.png'
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});
