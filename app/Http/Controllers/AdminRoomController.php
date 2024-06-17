<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Cloudinary\Api\Upload\UploadApi;

class AdminRoomController extends Controller
{
    function list()
    {
        $list_action = ['delete' => 'Xóa tạm thời'];
        switch (request()->status) {
            case ('trash'):
                $products = Listing::onlyTrashed()->where('status', 'confirm')
                    ->join('categories', 'categories.id', '=', 'listings.category_id')->with('images')
                    ->select('listings.*')->when(request()->search, function ($q) {
                        return $q->where('listings.name', 'LIKE', '%' . request()->search . '%');
                    })->orderBy("id", "DESC")->paginate(10);
                $products->appends(['status' => 'trash', 'search' => request()->search]); // khi nhấn qua page 2 vẫn ở status=trash
                $list_action = ['restore' => 'Khôi phục', 'force_delete' => 'Xóa vĩnh viễn'];
                break;
            default:
                $products = Listing::where('status', 'confirm')->join('categories', 'categories.id', '=', 'listings.category_id')
                    ->select('listings.*', 'categories.name as cate_name')->with('images')->when(request()->search, function ($q) {
                        return $q->where('listings.name', 'LIKE', '%' . request()->search . '%');
                    })->orderBy("id", "DESC")->paginate(10);
                $products->appends(['status' => 'active', 'search' => request()->search]);
                break;
        }
        $count_order_active = Listing::count();
        $count_order_trash = Listing::onlyTrashed()->count();

        $count = [$count_order_active, $count_order_trash];

        return Inertia::render('Room/RoomList', ['products' => $products, 'list_action' => $list_action, 'count' => $count]);
    }

    function delete($id)
    {
        $product = Listing::withTrashed()->find($id);

        if ($product->deleted_at) {
            if ($product->public_id_thumbnail !== null) {
                (new UploadApi())->destroy($product->public_id_thumbnail);
            }
            $product->forceDelete();
        } else {
            $product->delete();
        }
    }

    function action()
    {
        $list_check = request()->list_check; // $list_check là 1 mảng có mảng thì phải duyệt
       
        if (!$list_check) {
            return to_route('admin.room.list')->withErrors(['error' => 'Bạn cần chọn phần tử để thực hiện']);
        } else {
            switch (request()->action) {
                case 'delete':
                    Listing::destroy($list_check);
                    return redirect()->back()->with(['status' => 'Xóa thành công']);
                    break;
                case 'restore':
                    Listing::whereIn('id', $list_check)->restore();
                    return redirect()->back()->with(['status' => 'Khôi phục thành công']);
                    break;
                case 'force_delete':
                    $products = Listing::withTrashed()->whereIn('id', $list_check)->with('detailImages');
                    foreach ($products->get() as $product) {
                        if ($product->public_id_thumbnail !== null) {
                            (new UploadApi())->destroy($product->public_id_thumbnail);
                        }

                        foreach ($product->detailImages as $detailImage) {
                            if ($detailImage->public_id_image !== null) {
                                (new UploadApi())->destroy($detailImage->public_id_image);
                            }
                            ListingImage::destroy($detailImage->id);
                        }
                    }
                    $products->forceDelete();
                    return redirect()->back()->with(['status' => 'Xóa vĩnh viễn thành công']);
                    break;
                default:
                    return redirect()->back()->withErrors(['error' => 'Chưa chọn tác vụ nào']);
            }
        }
    }
}
