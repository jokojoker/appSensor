<?php

namespace App\Models\Sensor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemperatureReading extends Model
{
    use HasFactory;

    protected $table = 'sensor_readings';
    protected $primaryKey = 'id';

    protected $fillable = [
        'sensor_id',
        'reading_id_from_sensor',
        'reading_dt',
        'temperature',
        'reading_type'
    ];

    public function sensor(){
        return $this->hasOne(TemperatureSensor::class, 'id', 'sensor_id')->select(['id','name']);
    }

    protected array $dates = ['created_at', 'updated_at','deleted_at'];
}
