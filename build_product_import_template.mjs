import fs from "node:fs/promises";
import { Workbook, SpreadsheetFile } from "@oai/artifact-tool";

const outputDir = "C:/Users/buiqu/OneDrive/Tài liệu/shop/Interdisciplinary-Project/outputs/2026-10-25-product-import-template";
await fs.mkdir(outputDir, { recursive: true });

const workbook = Workbook.create();
const products = workbook.worksheets.add("Products");
const categories = workbook.worksheets.add("Categories");
const guide = workbook.worksheets.add("HuongDan");

const headers = [
  "sku", "name", "category_id", "category_name", "description", "main_image_url", "price", "compare_price",
  "variant_stock", "variant_size", "variant_color", "variant_sku", "variant_price",
  "variant_image_url", "is_active", "status",
];

const categoryRows = [
  [1, "Thời trang nữ", "", 1],
  [2, "Thời trang nam", "", 1],
  [3, "Trẻ em", "", 1],
  [4, "Giày dép & túi xách", "", 1],
  [5, "Làm đẹp", "", 1],
];

const productConfigs = [
  { cat: 1, prefix: "VAY-LEN", name: "Váy len basic", desc: "Váy len mặc hằng ngày", price: 289000, colors: ["Đen", "Kem"], sizes: ["S", "M", "L"] },
  { cat: 1, prefix: "CARDIGAN", name: "Áo cardigan dệt kim", desc: "Áo cardigan mềm nhẹ", price: 259000, colors: ["Hồng", "Be"], sizes: ["S", "M", "L"] },
  { cat: 2, prefix: "BOMBER", name: "Áo khoác bomber", desc: "Áo khoác bomber chống gió", price: 399000, colors: ["Đen", "Xanh rêu"], sizes: ["S", "M", "L", "XL"] },
  { cat: 2, prefix: "JEANS", name: "Quần jeans slim fit", desc: "Quần jeans co giãn nhẹ", price: 359000, colors: ["Xanh nhạt", "Đen"], sizes: ["29", "30", "31", "32"] },
  { cat: 3, prefix: "KIDS-HOODIE", name: "Áo hoodie trẻ em", desc: "Áo hoodie nỉ cho trẻ em", price: 199000, colors: ["Vàng", "Xanh"], sizes: ["2T", "4T", "6T"] },
  { cat: 3, prefix: "KIDS-DRESS", name: "Váy bé gái", desc: "Váy cotton mềm cho bé", price: 229000, colors: ["Hồng", "Tím"], sizes: ["2T", "4T", "6T"] },
  { cat: 4, prefix: "SNEAKER", name: "Giày sneaker thể thao", desc: "Giày sneaker đế nhẹ", price: 499000, colors: ["Trắng", "Đen"], sizes: ["39", "40", "41"] },
  { cat: 4, prefix: "TOTE", name: "Túi tote canvas", desc: "Túi tote canvas tiện dụng", price: 189000, colors: ["Trắng", "Đen"], sizes: ["Nhỏ", "Vừa", "Lớn"] },
  { cat: 5, prefix: "SERUM", name: "Serum vitamin C", desc: "Serum chăm sóc da vitamin C", price: 159000, colors: ["10%", "15%"], sizes: ["30ml", "50ml", "100ml"] },
  { cat: 5, prefix: "SUNSCREEN", name: "Kem chống nắng SPF50", desc: "Kem chống nắng bảo vệ da", price: 179000, colors: ["Gel", "Sữa"], sizes: ["30ml", "50ml", "100ml"] },
];

const clean = (value) => String(value).replace(/[Đđ]/g, "D").normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^A-Za-z0-9]/g, "").toUpperCase();
const categoryNames = {1:"Thời trang nữ",2:"Thời trang nam",3:"Trẻ em",4:"Giày dép & túi xách",5:"Làm đẹp"};
const rows = [];
for (const product of productConfigs) {
  for (const color of product.colors) {
    for (const size of product.sizes) {
      const base = `${product.prefix}-${String(product.cat).padStart(2, "0")}`;
      const variantSku = `${base}-${clean(size)}-${clean(color)}`;
      rows.push([base, product.name, product.cat, categoryNames[product.cat], product.desc, "", product.price, product.price + 50000, 10, size, color, variantSku, "", "", 1, "active"]);
    }
  }
}

