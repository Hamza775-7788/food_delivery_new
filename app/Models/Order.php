<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = "orders";
    protected $guarded = [
        "id"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'orderStatus_id');
    }

    public function orderDetils()
    {
        return $this->hasMany(OrderDetails::class);
    }
}
