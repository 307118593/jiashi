<?php

namespace App\Http\Controllers;
use DB;
use QrCode;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;
class CompanyController extends Controller
{
	public function companyHome(Request $request){
		$cid = $request->input('cid',2);
		$banner = DB::table('banner')->where('cid',$cid)->orderBy('sort','desc')->get();
		foreach ($banner as $k => $v) {
			if ($v->image) {
				$banner[$k]->image = $this->upload.$v->image;
			}
		}
		$company = DB::table('admin_users')->where('id',$cid)->select('id','name','avatar','content','image','address','style','year','age','homeurl','tel','logo','company_bg')->first();
		if ($company->image) {
			$company->image = $this->host.$company->image;
		}
		if ($company->avatar) {
			$company->avatar = $this->upload.$company->avatar;
		}
		if ($company->logo) {
			$company->logo = $this->host.$company->logo;
		}
		if ($company->company_bg) {
			$company->company_bg = $this->host.$company->company_bg;
		}

		$designer = DB::table('admin_users')->where('pid',$cid)->where('job',3)->select('id','name','avatar','style','year','position','username','honor','content')->orderBy('is_up',1)->orderBy('sort','desc')->take(5)->get();
			foreach ($designer as $k => $v) {
				if ($v->avatar) {
					$designer[$k]->avatar = $this->upload.$v->avatar;
				}
			}

		$cases = DB::table('cases')->where('cid',$cid)->where('is_up',1)->orderBy('sort','desc')->take(10)->get();
			foreach ($cases as $k => $v) {
				if ($v->photo) {
					$cases[$k]->photo = $this->upload.$v->photo;
				}
				$cases[$k]->author = DB::table('admin_users')->where('id',$v->uid)->value('name');
			}
		$build = DB::table('admin_users')->where('pid',$cid)->where('job',11)->first();
		$data['build_team'] = 0;
		if ($build) {
			$data['build_team'] = 1;
		}
		$data['banner'] = $banner;
		$data['company'] = $company;
		$data['designer'] = $designer;
		$data['cases'] = $cases;


		return response()->json(['error'=>0,'data'=>$data]);
	}
	//公司首页1031 多地址
	public function companyHome1031(Request $request){
		$cid = $request->input('cid',2);
		if ($cid == 0) {
			$cid = 2;
		}
		if ($cid > 0) {//公司首页
				$banner = DB::table('banner')->where('cid',$cid)->orderBy('sort','desc')->get();
				foreach ($banner as $k => $v) {
					if ($v->image) {
						$banner[$k]->image = $this->upload.$v->image;
					}
				}
				$company = DB::table('admin_users')->where('id',$cid)->select('id','name','avatar','content','image','address','style','year','age','homeurl','tel')->first();
				if ($company->image) {
					$company->image = $this->host.$company->image;
				}
				if ($company->avatar) {
					$company->avatar = $this->upload.$company->avatar;
				}
				$fg = strstr($company->address, ';');;
				if (!$fg) {
					$company->addressinfo[0]['address'] =  $company->address;
					$company->addressinfo[0]['tel'] =  $company->tel;
				}else{
					$address = explode(";", $company->address);
					$tel = explode(";", $company->tel);
					$k = min(count($address),count($tel));
					for ($i=0; $i <$k ; $i++) { 
						$company->addressinfo[$i]['address'] = $address[$i];
						$company->addressinfo[$i]['tel'] = $tel[$i];
					}
				}
				$designer = DB::table('admin_users')->where('pid',$cid)->where('job',3)->select('id','name','avatar','style','year','position','username','honor','content')->orderBy('is_up',1)->orderBy('sort','desc')->take(5)->get();
					foreach ($designer as $k => $v) {
						if ($v->avatar) {
							$designer[$k]->avatar = $this->upload.$v->avatar;
						}
					}

				$cases = DB::table('cases')->where('cid',$cid)->where('is_up',1)->orderBy('sort','desc')->take(10)->get();
					foreach ($cases as $k => $v) {
						if ($v->photo) {
							$cases[$k]->photo = $this->upload.$v->photo;
						}
						$cases[$k]->author = DB::table('admin_users')->where('id',$v->uid)->value('name');
					}
				$data['banner'] = $banner;
				$data['company'] = $company;
				$data['designer'] = $designer;
				$data['cases'] = $cases;

		}
		

		return response()->json(['error'=>0,'data'=>$data]);
	}

