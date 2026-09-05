<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        Storage::disk('public')->put('seed/px.png', $png);

        $sampleImages = [
            'cat_women.png' => 'images/shop/categories/cat_women.png',
            'cat_men.png' => 'images/shop/categories/cat_men.png',
            'dress.png' => 'images/shop/products/dress.png',
            'skirt.png' => 'images/shop/products/skirt.png',
            'blouse.png' => 'images/shop/products/blouse.png',
            'tshirt.png' => 'images/shop/products/tshirt.png',
            'jeans.png' => 'images/shop/products/jeans.png',
            'polo.png' => 'images/shop/products/polo.png',
            'jacket.png' => 'images/shop/products/jacket.png',
            'hero.png' => 'images/shop/categories/hero.png',
        ];

        foreach ($sampleImages as $source => $target) {
            $sourcePath = base_path('ngocdo-fashion-ui/images/'.$source);
            if (File::exists($sourcePath)) {
                Storage::disk('public')->put($target, File::get($sourcePath));
            }
        }

        $categoryImages = [
            'women' => 'images/shop/categories/cat_women.png',
            'men' => 'images/shop/categories/cat_men.png',
            'kids' => 'images/shop/products/tshirt.png',
            'shoes-bags' => 'images/shop/products/jeans.png',
            'beauty' => 'images/shop/products/blouse.png',
            'women-dresses' => 'images/shop/products/dress.png',
            'women-tops' => 'images/shop/products/blouse.png',
            'women-bottoms' => 'images/shop/products/skirt.png',
            'men-shirts' => 'images/shop/products/polo.png',
            'men-pants' => 'images/shop/products/jeans.png',
            'men-jackets' => 'images/shop/products/jacket.png',
            'kids-boys' => 'images/shop/products/tshirt.png',
            'kids-girls' => 'images/shop/products/skirt.png',
            'giay-dep' => 'images/shop/products/jeans.png',
            'tui-va-balo' => 'images/shop/products/jacket.png',
            'cham-soc-da' => 'images/shop/products/blouse.png',
            'chong-nang' => 'images/shop/products/blouse.png',
        ];

        $productImages = [
            'midi-dress' => 'images/shop/products/dress.png',
            'wrap-midi-dress' => 'images/shop/products/dress.png',
            'shirt-dress-linen' => 'images/shop/products/blouse.png',
            'knit-maxi-dress' => 'images/shop/products/skirt.png',
            'cropped-cardigan' => 'images/shop/products/jacket.png',
            'graphic-tee-cotton' => 'images/shop/products/tshirt.png',
            'silk-camisole' => 'images/shop/products/blouse.png',
            'high-rise-skinny-jeans' => 'images/shop/products/jeans.png',
            'wide-leg-trousers' => 'images/shop/products/skirt.png',
            'linen-shirt' => 'images/shop/products/polo.png',
            'oxford-shirt-white' => 'images/shop/products/polo.png',
            'flannel-shirt-check' => 'images/shop/products/tshirt.png',
            'polo-pique' => 'images/shop/products/polo.png',
            'cargo-pants-tech' => 'images/shop/products/jeans.png',
            'selvedge-jeans' => 'images/shop/products/jeans.png',
            'wool-overcoat' => 'images/shop/products/jacket.png',
            'bomber-jacket-nylon' => 'images/shop/products/jacket.png',
            'kids-hoodie-fleece' => 'images/shop/products/tshirt.png',
            'kids-chino-shorts' => 'images/shop/products/jeans.png',
            'kids-tulle-skirt' => 'images/shop/products/skirt.png',
            'kids-denim-jacket' => 'images/shop/products/jacket.png',
            'leather-loafers' => 'images/shop/products/jeans.png',
            'canvas-sneakers-low' => 'images/shop/products/polo.png',
            'leather-tote' => 'images/shop/products/jacket.png',
            'nylon-backpack' => 'images/shop/products/jacket.png',
            'vitamin-c-serum' => 'images/shop/products/blouse.png',
            'spf50-gel-cream' => 'images/shop/products/blouse.png',
        ];

        $roots = [
            ['name' => 'Thời trang nữ', 'slug' => 'women', 'position' => 1, 'image_path' => $categoryImages['women']],
            ['name' => 'Thời trang nam', 'slug' => 'men', 'position' => 2, 'image_path' => $categoryImages['men']],
            ['name' => 'Trẻ em', 'slug' => 'kids', 'position' => 3, 'image_path' => $categoryImages['kids']],
            ['name' => 'Giày dép & túi xách', 'slug' => 'shoes-bags', 'position' => 4, 'image_path' => $categoryImages['shoes-bags']],
            ['name' => 'Làm đẹp', 'slug' => 'beauty', 'position' => 5, 'image_path' => $categoryImages['beauty']],
        ];

        $rootsBySlug = [];
        foreach ($roots as $row) {
            $rootsBySlug[$row['slug']] = Category::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'image_path' => $row['image_path'],
                    'parent_id' => null,
                    'position' => $row['position'],
                    'is_active' => true,
                ]
            );
        }

        $children = [
            'women' => [
                ['name' => 'Váy & đầm', 'slug' => 'women-dresses', 'position' => 1],
                ['name' => 'Áo & áo thun', 'slug' => 'women-tops', 'position' => 2],
                ['name' => 'Quần & chân váy', 'slug' => 'women-bottoms', 'position' => 3],
            ],
            'men' => [
                ['name' => 'Áo sơ mi & polo', 'slug' => 'men-shirts', 'position' => 1],
                ['name' => 'Quần & jean', 'slug' => 'men-pants', 'position' => 2],
                ['name' => 'Áo khoác', 'slug' => 'men-jackets', 'position' => 3],
            ],
            'kids' => [
                ['name' => 'Bé trai', 'slug' => 'kids-boys', 'position' => 1],
                ['name' => 'Bé gái', 'slug' => 'kids-girls', 'position' => 2],
            ],
            'shoes-bags' => [
                ['name' => 'Giày dép', 'slug' => 'giay-dep', 'position' => 1],
                ['name' => 'Túi & balo', 'slug' => 'tui-va-balo', 'position' => 2],
            ],
            'beauty' => [
                ['name' => 'Chăm sóc da', 'slug' => 'cham-soc-da', 'position' => 1],
                ['name' => 'Chống nắng', 'slug' => 'chong-nang', 'position' => 2],
            ],
        ];

        $cats = $rootsBySlug;
        foreach ($children as $parentSlug => $rows) {
            $parent = $rootsBySlug[$parentSlug];
            foreach ($rows as $row) {
                $cats[$row['slug']] = Category::query()->updateOrCreate(
                    ['slug' => $row['slug']],
                    [
                        'name' => $row['name'],
                        'image_path' => $categoryImages[$row['slug']] ?? null,
                        'parent_id' => $parent->id,
                        'position' => $row['position'],
                        'is_active' => true,
                    ]
                );
            }
        }

        $items = [
            ['slug' => 'midi-dress', 'cat' => 'women-dresses', 'name' => 'Đầm midi xếp ly thanh lịch', 'desc' => 'Dáng chữ A, có lót trong, khóa ẩn bên hông. Phù hợp công sở và dạo phố cuối tuần.', 'price' => 899000, 'compare' => 1190000, 'sku' => 'DR-101', 'feat' => true, 'img' => 'https://picsum.photos/seed/midi-dress/600/750'],
            ['slug' => 'wrap-midi-dress', 'cat' => 'women-dresses', 'name' => 'Đầm midi satin cổ chữ V', 'desc' => 'Thắt eo điều chỉnh được, vải satin mềm óng ánh. Nên giặt khô hoặc giặt tay nhẹ.', 'price' => 759000, 'compare' => 990000, 'sku' => 'DR-204', 'feat' => true, 'img' => 'https://picsum.photos/seed/wrap-midi/600/750'],
            ['slug' => 'shirt-dress-linen', 'cat' => 'women-dresses', 'name' => 'Đầm sơ mi pha linen', 'desc' => 'Pha linen thoáng mát, có túi ngực, kèm thắt lưng cùng màu. Dễ phối sneaker hoặc sandal.', 'price' => 649000, 'compare' => null, 'sku' => 'DR-310', 'feat' => false, 'img' => 'https://picsum.photos/seed/shirt-dress/600/750'],
            ['slug' => 'knit-maxi-dress', 'cat' => 'women-dresses', 'name' => 'Đầm maxi len gân dài', 'desc' => 'Len co giãn nhẹ, xẻ tà vừa phải, giặt máy chế độ len/lạnh.', 'price' => 529000, 'compare' => 699000, 'sku' => 'DR-422', 'feat' => false, 'img' => 'https://picsum.photos/seed/knit-maxi/600/750'],

            ['slug' => 'cropped-cardigan', 'cat' => 'women-tops', 'name' => 'Áo len cardigan crop', 'desc' => 'Pha len mềm, hàng nút ngọc trai, dáng ngắn vừa eo quần cạp cao.', 'price' => 459000, 'compare' => 599000, 'sku' => 'WT-011', 'feat' => true, 'img' => 'https://picsum.photos/seed/cardigan/600/750'],
            ['slug' => 'graphic-tee-cotton', 'cat' => 'women-tops', 'name' => 'Áo thun cotton in họa tiết', 'desc' => 'Cotton 240gsm dày vừa, vai rộng, hiệu ứng wash vintage.', 'price' => 279000, 'compare' => null, 'sku' => 'WT-045', 'feat' => false, 'img' => 'https://picsum.photos/seed/graphic-tee/600/750'],
            ['slug' => 'silk-camisole', 'cat' => 'women-tops', 'name' => 'Áo hai dây pha lụa', 'desc' => 'Dây điều chỉnh được, viền ren cổ tinh tế, mặc trong áo khoác hoặc riêng mùa hè.', 'price' => 349000, 'compare' => 449000, 'sku' => 'WT-088', 'feat' => false, 'img' => 'https://picsum.photos/seed/camisole/600/750'],

            ['slug' => 'high-rise-skinny-jeans', 'cat' => 'women-bottoms', 'name' => 'Quần jean skinny cạp cao', 'desc' => 'Denim co giãn, dài qua mắt cá chân, túi năm cổ điển.', 'price' => 619000, 'compare' => 799000, 'sku' => 'WB-120', 'feat' => true, 'img' => 'https://picsum.photos/seed/skinny-jeans-w/600/750'],
            ['slug' => 'wide-leg-trousers', 'cat' => 'women-bottoms', 'name' => 'Quần ống rộng ly giữa', 'desc' => 'Vải dệt rũ nhẹ, ly giữa thanh lịch, cúc cài phía trước.', 'price' => 589000, 'compare' => null, 'sku' => 'WB-205', 'feat' => false, 'img' => 'https://picsum.photos/seed/wide-trousers/600/750'],

            ['slug' => 'linen-shirt', 'cat' => 'men-shirts', 'name' => 'Áo sơ mi linen hằng ngày', 'desc' => 'Pha linen nhẹ, thoáng khí. Mẫu cao 180cm mặc size M. Giặt máy nước lạnh.', 'price' => 599000, 'compare' => 799000, 'sku' => 'SH-001', 'feat' => true, 'img' => 'https://picsum.photos/seed/linen-shirt/600/750'],
            ['slug' => 'oxford-shirt-white', 'cat' => 'men-shirts', 'name' => 'Áo sơ mi Oxford slim fit', 'desc' => 'Vải Oxford cotton đứng form, cổ bẻ cài nút, dễ ủi.', 'price' => 449000, 'compare' => null, 'sku' => 'SH-112', 'feat' => true, 'img' => 'https://picsum.photos/seed/oxford-shirt/600/750'],
            ['slug' => 'flannel-shirt-check', 'cat' => 'men-shirts', 'name' => 'Áo flannel kẻ caro', 'desc' => 'Cotton brushed ấm, túi ngực, form vừa vặn không quá rộng.', 'price' => 499000, 'compare' => 649000, 'sku' => 'SH-240', 'feat' => false, 'img' => 'https://picsum.photos/seed/flannel-shirt/600/750'],
            ['slug' => 'polo-pique', 'cat' => 'men-shirts', 'name' => 'Áo polo piqué thể thao', 'desc' => 'Dệt piqué thấm hút mồ hôi, bo cổ tay gân co giãn.', 'price' => 359000, 'compare' => null, 'sku' => 'SH-305', 'feat' => false, 'img' => 'https://picsum.photos/seed/polo-pique/600/750'],

            ['slug' => 'cargo-pants-tech', 'cat' => 'men-pants', 'name' => 'Quần cargo kỹ thuật', 'desc' => 'Vỏ chống nước nhẹ, nhiều túi hộp, khớp gối dễ vận động.', 'price' => 729000, 'compare' => 949000, 'sku' => 'MP-410', 'feat' => true, 'img' => 'https://picsum.photos/seed/cargo-pants/600/750'],
            ['slug' => 'selvedge-jeans', 'cat' => 'men-pants', 'name' => 'Quần jean selvedge ống thẳng', 'desc' => 'Denim raw cứng cáp, ống thẳng, đường chỉ contrast nổi bật.', 'price' => 899000, 'compare' => null, 'sku' => 'MP-520', 'feat' => false, 'img' => 'https://picsum.photos/seed/selvedge-jeans/600/750'],

            ['slug' => 'wool-overcoat', 'cat' => 'men-jackets', 'name' => 'Áo khoác len một hàng nút', 'desc' => 'Pha len ấm, ve cổ chữ V, lót đầy đủ, phù hợp mùa se lạnh.', 'price' => 1890000, 'compare' => 2490000, 'sku' => 'MJ-601', 'feat' => true, 'img' => 'https://picsum.photos/seed/wool-coat/600/750'],
            ['slug' => 'bomber-jacket-nylon', 'cat' => 'men-jackets', 'name' => 'Áo khoác bomber nylon', 'desc' => 'Đệm nhẹ, bo cổ tay gân, túi khóa kéo hai bên.', 'price' => 1090000, 'compare' => null, 'sku' => 'MJ-702', 'feat' => false, 'img' => 'https://picsum.photos/seed/bomber/600/750'],

            ['slug' => 'kids-hoodie-fleece', 'cat' => 'kids-boys', 'name' => 'Áo hoodie nỉ bé trai', 'desc' => 'Nỉ chải mềm, túi kangaroo, cổ không nhãn cứng gây ngứa da.', 'price' => 259000, 'compare' => 329000, 'sku' => 'KB-801', 'feat' => true, 'img' => 'https://picsum.photos/seed/kids-hoodie/600/750'],
            ['slug' => 'kids-chino-shorts', 'cat' => 'kids-boys', 'name' => 'Quần short kaki co giãn bé trai', 'desc' => 'Lưng chun dây rút, cotton stretch bền giặt.', 'price' => 199000, 'compare' => null, 'sku' => 'KB-815', 'feat' => false, 'img' => 'https://picsum.photos/seed/kids-shorts/600/750'],

            ['slug' => 'kids-tulle-skirt', 'cat' => 'kids-girls', 'name' => 'Chân váy tuần dự tiệc bé gái', 'desc' => 'Nhiều lớp tuần bồng, lót cotton mềm, lưng chun co giãn.', 'price' => 229000, 'compare' => 299000, 'sku' => 'KG-901', 'feat' => true, 'img' => 'https://picsum.photos/seed/tulle-skirt/600/750'],
            ['slug' => 'kids-denim-jacket', 'cat' => 'kids-girls', 'name' => 'Áo khoác denim bé gái', 'desc' => 'Denim co giãn nhẹ, túi ngực, nút kim loại bạc.', 'price' => 349000, 'compare' => null, 'sku' => 'KG-915', 'feat' => false, 'img' => 'https://picsum.photos/seed/kids-denim/600/750'],

            ['slug' => 'leather-loafers', 'cat' => 'giay-dep', 'name' => 'Giày loafer da cổ điển', 'desc' => 'Da thật, đệm êm chân, đế cao su chống trơn.', 'price' => 1290000, 'compare' => 1690000, 'sku' => 'SB-1001', 'feat' => true, 'img' => 'https://picsum.photos/seed/loafers/600/750'],
            ['slug' => 'canvas-sneakers-low', 'cat' => 'giay-dep', 'name' => 'Giày sneaker canvas cổ thấp', 'desc' => 'Đế lưu hóa bền, cổ đệm mút, đi làm đi chơi đều thoải mái.', 'price' => 599000, 'compare' => 799000, 'sku' => 'SB-1012', 'feat' => true, 'img' => 'https://picsum.photos/seed/canvas-sneakers/600/750'],
            ['slug' => 'leather-tote', 'cat' => 'tui-va-balo', 'name' => 'Túi tote da đứng form', 'desc' => 'Chứa laptop 13 inch, ngăn khóa kéo trong, quai đeo vai chắc chắn.', 'price' => 1590000, 'compare' => null, 'sku' => 'SB-1103', 'feat' => false, 'img' => 'https://picsum.photos/seed/tote-bag/600/750'],
            ['slug' => 'nylon-backpack', 'cat' => 'tui-va-balo', 'name' => 'Balo nylon miệng gập', 'desc' => 'Vỏ chống nước, ngăn laptop đệm, túi bình nước hai bên hông.', 'price' => 890000, 'compare' => 1090000, 'sku' => 'SB-1120', 'feat' => false, 'img' => 'https://picsum.photos/seed/backpack/600/750'],

            ['slug' => 'vitamin-c-serum', 'cat' => 'cham-soc-da', 'name' => 'Serum vitamin C sáng da', 'desc' => '15% dẫn xuất ổn định, kết hợp HA cấp ẩm. Dùng buổi sáng, luôn kèm kem chống nắng.', 'price' => 399000, 'compare' => 529000, 'sku' => 'BY-2001', 'feat' => true, 'img' => 'https://picsum.photos/seed/vitc-serum/600/750'],
            ['slug' => 'spf50-gel-cream', 'cat' => 'chong-nang', 'name' => 'Kem chống nắng dạng gel SPF 50+', 'desc' => 'Quang phổ rộng UVA/UVB, finish trong không bết dính, 50ml.', 'price' => 279000, 'compare' => null, 'sku' => 'BY-2010', 'feat' => false, 'img' => 'https://picsum.photos/seed/spf-cream/600/750'],
        ];

        foreach ($items as &$item) {
            if (in_array($item['slug'], ['midi-dress', 'shirt-dress-linen', 'kids-hoodie-fleece', 'vitamin-c-serum'], true)) {
                $item['new'] = true;
            }
            $item['img'] = $productImages[$item['slug']] ?? $categoryImages[$item['cat']] ?? 'images/shop/products/tshirt.png';
        }
        unset($item);

        $variantSets = [
            'dress' => [
                ['size' => 'S', 'color' => 'Đen', 'stock' => 12],
                ['size' => 'M', 'color' => 'Ô liu', 'stock' => 10],
                ['size' => 'L', 'color' => 'Navy', 'stock' => 8],
            ],
            'tops' => [
                ['size' => 'S', 'color' => 'Ngà', 'stock' => 15],
                ['size' => 'M', 'color' => 'Đen', 'stock' => 20],
                ['size' => 'L', 'color' => 'Nâu camel', 'stock' => 9],
            ],
            'bottoms' => [
                ['size' => '28', 'color' => 'Indigo', 'stock' => 14],
                ['size' => '30', 'color' => 'Indigo', 'stock' => 18],
                ['size' => '32', 'color' => 'Đen', 'stock' => 11],
            ],
            'shirt' => [
                ['size' => 'S', 'color' => 'Trắng', 'stock' => 16],
                ['size' => 'M', 'color' => 'Trắng', 'stock' => 22],
                ['size' => 'L', 'color' => 'Navy', 'stock' => 17],
            ],
            'shoes' => [
                ['size' => '39', 'color' => 'Nâu', 'stock' => 6],
                ['size' => '40', 'color' => 'Nâu', 'stock' => 8],
                ['size' => '41', 'color' => 'Đen', 'stock' => 7],
                ['size' => '42', 'color' => 'Đen', 'stock' => 5],
            ],
            'bags' => [
                ['size' => 'Freesize', 'color' => 'Nâu sáng', 'stock' => 10],
                ['size' => 'Freesize', 'color' => 'Đen', 'stock' => 12],
            ],
            'beauty' => [
                ['size' => '50ml', 'color' => 'Mặc định', 'stock' => 40],
                ['size' => '50ml', 'color' => 'Combo x2', 'stock' => 15],
            ],
            'kids' => [
                ['size' => '4 tuổi', 'color' => 'Xám', 'stock' => 14],
                ['size' => '6 tuổi', 'color' => 'Navy', 'stock' => 12],
                ['size' => '8 tuổi', 'color' => 'Đỏ', 'stock' => 10],
            ],
        ];

        foreach ($items as $row) {
            $cat = $cats[$row['cat']] ?? null;
            if (! $cat) {
                continue;
            }

            $variantKey = match ($row['cat']) {
                'women-dresses' => 'dress',
                'women-tops' => 'tops',
                'women-bottoms' => 'bottoms',
                'men-shirts' => 'shirt',
                'men-pants' => 'bottoms',
                'men-jackets' => 'tops',
                'kids-boys', 'kids-girls' => 'kids',
                'giay-dep' => 'shoes',
                'tui-va-balo' => 'bags',
                'cham-soc-da', 'chong-nang' => 'beauty',
                default => 'tops',
            };

            $isOnSaleSeed = isset($row['compare']) && $row['compare'] !== null && (float) $row['compare'] > (float) $row['price'];

            $product = Product::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'category_id' => $cat->id,
                    'name' => $row['name'],
                    'description' => $row['desc'],
                    'main_image' => $row['img'],
                    'price' => $row['price'],
                    'compare_price' => $row['compare'],
                    'sku' => $row['sku'],
                    'is_featured' => $row['feat'],
                    'is_hot' => (bool) ($row['hot'] ?? $row['feat']),
                    'is_on_sale' => (bool) ($row['sale'] ?? $isOnSaleSeed),
                    'is_new' => (bool) ($row['new'] ?? false),
                    'is_active' => true,
                ]
            );

            ProductVariant::query()->where('product_id', $product->id)->delete();
            ProductImage::query()->where('product_id', $product->id)->delete();
            ProductImage::query()->create([
                'product_id' => $product->id,
                'path' => $row['img'],
                'position' => 1,
            ]);

            $set = $variantSets[$variantKey];
            foreach ($set as $i => $v) {
                ProductVariant::query()->create([
                    'product_id' => $product->id,
                    'size' => $v['size'],
                    'color' => $v['color'],
                    'stock' => $v['stock'],
                    'sku' => $row['sku'].'-'.($i + 1),
                ]);
            }
        }

        $bannerSlides = [
            ['title' => 'Siêu sale thời trang — giảm sâu cuối tuần', 'path' => 'https://picsum.photos/seed/banner-mega/1400/420', 'link' => '/products', 'order' => 0],
            ['title' => 'Giày dép & sneaker mùa mới', 'path' => 'https://picsum.photos/seed/banner-shoes/1400/420', 'link' => '/products?category='.$cats['giay-dep']->id, 'order' => 1],
            ['title' => 'Chăm sóc da & làm đẹp', 'path' => 'https://picsum.photos/seed/banner-beauty/1400/420', 'link' => '/products?category='.$cats['beauty']->id, 'order' => 2],
            ['title' => 'Đồ trẻ em — bền đẹp cho bé yêu', 'path' => 'https://picsum.photos/seed/banner-kids/1400/420', 'link' => '/products?category='.$cats['kids']->id, 'order' => 3],
            ['title' => 'Miễn phí vận chuyển đơn từ 500.000đ', 'path' => 'https://picsum.photos/seed/banner-ship/1400/420', 'link' => '/cart', 'order' => 4],
        ];

        foreach ($bannerSlides as $b) {
            Banner::query()->updateOrCreate(
                ['sort_order' => $b['order']],
                [
                    'title' => $b['title'],
                    'image_path' => $b['path'],
                    'link_url' => $b['link'],
                    'is_active' => true,
                ]
            );
        }

        $legacyPath = 'banners/hero-seed.png';
        Storage::disk('public')->put($legacyPath, $png);
    }
}