products.getRangeByIndexes(0, 0, 1, headers.length).values = [headers];
products.getRangeByIndexes(1, 0, rows.length, headers.length).values = rows;
categories.getRange("A1:D1").values = [["category_id", "category_name", "parent_category_id", "is_active"]];
categories.getRangeByIndexes(1, 0, categoryRows.length, 4).values = categoryRows;
guide.getRange("A1:B10").values = [
  ["HƯỚNG DẪN NHẬP SẢN PHẨM", ""],
  ["Cấu trúc", "Mỗi dòng trong Products là một biến thể size/màu hoặc loại."],
  ["sku", "Giống nhau giữa các dòng của cùng một sản phẩm chính."],
  ["variant_sku", "Bắt buộc khác nhau ở từng biến thể."],
  ["variant_stock", "Tồn kho riêng của từng size/màu/loại."],
  ["variant_price", "Để trống nếu dùng giá chung ở cột price."],
  ["main_image_url", "Để trống để tự chèn ảnh sau. Có thể chèn ảnh vào ô tương ứng."],
  ["variant_image_url", "Để trống để tự chèn ảnh theo từng màu/loại."],
  ["category_id", "Đối chiếu với sheet Categories. Nếu ID database khác, sửa lại trước khi import."],
  ["status", "Dùng active, draft, hidden hoặc discontinued."],
];

for (const sheet of [products, categories, guide]) {
  sheet.showGridLines = false;
  const used = sheet.getUsedRange();
  used.format.font = { name: "Arial", size: 10, color: "#1F2937" };
  used.format.verticalAlignment = "center";
}

products.getRange("A1:P1").format = { fill: "#1F2937", font: { name: "Arial", size: 10, bold: true, color: "#FFFFFF" }, horizontalAlignment: "center", verticalAlignment: "center", wrapText: true };
products.getRange("A1:P61").format.borders = { preset: "all", style: "thin", color: "#D9E2EC" };
products.getRange("G2:I61").format.numberFormat = "#,##0";
products.getRange("O2:O61").format.horizontalAlignment = "center";
products.getRange("A1:P61").format.rowHeight = 22;
products.getRange("A1:P1").format.rowHeight = 34;
products.freezePanes.freezeRows(1);
products.getRange("A1:P61").format.autofitColumns();
products.getRange("A:A").format.columnWidth = 18;
products.getRange("B:B").format.columnWidth = 25;
products.getRange("D:D").format.columnWidth = 28;
products.getRange("E:E").format.columnWidth = 28;
products.getRange("F:F").format.columnWidth = 22;
products.getRange("N:N").format.columnWidth = 22;

categories.getRange("A1:D1").format = { fill: "#1F4E78", font: { name: "Arial", size: 10, bold: true, color: "#FFFFFF" }, horizontalAlignment: "center", verticalAlignment: "center" };
categories.getRange("A1:D6").format.borders = { preset: "all", style: "thin", color: "#D9E2EC" };
categories.getRange("A1:D6").format.rowHeight = 22;
categories.getRange("A1:D6").format.autofitColumns();
categories.freezePanes.freezeRows(1);

guide.getRange("A1:B1").format = { fill: "#1F4E78", font: { name: "Arial", size: 12, bold: true, color: "#FFFFFF" } };
guide.getRange("A2:B10").format.borders = { preset: "all", style: "thin", color: "#D9E2EC" };
guide.getRange("A1:B10").format.wrapText = true;
guide.getRange("A:A").format.columnWidth = 24;
guide.getRange("B:B").format.columnWidth = 80;
guide.getRange("A1:B10").format.rowHeight = 30;

products.tables.add("A1:P61", true, "ProductsImportTable");
categories.tables.add("A1:D6", true, "CategoriesReferenceTable");
workbook.recalculate();

const check = await workbook.inspect({ kind: "table", sheetId: "Products", range: "A1:O8", include: "values", tableMaxRows: 8, tableMaxCols: 15 });
console.log(check.ndjson);
const preview = await workbook.render({ sheetName: "Products", range: "A1:O14", scale: 1, format: "png" });
await fs.writeFile(`${outputDir}/products-preview.png`, new Uint8Array(await preview.arrayBuffer()));
const categoryPreview = await workbook.render({ sheetName: "Categories", range: "A1:D6", scale: 1, format: "png" });
await fs.writeFile(`${outputDir}/categories-preview.png`, new Uint8Array(await categoryPreview.arrayBuffer()));
const xlsx = await SpreadsheetFile.exportXlsx(workbook);
await xlsx.save(`${outputDir}/products-import-template.xlsx`);
console.log(`SAVED rows=${rows.length} products=${productConfigs.length}`);
