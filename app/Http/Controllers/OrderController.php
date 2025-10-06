<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Builder;

class OrderController extends Controller
{
    /**
     * POST /api/orders
     * Create new order
     */
    public function store(Request $request)
    {
        $data = $request->all();

        // Manual validation (no FormRequest, no Resource)
        $errors = [];

        // status validation (optional at create; default new)
        $allowedStatuses = ['new', 'processing', 'completed', 'cancelled'];
        $status = isset($data['status']) ? $data['status'] : 'new';
        if (!in_array($status, $allowedStatuses, true)) {
            $errors['status'] = 'Status must be one of: ' . implode(', ', $allowedStatuses);
        }

        // price validation
        if (!array_key_exists('price', $data)) {
            $errors['price'] = 'Price is required';
        } elseif (!is_numeric($data['price'])) {
            $errors['price'] = 'Price must be numeric';
        } elseif ($data['price'] < 0) {
            $errors['price'] = 'Price must be >= 0';
        }

        if ($errors) {
            return ApiResponse::error(422, 'Validation failed', $errors);
        }

        $order = Order::create([
            'status' => $status,
            'price' => (float) $data['price'],
        ]);

        return ApiResponse::success(201, $order, 'Order created');
    }

    /**
     * PATCH /api/orders/{order}/status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->all();
        $allowedStatuses = ['new', 'processing', 'completed', 'cancelled'];

        if (!isset($data['status']) || !in_array($data['status'], $allowedStatuses, true)) {
            return ApiResponse::error(422, 'Validation failed', [
                'status' => 'Status is required and must be one of: ' . implode(', ', $allowedStatuses)
            ]);
        }

        $order->status = $data['status'];
        $order->save();

        return ApiResponse::success(200, $order, 'Status updated');
    }

    /**
     * GET /api/orders
     * Filters: status, date_from, date_to, price_min, price_max
     */
    public function index(Request $request)
    {
        $query = Order::query();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }
        if (($min = $request->query('price_min')) !== null) {
            $query->where('price', '>=', (float)$min);
        }
        if (($max = $request->query('price_max')) !== null) {
            $query->where('price', '<=', (float)$max);
        }

        // Optional pagination
        $perPage = (int)($request->query('per_page', 15));
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 15;

        if ($request->boolean('paginate', true)) {
            $paginator = $query->orderByDesc('id')->paginate($perPage);
            return ApiResponse::success(200, [
                'items' => $paginator->items(),
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                ],
            ], 'Orders list');
        }

        $items = $query->orderByDesc('id')->get();
        return ApiResponse::success(200, [ 'items' => $items ], 'Orders list');
    }
}
