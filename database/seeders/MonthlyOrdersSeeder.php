<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SaleChannel;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonthlyOrdersSeeder extends Seeder
{
    public function __construct(
        private readonly int $year = 2026,
        private readonly int $month = 1,
        private readonly float $usdTarget = 19450.00,
        private readonly float $gelTarget = 4750.00,
    ) {}

    public function run(): void
    {
        $customers = Customer::query()->inRandomOrder()->limit(30)->get();

        if ($this->usdTarget > 0) {
            $usdProducts = Product::query()
                ->whereIn('currency_id', [2, 3])
                ->whereNotNull('sale_price')
                ->where('is_active', true)
                ->get();

            $this->createOrders($usdProducts, $customers, $this->usdTarget);
        }

        if ($this->gelTarget > 0) {
            $gelProducts = Product::query()
                ->where('currency_id', 1)
                ->whereNotNull('sale_price')
                ->where('is_active', true)
                ->get();

            $this->createOrders($gelProducts, $customers, $this->gelTarget);
        }
    }

    private function createOrders(Collection $products, Collection $customers, float $target): void
    {
        if ($products->isEmpty()) {
            return;
        }

        // Realistic variability: different number of orders each month
        $orderCount = random_int(5, 15);
        $budgets = $this->distribute($target, $orderCount);
        $now = Carbon::now();
        $isCurrentMonth = $this->year === $now->year && $this->month === $now->month;
        $maxDay = $isCurrentMonth ? $now->day : Carbon::create($this->year, $this->month)->daysInMonth;

        foreach ($budgets as $budget) {
            $channel = random_int(1, 100) <= 80 ? SaleChannel::MyParts : SaleChannel::Internal;
            $customer = $customers->isNotEmpty() ? $customers->random() : null;
            $date = Carbon::create($this->year, $this->month, random_int(1, $maxDay), random_int(10, 22), random_int(0, 59), random_int(0, 59));

            $order = Order::factory()->create([
                'customer_id' => $customer?->id,
                'status' => OrderStatus::Confirmed->value,
                'payment_status' => PaymentStatus::Paid->value,
                'sale_channel' => $channel->value,
                'subtotal' => 0,
                'discount' => 0,
                'total' => 0,
                'sold_at' => $date,
            ]);

            DB::table('orders')->where('id', $order->id)->update([
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $subtotal = $this->createItems($order->id, $products, $budget);

            DB::table('orders')->where('id', $order->id)->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);
        }
    }

    /**
     * Create 1–4 items for an order. The last item absorbs the remainder of the
     * budget so the order total always sums to exactly $budget.
     * Earlier items use real product prices (capped so there's always room for the last item).
     */
    private function createItems(int $orderId, Collection $products, float $budget): float
    {
        $itemCount = random_int(1, 4);
        $allocated = 0.0;

        for ($j = 0; $j < $itemCount; $j++) {
            $isLast = ($j === $itemCount - 1);
            $remaining = round($budget - $allocated, 2);

            if ($remaining <= 0) {
                break;
            }

            $product = $products->random();

            if ($isLast) {
                $unitPrice = $remaining;
                $qty = 1;
            } else {
                // Use the real price but cap it so at least 20% is left for the remaining items
                $cap = round($remaining * 0.75, 2);
                $unitPrice = min((float) $product->sale_price, $cap);
                $unitPrice = max($unitPrice, 0.01);
                $qty = random_int(1, 2);
                // Avoid consuming more than the cap with qty
                if (round($unitPrice * $qty, 2) > $cap) {
                    $qty = 1;
                }
            }

            $itemTotal = round($unitPrice * $qty, 2);
            $allocated = round($allocated + $itemTotal, 2);

            OrderItem::query()->create([
                'order_id' => $orderId,
                'product_id' => $product->id,
                'product_name_snapshot' => $product->name,
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'total_price' => $itemTotal,
            ]);
        }

        return $allocated;
    }

    /**
     * Split $target into $count amounts (rounded to 2dp) that sum exactly to $target.
     *
     * @return float[]
     */
    private function distribute(float $target, int $count): array
    {
        $rands = array_map(fn () => mt_rand(100, 1000), range(1, $count));
        $sum = array_sum($rands);
        $amounts = array_map(fn ($r) => round($r / $sum * $target, 2), $rands);

        $diff = round($target - array_sum($amounts), 2);
        $amounts[$count - 1] = round($amounts[$count - 1] + $diff, 2);

        return $amounts;
    }
}
