<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\Languageable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeGroup extends Model
{
    use Filterable, Languageable, SoftDeletes;

    protected $guarded = ['id'];

    public function get_attributes()
    {
        return $this->hasMany(Attribute::class);
    }
}
