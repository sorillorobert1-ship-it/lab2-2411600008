<?php

namespace App\Http\Controllers;

use App\Services\SupplierPerformanceService;

class ReportController extends Controller
{
    public function supplierPerformance(SupplierPerformanceService $performance)
    {
        $this->authorize('view-reports');

        $metrics = $performance->all();
        $withDeliveries = $metrics->where('delivered_orders', '>', 0)->values();

        return view('reports.supplier-performance', [
            'metrics' => $metrics,
            'topSuppliers' => $withDeliveries->take(3),
            'bottomSuppliers' => $withDeliveries->reverse()->take(3)->values(),
            'trend' => $performance->monthlyDeliveryTrend(),
        ]);
    }
}
