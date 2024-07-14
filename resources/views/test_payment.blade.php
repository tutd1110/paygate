<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Test payment cho nhân viên học mãi</title>
    <style>
        label {
            width: 100%;
            display: block;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
            integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
            integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
            integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
            crossorigin="anonymous"></script>
</head>
<body style="display: flex; justify-content: center;">
<div class="card" style="width: 480px; margin-top: 10px;">
    <div class="card-body">
        <form action="{{url('/api/v1/public/pgw-orders')}}" method="GET">
            <div class="form">
                <label>Partner Code</label>
                <input type="text" name="partner_code" value="" required>
                <label>landing_page_id</label>
                <input type="text" name="landing_page_id" value="12">
                <label>full_name</label>
                <input type="text" class="form-control" placeholder="full_name" name="full_name" value="" required>
                <br>
                <label>Phone</label>
                <input type="text" class="form-control" name="phone" placeholder="phone" value="" required>
                <br>
                <label>Email</label>
                <input type="email" class="form-control" name="email" placeholder="email" value="">
                <br>
                <label>Voucher code</label>
                <input type="text" class="form-control" name="voucher_code" placeholder="voucher_code" value="">
                <br>
                <label>Product name</label>
                <input class="form-control" type="text" name="item_product_name[]" value="" required>
                <br>
                <input type="hidden" name="amount" value="1">
                <label>product id </label>
                <input class="form-control" type="text" name="item_product_id[]" value="1136">
                <br>
                <label>Product type</label>
                <select class="form-control" name="item_product_type[]">
                    <option value="combo">Combo</option>
                    <option value="package">Package</option>
                </select>
                <br>
                <label>Giá:</label>
                <input class="form-control" type="number" name="item_price[]" value="">
                <br>
                <label>product id </label>
                <input class="form-control" type="text" name="item_product_id[]" value="4834">
                <br>
                <label>Product type</label>
                <select class="form-control" name="item_product_type[]">
                    <option value="package">Package</option>
                    <option value="combo">Combo</option>
                </select>
                <br>
                <label>Giá:</label>
                <input class="form-control" type="number" name="item_price[]" value="">
                <br>
                <input type="hidden" name="item_quantity[]" value="1">
                <input type="hidden" name="item_discount[]" value="0">
                <input type="hidden" name="item_quantity[]" value="1">
                <label>return_url_true</label>
                <input type="text" class="form-control" name="return_url_true" value="https://hocmai.vn">
                <label>return_url_false</label>
                <input type="text" class="form-control" name="return_url_false" value="https://hocmai.vn">
                <label>return_data</label>
                <input type="text" class="form-control" name="return_data" value="https://hocmai.vn">
                <br>
                <button class="btn btn-success btn-block">Thanh toán</button>
            </div>
        </form>
    </div>
</div>

</body>


</html>
