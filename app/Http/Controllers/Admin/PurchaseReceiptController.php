<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\PurchaseReceipt;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseReceiptController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
    ) {}

    public function index(Request $request): View
    {
        $q = PurchaseReceipt::query()->latest('received_at')->latest('id');
        if ($request->filled('q')) {
            $term = trim((string) $request->query('q', ''));
            if ($term !== '') {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('code', 'like', $like)
                        ->orWhere('supplier_name', 'like', $like);
                });
            }
        }
        $receipts = $q->paginate(20)->withQueryString();

        return view('admin.purchases.index', compact('receipts'));
    }

    public function create(): View
    {
        $variants = ProductVariant::query()
            ->with('product')
            ->orderBy('id')
            ->limit(300)
            ->get();

        return view('admin.purchases.create', compact('variants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'received_at' => 'required|date',
            'supplier_name' => 'nullable|string|max:160',
            'note' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $code = 'PN'.now()->format('YmdHis').str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);

        $receipt = $this->inventoryService->receive([
            'code' => $code,
            'received_at' => $data['received_at'],
            'supplier_name' => $data['supplier_name'] ?? null,
            'note' => $data['note'] ?? null,
            'items' => $data['items'],
        ]);

        return redirect()->route('admin.purchases.show', $receipt)->with('status', 'Đã tạo phiếu nhập.');
    }

    public function show(PurchaseReceipt $purchase): View
    {
        $purchase->load(['items.variant.product']);

        return view('admin.purchases.show', ['receipt' => $purchase]);
    }
}
