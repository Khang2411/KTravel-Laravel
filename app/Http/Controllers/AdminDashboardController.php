<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminDashboardController extends Controller
{
    function index()
    {
        $yearNow = Carbon::now()->year;
        $sumTotal = 0;
        $ordersByMonth = Order::with('listing', 'user')->where('updated_at', '>=',  now()->subMonth())->where('status', 'completed')->get();

        foreach ($ordersByMonth as $order) {
            $sumTotal += $order['price'] * 0.2;
        }

        // Revenue By Months Year Now
        $sumRevenue = 0;
        for ($i = 1; $i <= 12; $i++) {
            $orders = Order::whereYear('updated_at', $yearNow)
                ->whereMonth('updated_at', $i)
                ->where('status', '!=', 'cancelled')
                ->get();
            foreach ($orders as $order) {
                $sumRevenue += $order['price'] * 0.2;
            }

            $revenueByMonths[] = $orders->count() === 0 ? 0 : $sumRevenue;
            $sumRevenue = 0;
        }


        // Count order By Month
        for ($i = 1; $i <= 12; $i++) {
            $orderByMonths[] = Order::whereYear('updated_at', $yearNow)->whereMonth('updated_at', $i)->count();
        };

        $products = Listing::where('updated_at', '>=',  now()->subMonth())->get();

        $users = User::where('updated_at', '>=',  now()->subMonth())->get();

        return Inertia::render('Dashboard', [
            'orders' => $ordersByMonth, 'order_number' => count($ordersByMonth),
            'revenueByMonths' => $revenueByMonths, 'orderByMonths' => $orderByMonths, 'sumTotal' => $sumTotal, 'product_number' => count($products), 'user_number' => count($users)
        ]);
    }

    function lineChart($xValues, $yValues)
    {
        $chart = "{
            type: 'line',
            data: {
              labels: $xValues,
              datasets: [{
                fill: false,
                lineTension: 0,
                backgroundColor: 'rgba(0,0,255,1.0)',
                borderColor: 'rgba(0,0,255,0.1)',
                data: [$yValues]
              }]
            },
            options: {
              legend: {display: false},
              scales: {
                yAxes: [
                    {
                        ticks: { 
                        callback: function(value, index, values) {
                        return value.toLocaleString('vi',{style:'currency', currency:'VND'})}}
                    }],
              }
            }
          }";
        return urlencode($chart);
    }

    function barChart($xValues, $yValues)
    {
        $chart = "{
            type: 'bar',
            data: {
              labels: [$xValues],
              datasets: [{
                data: [$yValues]
              }]
            },
            options: {
              legend: {display: false},
              scales: {
                yAxes: [
                    {
                        ticks: {precision: 0}
                    }],
              }
            }
          }";
        return urlencode($chart);
    }

    function pdf()
    {
        // Count orders by month
        $yearNow = Carbon::now()->year;
        $oneYearAgo = Carbon::now()->year - 1;
        $twoYearAgo = Carbon::now()->year - 2;
        $years = [$yearNow, $oneYearAgo, $twoYearAgo];
        $ordersByMonth = [];
        $orderByYears = [];

        foreach ($years as $year) {
            $orderByYears[] = Order::whereYear('updated_at', $year)->count();
        };

        // Revenue By Months Year Now
        $sumRevenue = 0;
        for ($i = 1; $i <= 12; $i++) {
            $orders = Order::whereYear('updated_at', $yearNow)->whereMonth('updated_at', $i)->where('status', 'completed')->get();

            foreach ($orders as $order) {
                $sumRevenue += $order['price'] * 0.2;
            }

            $revenueByMonths[] = $orders->count() === 0 ? 0 : $sumRevenue;
            $sumRevenue = 0;
        }

        // Revenue by years
        $revenueByYears = [];
        $sumRevenueYear = 0;
        foreach ($years as $year) {
            $orders = Order::whereYear('updated_at', $year)->where('status', 'completed')->get();
            foreach ($orders as $order) {
                $sumRevenueYear += $order['price'] * 0.2;
            }
            $revenueByYears[] = $orders->count() === 0 ? 0 : $sumRevenueYear;
            $sumRevenueYear = 0;
        }

        // return $orderByYears;
        $lineChart = $this->lineChart('[1,2,3,4,5,6,7,8,9,10,11,12]', implode(',', $revenueByMonths));
        $barChart = $this->barChart(implode(',', $years), implode(',', $orderByYears));
        $pdf = Pdf::loadView('statistic', compact('yearNow', 'ordersByMonth', 'revenueByMonths', 'revenueByYears', 'lineChart', 'barChart'));
        return $pdf->download('statistic.pdf');
    }
}
