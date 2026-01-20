<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;
use Carbon\Carbon;

class AdminController extends Controller
{
    //
    public function userChart(Request $request)
    {
        // يمكن أخذ السنة والشهر من query params أو افتراضيًا الشهر الحالي
        $year = $request->query('year', Carbon::now()->year);
        $month = $request->query('month', Carbon::now()->month);

        $data = $this->getUserCountsByMonth($year, $month);

        return response()->json($data);
    }
    public function showUsers() {}
    public function showOrders(Request $request)
    {
        // السنة والشهر من query params أو افتراضيًا الشهر الحالي
        $year = $request->query('year', Carbon::now()->year);
        $month = $request->query('month', Carbon::now()->month);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $totalDays = $startOfMonth->diffInDays($endOfMonth) + 1;
        $periodLength = ceil($totalDays / 6);

        $results = [];

        for ($i = 0; $i < 6; $i++) {
            $periodStart = $startOfMonth->copy()->addDays($i * $periodLength);
            $periodEnd = $periodStart->copy()->addDays($periodLength - 1);
            if ($periodEnd->gt($endOfMonth)) {
                $periodEnd = $endOfMonth->copy();
            }

            $count = Order::whereBetween('created_at', [$periodStart, $periodEnd])->count();

            $results[] = [
                'label' => $periodStart->format('d-m') . ' - ' . $periodEnd->format('d-m'),
                'count' => $count
            ];
        }

        return response()->json($results);
    }
    public function showProducts() {}
    function getUserCountsByMonth($year, $month)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $totalDays = $startOfMonth->diffInDays($endOfMonth) + 1;
        $periodLength = ceil($totalDays / 6); // طول كل فترة

        $results = [];

        for ($i = 0; $i < 6; $i++) {
            $periodStart = $startOfMonth->copy()->addDays($i * $periodLength);
            $periodEnd = $periodStart->copy()->addDays($periodLength - 1);
            if ($periodEnd->gt($endOfMonth)) {
                $periodEnd = $endOfMonth->copy();
            }

            $count = User::whereBetween('created_at', [$periodStart, $periodEnd])->count();

            $results[] = [
                'label' => $periodStart->format('d-m') . ' - ' . $periodEnd->format('d-m'),
                'count' => $count
            ];
        }

        return $results;
    }
}
