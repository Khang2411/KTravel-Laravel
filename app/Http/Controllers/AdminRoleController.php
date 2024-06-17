<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class AdminRoleController extends Controller
{
    function list()
    {
        $roles = Role::with('permissions')->when(request()->search, function ($q) {
            return $q->where('name', 'LIKE', '%' . request()->search . '%');
        })->paginate(10);
        $roles->appends(['search' => request()->search]);
        $list_action = ['force_delete' => 'Xóa'];

        return Inertia::render('Role/RoleList', ['roles' => $roles, 'list_action' => $list_action]);
    }

    function add()
    {
        $permissions = Permission::all();
        return Inertia::render('Role/RoleAdd', ['permissions' => $permissions]);
    }

    function store()
    {
        Validator::make(
            request()->all(),
            [
                'name' =>  'required|unique:roles,name',
                'description' => 'required',
            ],
            [
                'name.required' => 'Tên là bắt buộc',
                'name.unique' => 'Tên đã tồn tại',
                'description.required' => 'Mô tả là bắt buộc',
            ]
        )->validate();
        $role = Role::create(
            [
                'name' => request()->name,
                'description' => request()->description
            ]
        );
        $role->permissions()->attach(request()->list_permission);
        return to_route('admin.role.list');
    }

    function edit($id)
    {
        $role = Role::find($id);
        $role->permissions;

        $permissions = Permission::all();
        return Inertia::render('Role/RoleEdit', ['role' => $role, 'permissions' => $permissions]);
    }

    function update($id)
    {
        Validator::make(
            request()->all(),
            [
                'name' => 'required|unique:roles,name,' . $id,
                'description' => 'required',
            ],
            [
                'name.required' => 'Tên là bắt buộc',
                'name.unique' => 'Tên đã tồn tại',
                'description.required' => 'Mô tả là bắt buộc',
            ]
        )->validate();
        $role = Role::find($id);
        $role->name = request()->name;
        $role->description = request()->description;

        $role->permissions()->sync(request()->list_permission);
        $role->save();

        $page = request()->back_to;
        return redirect('/admin/role/list?page=' . $page);
    }

    function delete($id)
    {
        Role::destroy($id);
    }

    function action()
    {
        $list_check = request()->list_check;
        Role::destroy($list_check);
        return redirect()->back()->with(['status' => 'Xóa thành công']);
    }
}
