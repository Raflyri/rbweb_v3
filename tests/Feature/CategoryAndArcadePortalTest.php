<?php

use App\Models\ArcadeGame;
use App\Models\Category;
use App\Models\GameCategory;
use App\Models\Product;
use App\Models\User;
use App\Support\ProductType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function () {
    foreach (['super_admin', 'admin', 'regular_user'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

/*
|--------------------------------------------------------------------------
| Models & Relationships
|--------------------------------------------------------------------------
*/

it('links categories and products with hasMany and belongsTo relationships', function () {
    $category = Category::factory()->barang()->create([
        'name' => 'Perangkat Jaringan',
        'slug' => 'perangkat-jaringan',
    ]);

    $product = Product::factory()->barang()->create([
        'category_id' => $category->id,
        'name'        => ['id' => 'RouterMikroTik RB4011'],
    ]);

    expect($category->products)->toHaveCount(1)
        ->and($category->products->first()->id)->toBe($product->id)
        ->and($product->category->id)->toBe($category->id);
});

it('keeps existing products intact with null category_id when a category is deleted', function () {
    $category = Category::factory()->jasa()->create();
    $product  = Product::factory()->jasa()->create(['category_id' => $category->id]);

    $category->delete();

    expect($product->fresh())->not->toBeNull()
        ->and($product->fresh()->category_id)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| CategoryController CRUD (/rbdashboard/categories)
|--------------------------------------------------------------------------
*/

it('protects rbdashboard category and arcade routes with auth middleware', function () {
    get('/rbdashboard/categories')->assertRedirect();
    get('/rbdashboard/arcade-games')->assertRedirect();
});

it('allows authenticated users to create, update, and delete categories', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    actingAs($admin)
        ->post(route('categories.store'), [
            'name' => 'Jasa Cloud & DevOps',
            'type' => 'Jasa',
        ])
        ->assertRedirect(route('categories.index'));

    $category = Category::where('slug', 'jasa-cloud-devops')->firstOrFail();
    expect($category->type)->toBe('Jasa');

    actingAs($admin)
        ->put(route('categories.update', $category), [
            'name' => 'Jasa Cloud Server',
            'slug' => 'jasa-cloud-server',
            'type' => 'Jasa',
        ])
        ->assertRedirect(route('categories.index'));

    expect($category->fresh()->name)->toBe('Jasa Cloud Server')
        ->and($category->fresh()->slug)->toBe('jasa-cloud-server');

    actingAs($admin)
        ->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'));

    expect(Category::find($category->id))->toBeNull();
});

/*
|--------------------------------------------------------------------------
| ArcadeGameController CRUD & Thumbnail Upload (/rbdashboard/arcade-games)
|--------------------------------------------------------------------------
*/

it('stores, updates, and deletes arcade games with thumbnail upload', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $initialThumb = UploadedFile::fake()->image('tetris.png', 600, 400);

    actingAs($admin)
        ->post(route('arcade-games.store'), [
            'title'       => 'Tetris Web RB',
            'description' => 'Game menyusun balok klasik berbasis web.',
            'thumbnail'   => $initialThumb,
            'game_url'    => 'https://tetris-web-rb.pages.dev',
            'status'      => 'Tayangkan',
        ])
        ->assertRedirect(route('arcade-games.index'));

    $game = ArcadeGame::where('slug', 'tetris-web-rb')->firstOrFail();
    expect($game->thumbnail)->not->toBeNull();
    Storage::disk('public')->assertExists($game->thumbnail);

    $oldThumbPath = $game->thumbnail;
    $newThumb     = UploadedFile::fake()->image('tetris-v2.png', 600, 400);

    actingAs($admin)
        ->put(route('arcade-games.update', $game), [
            'title'       => 'Tetris Web RB v2',
            'slug'        => 'tetris-web-rb',
            'description' => 'Versi terbaru Tetris Web RB.',
            'thumbnail'   => $newThumb,
            'game_url'    => 'https://tetris-web-rb.pages.dev',
            'status'      => 'Tayangkan',
        ])
        ->assertRedirect(route('arcade-games.index'));

    $game->refresh();
    Storage::disk('public')->assertMissing($oldThumbPath);
    Storage::disk('public')->assertExists($game->thumbnail);

    $currentThumb = $game->thumbnail;

    actingAs($admin)
        ->delete(route('arcade-games.destroy', $game))
        ->assertRedirect(route('arcade-games.index'));

    Storage::disk('public')->assertMissing($currentThumb);
    expect(ArcadeGame::find($game->id))->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Arcade Subdomain Frontend (arcade.rbeverything.com)
|--------------------------------------------------------------------------
*/

it('lists only Tayangkan games on arcade.rbeverything.com and hides Draft games', function () {
    ArcadeGame::factory()->published()->create([
        'title' => 'Laboratorium Kata',
        'slug'  => 'laboratorium-kata',
    ]);
    ArcadeGame::factory()->draft()->create([
        'title' => 'Game Masih Draft',
        'slug'  => 'game-masih-draft',
    ]);

    get('http://arcade.rbeverything.com/')
        ->assertOk()
        ->assertSee('Laboratorium Kata')
        ->assertDontSee('Game Masih Draft');
});

it('renders the full-screen iframe player for a published game on arcade.rbeverything.com', function () {
    $game = ArcadeGame::factory()->published()->create([
        'title'    => 'Petualangan Penjelajah IPAS',
        'slug'     => 'petualangan-ipas',
        'game_url' => 'https://petualangan-ipas.pages.dev',
    ]);

    get('http://arcade.rbeverything.com/play/' . $game->slug)
        ->assertOk()
        ->assertSee('Petualangan Penjelajah IPAS')
        ->assertSee('<iframe', false)
        ->assertSee('src="https://petualangan-ipas.pages.dev"', false);
});

it('returns 404 when trying to play a Draft game on arcade.rbeverything.com', function () {
    $draft = ArcadeGame::factory()->draft()->create(['slug' => 'secret-draft-game']);

    get('http://arcade.rbeverything.com/play/' . $draft->slug)
        ->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Global Categories View Composer & Catalogue Category Filter
|--------------------------------------------------------------------------
*/

it('shares categories globally to the public layout and filters catalogue by category', function () {
    $catNetworking = Category::factory()->barang()->create([
        'name' => 'Networking Gear',
        'slug' => 'networking-gear',
    ]);
    $catOther = Category::factory()->barang()->create([
        'name' => 'Storage Devices',
        'slug' => 'storage-devices',
    ]);

    Product::factory()->barang()->create([
        'category_id' => $catNetworking->id,
        'name'        => ['id' => 'Switch Gigabit 8 Port'],
    ]);
    Product::factory()->barang()->create([
        'category_id' => $catOther->id,
        'name'        => ['id' => 'SSD NVMe 1TB'],
    ]);

    get(route('products.index', ['category' => 'networking-gear']))
        ->assertOk()
        ->assertSee('Networking Gear')
        ->assertSee('Switch Gigabit 8 Port')
        ->assertDontSee('SSD NVMe 1TB');

    get(route('products.index', ['type' => 'networking-gear']))
        ->assertOk()
        ->assertSee('Networking Gear')
        ->assertSee('Switch Gigabit 8 Port')
        ->assertDontSee('SSD NVMe 1TB');
});

it('dynamically expands ProductType options from database categories and syncs category_id from type', function () {
    $digitalType = Category::create([
        'name' => 'Produk Digital & SaaS',
        'slug' => 'produk-digital-saas',
        'type' => Category::TYPE_JASA,
    ]);

    expect(ProductType::options('id'))->toHaveKey('produk-digital-saas', 'Produk Digital & SaaS')
        ->and(ProductType::isValid('produk-digital-saas'))->toBeTrue();

    $product = Product::factory()->create([
        'type' => 'produk-digital-saas',
        'name' => ['id' => 'Lisensi RB POS Cloud'],
    ]);

    expect($product->fresh()->category_id)->toBe($digitalType->id)
        ->and($product->fresh()->isJasa())->toBeTrue();
});

it('renders futuristic arcade button and does not display category type in navbar dropdown', function () {
    Category::factory()->barang()->create([
        'name' => 'Perangkat Jaringan Unik',
        'slug' => 'perangkat-jaringan-unik',
    ]);

    get(route('home'))
        ->assertOk()
        ->assertSee('rb-nav-arcade-btn', false)
        ->assertSee(route('arcade.index'), false)
        ->assertSee('Perangkat Jaringan Unik')
        ->assertDontSee('<span style="font-size:0.65rem;opacity:0.65;">', false);

    get(route('products.index'))
        ->assertOk()
        ->assertSee('rb-nav-arcade-btn', false)
        ->assertSee(route('arcade.index'), false)
        ->assertSee('Perangkat Jaringan Unik')
        ->assertDontSee('<span style="font-size:0.65rem;opacity:0.65;">', false);
});

/*
|--------------------------------------------------------------------------
| Game Categories, Manual Spotlight, and Domain Redirections
|--------------------------------------------------------------------------
*/

it('links game categories and arcade games with hasMany and belongsTo', function () {
    $category = GameCategory::create([
        'name' => 'Retro Arcade & 8-Bit',
        'slug' => 'retro-arcade-8-bit',
    ]);

    $game = ArcadeGame::factory()->create([
        'game_category_id' => $category->id,
        'title'            => 'Space Invaders RB',
    ]);

    expect($category->games)->toHaveCount(1)
        ->and($category->games->first()->id)->toBe($game->id)
        ->and($game->category->id)->toBe($category->id);
});

it('only displays spotlight banner when an arcade game is explicitly marked as is_spotlight', function () {
    $normalGame = ArcadeGame::factory()->published()->create([
        'title'        => 'Normal Game Alpha',
        'is_spotlight' => false,
    ]);

    get('http://arcade.rbeverything.com/')
        ->assertOk()
        ->assertDontSee('SPOTLIGHT GAME')
        ->assertSee('Normal Game Alpha')
        ->assertDontSee('RBeverything Arcade Hub · v3.0')
        ->assertDontSee('Cloudflare Edge Optimized');

    $spotlightGame = ArcadeGame::factory()->published()->create([
        'title'        => 'Flagship Retro Quest',
        'is_spotlight' => true,
    ]);

    get('http://arcade.rbeverything.com/')
        ->assertOk()
        ->assertSee('SPOTLIGHT GAME')
        ->assertSee('Flagship Retro Quest');
});

it('redirects non-arcade routes on arcade subdomain back to apex domain', function () {
    get('http://arcade.rbeverything.com/layanan')
        ->assertRedirect('https://rbeverything.com/layanan');

    get('http://arcade.rbeverything.com/blog/artikel-baru')
        ->assertRedirect('https://rbeverything.com/blog/artikel-baru');
});

it('ensures arcade header HUD does not overlap the iframe canvas', function () {
    $game = ArcadeGame::factory()->published()->create([
        'title' => 'Tetris HUD Check',
        'slug'  => 'tetris-hud-check',
    ]);

    $response = get('http://arcade.rbeverything.com/play/' . $game->slug);
    $response->assertOk();
    $response->assertSee('arcade-hud-bar', false);
    $response->assertSee('arcade-frame-wrapper', false);
    // Ensure height calculation calc(100vh - var(--hud-height)) and --hud-height: 52px prevents overlap
    $response->assertSee('calc(100vh - var(--hud-height))', false);
    $response->assertSee('--hud-height: 52px', false);
});

