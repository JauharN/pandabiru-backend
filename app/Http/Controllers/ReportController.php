<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Report;
use App\Models\Store;
use App\Models\Product;

class ReportController extends Controller
{
    /**
     * Menerima laporan dengan berbagai context
     * Context: attendance, availability, promo
     */
    public function store(Request $request, $context)
    {
        // Validasi context yang diperbolehkan
        $allowedContexts = ['attendance', 'availability', 'promo'];

        if (!in_array($context, $allowedContexts)) {
            return response()->json([
                'message' => 'Context tidak valid',
                'allowed_contexts' => $allowedContexts
            ], 400);
        }

        // Validasi input harus array
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.client_uuid' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Format data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        $items = $request->items;
        $results = [
            'ok' => 0,
            'duplicate' => 0,
            'invalid' => 0,
            'items' => []
        ];

        // Process setiap item dalam batch
        foreach ($items as $index => $item) {
            $clientUuid = $item['client_uuid'];

            // Cek duplikasi berdasarkan client_uuid
            $exists = Report::where('client_uuid', $clientUuid)->exists();

            if ($exists) {
                $results['duplicate']++;
                $results['items'][] = [
                    'index' => $index,
                    'client_uuid' => $clientUuid,
                    'status' => 'duplicate',
                    'message' => 'Data sudah pernah dikirim'
                ];
                continue;
            }

            // Validasi berdasarkan context
            $validation = $this->validateByContext($context, $item);

            if (!$validation['valid']) {
                $results['invalid']++;
                $results['items'][] = [
                    'index' => $index,
                    'client_uuid' => $clientUuid,
                    'status' => 'invalid',
                    'message' => $validation['message']
                ];
                continue;
            }

            // Simpan ke database
            try {
                Report::create([
                    'user_id' => $request->user()->id,
                    'context' => $context,
                    'client_uuid' => $clientUuid,
                    'payload' => ($item),
                    'status' => 'synced',
                    'synced_at' => now(),
                ]);

                $results['ok']++;
                $results['items'][] = [
                    'index' => $index,
                    'client_uuid' => $clientUuid,
                    'status' => 'ok',
                    'message' => 'Berhasil disimpan'
                ];
            } catch (\Exception $e) {
                $results['invalid']++;
                $results['items'][] = [
                    'index' => $index,
                    'client_uuid' => $clientUuid,
                    'status' => 'error',
                    'message' => 'Gagal menyimpan: ' . $e->getMessage()
                ];
            }
        }

        return response()->json([
            'message' => 'Laporan berhasil diproses',
            'context' => $context,
            'summary' => [
                'total' => count($items),
                'ok' => $results['ok'],
                'duplicate' => $results['duplicate'],
                'invalid' => $results['invalid'],
            ],
            'details' => $results['items']
        ]);
    }

    /**
     * Validasi data berdasarkan context
     */
    private function validateByContext($context, $item)
    {
        switch ($context) {
            case 'attendance':
                return $this->validateAttendance($item);

            case 'availability':
                return $this->validateAvailability($item);

            case 'promo':
                return $this->validatePromo($item);

            default:
                return ['valid' => false, 'message' => 'Context tidak dikenali'];
        }
    }

    /**
     * Validasi attendance
     */
    private function validateAttendance($item)
    {
        $required = ['date', 'status'];

        foreach ($required as $field) {
            if (!isset($item[$field])) {
                return ['valid' => false, 'message' => "Field {$field} wajib diisi"];
            }
        }

        // Validasi status
        $validStatus = ['present', 'absent'];
        if (!in_array($item['status'], $validStatus)) {
            return ['valid' => false, 'message' => 'Status harus present atau absent'];
        }

        // Validasi format tanggal
        $date = \DateTime::createFromFormat('Y-m-d', $item['date']);
        if (!$date || $date->format('Y-m-d') !== $item['date']) {
            return ['valid' => false, 'message' => 'Format tanggal harus Y-m-d (contoh: 2025-10-14)'];
        }

        return ['valid' => true, 'message' => 'Valid'];
    }

