<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vehicle;
use App\Models\Make;
use App\Models\Range;
use App\Models\Model;
use App\Models\Derivative;
use App\Models\Image;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class VehicleController extends Controller
{
    public function uploadContent(Request $request){

        $file = $request->file('uploaded_file');

        if ($file) {
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension(); //Get extension of uploaded file
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize(); //Get size of uploaded file in bytes

            //Where uploaded file will be stored on the server
            $location = 'uploads'; //Created an "uploads" folder for that

            // Upload file
            $file->move($location, $filename);

            // In case the uploaded file path is to be stored in the database
            $filepath = public_path($location . "/" . $filename);

            // Reading file
            $file = fopen($filepath, "r");
            $importData_arr = array(); // Read through the file and store the contents as an array

            $i = 0;
            //Read the contents of the uploaded file
            while (($filedata = fgetcsv($file, 10000, ",")) !== FALSE) {

                $num = count($filedata);

                // Skip first row (Remove below comment if you want to skip the first row)
                if ($i == 0) {
                $i++;
                continue;
                }

                for ($c = 0; $c < $num; $c++) {
                    $importData_arr[$i][] = $filedata[$c];
                }
                $i++;
            }

            fclose($file); //Close after reading

            $current_date = Carbon::now();

            $j = 0;

            foreach ($importData_arr as $importData) {

                $reg = $importData[0];
                $make = $importData[1];
                $range = $importData[2];
                $model = $importData[3];
                $derivative = $importData[4];
                $price_inc_vat = str_replace(',', '', $importData[5]);
                $colour = $importData[6];
                $mileage = $importData[7];
                $vehicle_type = $importData[8];
                $date_on_forecourt = $importData[9];
                if ($current_date > $date_on_forecourt){
                    $available = true;
                } else {
                    $available = false;
                }

                $images = explode(',', $importData[10]);
                $images = array_filter($images);

                if (isset($reg) && ($price_inc_vat > 0) && (count($images) >= 3)){

                try {
                    DB::beginTransaction();

                    if(!Make::where('name', $make)->exists()){
                        $makeModel = Make::create([
                            'name' => $make
                        ]);
                    } else {
                        $makeModel = Make::where('name', $make)->get()->first()->fresh();
                    }

                    if(!Range::where('name', $range)->exists()){
                        $rangeModel = Range::create([
                            'make_id' => $makeModel->id,
                            'name' => $range,
                        ]);
                    } else {
                        $rangeModel = Range::where('name', $range)->get()->first()->fresh();
                    }

                    if(!Model::where('name', $model)->exists()){
                        $modelModel = Model::create([
                            'range_id' => $rangeModel->id,
                            'name' => $model,
                        ]);
                    } else {
                        $modelModel = Model::where('name', $model)->get()->first()->fresh();
                    }

                    if(!Derivative::where('name', $derivative)->exists()){
                        $derivativeModel = Derivative::create([
                            'model_id' => $modelModel->id,
                            'name' => $derivative,
                        ]);
                    } else {
                        $derivativeModel = Derivative::where('name', $derivative)->get()->first()->fresh();
                    }


                    if(!Vehicle::where('reg', $reg)->exists()){

                        $vehicleModel = Vehicle::create([
                            'make_id' => $makeModel->id,
                            'range_id' => $rangeModel->id,
                            'model_id' => $modelModel->id,
                            'derivative_id' => $derivativeModel->id,
                            'reg' => $reg,
                            'colour' => $colour,
                            'price_including_vat' => $price_inc_vat,
                            'mileage' => $mileage,
                            'vehicle_type' => $vehicle_type,
                            'date_on_forecourt' => $date_on_forecourt,
                            'available' => $available,
                        ]);

                        foreach ($images as $image){
                            Image::create([
                                'vehicle_id' => $vehicleModel->id,
                                'src' => $image,
                            ]);
                        }

                        $j++;

                    }

                    DB::commit();

                    $this->sendEmail('adamluckman@yahoo.co.uk', $i, $j);



                } catch (\Exception $e) {
                    //throw $th;
                    DB::rollBack();
                }

                }

            }
            return response()->json([
                'message' => "$i total records, $j records successfully uploaded"
            ]);
        }
    }

    public function sendEmail($email, $i, $j)
    {
    $data = array(
    'email' => $email,
    'subject' => 'Stoneacre Vehicle Import Report',
    'total_records' => $i,
    'successfully_imported' => $j,
    'failed' => ($i - $j),
    );
    Mail::send('importReportEmail', $data, function ($message) use ($data) {
    $message->from('noreply@stoneacre.com');
    $message->to($data['email']);
    $message->subject($data['subject']);
    });
    }

    public function exportFord(Request $request)
    {
        $fileName = time() . '.csv';

        $vehicles = Vehicle::whereHas('make', function($q){
            $q->where('name', 'Ford');
         })->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Vehicle', 'Price Exc VAT', 'VAT Amount', 'Image');

        $callback = function() use($vehicles, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($vehicles as $vehicle) {
                $row['Vehicle']  = $vehicle->vehicleName();
                $row['Price Exc VAT'] = '£'.round($vehicle->price_including_vat/1.2, 2);
                $row['VAT Amount'] = '£'.round(($vehicle->price_including_vat/1.2)/5, 2);
                $row['Image']  = $vehicle->images[0]->src;

                fputcsv($file, array($row['Vehicle'], $row['Price Exc VAT'], $row['VAT Amount'], $row['Image']));
            }

            fclose($file);

            $ftp_server = env("FTP_SERVER", "");
            $ftp_user_name = env("FTP_USER_NAME", "");
            $ftp_user_pass = env("FTP_USER_PASS", "");
            $remote_file = "";

            $conn_id = ftp_connect($ftp_server);

            $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

            if (ftp_put($conn_id, $remote_file, $file, FTP_ASCII)) {
                echo "successfully uploaded $file\n";
                exit;
            } else {
                echo "There was a problem while uploading $file\n";
                exit;
            }

            ftp_close($conn_id);
        };



        return response()->stream($callback, 200, $headers);
    }

}
