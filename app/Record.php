<?php

namespace App;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Ixudra\Curl\Facades\Curl;

class Record extends Model
{
    protected $fillable = ['source', 'destination', 'start', 'answer', 'end', 'duration', 'billsec', 'dialstatus', 'amount','pin_code', 'bridged_call_id', 'incoming_channel_id', 'outgoing_channel_id'];

//    public function getBridgedCallIdAttribute($value)
//    {
//        $response = Curl::to("http://10.0.0.80:8088/ari/recordings/stored/$value/file")
//            ->withData(["api_key" => "asterisk:asterisk"])
//            ->get();
//        return $response;
//    }

    public function getPinCodeAttribute($value)
    {
        return str_limit($value, 4, '');
    }

    public function getStartAttribute($value)
    {
        return Carbon::parse($value)->format("d-m-Y H:i:s");
    }

    public function getAnswerAttribute($value)
    {
        return Carbon::parse($value)->format("d-m-Y H:i:s");
    }

    public function getEndAttribute($value)
    {
        return Carbon::parse($value)->format("d-m-Y H:i:s");
    }

    public function bridged_call()
    {
    	return $this->belongsTo('App\BridgedCall');
    }

    public function incoming_channel()
    {
    	return $this->belongsTo('App\IncomingChannel');
    }

    public function outgoing_channel()
    {
    	return $this->belongsTo('App\OutgoingChannel');
    }
}