	//设计师列表
	public function designer_list(Request $request){
		$cid = $request->input('cid',2);
		$designer = DB::table('admin_users')->where('pid',$cid)->where('job',3)->select('id','name','avatar','style','year','content','position','honor','content')->orderBy('sort','desc')->get();
		foreach ($designer as $k => $v) {
			if ($v->avatar) {
				$designer[$k]->avatar = $this->upload.$v->avatar;
			}
		}
		return response()->json(['error'=>0,'data'=>$designer]);
	}

	//设计师详情
	public function designer_detail(Request $request){
		$uid = $request->input('uid');
		$designer = DB::table('admin_users')->where('id',$uid)->where('job',3)->select('id','name','avatar','style','year','content','position','honor','background')->first();
		if ($designer->avatar) {
			$designer->avatar = $this->upload.$designer->avatar;
		}
		$cases = DB::table('cases')->where('uid',$uid)->orderBy('sort','desc')->get();
		foreach ($cases as $k => $v) {
			if ($v->photo) {
				$cases[$k]->photo = $this->upload.$v->photo;
			}
			$cases[$k]->author = DB::table('admin_users')->where('id',$v->uid)->value('name');
		}
		$data['designer'] = $designer;
		$data['cases'] = $cases;
		return response()->json(['error'=>0,'data'=>$data]);
	}

	//设计师案例
	public function designer_cases(Request $request){
		$uid = $request->input('uid');
		$cases = DB::table('cases')->where('uid',$uid)->orderBy('sort','desc')->get();
		foreach ($cases as $k => $v) {
			if ($v->photo) {
				$cases[$k]->photo = $this->upload.$v->photo;
			}
			$cases[$k]->author = DB::table('admin_users')->where('id',$v->uid)->value('name');
		}
		return response()->json(['error'=>0,'data'=>$cases]);
	}

	public function get_pics(Request $request){
		$cid = $request->input('cid');
		$page = $request->input('page', 1);
		$page_size = $request->input('page_size', 10);
		$count = DB::table('pics')->where('cid',$cid)->count();
		$pagenum = ceil($count / $page_size);
		if ($page > $pagenum){
			return response()->json(['error'=>0,'mes'=>'没有更多了']);
		}
		$pageStart = ($page -1) * $page_size;
		// if ($pageStart > 0) {
			// $pageStart = $pageStart;
		// }
		$pics = DB::table('pics')->where('cid',$cid)->orderBy('sort','desc')->skip($pageStart)->take($page_size)->get();
		foreach ($pics as $k => $v) {
			$pics[$k]->image = $this->upload.$v->image;
		}
		$data['pics'] = $pics;
		$data['page'] = $page;
		$data['pagenum'] = $pagenum;
		return response()->json(['error'=>0,'data'=>$data]);


	}
	// 获取邀请页面链接
	public function getShareLink(Request $request){
		$array = [
			2=>'',
			7=>'',
			15=>'hp',
			72=>'asj',
			75=>'yfj',
			79=>'dy',
		];
		$cid = $request->input('cid');
		$key = array_keys($array);
		if (!in_array($cid,$key)) {
			return response()->json(['error'=>1,'mes'=>'未开放']);
		}
		$co = DB::table('admin_users')->where('id',$cid)->select('name','sharetitle','sharecontent','logo')->first();
		$data['title'] = $co->name.'诚邀你体验装饰直播';
		$data['content'] = '透明装修直播让装修更放心，还有项目管理与进度监控';
		if ($co->sharetitle) {
			$data['title'] = $co->sharetitle;
		} 
		if ($co->sharecontent) {
			$data['content'] = $co->sharecontent;
		}
		$data['logo'] = $this->host.$co->logo;
		$invitation = 1000+$cid;
		$data['invite'] = 'https://www.homeeyes.cn/app/livedemo/'.$array[$cid].'/'.$array[$cid].'invite.html?invitation='.$invitation;
		$data['sharecode'] = 'https://www.homeeyes.cn/app/livedemo/'.$array[$cid].'/'.$array[$cid].'sharecode.html';
		return response()->json(['error'=>0,'data'=>$data]);

	}
	public function getAndriod(){
        // QrCode::format('png')->size(500)->generate('https://888.ph100.cn/qrcode?shopid='.$shopid,public_path('H5qrcodes/pay_'.$shopid.'.png'));
	}

