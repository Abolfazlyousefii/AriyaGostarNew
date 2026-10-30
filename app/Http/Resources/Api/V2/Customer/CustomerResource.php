<?php

namespace App\Http\Resources\Api\V2\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->fullname,
            'username' => $this->username,
            'email' => $this->email,
            'birthday' => $this->birthday,
            'bio' => $this->bio,
            'profile_photo_url' => $this->profile_photo_url,
            'is_verified' => $this->verified_at !== null,
            'verified_at' => $this->verified_at,
            'registered_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'addresses' => $this->address ? [[
                'id' => $this->address->id,
                'province' => $this->address->province ? [
                    'id' => $this->address->province->id,
                    'name' => $this->address->province->name,
                    'name_en' => $this->address->province->name_en,
                ] : null,
                'city' => $this->address->city ? [
                    'id' => $this->address->city->id,
                    'name' => $this->address->city->name,
                    'name_en' => $this->address->city->name_en,
                ] : null,
                'postal_code' => $this->address->postal_code,
                'address' => $this->address->address,
            ]] : [],
        ];
    }
}
