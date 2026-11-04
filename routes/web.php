<?php

use Illuminate\Support\Facades\Route;
use App\Models\{Product,PurchaseOrder,Supplier};
use App\Http\Controllers\{SupplierController,PurchaseOrderController};
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Auth;
use App\Services\ReplenishmentService;
use App\Mail\PurchaseOrderStatusMail;

Route::get('/', function () { return view('dashboard', ['products'=>Product::where('is_active',true)->get(), 'supplierCount'=>Supplier::where('is_active',true)->count(), 'pendingOrders'=>PurchaseOrder::whereIn('status',['draft','pending_approval'])->count(), 'orderValue'=>PurchaseOrder::whereIn('status',['approved','ordered'])->sum('total')]); })->name('dashboard');
Route::middleware('auth')->group(function () { Route::resource('suppliers', SupplierController::class); });
Route::get('/inventory',[InventoryController::class,'index'])->name('inventory.index');
Route::post('/inventory/movements',[InventoryController::class,'store'])->name('inventory.store');
Route::get('/api/inventory/live',[InventoryController::class,'api'])->name('inventory.api');
Route::middleware('auth')->group(function () { Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index','create','store','show']); });
Route::post('purchase-orders/{purchaseOrder}/approve',[PurchaseOrderController::class,'approve'])->middleware('auth')->name('purchase-orders.approve');
Route::post('purchase-orders/{purchaseOrder}/receive',[PurchaseOrderController::class,'receive'])->middleware('auth')->name('purchase-orders.receive');
Route::post('/replenishment/check', function (ReplenishmentService $service) { $created=0; Product::with('suppliers')->where('is_active',true)->each(function($product) use ($service,&$created){ if($service->generateFor($product)) $created++; }); return redirect()->route('dashboard')->with('success',"Automatic reorder check complete: {$created} purchase order(s) created or already pending."); })->name('replenishment.check');
Route::get('/supplier-performance', function () { $suppliers=Supplier::withCount('products')->with(['purchaseOrders'])->where('is_active',true)->orderByDesc('reliability_score')->get(); return view('suppliers.performance', compact('suppliers')); })->name('suppliers.performance');
Route::get('/email-preview', function () { $order=PurchaseOrder::with('supplier')->latest()->firstOrFail(); return (new PurchaseOrderStatusMail($order))->render(); })->name('email.preview');
Route::get('/login', fn () => redirect()->route('demo-login'))->name('login');
Route::get('/demo-login', function(){ $user=\App\Models\User::firstOrCreate(['email'=>'manager@example.com'],['name'=>'Lab Manager','password'=>'password','role'=>'manager']); Auth::login($user); return redirect()->route('dashboard'); })->name('demo-login');
Route::post('/logout', function(){ Auth::logout(); return redirect()->route('dashboard'); })->name('logout');
Route::get('/email-inbox', function () { $order=PurchaseOrder::with('supplier')->latest()->firstOrFail(); return view('emails.inbox', ['order'=>$order, 'mailBody'=>(new PurchaseOrderStatusMail($order))->render()]); })->name('email.inbox');
Route::get('/email-send', function () { $order=PurchaseOrder::with('supplier')->latest()->firstOrFail(); \Illuminate\Support\Facades\Mail::to(env('MAIL_TO','manager@example.com'))->send(new PurchaseOrderStatusMail($order)); return 'Email sent to Mailtrap test inbox. Open your Mailtrap Inbox to view it.'; })->name('email.send');
