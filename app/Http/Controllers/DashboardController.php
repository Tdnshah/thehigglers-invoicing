<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Base query depending on user role
        $query = Invoice::query();

        if ($user->isCompanyAdmin()) {
            $query->where("user_id", $user->id);
        } elseif ($user->isClientUser()) {
            $query->where("client_id", $user->client_id);
        } else {
            // Should not happen, but return empty
             return view("dashboard", [
                "statusCounts" => [],
                "outstanding" => [],
                "earnings" => [],
                "recentInvoices" => []
            ]);
        }

        // 1. Status Counts
        $statusCounts = (clone $query)
            ->select("status", DB::raw("count(*) as count"))
            ->groupBy("status")
            ->pluck("count", "status")
            ->toArray();

        // 2. Outstanding Amounts (Sent + Overdue) Grouped by Currency
        $outstanding = (clone $query)
            ->whereIn("status", ["sent", "overdue"])
            ->select("currency", DB::raw("sum(total) as total_amount"))
            ->groupBy("currency")
            ->get();

        // 3. Total Earnings (Paid) Grouped by Currency
        $earnings = (clone $query)
            ->where("status", "paid")
            ->select("currency", DB::raw("sum(total) as total_amount"))
            ->groupBy("currency")
            ->get();

        // 4. Recent Invoices
        $recentInvoices = (clone $query)
            ->with("client")
            ->latest()
            ->take(5)
            ->get();

        return view("dashboard", compact("statusCounts", "outstanding", "earnings", "recentInvoices"));
    }
}
