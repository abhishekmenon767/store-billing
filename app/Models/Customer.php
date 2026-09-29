<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function email(): Attribute
    {
        return Attribute::set(fn (string $value) => Str::lower(trim($value)));
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
