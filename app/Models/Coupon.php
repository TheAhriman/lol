<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'status'
    ];

    public static function findByCode($code)
    {
        return self::query()->where('code',$code)->first();
    }
    public function discount($total)
    {
        switch($this->type) {
            case 'fixed':
                return $this->value;
            case 'percent':
                return ($this->value / 100) * 100;
            default:
                return 0;
        }
    }
}
