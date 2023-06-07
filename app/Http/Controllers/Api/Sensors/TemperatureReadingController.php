<?php

namespace App\Http\Controllers\Api\Sensors;

use App\Http\Controllers\Controller;
use App\Http\Resources\TemperatureReadings;
use App\Models\Sensor\TemperatureReading;
use App\Models\Sensor\TemperatureSensor;
use App\Models\TemperatureSensor\Sensor;
use App\Models\TemperatureSensor\SensorReading;
use http\Env\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
     * Receive data from external call to API.
     */
    public function checkDataFromSensor(): jsonResponse{

        $sensors = TemperatureSensor::all();

        if(empty($sensors)){
            return response()
                ->json([
                    'message' => 'Sensor not found!'
                ]);
        }

        foreach ($sensors as $sensorItem){
            $wsProtocol = 'http://';
            $wsMethod = '/data';
            //$endPoint = $wsProtocol.$sensorItem->ip_address.$wsMethod;
            $endPoint = 'http://app.sensor.lv/data';

            $responseData = Http::get($endPoint);

            if($responseData->status() == 200){
                $dataCSV = $responseData->body();
                $dataArr = explode(',',$dataCSV);
                $inputArr = ['reading_id_from_sensor' => $dataArr[0], 'temperature' => $dataArr[1]];

                $isValid = Validator::make($inputArr,[
                    'reading_id_from_sensor' => 'required|string',
                    'temperature' => 'required|decimal:2'
                ]);

                if($isValid->fails()){
                    return response()
                        ->json([
                            'message' => $isValid->errors()
                        ]);
                }

                $validated = $isValid->validated();

                $sensorReadingExist = TemperatureReading::where('reading_id_from_sensor','=',$validated['reading_id_from_sensor'])->count();

                if($sensorReadingExist < 1) {
                    $this->store([
                        'sensor_id' => $sensorItem->id,
                        'reading_id_from_sensor' => $validated['reading_id_from_sensor'],
                        'temperature' => $validated['temperature'],
                        'reading_type' => 1
                    ]);
                }
            }
        }

        return response()
            ->json([
                'message' => 'Data saved!'
            ]);
    }

    /**
     * Faker for data generator on Call.
     */
    public function fakeSensorCSV(): string{
        return rand(20000,29999).','.(rand(1000,19999)/100);
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
