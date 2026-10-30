<?php

namespace App\Http\Controllers\Api\V2\Sync;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Order\PaidOrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class PaidOrderController extends Controller
{
    public function __invoke(Request $request)
    {
        $orders = Order::query()
            ->paid()
            ->with([
                'user:id,first_name,last_name,username',
                'province:id,name',
                'city:id,name',
                'items.product:id,product_code,type',
                'items.get_price:id,variant_code',
            ])
            ->latest('id')->get();

        return $this->respondWithResourceCollection(
            PaidOrderResource::collection($orders)
        );
    }
}
