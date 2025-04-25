<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Repository\Facades\OrderProductRepository;
use App\Repository\Facades\OrderRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Illuminate\Support\Facades\Storage;

class TransactionService
{
    public function getDataOrders($request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');
        $selectedPeriode = $request->input('periode');

        $criteria = [];

        if (!empty($selectedPeriode['from']) || !empty($selectedPeriode['to'])) {
            $formattedStartDate = Carbon::parse($selectedPeriode['from'])->format('Y-m-d H:i:s');
            $formattedEndDate = Carbon::parse($selectedPeriode['to'])->format('Y-m-d H:i:s');            

            if ($formattedStartDate === $formattedEndDate) {
                $criteria[] = ['order_date', 'like', "%$formattedStartDate%"];
            } else {
                $criteria[] = ['order_date', 'BETWEEN', [$formattedStartDate, $formattedEndDate]];
            }
        }

        $joins = [];
        $attributes = [
            'id', 'order_no', 'customer_name', 'order_date', 'grand_total'
        ];

        return OrderRepository::where($criteria, $joins, $attributes)
            ->whereRaw('`order_no` LIKE ?', ["%{$search}%"])
            ->paginate($perPage, $attributes, 'page', $page);
    }

    public function deleteDataOrder($orderId)
    {
        $order = OrderRepository::find($orderId);

        if ($order) {
            OrderRepository::delete($orderId);
            return response()->json(['message' => 'Order deleted successfully!'], 200);
        }
    
        return response()->json(['message' => 'Order not found.'], 404);
    }

