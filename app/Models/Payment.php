<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'payment_id', 'payment_status',
        'payment_method', 'payer_id',
        'payer_email', 'payer_name', 'currency'
    ];
}
