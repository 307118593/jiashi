<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use Umeng;
class PushController extends Controller
{
	public function send_push(){
		$device_token = 'xxxx';
	    $predefined = array(
	    	"ticker"=>"ticker",
            "title"=>"2003",
            "text"=>"2003",	
            "after_open" => "go_app");
	    $extraField = array(
	    	"test"=>"helloworld",

	    	); //other extra filed
	    // Umeng::android()->sendUnicast($device_token,$predefined,$extraField); //单播
	    // $res =  Umeng::android()->sendBroadcast($predefined); //广播
    			// Umeng::android()->sendGroupcast($filter = [], $predefined= [], $extraField = []); //组播
	    // $filter = ['where'=>['and'=>['or'=>['tag'=>2001]]]];
	    $filter = '{"where":{"and":[{"or":[{"tag":"2000"}]}]}}';
	    $res = Umeng::android()->sendGroupcast($filter, $predefined,$extraField);
	    return $res;
		// $data = [
		// 	'appkey'=>$this->Appkey,
		// 	'timestamp'=>time(),
		// 	'type'=>'groupcast',
		// 	"device_tokens"=>"",
		// 	"alias_type"=> "",
		// 	"alias"=>"",
		// 	"file_id"=>"",
		// 	"filter"=>[
		// 		'tag'=>'company'
		// 	],
		// 	"payload"=>[
		// 			"display_type"=>"message",
		// 			"body"=>[
		// 			  "custom"=>"dsfdsfa"
		// 			]
		// 		]
		// ];

		// $data = json_encode($data,true);
		// $mysign = MD5('POST'.'http://msg.umeng.com/api/send'.$data.$this->AppMasterSecret);
		// $res  = $this->vpost('http://msg.umeng.com/api/send?sign='.$mysign,$data);
		// return $res;
	}
}