    /**
     * Validasi availability
     */
    private function validateAvailability($item)
    {
        $required = ['store_id', 'product_id', 'available'];

        foreach ($required as $field) {
            if (!isset($item[$field])) {
                return ['valid' => false, 'message' => "Field {$field} wajib diisi"];
            }
        }

        // Validasi store exists
        if (!Store::where('id', $item['store_id'])->exists()) {
            return ['valid' => false, 'message' => 'Toko tidak ditemukan'];
        }

        // Validasi product exists
        if (!Product::where('id', $item['product_id'])->exists()) {
            return ['valid' => false, 'message' => 'Produk tidak ditemukan'];
        }

        // Validasi available adalah boolean
        if (!is_bool($item['available'])) {
            return ['valid' => false, 'message' => 'Field available harus boolean (true/false)'];
        }

        return ['valid' => true, 'message' => 'Valid'];
    }

    /**
     * Validasi promo
     */
    private function validatePromo($item)
    {
        $required = ['store_id', 'product_id', 'price_normal', 'price_promo'];

        foreach ($required as $field) {
            if (!isset($item[$field])) {
                return ['valid' => false, 'message' => "Field {$field} wajib diisi"];
            }
        }

        // Validasi store exists
        if (!Store::where('id', $item['store_id'])->exists()) {
            return ['valid' => false, 'message' => 'Toko tidak ditemukan'];
        }

        // Validasi product exists
        if (!Product::where('id', $item['product_id'])->exists()) {
            return ['valid' => false, 'message' => 'Produk tidak ditemukan'];
        }

        // Validasi harga normal > 0
        if (!is_numeric($item['price_normal']) || $item['price_normal'] <= 0) {
            return ['valid' => false, 'message' => 'Harga normal harus lebih dari 0'];
        }

        // Validasi harga promo > 0 dan < harga normal
        if (!is_numeric($item['price_promo']) || $item['price_promo'] <= 0) {
            return ['valid' => false, 'message' => 'Harga promo harus lebih dari 0'];
        }

        if ($item['price_promo'] >= $item['price_normal']) {
            return ['valid' => false, 'message' => 'Harga promo harus lebih kecil dari harga normal'];
        }

        return ['valid' => true, 'message' => 'Valid'];
    }

    /**
    * Get summary laporan per context
    */
    public function summary(Request $request)
    {
    $userId = $request->user()->id;

    $summary = [
        'attendance' => [
            'total' => Report::where('user_id', $userId)
                ->where('context', 'attendance')
                ->count(),
            'today' => Report::where('user_id', $userId)
                ->where('context', 'attendance')
                ->whereDate('created_at', today())
                ->count(),
        ],
        'availability' => [
            'total' => Report::where('user_id', $userId)
                ->where('context', 'availability')
                ->count(),
            'today' => Report::where('user_id', $userId)
                ->where('context', 'availability')
                ->whereDate('created_at', today())
                ->count(),
        ],
        'promo' => [
            'total' => Report::where('user_id', $userId)
                ->where('context', 'promo')
                ->count(),
            'today' => Report::where('user_id', $userId)
                ->where('context', 'promo')
                ->whereDate('created_at', today())
                ->count(),
        ],
        'last_sync' => Report::where('user_id', $userId)
            ->latest('synced_at')
            ->first()
            ?->synced_at
            ?->diffForHumans(),
    ];

    return response()->json($summary);
}

    /**
     * Endpoint untuk melihat laporan (opsional, untuk debugging)
     */
    public function index(Request $request)
    {
        $query = Report::with('user:id,name,email')
            ->orderBy('created_at', 'desc');

        // Filter by context
        if ($request->has('context')) {
            $query->where('context', $request->context);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $reports = $query->paginate(20);

        return response()->json($reports);
    }
}
