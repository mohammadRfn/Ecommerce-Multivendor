<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\VariationType;
use App\Models\VariationTypeOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Str;

class CartService
{
    private ?array $cachedCartItems = null;
    protected const COOKIE_NAME = 'cartItems';
    protected const COOKIE_LIFETIME = 60 * 24 * 365;
    public function addItemToCart(Product $product, int $quantity = 1, $optionIds = null)
    {
        if ($optionIds === null) {
            $optionIds = $product->variation_types
                ->mapWithKeys(fn(VariationType $type) => [$type->id => $type->options[0]?->id])
                ->toArray();
        }
        $price = $product->getPriceForOptions($optionIds);
        if (Auth::check()) {
            $this->saveItemToDatabase($product->id, $quantity, $price, $optionIds);
        } else {
            $this->saveItemToCookies($product->id, $quantity, $price, $optionIds);
        }
    }
    public function updateItemQuantity(int $productId, int $quantity, $optionIds = null)
    {
        if (Auth::check()) {
            $this->updateItemQuantityInDatabase($productId, $quantity, $optionIds);
        } else {
            $this->updateItemQuantityInCookies($productId, $quantity, $optionIds);
        }
    }
    public function removeItemFromCart(int $productId, $optionIds = null)
    {
        if (Auth::check()) {
            $this->removeItemFromDatabase($productId,  $optionIds);
        } else {
            $this->removeItemFromCookies($productId, $optionIds);
        }
    }
    public function getCartItems(): array
    {
        try {
            if ($this->cachedCartItems === null) {
                if (Auth::check()) {
                    $cartItems = $this->getCartItemsFromDatabase();
                } else {
                    $cartItems = $this->getCartItemsFromCookies();
                }
                $productIds = collect($cartItems)->map(fn($items) => $items['product_id']);
                $products = Product::whereIn('id', $productIds)
                    ->with('user.vendor')
                    ->forWebsite()
                    ->get()
                    ->keyBy('id');
                $cartItemData = [];
                foreach ($cartItems as $key => $cartItem) {
                    $products = data_get($products, $cartItem['product_id']);
                    if (!$products) continue;
                    $optionInfo = [];
                    $options = VariationTypeOption::with('variation_types')
                        ->whereIn('id', $cartItem['option_ids'])
                        ->get()
                        ->keyBy('id');
                    $imageUrl = null;
                    foreach ($cartItems['option_ids'] as $option_id) {
                        $options = data_get($options, $option_id);
                        if (!$imageUrl) {
                            $imageUrl = $options->getFirstMediaUrl('images', 'small');
                        }
                        $optionInfo[] = [
                            'id' => $option_id,
                            'name' => $options->name,
                            'type' => [
                                'id' => $options->variation_types->id,
                                'name' => $options->variation_types->name,
                            ],

                        ];
                    }
                    $cartItemData[] = [
                        'id' => $cartItem['id'],
                        'product_id' => $products->id,
                        'title' => $products->title,
                        'slug' => $products->slug,
                        'price' => $cartItem['price'],
                        'quantity' => $cartItem['quantity'],
                        'option_ids' => $cartItem['option_ids'],
                        'option' => $optionInfo,
                        'image' => $imageUrl ?: $products->getFirstMediaUrl('images', 'small'),
                        'user' => [
                            'id' => $products->created_by,
                            'name' => $products->user->vendor->store_name,
                        ],
                    ];
                }
                $this->cachedCartItems = $cartItemData;
            }
            return $this->cachedCartItems;
        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        return [];
    }
    public function getTotalQuantity(): int
    {
        $totalQuantity = 0;
        foreach ($this->getCartItems() as $item) {
            $totalQuantity += $item['quanitity'];
        }
        return $totalQuantity;
    }
    public function getTotalPrice(): float
    {
        $total = 0;
        foreach ($this->getCartItems() as $item) {
            $total += $item['quantity'] * $item['price'];
        }
        return $total;
    }
    protected function updateItemQuantityInDatabase(int $productId, int $quantity, $optionIds = null)
    {
        $userId = Auth::id();
        $cartItems = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_type_option_ids', json_encode($optionIds))
            ->first();
        if ($cartItems) {
            $cartItems->update([
                'quantity' => $quantity,
            ]);
        }
    }
    protected function updateItemQuantityInCookies(int $productId, int $quantity, $optionIds = null)
    {
        $cartItems = $this->getCartItemsFromCookies();
        ksort($optionIds);
        $itemKey = $productId . '_' . json_encode($optionIds);
        if (isset($cartItems[$itemKey])) {
            $cartItems[$itemKey]['quantity'] = $quantity;
        }
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }
    protected function saveItemToDatabase(int $productId, int $quantity, $price)
    {
        $userId = Auth::id();
        ksort($optionIds);
        $cartItem = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_type_option_ids', json_encode($optionIds))
            ->first();
        if ($cartItem) {
            $cartItem->update([
                'quantity' => DB::raw('quantity + ' . $quantity),
            ]);
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'variation_type_option_ids' => $optionIds,
            ]);
        }
    }
    protected function saveItemToCookies(int $productId, int $quantity, $price, $optionIds)
    {
        $cartItems = $this->getCartItemsFromCookies();

       // dd($cartItems, $productId, $quantity, $price, $optionIds);
        ksort($optionIds);

        $itemKey = $productId . '_' . json_encode($optionIds);

        if (isset($cartItems[$itemKey])) {
            $cartItems[$itemKey]['quantity'] += $quantity;
            $cartItems[$itemKey]['price'] = $price;
        } else {
            $cartItems[$itemKey] = [
                'id' => \Str::uuid(),
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'option_ids' => $optionIds,
            ];
        }
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }
    protected function removeItemFromDatabase(int $productId, array $optionIds)
    {
        $userId = Auth::id();
        ksort($optionIds);
        CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_type_option_ids', json_encode($optionIds))
            ->delete();
    }
    protected function removeItemFromCookies(int $productId, array $optionIds)
    {
        $cartItems = $this->getCartItemsFromCookies();
        ksort($optionIds);
        $cartKey = $productId . '-' . json_encode($optionIds);
        unset($cartItems[$cartKey]);

        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }
    protected function getCartItemsFromDatabase()
    {
        $userId = Auth::id();
        $cartItems = CartItem::where('user_id', $userId)
            ->get()
            ->map(function ($cartItem) {
                return [
                    'id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'option_ids' => $cartItem->variation_type_option_ids,
                ];
            })->toArray();
        return $cartItems;
    }

    protected function getCartItemsFromCookies()
    {
        $cartItems = json_decode(Cookie::get(self::COOKIE_NAME, '[]'), true);
        return $cartItems;
    }
    public function getCartItemsGrouped():array
    {
        $cartItems = $this->getCartItems();
        return collect($cartItems)
            ->groupBy(fn($item) => $item['user']['id'])
            ->map(fn($items, $userId) => [
                'user' => $items->first()['user'],
                'items' => $items->toArray(),
                'totalQuantity' => $items->sum('quantity'),
                'totalPrice' => $items->sum(fn($item) => $item['price'] * $item['quantity']),
            ])->toArray();
    }
    public function moveCartItemsToDatabase($userId): void
    {
        $cartItems = $this->getCartItemsFromCookies();
        foreach($cartItems as $itemKey => $cartItem)
        {
            $existingItem = CartItem::where('user_id', $userId)
                ->where('product_id', $cartItem['product_id'])
                ->where('varaition_type_option_ids', json_encode($cartItem['option_ids']))
                ->first();
            if($existingItem){
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $cartItem['quantity'],
                    'price' => $cartItem['price'],
                ]);
            }else{
                CartItem::create([
                    'user_id' => $userId,
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $cartItem['price'],
                    'variation_type_option_ids' => $cartItem['option_ids'],
                ]);
            }
        }
        Cookie::queue(self::COOKIE_NAME, '', -1);
    }
}
