<?php
namespace Lego;

use Xendit\Invoice;
use Xendit\Xendit;

class Payment
{
    public function __construct()
    {
        Xendit::setApiKey($_ENV["XENDIT_API_KEY"]);
    }

    public function createInvoice($params): string
    {
        $invoice = Invoice::create($params);

        return $invoice["invoice_url"];
    }

    public function getInvoice($id)
    {
        return Invoice::retrieve($id);
    }
}
