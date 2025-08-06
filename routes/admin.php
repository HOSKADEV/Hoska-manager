<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ClientsController;
use App\Http\Controllers\Admin\DevelopmentsController;
use App\Http\Controllers\Admin\EmployeesController;
use App\Http\Controllers\Admin\InvoicesController;
use App\Http\Controllers\Admin\KpiController;
use App\Http\Controllers\Admin\NotesController;
use App\Http\Controllers\Admin\PaymentsController;
use App\Http\Controllers\Admin\ProjectsController;
use App\Http\Controllers\Admin\TaskImportController;
use App\Http\Controllers\Admin\TasksController;
use App\Http\Controllers\Admin\TimesheetsController;
use App\Http\Controllers\Admin\WalletsController;
use App\Http\Controllers\Admin\WalletTransactionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Mails\VerficationEmailController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['isAuth'])->group(function () {

    // متاحة للموظف و الأدمن
    Route::get('/', [AdminController::class, 'index'])->name('index'); // Dashboard

    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::put('/profile', [AdminController::class, 'profile_save']);

    Route::resource('projects', ProjectsController::class);

    Route::resource('tasks', TasksController::class);
    Route::post('tasks/import', [TaskImportController::class, 'import'])->name('tasks.import');

    Route::resource('notes', NotesController::class);
});

Route::prefix('admin')->name('admin.')->middleware(['isAuth', 'IsAdminOrAccountant'])->group(function () {
    Route::get('wallets/{wallet}/balance', [WalletsController::class, 'getBalance'])->name('wallets.balance');
    Route::resource('wallet-transactions', WalletTransactionController::class);
});

Route::prefix('admin')->name('admin.')->middleware(['isAuth', 'IsAdminOrMarketer'])->group(function () {
    // مثلاً السماح للمسوق برؤية العملاء وإضافة عملاء جدد
    // Route::resource('clients', ClientsController::class)->only(['index', 'create', 'store']);
    Route::resource('clients', ClientsController::class);
});


Route::prefix('admin')->name('admin.')->middleware(['isAuth', 'IsAdmin'])->group(function () {

    Route::get('kpis', [KpiController::class, 'index'])->name('kpis.index');

    // Route::resource('clients', ClientsController::class);
    Route::get('clients/{client}/has-projects', [ClientsController::class, 'hasProjects'])->name('clients.hasProjects');
    Route::resource('employees', EmployeesController::class);
    Route::get('employees/{employee}/projects', [EmployeesController::class, 'projects']);
    // Route::resource('projects', ProjectsController::class);
    Route::resource('developments', DevelopmentsController::class);
    Route::get('developments/{id}/details', [DevelopmentsController::class, 'details']);

    Route::resource('invoices', InvoicesController::class);
    Route::get('invoices/{id}/info', [InvoicesController::class, 'info']);

    Route::get('invoices/{invoice}/export-excel', [InvoicesController::class, 'exportInvoiceExcel'])->name('invoices.exportExcel');


    Route::get('projects/{project}/financials', [InvoicesController::class, 'getProjectFinancials']);


    Route::resource('payments', PaymentsController::class);
    Route::resource('timesheets', TimesheetsController::class);
    Route::patch('/timesheets/{id}/mark-paid', [TimesheetsController::class, 'markPaid'])->name('timesheets.markPaid');
    Route::post('timesheets/export', [TimesheetsController::class, 'exportSelectedColumns'])->name('export.timesheet');

    Route::resource('wallets', WalletsController::class);
    // Route::get('wallets/{wallet}/balance', [WalletsController::class, 'getBalance'])->name('wallets.balance');
});


Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/signin', [AuthController::class, 'signin'])->name('signin');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/signup', [AuthController::class, 'signup'])->name('signup');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// otp
Route::get('verfactionemail/{token}', [VerficationEmailController::class, 'verficationemailpage'])->name('verficationemailpage');
Route::post('verify-email', [VerficationEmailController::class, 'verifyemail'])->name('verifyemail');
