<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    function list()
    {
        $list_action = ['delete' => 'Xóa tạm thời'];
        if (request()->status === 'trash') {
            $users = User::onlyTrashed()->where('role_id', '!=', null)->when(request()->search, function ($q) {
                return $q->where('name', 'LIKE', '%' . request()->search . '%');
            })->with('role')->paginate(10);

            $users->appends(['status' => 'trash']); // khi nhấn qua page 2 vẫn ở status=trash
            $list_action = ['restore' => 'Khôi phục', 'force_delete' => 'Xóa vĩnh viễn'];
        } else {
            $users = User::where('role_id', '!=', null)->when(request()->search, function ($q) {
                return $q->where('name', 'LIKE', '%' . request()->search . '%');
            })->with('role')->paginate(10);
            $users->appends(['status' => 'active']);
        }
        $count_order_active = User::where('role_id', '!=', null)->count();
        $count_order_trash = User::where('role_id', '!=', null)->onlyTrashed()->count();
        $count = [$count_order_active, $count_order_trash];
        $roles = Role::all();
        return Inertia::render('User/UserList', ['users' => $users, 'list_action' => $list_action, 'roles' => $roles, 'count' => $count]);
    }

    function add()
    {
        $roles = Role::all();
        return Inertia::render('User/UserAdd', ['roles' => $roles]);
    }

    function store()
    {
        request()->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['required', 'regex:/^(\+84|84|0)[0-9]{9}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => 'required'
        ], [
            'name.required' => 'Tên là bắt buộc',
            'email.required' => 'Email là bắt buộc',
            'phone.required' => 'SĐT phải là 10 số',
            'phone.regex' => 'SĐT không hợp lệ',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min' => 'Mật khẩu tối thiều 8 ký tự',
            'password.confirmed' => 'Nhập lại mật khẩu không chính xác',
            'role_id.required' => 'Cấp quyền là bắt buộc'
        ]);

        $user = User::create([
            'name' => request()->name,
            'email' => request()->email,
            'phone' => request()->phone,
            'password' => Hash::make(request()->password),
            'role_id' => request()->role_id
        ]);
        event(new Registered($user));
        return to_route('admin.user.list');
    }

    function update()
    {
        Validator::make(request()->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^(\+84|84|0)[0-9]{9}$/'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role_id' => 'required'
        ], [
            'name.required' => 'Tên không được trống',
            'phone.required' => 'SĐT phải là 10 số',
            'phone.regex' => 'SĐT không hợp lệ',
            'password.confirmed' => 'Nhập lại mật khẩu không chính xác',
            'role_id.required' => 'Phải cấp quyền'
        ])->validate();

        $user = User::find(request()->id);
        $user->name = request()->name;
        $user->phone = request()->phone;
        $user->role_id = request()->role_id;
        if (request()->password) {
            $user->password = Hash::make(request()->password);
        }

        $user->save();
    }

    function delete($id)
    {
        $user = User::withTrashed()->find($id);

        if ($user->deleted_at) {
            $user->forceDelete();
        } else {
            $user->delete();
        }
    }

    function action()
    {
        $list_check = request()->list_check; // $list_check là 1 mảng có mảng thì phải duyệt
        if (!$list_check) {
            return redirect()->back()->withErrors(['error' => 'Bạn cần chọn phần tử để thực hiện']);
        }
        foreach ($list_check as $key => $values) { // lấy $key is index   
            if ($values == Auth::id()) {
                unset($list_check[$key]);
                return redirect()->back()->withErrors(['error' => 'Không thể thao tác trên chính bản thân']);
            } else if (request()->action == 'delete') {
                User::destroy($list_check);
                return redirect()->back()->with(['success' => 'Xóa thành công']);
            } else if (request()->action == 'restore') {
                User::wherein('id', $list_check)->restore(); // where in là dk chứa mảng
                return redirect()->back()->with(['success' => 'Khôi phục thành công']);
            } else if (request()->action == 'force_delete') {
                User::withTrashed()->wherein('id', $list_check)->forceDelete();
                return redirect()->back()->with(['success' => 'Xóa vĩnh viễn thành công']);
            } else if (request()->action == '') {
                return redirect()->back()->withErrors(['error' => 'Chưa chọn tác vụ nào']);
            }
        }
    }
}
