<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariationType extends Model
{
    public $timestamps = false;
    public function options()
    {
        return $this->hasMany(VariationTypeOption::class, 'variation_type_id');
    }
}
