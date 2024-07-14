const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
var paymentMerchant = $('#merchant_code').val();
var bankTransferMerchantId = 0;
if (paymentMerchant) {
    if ($('#' + paymentMerchant).attr('id')) {
        var arrayClass = ($('#' + paymentMerchant).attr('class')).split(' ');

        if (arrayClass.includes("merchant-item")) {
            let merchantId = $('#' + paymentMerchant).data('merchant-id');
            let bankRegId = $('#' + paymentMerchant).data('bank-id');
            if (merchantId) pay(merchantId, bankRegId, true);
        }
        if (arrayClass.includes("bank-item")) {
            bankTransferMerchantId = $('.bank-transfer').data('merchant-id');
            let bankRegId = $('#' + paymentMerchant).data('id');
            if (bankTransferMerchantId) pay(bankTransferMerchantId, bankRegId, true);
        }
    }
}

$('.merchant-item').click(function () {
    let merchantId = $(this).data('merchant-id');
    let bankRegId = $(this).data('bank-id');
    if (merchantId) pay(merchantId, bankRegId);
});
$('.bank-transfer').click(function () {
    bankTransferMerchantId = $(this).data('merchant-id');
});
$('.bank-item').click(function () {
    let bankRegId = $(this).data('id');
    if (bankTransferMerchantId) pay(bankTransferMerchantId, bankRegId);
});
var paying = false;
var checkStatusInterval;

function pay(merchantId, bankRegId, auto = false) {
    if (!auto) {
        $('#loadMerchant').modal('show');
    }
    if (paying) {
        return false;
    }
    paying = true;
    let orderId = $('#order-id').val();
    $.ajax({
        type: 'POST',
        url: "/api/v1/pgw/pay",
        data: {
            merchantId: merchantId,
            bankRegId: bankRegId,
            orderId: orderId
        },
        success: function (res) {
            $('#loadMerchant').modal('hide');
            if (res.data.type === 'transfer') {
                $('#image_bank').prop('src', res.data.qrUrl);
                $('#modalBankTransfer #bank_number').html(res.data.payUrl);
                $('#modalBankTransfer .bank_amount font').html(res.data.amount + " VND");
                $('#bank_content').html(res.data.code);
                $('#bank_branch').html(res.data.bank.branch);
                $('#bank_owner').html(res.data.bank.owner);
                $('#vpc_MerchTxnRef').val(res.data.vpcMerchTxnRef);
                notifyTransfer('info', 'Sau khi chuyển khoản vui lòng chờ trong giây lát để được xác minh!');
                $('#modalBankTransfer').modal('show');

                checkStatusInterval = setInterval(function () {
                    if ($('#vpc_MerchTxnRef').val()) payStatus();
                }, 30000);
            } else if (res.data.type === 'qrcode') {

            } else {
                window.location.href = res.data.payUrl;
            }
        },
        error: function (xhr, status, error) {
            let res = xhr.responseJSON;
            notify('error', res.message);
            console.log(res);
        },
        complete: function (xhr) {

        }
    });

    paying = false;
    return false;
}

function payStatus() {
    $('#loadMerchant').modal('hide');
    let code = $('#vpc_MerchTxnRef').val();
    if (!code) {
        notifyTransfer('error', 'Giao dịch không xác định');
        return false;
    }
    $.ajax({
        type: 'POST',
        url: "/api/v1/pgw/checkbill",
        data: {
            code: code,
        },
        success: function (res) {
            if (res.data.status === 'paid') {
                notifyTransfer('success', 'Thanh toán thành công! Truy cập vào <a href="' + res.data.return_url_true + '">đây</a> để kích hoạt');
                clearInterval(checkStatusInterval);
            } else if (res.data.status === 'fail') {
                notifyTransfer('success', 'Thanh toán thất bại! Truy cập vào <a href="' + res.data.return_url_false + '">đây</a> để thanh toán lại');
                clearInterval(checkStatusInterval);
            } else {
                notifyTransfer('warning', 'Giao dịch đang được xác minh. Xin vui lòng chờ trong giây lát');
            }
        },
        error: function (xhr, status, error) {
            let res = xhr.responseJSON;
            notifyTransfer('error', res.message);
        },
        complete: function (xhr) {

        }
    });
}

const toastElm = document.getElementById('toastPayment');
const toast = new bootstrap.Toast(toastElm);

function notify(status, message) {
    $('#toastPayment .toast-body').html(message);
    toast.show();
}

function notifyTransfer(status, message) {
    $("#modalBankTransfer .alert").hide();
    $('#transfer-' + status).html(message).show();
}

const modalBankTransfer = document.getElementById('modalBankTransfer')
modalBankTransfer.addEventListener('hidden.bs.modal', event => {
    clearInterval(checkStatusInterval);
});
$('.fa-copy').hover(function () {
    $(this).attr('data-bs-original-title', 'Sao chép').tooltip('show');
});

$('.fa-copy').click(function () {
    let content = $.trim($(this).prev().text());
    copyToClipboard(content);

    $(this).attr('data-bs-original-title', 'Đã sao chép').tooltip('show');
});

function copyToClipboard(text) {
    if (navigator.clipboard !== undefined) {
        navigator.clipboard.writeText(text);
    }
}

$('#link-cancel-order').on('click', function () {
    swal.fire({
        title: 'Huỷ đơn hàng',
        text: "Bạn có chắc chắn muốn huỷ đơn hàng?",
        type: 'warning',
        showCancelButton: true,
        customClass: 'swal-wide',
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy',
        reverseButtons: true
    }).then(function (result) {
        if (result.value) {
            cancelOrder();
        }
    });
})

function cancelOrder() {
    var bill = $('#link-cancel-order').attr('data-value');
    $.ajax({
        type: 'POST',
        url: "/api/v1/pgw/changeStatusCancelOrder",
        data: {
            bill: bill
        },
        success: function (res) {
            if (res.data.type == 'error') {
                if (res.data.error_order) {
                    $('#toastPayment .toast-body').html(res.data.message);
                    toast.show();
                }else {
                    $('#toastPayment .toast-body').html('Lỗi hệ thống, xin vui lòng thử lại!');
                    toast.show();
                }
            }
            if (res.data.type == 'success') {
                window.location.href = res.data.url;
            }
        }
    });
}
