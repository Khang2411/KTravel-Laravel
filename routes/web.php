<?php

use App\Http\Controllers\ProfileController;
use App\Models\Address;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test', function () {
    // $array = [
    //     'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606937/ktravel/listings/eed62e09-4179-441c-9dd5-fd90a9c2e4a1_fhn3b0.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606937/ktravel/listings/e6e5eb1d-b5c4-4672-bb8a-b20b9b428dc3_ixmrwh.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606937/ktravel/listings/b025983a-34d3-4ebb-8223-975dd9c8208c_rngmcb.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606937/ktravel/listings/c648e392-b363-42ec-b9c2-fd7bbd8d210a_a6xthf.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606936/ktravel/listings/e0f0a277-0a18-43e3-b5db-315133dc5861_erxet5.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606936/ktravel/listings/b7c6a85e-cc53-45b5-891d-a679a4e81ff3_tvfq5d.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606936/ktravel/listings/a66902f4-5d57-4586-9364-6f65b4f9a5d8_u7w8ns.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606594/ktravel/listings/a63202c8-43df-4462-9bc4-1a05346f9dc7_ybdl7u.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606542/ktravel/listings/6931af73-177a-42fa-89b0-c2cc8845561a_w8vo7e.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606540/ktravel/listings/766ddbb0-aa17-4623-aee6-fc081de879a4_k0wu7k.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606540/ktravel/listings/6147efbe-2335-4a37-a8b9-40bd6fc2b4e6_fajohb.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606540/ktravel/listings/399bd57a-f20f-407c-ae72-6db90512293e_hdrnp4.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606539/ktravel/listings/79b20e95-b32f-4051-a1f7-dca25a691528_hwp2xs.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606539/ktravel/listings/3d3b1e0e-529a-4e9c-b6d8-b04df3bab6c1_none5p.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606539/ktravel/listings/51d368b2-33d2-4e53-a2c1-75a1ef43f866_qblq8p.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606539/ktravel/listings/8daa0f78-e9a6-4a4d-b08d-4b32ae7317f8_ocaz7o.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606539/ktravel/listings/21e200b0-2b5f-4aae-958a-6a760ab56b32_qqzcoh.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711606538/ktravel/listings/5c2d9918-7d9f-4dea-aaf3-32e5d4cae728_q1jqvx.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711607372/ktravel/listings/70732ef5-9768-4ea1-a176-6a46e183cc0a_q1hg1n.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711607373/ktravel/listings/ae64b3da-dac4-47f6-92a9-a0a8e93c13dc_eqthyx.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711607371/ktravel/listings/29d80e6b-547f-4933-96e9-655c8b8e9078_esdzwy.webp', 'https://res.cloudinary.com/dqsfwus9c/image/upload/v1711607374/ktravel/listings/c4cf2c77-5081-4a4c-bec8-6eba4165f4eb_zrukgo.webp'
    // ];

    // $listings = Listing::where('id', '!=', '4750174')->get();
    // foreach ($listings as $listing) {
    //     for ($x = 1; $x <= 5; $x++) {
    //         $k = array_rand($array);
    //         $v = $array[$k];
    //         $ex = basename($v, ".webp");
    //         $filename = "ktravel/listings/{$ex}";
    //         ListingImage::where('listing_id', $listing->id)
    //             ->create(['listing_id' => $listing->id, 'image' => $v, 'public_id_image' => $filename]);
    //     }
    //     // ListingImage::where('listing_id',$listing->id)
    //     // ->create(['listing_id' => $listing->id, 'image' => $v]);

    //}
    $listings = Listing::where('id', '!=', null)->update(['description' => '<h2><strong>Giới thiệu về chỗ ở này</strong></h2><p>[LƯU Ý: Không đặt chỗ chụp ảnh, tổ chức sự kiện, tiệc tùng hoặc các vật dụng bị cấm]</p><p>[CHÚ Ý: Không quay phim, chụp ảnh và tổ chức tiệc, hoặc chất cấm]</p><p><br></p><p>Căn hộ của chúng tôi là TRUNG TÂM, TUYỆT ĐẸP và RIÊNG TƯ.</p><p><br></p><p>Nằm ngay giữa đường D1, bạn chỉ cách tất cả những hành động thú vị và thú vị ở Sài Gòn một quãng đi bộ ngắn. Từ trung tâm mua sắm, nhà hàng đến quán bia và quán bar đêm, bạn có thể kể tên, chúng tôi có nó.</p><h3>Chỗ ở</h3><p>NHẬN/NHẬN/TRẢ PHÒNG SUÔN SẺ</p><p>Tận hưởng thời gian đến nhanh chóng và đơn giản vì bạn có thể tự nhận phòng tại căn hộ 24/7.</p><p><br></p><p>TIỆN NGHI và DỊCH VỤ</p><p>Căn hộ có:</p><p>- Khóa thông minh để dễ dàng tự nhận phòng 24/7</p><p>- Thang máy</p><p>- Nội thất &amp; trang trí</p><p>- Nhà bếp đầy đủ tiện nghi</p><p>- Truyền hình cáp</p><p>- Wifi tốc độ cao</p><p>- Đồ dùng thiết yếu cho phòng tắm</p><p>- Gối, nệm và ga trải giường tốt nhất</p><p>- Dịch vụ phòng miễn phí hai lần một tuần (đối với thời gian ở từ 3 đêm trở lên)</p><p>- Máy giặt và máy sấy trong phòng</p><p>- Phòng tập thể dục</p><p><br></p><p>Bạn sẽ thấy căn hộ của chúng tôi là chỗ ở hoàn hảo cho chuyến đi của mình, cho dù đó là kỳ nghỉ hay doanh nghiệp.</p><h3>Tiện nghi khách có quyền sử dụng</h3><p>Bạn có toàn quyền sử dụng:</p><p>- Toàn bộ căn hộ</p><p>- Bất kỳ khu vực công cộng nào của tòa nhà (sảnh chính, phòng tập thể dục, sân thượng ...)</p><h3>Những điều cần lưu ý khác</h3><p>- Nếu bạn đến sớm hơn nhận phòng, bạn có thể cất giữ hành lý của mình với chúng tôi.</p><p>- Xin lưu ý rằng chúng tôi không thể chấp nhận đặt phòng cho mục đích chụp ảnh.</p><p>- Chính sách không hút thuốc nghiêm ngặt trong căn hộ.</p><p>- Cần cung cấp giấy tờ tùy thân/Hộ chiếu để đăng ký theo quy định của địa phương.</p>']);
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
