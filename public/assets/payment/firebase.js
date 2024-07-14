//Firebase notification
import {initializeApp} from "../js/firebase-app.js";
import {getDatabase, ref, onValue, set} from "../js/firebase-database.js";
// Check that it worked and returned a function:
const firebaseConfig = {
    apiKey: "AIzaSyAuD6OAeF30oTD4pYNbSilCr6_ybTIh_hA",
    authDomain: "hm-paygate.firebaseapp.com",
    databaseURL: "https://hm-paygate-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "hm-paygate",
    storageBucket: "hm-paygate.appspot.com",
    messagingSenderId: "930593716023",
    appId: "1:930593716023:web:ab4c05e6dfc6b807090cb2",
    measurementId: "G-8F0CYEKHGS"
};

var queryString = window.location.search;
var bill_code = queryString.replace("?bill=", "");
var url_notification = 'payment/ntf-payment-' + bill_code;
// Initialize Firebase
const app = initializeApp(firebaseConfig);
// Initialize Realtime Database and get a reference to the service
const database = getDatabase(app);
const starCountRef = ref(database,url_notification);

onValue(starCountRef, (snapshot) => {
    const data = snapshot.val();
    if (data && checkStatusInterval) {
        if (data.RspCode == '00' && data.message == "Success") {
            notifyTransfer('success', 'Thanh toán thành công! Truy cập vào <a href="'+ data.order_url_return_true + '">đây</a> để kích hoạt');
            clearInterval(checkStatusInterval);
        }else {
            notifyTransfer('error', 'Thanh toán thất bại! Truy cập vào <a href="' + data.order_url_return_false + '">đây</a> để thanh toán lại');
            clearInterval(checkStatusInterval);
        }
    }
});
function notifyTransfer(status, message) {
    $("#modalBankTransfer .alert").hide();
    $('#transfer-' + status).html(message).show();
}
