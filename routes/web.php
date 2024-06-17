<?php

use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminRoomController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
   
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/statistic/pdf', [AdminDashboardController::class, 'pdf'])->name('admin.statistic.pdf');

    Route::middleware('can:module.category')->group(function () {
        Route::get('admin/category/add', [AdminCategoryController::class, 'add'])->name('admin.category.add.view');
        Route::post('admin/category/store', [AdminCategoryController::class, 'store'])->name('admin.category.store');
        Route::get('admin/category/list', [AdminCategoryController::class, 'list'])->name('admin.category.list');
        Route::post('admin/category/update', [AdminCategoryController::class, 'update'])->name('admin.category.update');
        Route::post('admin/category/action', [AdminCategoryController::class, 'action'])->name('admin.category.action');
        Route::post('admin/category/delete/{id}', [AdminCategoryController::class, 'delete'])->name('admin.category.delete');
    });

    Route::middleware('can:module.users')->group(function () {
        Route::get('admin/user/add', [AdminUserController::class, 'add'])->name('admin.user.add.view');
        Route::post('admin/user/store', [AdminUserController::class, 'store'])->name('admin.user.store');
        Route::get('admin/user/list', [AdminUserController::class, 'list'])->name('admin.user.list');
        Route::post('admin/user/update', [AdminUserController::class, 'update'])->name('admin.user.update');
        Route::post('admin/user/action', [AdminUserController::class, 'action'])->name('admin.user.action');
        Route::post('admin/user/delete/{id}', [AdminUserController::class, 'delete'])->name('admin.user.delete');
    });

    Route::middleware('can:module.customers')->group(function () {
        Route::post('admin/customer/store', [AdminCustomerController::class, 'store'])->name('admin.customer.store');
        Route::get('admin/customer/list', [AdminCustomerController::class, 'list'])->name('admin.customer.list');
        Route::post('admin/customer/update', [AdminCustomerController::class, 'update'])->name('admin.customer.update');
        Route::post('admin/customer/action', [AdminCustomerController::class, 'action'])->name('admin.customer.action');
        Route::post('admin/customer/delete/{id}', [AdminCustomerController::class, 'delete'])->name('admin.customer.delete');
    });

    Route::middleware('can:module.roles')->group(function () {
        Route::get('admin/role/add', [AdminRoleController::class, 'add'])->name('admin.role.add.view');
        Route::post('admin/role/store', [AdminRoleController::class, 'store'])->name('admin.role.store');
        Route::get('admin/role/list', [AdminRoleController::class, 'list'])->name('admin.role.list');
        Route::get('admin/role/edit/{id}', [AdminRoleController::class, 'edit'])->name('admin.role.edit');
        Route::post('admin/role/update/{id}', [AdminRoleController::class, 'update'])->name('admin.role.update');
        Route::post('admin/role/delete/{id}', [AdminRoleController::class, 'delete'])->name('admin.role.delete');
        Route::post('admin/role/action', [AdminRoleController::class, 'action'])->name('admin.role.action');
    });

    Route::middleware('can:module.rooms')->group(function () {
        Route::get('admin/room/list', [AdminRoomController::class, 'list'])->name('admin.room.list');
        Route::get('admin/room/edit/{id}', [AdminRoomController::class, 'edit'])->name('admin.room.edit');
        Route::post('admin/room/update/{id}', [AdminRoomController::class, 'update'])->name('admin.room.update');
        Route::post('admin/room/delete/{id}', [AdminRoomController::class, 'delete'])->name('admin.room.delete');
        Route::post('admin/room/action', [AdminRoomController::class, 'action'])->name('admin.room.action');
    });

    Route::middleware('can:module.payments')->group(function () {
        Route::get('admin/payment/list', [AdminPaymentController::class, 'list'])->name('admin.payment.list');
        Route::post('admin/payment/transfer', [AdminPaymentController::class, 'transfer'])->name('admin.payment.transfer');
        Route::get('admin/payment/success', [AdminPaymentController::class, 'success'])->name('admin.payment.success');
        Route::get('admin/payment/cancel', [AdminPaymentController::class, 'cancel'])->name('admin.payment.cancel');
    });
});

require __DIR__ . '/auth.php';
