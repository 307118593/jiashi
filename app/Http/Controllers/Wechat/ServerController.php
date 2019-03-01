<?php 
namespace App\Http\Controllers\wechat;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Session;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Transfer;
class ServerController extends Controller
{
// $config = [
//     'app_id' => 'wx3cf0f39249eb0xxx',
//     'secret' => 'f1c242f4f28f735d4687abb469072xxx',
//     'token' => 'zjs',
//     'response_type' => 'array',
//     //...
// ];
	public function index(){
		
		$this->app = Factory::officialAccount(config('wechat.official_account.default'));
		// $app = Factory::officialAccount(config('wechat'));
		// dd($app);
		$response = $app->server->serve();

		// 将响应输出
		return $response;
	}
}