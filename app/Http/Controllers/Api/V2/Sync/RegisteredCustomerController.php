<?php

namespace App\Http\Controllers\Api\V2\Sync;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\Customer\CustomerResource;
use App\Models\User;
use Illuminate\Http\Request;

class RegisteredCustomerController extends Controller {
    public function __invoke( Request $request ) {
        $validated = $request->validate([
            'per_page' => [ 'nullable', 'integer', 'min:1', 'max:100' ],
        ]);

        $customers = User::query()
            ->where('level', 'user')
            ->with([
                'address.province:id,name,name_en',
                'address.city:id,name,name_en',
            ])
            ->latest('id')
            ->get();

        return $this->respondWithResourceCollection(CustomerResource::collection($customers));
    }
}
