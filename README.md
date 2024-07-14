
version 0.9
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<h2 style="color: red;">required</h2>

- composer version >= 2
- php version >= 7.4

<h2 style="color: red;">php extension </h2>

- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

<h2 style="color: red;">Hướng dẫn chạy project</h2>
- Chạy composer install khi có sự thay đổi file composer.json
- copy file .env.example thành .env và sửa thông tin cấu hình khớp với server 
- Chạy php artisan migrate để cập nhật những thay đổi trong cấu trúc DB
- File config nginx giống với file <code>root/nginx.config</code>
- Cron job chạy 15 phút một lần tự đậu quét hủy đơn hàng<br>
  (*/15 * * * * php /home/manager_ldp/api/artisan scan_push_cancel_pgw_orders)


<h2 style="color: red;">Cách tạo ra 1 người bên thứ 3 mới </h2>

- Thêm 1 bên thứ 3 vào bảng third_parties 
- Trong thư mục dự án chạy lệnh <code> php artisan third_party:token </code> 
- Nhập key giống trong cột me được tạo tại bảng third_parties để tạo key mới sau đó copy token và gửi cho đối tác

<blockquote>
Chú ý: Với mỗi 1 third_parties chỉ có 1 key tại 1 thời điểm nếu token được tạo mới thì token cũ sẽ hết hạn
</blockquote>

<h2 style="color: red;">Log error </h2>
Xem log error 
<blockquote>
<a href="https://api-ldp.hocmai.vn/logs">https://api-ldp.hocmai.vn/logs</a>
<br>
- Username: admin
<br>
- pass: Xem tại app/Http/Middleware/BasicAuth.php
</blockquote>