    public function generateTransaction($request)
    {
        try {
            $orderId = $this->storeOrder($request);
            $this->storeDataProductOrder($request, $orderId);
            $this->updateGrandTotalOrder($orderId);

            return response()->json(['message' => 'Order created successfully!'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Failed to create order'], 404);
        }
    }

    public function generateOrderNumber()
    {
        $dateNow = now()->format('Ymd');

        $criteria[] = ['order_no', 'like', 'INV' . $dateNow . '%'];
        $attributes = [
            'order_no'
        ];

        $latestOrder =  OrderRepository::where($criteria, [], $attributes)->orderByDesc('order_no')->first();

        $orderNumber = 'INV' . $dateNow . '0001';

        if ($latestOrder) {
            // Extract the numeric part of the last order number
            $lastNumber = (int) substr($latestOrder->order_no, 11);

            // Increment the last number by 1
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            // Create the new order number
            $orderNumber = 'INV' . $dateNow . $nextNumber;
        }

        return $orderNumber;
    }

    private function storeDataProductOrder($request, $orderId)
    {
        $datas = $request->json()->all();
        $orderProducts = $datas['products'];

        foreach ($orderProducts as $product) {
            $params = [
                'order_id'    => $orderId,
                'product_name'=> $product['product_name'],
                'qty'         => $product['quantity'],
                'price'       => $product['price'],
                'subtotal'    => ($product['quantity'] * $product['price']),
            ];

            OrderProductRepository::create($params);
        }
    }

    private function storeOrder($request)
    {
        $data = $request->json()->all();
        $newOrderNumber = $this->generateOrderNumber();

        $params = [
            'order_no' => $newOrderNumber,
            'customer_name' => $data['customer_name'],
            'order_date'    => $data['order_date'],
            'grand_total'   => 0
        ];

        return OrderRepository::createAndReturnId($params);
    }

    private function updateGrandTotalOrder($orderId)
    {
        $grandTotalOrder = OrderProduct::where('order_id', $orderId)->sum('subtotal');

        OrderRepository::update(['grand_total' => $grandTotalOrder], $orderId);
    }

    public function findDataOrder($orderId)
    {
        $selectedColumn = [
            'id',
            'customer_name',
            DB::raw("DATE_FORMAT(order_date, '%Y-%m-%d') as order_date"),
        ];

        return OrderRepository::findBy('id', $orderId, $selectedColumn);
    }

    public function findDataOrderProduct($orderId)
    {
        $criteria[] = ['order_id', '=', $orderId];
        $attributes = [
            'id',
            'product_name',
            'qty as quantity',
            'price'
        ];
        return OrderProductRepository::where($criteria, [], $attributes)->get();
    }

    public function updateOrderTransaction($request)
    {
        try {
            $datas = $request->json()->all();

            $this->updateDataOrder($request);
            $this->updateDataOrderProduct($request);
            $this->updateGrandTotalOrder($datas['order_id']);

            return response()->json(['message' => 'Order updated successfully!'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Failed to update order'], 404);
        }
    }

    private function updateDataOrder($request)
    {
        $data = $request->json()->all();

        OrderRepository::update(['customer_name' => $data['customer_name'], 'order_date' => $data['order_date']], $data['order_id']);
    }

    private function updateDataOrderProduct($request)
    {
        $datas = $request->json()->all();

        $incomingProductIds = collect($datas['products'])->pluck('id')->filter(fn($id) => !is_null($id) && $id !== '')->all();

        if($incomingProductIds == null) {
            foreach ($datas['products'] as $product) {
                $params = [
                    'order_id'    => $datas['order_id'],
                    'product_name'=> $product['product_name'],
                    'qty'         => $product['quantity'],
                    'price'       => $product['price'],
                    'subtotal'    => ($product['quantity'] * $product['price']),
                ];
    
                OrderProductRepository::create($params);
            }
        }

        $criteria[] = ['id', 'NOT IN', $incomingProductIds];
        $criteria[] = ['order_id', '=', $datas['order_id']];

        OrderProductRepository::where($criteria, [], ['id'])->get();

        $productsToDelete = OrderProductRepository::where($criteria, [], ['id'])->get();
        foreach ($productsToDelete as $product) {
            OrderProductRepository::delete($product->id);
        }

        // update or create data
        $orderProducts = $datas['products'];

        foreach ($orderProducts as $product) {
            
            if (isset($product['id']) && $product['id'] !== null) {
                $params = [
                    'product_name'=> $product['product_name'],
                    'qty'         => $product['quantity'],
                    'price'       => $product['price'],
                    'subtotal'    => ($product['quantity'] * $product['price']),
                ];
    
                OrderProductRepository::update($params, $product['id']);
            } else {
                $params = [
                    'order_id'    => $datas['order_id'],
                    'product_name'=> $product['product_name'],
                    'qty'         => $product['quantity'],
                    'price'       => $product['price'],
                    'subtotal'    => ($product['quantity'] * $product['price']),
                ];
    
                OrderProductRepository::create($params);
            }

        }
    }

    public function exportTransactionToXlsx()
    {

        $orderHeading = ['Order Number', 'Customer Name', 'Order Date', 'Grand Total'];
        $orderProductHeading = ['Order Number', 'Product Name', 'Quantity', 'Price', 'Subtotal'];

        $options = new Options();
        $options->SHOULD_CREATE_NEW_SHEETS_AUTOMATICALLY = true;

        $fileName = 'orders_export_' . now()->format('Ymd_His') . '.xlsx';
        $fullPath = storage_path("app/{$fileName}");

        $writer = new Writer($options);
        $writer->openToFile($fullPath);

        // Sheet 1: Order Sheet
        $this->writeOrdersSheet($writer, $orderHeading);

        // Sheet 2: Order Product Sheet
        $this->writeOrderProductSheet($writer, $orderProductHeading);

        $writer->close();

        // Simpan file ke storage
        Storage::disk('local')->put($fileName, file_get_contents($fullPath));

        return response()->json(['file' => $fileName]);
    }

    private function writeOrdersSheet($writer, $header)
    {
        $sheet = $writer->getCurrentSheet();
        $sheet->setName("Orders");
        $writer->addRow(Row::fromValues($header));

        Order::select([
                'order_no', 'customer_name', 'order_date', 'grand_total'
            ])
            ->chunk(5000, function ($rows) use ($writer) {
                foreach ($rows as $row) {
                    $formattedDate = Carbon::parse($row->order_date)->format('d-m-Y');
                    $writer->addRow(Row::fromValues([
                        $row->order_no, 
                        $row->customer_name, 
                        $formattedDate,
                        $row->grand_total
                    ]));
                }
            });
    }
    
    private function writeOrderProductSheet($writer, $header)
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName("Order Product");
        $writer->addRow(Row::fromValues($header));

        OrderProduct::select([
                'orders.order_no', 'order_products.product_name', 'order_products.qty', 'order_products.price', 'order_products.subtotal'
            ])
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->chunk(5000, function ($rows) use ($writer) {
                foreach ($rows as $row) {
                    $writer->addRow(Row::fromValues([
                        $row->order_no, 
                        $row->product_name, 
                        $row->qty,
                        $row->price,
                        $row->subtotal
                    ]));
                }
            });
    }

    public function downloadFileExcel($fileName)
    {
        $filePath = storage_path('app/' . $fileName);

        if (file_exists($filePath)) {
            return response()->download($filePath);
        }

        return response()->json(['error' => 'File not found'], 404);
    }


}
