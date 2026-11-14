<?php

namespace App\Http\Controllers;

use App\Exports\BaseExport;
use App\Exports\InventoryTransactionsExport;
use App\Exports\ProductsExport;
use App\Exports\PurchaseOrdersExport;
use App\Exports\SupplierPerformanceExport;
use App\Models\PurchaseOrder;
use App\Services\SearchService;
use App\Services\SupplierPerformanceService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function products(Request $request, SearchService $search, string $format)
    {
        return $this->download(new ProductsExport($search->products($request->query())->get()), $format);
    }

    public function purchaseOrders(Request $request, SearchService $search, string $format)
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        return $this->download(new PurchaseOrdersExport($search->purchaseOrders($request->query())->get()), $format);
    }

    public function transactions(Request $request, SearchService $search, string $format)
    {
        return $this->download(new InventoryTransactionsExport($search->transactions($request->query())->get()), $format);
    }

    public function supplierPerformance(SupplierPerformanceService $performance, string $format)
    {
        $this->authorize('view-reports');

        return $this->download(new SupplierPerformanceExport($performance->all()), $format);
    }

    private function download(BaseExport $export, string $format)
    {
        return $format === 'pdf' ? $export->toPdf() : $export->toCsv();
    }
}
