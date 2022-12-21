<?php

namespace Controller;

use Carbon\Carbon;
use Lego\App;
use Lego\CreateUnique;
use Model\Transaction as ModelTransaction;
use Model\User;
use Model\Withdraw;

class Transaction
{
    public static function topup(App $app)
    {
        $app->auth();

        $app->validate([
            "amount" => "required|numeric|min:15K",
        ]);

        $invoice_id = CreateUnique::create(ModelTransaction::class, "invoice_id");

        $transaction = new ModelTransaction();
        $transaction->user_id = $app->user->user->id;
        $transaction->type = "in";
        $transaction->title = "TOP UP SALDO";
        $transaction->amount = $app->request->post("amount");
        $transaction->invoice_id = $invoice_id;
        $transaction->link = $app->payment->createInvoice([
            'external_id' => $invoice_id,
            'amount' => $app->request->post("amount"),
            'description' => 'TOP UP SALDO',
            'customer' => [
                'given_names' => (explode(" ", $app->user->user->name))[0],
            ],
            'currency' => 'IDR',
        ]);
        $transaction->status = "pending";

        $transaction->save();

        return $transaction;
    }

    public static function callback(App $app)
    {
        if ($app->request->post("merchant_name") == "Xendit") {
            echo "Hello Xendit!";
            exit();
        }

        $token = $app->request->getHeader("x-callback-token") ?? "";

        if ($token !== $_ENV["XENDIT_CALLBACK_TOKEN"]) {
            $app->response->setStatus(401);

            return [
                "error" => "Wrong token",
            ];
        }

        $app->validate([
            "id" => "required",
        ]);

        $getInvoice = $app->payment->getInvoice($app->request->post("id"));

        $transaction = ModelTransaction::where("invoice_id", $getInvoice["external_id"])->firstOrFail();

        if ($transaction->status === "success") {
            $app->response->setStatus(404);
            return [
                "error" => "Transaction has been succeed",
            ];
        } else if ($getInvoice["status"] === "SETTLED" || $getInvoice["status"] === "PAID") {
            $transaction->status = "success";
            $transaction->user->balance = $transaction->user->balance + $transaction->amount;
        } else if ($getInvoice["status"] === "EXPIRED") {
            $transaction->status = "failed";
        }

        $transaction->save();
        $transaction->user->save();

        return $transaction->makeHidden("user")->toArray();
    }

    public static function send(App $app)
    {
        $app->auth();

        $app->validate([
            "receiver_id" => "required|numeric",
            "amount" => "required|numeric",
            "pin" => "required",
        ]);

        $app->verifPin($app->request->post("pin"));

        $receiver = User::findOrFail($app->request->post("receiver_id"));
        $amount = $app->request->post("amount");

        if ($receiver->id === $app->user->user->id) {
            $app->response->setStatus(400);
            return [
                "error" => "Receiver is not allowed",
            ];
        } else if ($amount > $app->user->user->balance) {
            $app->response->setStatus(400);
            return [
                "error" => "Amount is bigger than balance",
            ];
        }

        $invoice_id = CreateUnique::create(ModelTransaction::class, "invoice_id");

        $dataForReceiver = [
            "user_id" => $receiver->id,
            "title" => "TERIMA DARI " . strtoupper($app->user->user->name),
            "note" => $app->request->post("note"),
            "type" => "in",
            "amount" => $amount,
            "invoice_id" => $invoice_id,
            "status" => "success",
        ];

        $dataForSender = [
            "user_id" => $app->user->user->id,
            "title" => "KIRIM KE " . strtoupper($receiver->name),
            "note" => $app->request->post("note"),
            "type" => "out",
            "amount" => $amount,
            "invoice_id" => $invoice_id,
            "status" => "success",
        ];

        try {
            $sent = new ModelTransaction($dataForSender);
            $received = new ModelTransaction($dataForReceiver);
            $app->user->user->balance = $app->user->user->balance - $amount;
            $receiver->balance = $receiver->balance + $amount;

            $sent->save();
            $received->save();
            $app->user->user->save();
            $receiver->save();
        } catch (\Throwable $e) {
            $app->response->setStatus(500);
            return [
                "error" => "Something went wrong",
            ];
        }

        return $sent;
    }

    public static function index(App $app)
    {
        $app->auth(false);

        if (!isset($app->user)) {
            $app->admin();
        }

        $status = $app->request->get("status", "success");
        $limit = $app->request->get("limit", 5);
        $q = $app->request->get("q", "");

        $offset = (((int) $app->request->get("page", 1)) - 1) * $limit;

        if (isset($app->user)) {
            $transactions = $app->user->user->transactions()->where("status", $status)->orderBy("created_at", "desc");
        } else {
            $transactions = ModelTransaction::whereHas("user", function ($query) use ($q) {
                return $query->where('username', 'LIKE', "%$q%");
            })->with(["user:id,username,name,created_at,updated_at"])->orderBy("created_at", "desc");
        }
        return [
            "page_total" => ceil($transactions->count() / $limit),
            "data" => $transactions->skip($offset)->take($limit)->get()->toArray(),
        ];
    }

    public static function get(App $app)
    {
        $app->auth();

        $transaction = $app->user->user->transactions()->findOrFail($app->params[0]);

        return $transaction->toArray();
    }

    public static function getWithdraw(App $app)
    {
        $app->auth();

        $app->verifPin($app->request->get("pin"));

        $withdraw = $app->user->user->withdraws()->where("valid_before", ">", Carbon::now())->first();

        if (!$withdraw) {
            $code = CreateUnique::withdrawCode($app->user->user->id);
            $withdraw = new Withdraw();
            $withdraw->user_id = $app->user->user->id;
            $withdraw->valid_before = Carbon::now()->addMinutes(1);
            $withdraw->code = $code;
            $withdraw->save();
        }

        return $withdraw->toArray();
    }

    public static function processWithdraw(App $app)
    {
        $app->admin();

        $app->validate([
            "code" => "required|numeric",
            "username" => "required",
            "amount" => "required|numeric|min:10K",
        ]);

        $withdraw = Withdraw::where("code", $app->request->post("code"))->where("valid_before", ">", Carbon::now())->whereRelation("user", "username", "=", $app->request->post("username"))->firstOrFail();

        if ($withdraw->user->balance < $app->request->post("amount")) {
            $app->response->setStatus(403);
            return [
                "error" => "Amount is greater than user balance",
            ];
        }

        $invoice_id = CreateUnique::create(ModelTransaction::class, "invoice_id");

        $data = [
            "user_id" => $withdraw->user->id,
            "title" => "PENARIKAN",
            "type" => "out",
            "amount" => $app->request->post("amount"),
            "invoice_id" => $invoice_id,
            "status" => "success",
        ];

        $transaction = new ModelTransaction($data);
        $transaction->save();
        $withdraw->valid_before = Carbon::now();
        $withdraw->save();
        $withdraw->user->balance = $withdraw->user->balance - $app->request->post("amount");
        $withdraw->user->save();

        return $transaction->toArray();
    }
}
