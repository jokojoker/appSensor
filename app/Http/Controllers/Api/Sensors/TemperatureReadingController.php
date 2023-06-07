<?php

namespace App\Http\Controllers\Api\Sensors;

use App\Http\Controllers\Controller;
use App\Http\Resources\TemperatureReadings;
use App\Models\Sensor\TemperatureReading;
use App\Models\Sensor\TemperatureSensor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TemperatureReadingController extends Controller
{
    public array $reverseType = [1 => 2, 2 => 1];
    public array $temperatureType = [1 => 'Celsius', 2 => 'Fahrenheit'];

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

        if($sensors->isEmpty()){
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
     * Middle temperature from all sensors, in submitted days range.
     */
    public function getAvarageFromAllSensors(Request $request): string|bool{
        $input = $request->all();

        $isValid = Validator::make($input,[
            'date_from' => 'required|date',
            'date_to' => 'required|date'
        ]);

        if($isValid->fails()){
            return $isValid->errors()->first();
        }

        $validated = $isValid->validated();

        $readingType = isset($input['reading_type']) ? (int)$input['reading_type'] : 1;

        $sensorReadings = TemperatureReading::whereBetween('reading_dt',[$validated['date_from'], $validated['date_to']])
            ->get();

        if ($sensorReadings->isEmpty()) {
            return 'No data found!';
        }

        return $this->calcTemperatureAVG($sensorReadings, $this->reverseType[$readingType], $readingType);
    }

    /**
     * Middle temperature for a particular sensor readings, in one-hour range.
     */
    public function getAverageFromSensor(Request $request): float|bool|string{
        $input = $request->all();

        $isValid = Validator::make($input,[
            'sensor_uuid' => 'required|string'
        ]);

        if($isValid->fails()){
            return $isValid->errors()->first();
        }

        $validated = $isValid->validated();

        $readingType = isset($input['reading_type']) ? (int)$input['reading_type'] : 1;

        $sensor = TemperatureSensor::select('id')
            ->where('sensor_uuid','=',$validated['sensor_uuid'])
            ->first();

        if(empty($sensor->id)){
            return 'Sensor not found!';
        }

        $sensorReadings = TemperatureReading::where('sensor_id', '=', $sensor->id)
            ->whereRaw('`reading_dt` >= datetime(\'now\', \'-1 Hour\')')
            ->get();

        if ($sensorReadings->isEmpty()) {
            return false;
        }

        return $this->calcTemperatureAVG($sensorReadings, $this->reverseType[$readingType], $readingType);
    }

    /**
     * Calculate average temperature.
     */
    public function calcTemperatureAVG($sensorReadings, int $reverseType, int $readingType): string|false {

        foreach ($sensorReadings as $readingItem) {
            $temperature = $readingItem['temperature'];

            if ($readingItem['reading_type'] === $reverseType) {
                $temperature = $this->convertTemperature($readingItem['temperature'], $readingItem['reading_type']);
            }

            $avgArr[] = (float)$temperature;
        }

        $avgTemperature = number_format(array_sum($avgArr) / count($avgArr), 3, '.');

        return $avgTemperature . ' ' . $this->temperatureType[$readingType];
    }

    /**
     * Convert type: 1 - From Celsius to Fahrenheit, 2 - From Fahrenheit to Celsius
     */
    public function convertTemperature(float $temperature, int $type): float {
        return match ($type) {
            2 => round((($temperature - 32) * 5) / 9, 3),
            default => round((($temperature * 9 / 5) + 32), 3),
        };
    }
}
