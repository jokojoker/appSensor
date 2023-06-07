<?php

namespace App\Http\Controllers\Api\Sensors;

use App\Http\Controllers\Controller;
use App\Http\Resources\TemperatureReadings;
use App\Models\Sensor\TemperatureReading;
use App\Models\Sensor\TemperatureSensor;
use App\Models\TemperatureSensor\SensorReading;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TemperatureReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return 'This resource is unavailable!';
    }

    /**
     * Receive data from external call to API.
     */
    public function receiveDataFromSensor(Request $request): JsonResponse{
        $input = $request->all();

        $isValid = Validator::make($input['reading'],[
            'sensor_uuid' => 'required',
            'temperature' => 'required|decimal:3'
        ]);

        if($isValid->fails()){
            return response()
                ->json([
                    'message' => $isValid->errors()
                ]);
        }

        $validated = $isValid->validated();

        $sensor = TemperatureSensor::select('id')
            ->where('sensor_uuid','=',$validated['sensor_uuid'])
            ->first();

        if(empty($sensor->id)){
            return response()
                ->json([
                    'message' => 'Sensor not found!'
                ]);
        }

        $sensorReading = $this->store([
            'sensor_id' => $sensor->id,
            'reading_id_from_sensor' => null,
            'temperature' => $validated['temperature'],
            'reading_type' => 2
        ]);

        return response()->json([
            'message' => new TemperatureReadings($sensorReading)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(array $storeData): Model|false {
        return TemperatureReading::create([
            'sensor_id' => $storeData['sensor_id'],
            'reading_dt' => now(),
            'reading_id_from_sensor' => $storeData['reading_id_from_sensor'],
            'temperature' => $storeData['temperature'],
            'reading_type' => $storeData['reading_type']
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
