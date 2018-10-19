<?php

namespace App\Http\Controllers;

use App\Record;
use Carbon\Carbon;
use function foo\func;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\DataTables;
use Ixudra\Curl\Facades\Curl;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function getData(Request $request)
    {
        $dt = new Carbon($request->date);
        $report = Record::whereDate("start", $dt->format("Y-m-d"))->get(['id', 'source', 'destination', 'start', 'answer', 'end', 'duration', 'billsec', 'dialstatus', 'amount', 'pin_code', 'bridged_call_id']);

        return DataTables::of($report)->make();
    }

    public function test()
    {
        $file = (object)parse_ini_file(storage_path("app\phpari.ini"), true);
        return dd($file->asterisk_ari);
        $value = "0d37503c-ed26-495a-bce0-8475810c188a";
        $obj = new \phpari("disa-test", storage_path("app\\phpari.ini"));
        $recording = $obj->recordings()->file($value);
        return $recording;

    }

//    public function getFile($fileId)
//    {
//        $file = (object)parse_ini_file(storage_path("app\\phpari.ini"), true);
//        $ari = $file->asterisk_ari;
//        $response = Curl::to($ari['protocol'] . "://" . $ari['host'] . ":" . $ari['port'] . $ari['endpoint'] . "/recordings/stored/$fileId/file")
//            ->withData(["api_key" => $ari['username'] . ":" . $ari['password']])
//            ->get();
//
//        return $response;
//    }

    public function getFile(Request $request)
    {
        $fileId = $request->file;
        $file = (object)parse_ini_file(storage_path("app\\phpari.ini"), true);
        $ari = $file->asterisk_ari;
        $response = Curl::to($ari['protocol'] . "://" . $ari['host'] . ":" . $ari['port'] . $ari['endpoint'] . "/recordings/stored/$fileId/file")
            ->withData(["api_key" => $ari['username'] . ":" . $ari['password']])
            ->get();
        $filePath = "public/" . $fileId . ".wav";
        $content = Storage::put($filePath, $response);
        return Storage::url($filePath);
    }

    public function downloadFile(Request $request)
    {
        $fileId = $request->file;
        $file = (object)parse_ini_file(storage_path("app\\phpari.ini"), true);
        $ari = $file->asterisk_ari;
        $response = Curl::to($ari['protocol'] . "://" . $ari['host'] . ":" . $ari['port'] . $ari['endpoint'] . "/recordings/stored/$fileId/file")
            ->withData(["api_key" => $ari['username'] . ":" . $ari['password']])
            ->get();
        $filePath = "recordings/" . $fileId . ".wav";
        $content = Storage::put($filePath, $response);
        return Storage::download($filePath);
    }
}
