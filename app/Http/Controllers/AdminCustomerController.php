<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendMail;

class AdminCustomerController extends Controller
{
    function list()
    {
        $list_action = ['delete' => 'Xóa tạm thời', 'send_mail' => 'Gửi mail'];
        if (request()->status === 'trash') {
            $users = User::onlyTrashed()->where('role_id', null)->with('role')
                ->when(request()->search, function ($q) {
                    return $q->where('name', 'LIKE', '%' . request()->search . '%');
                })->orderBy("id", "DESC")->paginate(10);

            $users->appends(['status' => 'trash']); // page 2 continue status=trash
            $list_action = ['restore' => 'Khôi phục', 'force_delete' => 'Xóa vĩnh viễn'];
        } else if (request()->status === 'verify') {
            $users = User::where('role_id', null)->where('verify_account', 1)->with('role')
                ->when(request()->search, function ($q) {
                    return $q->where('name', 'LIKE', '%' . request()->search . '%');
                })->orderBy("id", "DESC")->paginate(10);

            $users->appends(['status' => 'verify']);
        } else if (request()->status === 'not-verify') {
            $users = User::where('role_id', null)->where('verify_account', null)->with('role')
                ->when(request()->search, function ($q) {
                    return $q->where('name', 'LIKE', '%' . request()->search . '%');
                })->orderBy("id", "DESC")->paginate(10);

            $users->appends(['status' => 'not-verify']);
        } else if (request()->status === 'refuse-verify') {
            $users = User::where('role_id', null)->where('verify_account', 2)->with('role')
                ->when(request()->search, function ($q) {
                    return $q->where('name', 'LIKE', '%' . request()->search . '%');
                })->orderBy("id", "DESC")->paginate(10);

            $users->appends(['status' => 'refuse-verify']);
        } else {
            $users = User::where('role_id', null)->with('role')
                ->when(request()->search, function ($q) {
                    return $q->where('name', 'LIKE', '%' . request()->search . '%');
                })->orderBy("id", "DESC")->paginate(10);

            $users->appends(['status' => 'active']);
        }
        $count_customer_active = User::where('role_id', null)->count();
        $count_customer_trash = User::where('role_id', null)->onlyTrashed()->count();
        $count_customer_verify = User::where('role_id', null)->where('verify_account', 1)->count();
        $count_customer_not_verify = User::where('role_id', null)->where('verify_account', null)->count();
        $count_customer_refuse_verify = User::where('role_id', null)->where('verify_account', 2)->count();

        $count = [$count_customer_active, $count_customer_trash, $count_customer_verify, $count_customer_not_verify, $count_customer_refuse_verify];
        return Inertia::render('User/CustomerList', ['users' => $users, 'list_action' => $list_action, 'count' => $count]);
    }

    function update()
    {
        Validator::make(request()->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^(\+84|84|0)[0-9]{9}$/'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'Tên không được trống',
            'phone.required' => 'SĐT phải là 10 số',
            'phone.regex' => 'SĐT không hợp lệ',
        ])->validate();

        $user = User::find(request()->id);
        $user->name = request()->name;
        $user->phone = request()->phone;
        $user->verify_account = request()->verify_account;
        if (request()->password) {
            $user->password = Hash::make(request()->password);
        }

        $user->save();
    }

    function delete($id)
    {
        $customer = User::withTrashed()->find($id);

        if ($customer->deleted_at) {
            $customer->forceDelete();
        } else {
            $customer->delete();
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
            } else if (request()->action == 'send_mail') {
                $users = User::whereIn('id', $list_check)->get();
                foreach ($users as $user) {
                     SendMail::dispatch($user);
                }
            }
        }
    }
}