	//获取施工团队里列表
	public function getBuildTeam(Request $request){
		$cid = $request->input('cid',2);
		$build = DB::table('admin_users')->where('pid',$cid)->where('job',11)->select('id','name','avatar','build_number','year','star','praise','medal')->orderBy('medal','asc')->orderBy('sort','desc')->get();
		foreach ($build as $k => $v) {
			if ($v->avatar) {
				$build[$k]->avatar = $this->upload.$v->avatar;
				$build[$k]->rank = $k+1;
				$build[$k]->praise = $v->praise."%";
			}
		}
		return response()->json(['error'=>0,'data'=>$build]);

	}

	// 获取项目经理详情
	public function getBuilderDetail(Request $request){
		$uid = $request->input('uid');
		$builder = DB::table('admin_users')->where('id',$uid)->select('id','name','avatar','build_number','year','star','praise','medal')->first();
		if ($builder->avatar) {
			$builder->avatar = $this->upload.$builder->avatar;
		}
		$build_cases = DB::table('build_case')->where('uid',$uid)->orderBy('sort','desc')->get();
		foreach ($build_cases as $k => $v) {
			if ($v->photo) {
				$v->photo = $this->upload.$v->photo;
			}
			$build_cases[$k]->keting = $this->duotu($v->keting);
			$build_cases[$k]->woshi = $this->duotu($v->woshi);
			$build_cases[$k]->weishengjian = $this->duotu($v->weishengjian);
			$build_cases[$k]->chufang = $this->duotu($v->chufang);
			$build_cases[$k]->shuidianshigong = $this->duotu($v->shuidianshigong);
			$build_cases[$k]->qiqianggongyi = $this->duotu($v->qiqianggongyi);
			$build_cases[$k]->mugonggongyi = $this->duotu($v->mugonggongyi);
			$build_cases[$k]->youqigongyi = $this->duotu($v->youqigongyi);

		}
		$data['builder'] = $builder;
		$data['build_cases'] = $build_cases;
		return response()->json(['error'=>0,'data'=>$data]);

	}

	//获取施工案例详情
	public function getBuildDetail(Request $request){
		$bid = $request->input('bid');
		$build_cases = DB::table('build_case')->where('id',$bid)->first();
		if ($build_cases->photo) {
			$build_cases->photo = $this->upload.$build_cases->photo;
		}
		$build_cases->keting = $this->duotu($build_cases->keting);
		$build_cases->woshi = $this->duotu($build_cases->woshi);
		$build_cases->weishengjian = $this->duotu($build_cases->weishengjian);
		$build_cases->chufang = $this->duotu($build_cases->chufang);
		$build_cases->shuidianshigong = $this->duotu($build_cases->shuidianshigong);
		$build_cases->qiqianggongyi = $this->duotu($build_cases->qiqianggongyi);
		$build_cases->mugonggongyi = $this->duotu($build_cases->mugonggongyi);
		$build_cases->youqigongyi = $this->duotu($build_cases->youqigongyi);
		
		return response()->json(['error'=>0,'data'=>$build_cases]);
	}

	public function duotu($images){
		if (!$images) {
			return;
		}
		$images = json_decode($images);
		$pic = [];
		foreach ($images as $k => $v) {
			$pic[] = $this->upload.$v;
		}
		return $pic;
	}

}