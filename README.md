# chay code
## mo 1 terminal -> php artisan serve
## mo 1 terminal -> npm run dev
## mo chrome -> http://localhost:8000
## dang nhap customer@example.com/password
## admin -> http://localhost:8000/admin -> admin@example.com/password

## Sản phẩm thời trang nhập từ Excel

Chạy `php artisan migrate --seed` để tạo danh mục và nhập 17 sản phẩm từ
`database/imports/products-shopee-import.xlsx`. File Excel chứa ảnh sản phẩm,
67 màu và các biến thể size; dữ liệu được cập nhật theo SKU khi seed lại.
Sản phẩm được giữ ở trạng thái nháp để kiểm tra giá và size trước khi hiển thị.

Khi dùng PHP built-in server để tải file Excel này lên từ trang quản trị, chạy
`php -d upload_max_filesize=32M -d post_max_size=40M artisan serve`.
