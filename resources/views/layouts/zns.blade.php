<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/fontawesome/css/all.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/payment/index.css?v=1.0.1') }}">

    <title>@yield('title', config('app.name'))
    </title>
    @if(!empty($landingPageTracking['header_bottom']))
            <?php echo $landingPageTracking['header_bottom'] ?>
    @endif
</head>
<body>
@if(!empty($landingPageTracking['body']))
        <?php echo $landingPageTracking['body'] ?>
@endif
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="toastPayment" class="toast align-items-center text-white bg-danger border-0 px-2" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fs-4 p-3"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<header>
    <div class="overlay hidden" id="overlay"></div>

    <div class="header">
        <div class="logo-header"><img src="/assets/payment/img/Frame 828209.png" alt=""></div>
        <div class="logo-header-mobi"><img src="/assets/payment/img/Group 11.png" alt=""></div>
        <div class="title-header">
            <div class="img-header"> <img src="/assets/payment/img/Title.png" alt=""></div>
            <div class="img-header-mobi"> <img src="/assets/payment/img/Title-mobi.png" alt=""></div>
            <h2>Xác thực khách hàng</h2>
        </div>
    </div>
</header>

<main>
    <div class="container pt-5">
        @yield('content')
    </div>
</main>

<footer>
    @if(!empty($landingPageTracking['footer']))
            <?php echo $landingPageTracking['footer'] ?>
    @endif
    <div class="footer row">
        <div class="box-footer">
            <div class="footer-left col">
                <div class="logo-footer">
                    <div class="logo-left"><img src="/assets/payment/img/Logo.png" alt=""></div>
                    <div class="logo-right"><img src="/assets/payment/img/image 8.png" alt=""></div>
                </div>
                <div class="address">
                    <div class="title-address">
                        <h2>Công ty Cổ phần Đầu tư và Dịch vụ Giáo dục</h2>
                    </div>
                    <div class="content-address">
                        <p>Văn phòng Hà Nội: Tầng 4, Tòa nhà 25T2, Đường Nguyễn Thị Thập, Phường Trung Hoà, Quận Cầu
                            Giấy, Hà Nội.</p>
                        <p>Văn phòng TP.HCM: 13M đường số 14 khu đô thị Miếu Nổi, Phường 3, Quận Bình Thạnh, TP. Hồ
                            Chí
                            Minh</p>
                    </div>
                </div>
            </div>
            <div class="footer-right col">
                <div class="support-customer ">
                    <div class="title-support">
                        <h2>HỖ TRỢ KHÁCH HÀNG</h2>
                    </div>
                    <div class="content-support">
                        <p>Trung tâm hỗ trợ</p>
                        <p>Email: hotro@ hocmai.vn</p>
                        <p>Đường dây nóng: 1900 6933</p>
                    </div>
                </div>
                <div class="for-partners ">
                    <div class="title-partners">
                        <h2>DÀNH CHO ĐỐI TÁC</h2>
                    </div>
                    <div class="content-partners">
                        <p>Email: info@hocmai.vn</p>
                        <p>Tel: +84 (24) 3519-0591</p>
                        <p>Fax: +84 (24) 3519-0587</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer-mobi">
            <div class="footer-left col">
                <div class="logo-footer">
                    <div class="logo-left"><img src="/assets/payment/img/Logo.png" alt=""></div>
                    <div class="logo-right"><img src="/assets/payment/img/image 8.png" alt=""></div>
                </div>
                <div class="address">
                    <div class="title-address">
                        <h2>Công ty Cổ phần Đầu tư và Dịch vụ Giáo dục</h2>
                    </div>
                    <div class="content-address">
                        <p>Văn phòng Hà Nội: Tầng 4, Tòa nhà 25T2, Đường Nguyễn Thị Thập, Phường Trung Hoà, Quận Cầu
                            Giấy, Hà Nội.</p>
                        <p>Văn phòng TP.HCM: 13M đường số 14 khu đô thị Miếu Nổi, Phường 3, Quận Bình Thạnh, TP. Hồ
                            Chí
                            Minh</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="last-footer ">
            <div class="__box-last-footer">
                <div class="last-footer-left">
                    <p>MST: 0102183602 do Sở kế hoạch và Đầu tư thành phố Hà Nội <br> cấp ngày 13 tháng 03 năm 2007
                    </p>
                </div>
                <div class="last-footer-right">
                    <p>Giấy phép cung cấp dịch vụ mạng xã hội trực tuyến số 597/GP-BTTTT Bộ Thông tin và Truyền
                        thông
                        cấp ngày 30/12/2016.</p>
                </div>
            </div>
        </div>
        <div class="last-footer-mobi">
            <div class="__box-last-footer-mobi">
                <div class="last-footer-left-mobi">
                    <p>MST: 0102183602 do Sở kế hoạch và Đầu tư thành phố Hà Nội <br> cấp ngày 13 tháng 03 năm 2007
                    </p>
                </div>
                <div class="last-footer-right-mobi">
                    <p>Giấy phép cung cấp dịch vụ mạng xã hội trực tuyến số 597/GP-BTTTT Bộ Thông tin và Truyền
                        thông
                        cấp ngày 30/12/2016.</p>
                </div>
            </div>
        </div>
    </div>
</footer>
<script src="{{ asset('assets/js/jquery-1.11.3.min.js') }}"></script>
<script src="{{ asset('assets/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalest2.js') }}"></script>
<script src="{{ asset('assets/payment/index.js?v=1.0.8') }}"></script>
<script src="{{ asset('assets/zns/index.js') }}"></script>
@if(!empty($landingPageTracking['body_bottom']))
        <?php echo $landingPageTracking['body_bottom'] ?>
@endif
</body>

</html>
