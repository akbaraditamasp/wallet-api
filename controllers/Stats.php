<?php

namespace Controller;

use Carbon\Carbon;
use Illuminate\Database\Query\Expression as raw;
use Lego\App;
use Model\Transaction;
use Model\User;

class Stats
{
    public static function month($month)
    {
        switch ($month) {
            case 1:
                return "Januari";
            case 2:
                return "Februari";
            case 3:
                return "Maret";
            case 4:
                return "April";
            case 5:
                return "Mei";
            case 6:
                return "Juni";
            case 7:
                return "Juli";
            case 8:
                return "Agustus";
            case 9:
                return "September";
            case 10:
                return "Oktober";
            case 11:
                return "November";
            default:
                return "Desember";
        }
    }

    public static function index(App $app)
    {
        $app->admin();
        $usersCount = User::all()->count();
        $transactionsCount = Transaction::where("status", "success")->where("created_at", ">", Carbon::now()->toDateString())->count();
        $totalSaldo = User::select(new raw('SUM(balance) as total'))->get();

        return [
            "users" => $usersCount,
            "transactions" => $transactionsCount,
            "balances" => $totalSaldo ? (int) $totalSaldo[0]["total"] : 0
        ];
    }

    public static function transaction(App $app)
    {
        $app->admin();

        $data = [];

        $start = Carbon::now()->subMonth(11);
        $startMonth = $start->month;

        $transactions = Transaction::where("status", "success")->whereDate("created_at", ">=", $start->startOfMonth()->toDateString())
            ->select(new raw('COUNT(id) as total'), new raw('MONTH(created_at) as month'))->groupBy("month")->get()->toArray();

        for ($i = 0; $i < 12; $i++) {
            $month = ((int) $startMonth) + $i;
            if ($month > 12) {
                $month = $month - 12;
            }

            $value = array_values(array_filter($transactions, function ($data) use ($month) {
                return $data["month"] == $month;
            }));
            $data[] = [
                "label" => static::month($month),
                "data" => count($value) ? (int) $value[0]["total"] : 0
            ];
        }

        return $data;
    }
}
