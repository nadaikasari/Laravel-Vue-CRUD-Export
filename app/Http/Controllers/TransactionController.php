<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    private $transactionService;

    public function __construct()
    {
        $this->transactionService = new TransactionService();
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $orders = $this->transactionService->getDataOrders($request);

        return Inertia::render("Transaction/Index", compact('orders'));
    }

    /**
     * Display a form create order.
     * @return Renderable
     */
    public function create()
    {
        return Inertia::render('Transaction/Form-Create', [
            'isCreatePage' => true,
        ]);
    }

    public function deleteOrder($orderId)
    {
        return $this->transactionService->deleteDataOrder($orderId);
    }

    public function generateTransaction(Request $request)
    {
        return $this->transactionService->generateTransaction($request);
    }

    /**
     * Display a form create order.
     * @return Renderable
     */
    public function update($orderId)
    {
        $dataOrder = $this->transactionService->findDataOrder($orderId);
        $dataProductOrder = $this->transactionService->findDataOrderProduct($orderId);

        return Inertia::render('Transaction/Form-Update', [
            'dataOrder' => $dataOrder,
            'dataProductOrder' => $dataProductOrder,
        ]);
    }

    public function updateOrderTransaction(Request $request)
    {
        return $this->transactionService->updateOrderTransaction($request);
    }

    public function exportTransactionToXlsx()
    {
        return $this->transactionService->exportTransactionToXlsx();
    }

    public function downloadFileExcel($fileName)
    {
        return $this->transactionService->downloadFileExcel($fileName);
    }
}
