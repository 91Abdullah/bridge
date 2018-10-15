<?php

namespace App\Http\Controllers;

use App\Record;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
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
        $report = Record::whereDate("start", $dt->format("Y-m-d"))->get(['id', 'source', 'destination', 'start', 'answer', 'end', 'duration', 'billsec', 'dialstatus', 'bridged_call_id']);
        //get('id', 'source', 'destination', 'start', 'answer', 'end', 'duration', 'billsec', 'dialstatus', 'bridged_call_id');
//        return dd($report);
        return DataTables::of($report)->make();
    }

    public function test()
    {
        $value = "9bbf04ce-4009-4e36-b603-5660227e1fd8";
        $response = Curl::to("http://10.0.0.80:8088/ari/recordings/stored/$value/file")
            ->withData(["api_key" => "asterisk:asterisk"])
            ->get();

        return $response;
    }
}
