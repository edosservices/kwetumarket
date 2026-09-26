<?php

namespace Database\Seeders;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Enums\ShopStatus;
use App\Enums\StockMovementType;
use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Catalog\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = $this->category('Électronique', 'electronique', null, 1, 'Téléphones, informatique et image.');
        $phones = $this->category('Smartphones', 'smartphones', $electronics, 1);
        $computers = $this->category('Ordinateurs', 'ordinateurs', $electronics, 2);
        $tvs = $this->category('Télévisions', 'televisions', $electronics, 3);
        $accessories = $this->category('Accessoires', 'accessoires', $electronics, 4);

        $fashion = $this->category('Mode', 'mode', null, 2, 'Vêtements pour toute la famille.');
        $men = $this->category('Homme', 'homme', $fashion, 1);
        $women = $this->category('Femme', 'femme', $fashion, 2);
        $children = $this->category('Enfants', 'enfants', $fashion, 3);

        $home = $this->category('Maison', 'maison', null, 3, 'Meubles et équipement de la maison.');
        $furniture = $this->category('Meubles', 'meubles', $home, 1);
        $decor = $this->category('Décoration', 'decoration', $home, 2);
        $appliances = $this->category('Électroménager', 'electromenager', $home, 3);

        $grocery = $this->category('Alimentation', 'alimentation', null, 4, 'Produits du quotidien.');
        $pantry = $this->category('Épicerie', 'epicerie', $grocery, 1);

        $samsung = $this->brand('Samsung', 'samsung');
        $apple = $this->brand('Apple', 'apple');
        $tecno = $this->brand('Tecno', 'tecno');
        $itel = $this->brand('Itel', 'itel');
        $nike = $this->brand('Nike', 'nike');
        $binatone = $this->brand('Binatone', 'binatone');

        $patrick = $this->vendorUser('Patrick Mbuyi', 'vendeur@twende.market', '+243900000003');
        $amina = $this->vendorUser('Amina Kalala', 'amina.vendeuse@twende.market', '+243900000011');
        $david = $this->vendorUser('David Ilunga', 'david.vendeur@twende.market', '+243900000012');

        $tech = $this->shop($patrick, 'Kinois Tech', 'kinois-tech', 'Smartphones et accessoires à Gombe.', 'Gombe, Kinshasa', '+243810000101');
        $house = $this->shop($amina, 'Maison Amina', 'maison-amina', 'Meubles et décoration faits pour Kinshasa.', 'Lingwala, Kinshasa', '+243810000102');
        $style = $this->shop($david, 'Mode Lingwala', 'mode-lingwala', 'Mode homme, femme et enfants.', 'Lingwala, Kinshasa', '+243810000103');

        $stock = app(StockService::class);

        $iphone = $this->product($tech, $phones, $apple, 'iPhone 15', 'iphone-15', 'IPH-15', 145000000, 159000000, 'Le smartphone Apple, proposé avec plusieurs capacités.');
        $this->image($iphone);
        $this->variant($iphone, 'Noir / 128 GB', 'IPH-15-NOIR-128', ['color' => 'Noir', 'capacity' => '128 GB'], 8, $stock);
        $this->variant($iphone, 'Noir / 256 GB', 'IPH-15-NOIR-256', ['color' => 'Noir', 'capacity' => '256 GB'], 4, $stock, 165000000);
        $this->variant($iphone, 'Bleu / 128 GB', 'IPH-15-BLEU-128', ['color' => 'Bleu', 'capacity' => '128 GB'], 5, $stock);

        $this->stocked($stock, $this->product($tech, $phones, $tecno, 'Tecno Spark 20', 'tecno-spark-20', 'TEC-SP20', 18500000, null, 'Smartphone accessible, batterie longue durée.'), 24);
        $this->stocked($stock, $this->product($tech, $phones, $samsung, 'Samsung Galaxy A15', 'samsung-galaxy-a15', 'SAM-A15', 24000000, 27500000, 'Écran lumineux et double SIM.'), 15);
        $this->stocked($stock, $this->product($tech, $computers, $itel, 'Itel Able 1', 'itel-able-1', 'ITE-AB1', 32000000, null, 'Ordinateur portable pour les études et la boutique.'), 6);
        $this->stocked($stock, $this->product($tech, $tvs, $samsung, 'Samsung Téléviseur 43 pouces', 'samsung-tv-43', 'SAM-TV43', 41000000, null, 'Téléviseur pour le salon.'), 4);
        $this->stocked($stock, $this->product($tech, $accessories, null, 'Écouteurs filaires', 'ecouteurs-filaires', 'ACC-ECO', 1500000, null, 'Écouteurs simples, livrés avec étui.'), 40);

        $this->stocked($stock, $this->product($house, $furniture, null, 'Table basse en bois', 'table-basse-bois', 'MEU-TAB', 9500000, null, 'Table basse stable pour le salon.'), 7);
        $this->stocked($stock, $this->product($house, $decor, null, 'Lot de coussins', 'lot-coussins', 'DEC-COU', 2800000, 3500000, 'Quatre coussins aux couleurs vives.'), 12);
        $this->stocked($stock, $this->product($house, $appliances, $binatone, 'Mixeur Binatone', 'mixeur-binatone', 'BIN-MIX', 6200000, null, 'Mixeur de cuisine pour la préparation quotidienne.'), 9);

        $this->stocked($stock, $this->product($style, $men, $nike, 'Baskets homme', 'baskets-homme', 'MOD-BAS-H', 7500000, null, 'Baskets confortables pour la ville.', ProductCondition::New), 11);
        $this->stocked($stock, $this->product($style, $women, null, 'Robe wax', 'robe-wax', 'MOD-ROB', 4500000, 6000000, 'Robe en wax, coupe droite.'), 8);
        $this->stocked($stock, $this->product($style, $children, null, 'Ensemble enfant', 'ensemble-enfant', 'MOD-ENF', 2200000, null, 'Ensemble deux pièces pour enfant.', ProductCondition::New), 14);

        $rice = $this->product($house, $pantry, null, 'Riz parfumé 5 kg', 'riz-parfume-5kg', 'EPI-RIZ-5', 2800000, null, 'Sac de riz parfumé de 5 kg.');
        $this->stocked($stock, $rice, 30);

        $this->product($tech, $accessories, null, 'Coque brouillon', 'coque-brouillon', 'ACC-COQ-DRAFT', 800000, null, 'Fiche encore en brouillon, invisible du catalogue public.', ProductCondition::New, ProductStatus::Draft);
    }

    private function category(string $name, string $slug, ?Category $parent, int $sort, ?string $description = null): Category
    {
        return Category::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'parent_id' => $parent?->id,
                'name' => $name,
                'description' => $description,
                'status' => 'active',
                'sort_order' => $sort,
            ],
        );
    }

    private function brand(string $name, string $slug): Brand
    {
        return Brand::query()->updateOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'status' => 'active'],
        );
    }

    private function vendorUser(string $name, string $email, string $phone): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => DatabaseSeeder::DEMO_PASSWORD,
                'locale' => 'fr',
                'currency' => 'CDF',
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles([UserRole::Vendor->value]);

        Vendor::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['status' => VendorStatus::Active],
        );

        return $user;
    }

    private function shop(User $user, string $name, string $slug, string $description, string $location, string $phone): Shop
    {
        $vendor = $user->vendorProfile()->firstOrFail();

        return Shop::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'vendor_id' => $vendor->id,
                'name' => $name,
                'description' => $description,
                'location' => $location,
                'phone' => $phone,
                'email' => $user->email,
                'status' => ShopStatus::Active,
            ],
        );
    }

    private function product(
        Shop $shop,
        Category $category,
        ?Brand $brand,
        string $name,
        string $slug,
        string $sku,
        int $price,
        ?int $compare,
        string $description,
        ProductCondition $condition = ProductCondition::New,
        ProductStatus $status = ProductStatus::Published,
    ): Product {
        return Product::query()->updateOrCreate(
            ['sku' => $sku],
            [
                'shop_id' => $shop->id,
                'category_id' => $category->id,
                'brand_id' => $brand?->id,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'price' => $price,
                'compare_at_price' => $compare,
                'currency' => 'CDF',
                'status' => $status,
                'condition' => $condition,
                'published_at' => $status === ProductStatus::Published ? now() : null,
            ],
        );
    }

    private function image(Product $product): void
    {
        $existing = $product->images()->first();

        if ($existing && Storage::disk($existing->disk)->size($existing->path) > 1000) {
            return;
        }

        $existing?->delete();

        $path = 'products/'.$product->id.'/card.png';
        Storage::disk(config('twende.media.disk'))->put($path, $this->cardImage($product->name));

        ProductImage::query()->create([
            'product_id' => $product->id,
            'disk' => config('twende.media.disk'),
            'path' => $path,
            'alt_text' => $product->name,
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }

    private function cardImage(string $name): string
    {
        $canvas = imagecreatetruecolor(640, 640);
        $background = imagecolorallocate($canvas, 244, 247, 245);
        $red = imagecolorallocate($canvas, 242, 2, 5);
        $green = imagecolorallocate($canvas, 13, 152, 39);
        imagefilledrectangle($canvas, 0, 0, 639, 639, $background);
        imagefilledellipse($canvas, 320, 250, 220, 220, $green);
        imagefilledrectangle($canvas, 220, 390, 420, 470, $red);

        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: 'T';
        $initial = strtoupper(substr($ascii, 0, 1) ?: 'T');
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagestring($canvas, 5, 300, 230, $initial, $white);

        ob_start();
        imagepng($canvas);
        $binary = ob_get_clean();
        imagedestroy($canvas);

        return $binary === false ? '' : $binary;
    }

    private function variant(Product $product, string $name, string $sku, array $attributes, int $quantity, StockService $stock, ?int $price = null): void
    {
        $variant = $product->variants()->updateOrCreate(
            ['sku' => $sku],
            [
                'name' => $name,
                'attributes' => $attributes,
                'price' => $price,
                'status' => 'active',
            ],
        );

        if ($variant->inventory()->doesntExist()) {
            $stock->record($product, $variant, StockMovementType::Purchase, $quantity, $product->shop->vendor->user, $sku, 'Stock initial');
        }

        $this->image($product);
    }

    private function stocked(StockService $stock, Product $product, int $quantity): void
    {
        $this->image($product);

        if ($product->inventories()->whereNull('product_variant_id')->exists()) {
            return;
        }

        $product->load('shop.vendor.user');
        $stock->record($product, null, StockMovementType::Purchase, $quantity, $product->shop->vendor->user, $product->sku, 'Stock initial');
    }
}
