<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    function list()
    {
        $list_action = ['delete' => 'Xóa tạm thời'];
        switch (request()->status) {
            case ('trash'):
                $categories = Category::onlyTrashed()
                    ->when(request()->search, function ($q) {
                        return $q->where('name', 'LIKE', '%' . request()->search . '%');
                    })->orderBy("id", "DESC")->paginate(10);

                $categories->appends(['status' => 'trash', 'search' => request()->search]); // khi nhấn qua page 2 vẫn ở status=trash
                $list_action = ['restore' => 'Khôi phục', 'force_delete' => 'Xóa vĩnh viễn'];
                break;
            default:
                $categories = Category::when(request()->search, function ($q) {
                    return $q->where('name', 'LIKE', '%' . request()->search . '%');
                })->orderBy("id", "DESC")->paginate(10);

                $categories->appends(['status' => 'active', 'search' => request()->search]);
                break;
        }
        $count_order_active = Category::count();
        $count_order_trash = Category::onlyTrashed()->count();
        $count = [$count_order_active, $count_order_trash];

        return Inertia::render('Category/CategoryList', ['categories' => $categories, 'list_action' => $list_action, 'count' => $count]);
    }

    function add()
    {
        return Inertia::render('Category/CategoryAdd');
    }

    function store()
    {
        $input = request()->all();
        Validator::make(
            $input,
            [
                'name' => 'required',
                'icon' => 'required',
            ],
            [
                'name.required' => 'Tên thể loại là bắt buộc',
                'icon.required' => 'Ảnh thể loại là bắt buộc',
            ]
        )->validate();

        if (request()->hasFile('icon')) {
            $icon = (new UploadApi())->upload($_FILES['icon']['tmp_name'], [
                'folder' => 'ktravel/category',
                'quality' => '80',
            ]);
            $input['icon'] = $icon['secure_url'];
            $input['public_id_icon'] = $icon['public_id'];
        }
    
        $input['slug'] = Str::slug($input['name']); // vẫn thêm slug vô dc mảng input dù view kh có name 
        Category::create($input);

        return to_route('admin.category.list');
    }
    function update()
    {
        Validator::make(
            request()->all(),
            [
                'name' => 'required',
                'icon' => 'required',
            ],
            [
                'name.required' => 'Tên thể loại là bắt buộc',
                'icon.required' => 'Ảnh thể loại là bắt buộc',
            ]
        )->validate();
      
        $category = Category::find(request()->id);
        $category->name = request()->name;
       
        if (request()->hasFile('icon')) {
            $icon = (new UploadApi())->upload($_FILES['icon']['tmp_name'], [
                'folder' => 'ktravel/category',
                'quality' => '80',
            ]);
            $input['icon'] = $icon['secure_url'];
            if ($category->public_id_icon !== null) {
                (new UploadApi())->destroy($category->public_id_icon);
            }
            $category->icon =  $input['icon'];
            $category->public_id_icon = $icon['public_id'];
        }
        $category->save();
    }

    function delete($id)
    {
        $category = Category::withTrashed()->find($id);

        if ($category->deleted_at) {
            if ( $category->public_id_icon !== null) {
                (new UploadApi())->destroy($category->public_id_icon);
            }
            $category->forceDelete();
        } else {
            $category->delete();
        }
    }

    function action()
    {
        $list_check = request()->list_check;
        if (!$list_check) {
            return redirect()->back()->withErrors(['error' => 'Bạn cần chọn phần tử để thực hiện']);
        } else {
            switch (request()->action) {
                case 'delete':
                    Category::destroy($list_check);
                    return redirect()->back()->with(['success' => 'Xóa thành công']);
                    break;
                case 'restore':
                    Category::whereIn('id', $list_check)->restore();
                    return redirect()->back()->with(['success' => 'Khôi phục thành công']);
                    break;
                case 'force_delete':
                    $categories = Category::withTrashed()->whereIn('id', $list_check);
                    foreach ($categories->get() as $category) {
                        if ($category->public_id_icon !== null) {
                            (new UploadApi())->destroy($category->public_id_icon);
                        }
                    }
                    $categories->forceDelete();
                    return redirect()->back()->with(['success' => 'Xóa vĩnh viễn thành công']);
                    break;
                default:
                    return redirect()->back()->withErrors(['error' => 'Chưa chọn tác vụ nào']);
            }
        }
    }
}
