<?php

namespace App\Models\Sensor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemperatureSensor extends Model
{
    use HasFactory;

    protected $table = 'sensors';
    protected $primaryKey = 'id';

    protected $fillable = [
        'sensor_uuid' , 'ip_address', 'name', 'manufacturer'
    ];

    protected array $dates = ['created_at', 'updated_at','deleted_at'];
}
