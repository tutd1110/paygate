<div class="modal fade" id="modalBankTransfer" tabindex="-1" aria-labelledby="modalBankTransferLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="border-0 justify-content-center modal-header pt-4">
                <h1 class="fs-1 fw-bold modal-title" id="modalBankTransferLabel">Thông tin chuyển khoản</h1>
            </div>
            <div class="modal-body px-5">
                <div class="row">
                    <div class="align-items-center col-md-4 d-flex thumb">
                        <img id="image_bank" class="image_bank" src="https://hocmai.vn/payment/quickpay/images/bidv.png" alt="BIDV">
                    </div>
                    <div class="col-md-8 info pt-5">
                        <h6 class="bank_owner" id="bank_owner">Công ty Cổ phần Đầu tư và Dịch vụ Giáo dục</h6>
                        <div class="bank_number">
                            Số tài khoản:
                            <span id="bank_number"></span>
                            <i class="fa-regular fa-copy ms-2"></i>
                        </div>
                        <div class="bank_branch" id="bank_branch">Chi nhánh: Chi nhánh Đông Đô, Hà Nội</div>
                        <div class="bank_amount">Số tiền:
                            <font color="red">

                            </font>
                        </div>
                        <div class="bank_tranfer">Nội dung chuyển khoản:
                            <span class="content_transfer">
                                <span id="bank_content"></span> {{ $contact->phone }}
                            </span>
                            <i class="fa-regular fa-copy ms-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-0 justify-content-center modal-footer mb-3">
                <div id="transfer-success" class="alert alert-success fs-4 p-4  mx-4 mb-4 text-center w-100" role="alert" style="display: none">Thanhf coong!</div>
                <div id="transfer-warning" class="alert alert-warning fs-4 p-4  mx-4 mb-4 text-center w-100" role="alert" style="display: none"></div>
                <div id="transfer-error" class="alert alert-danger fs-4 p-4  mx-4 mb-4 text-center w-100" role="alert" style="display: none"></div>
                <div id="transfer-info" class="alert alert-info fs-4 p-4  mx-4 mb-4 text-center w-100" role="alert" style="display: none"></div>
{{--                <button type="button" class="btn btn-primary mx-3" onclick="payStatus()">Xác nhận thanh toán</button>--}}
                <button type="button" class="btn btn-cancel mx-3" data-bs-dismiss="modal">Quay lại</button>
            </div>
        </div>
    </div>
</div>
