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
				// if (empty($v->url)) {
				// 	$v->url = 'http://www.homeeyes.cn/app/3DShow/index.html?type=1&case_id='.$v->id;
				// }
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

		//更新数量
		//楼盘数量
		$residenceCount = DB::table('residence')->where('cid',$cid)->count();
		//设计师
		$designerCount = DB::table('admin_users')->where('pid',$cid)->where('job',3)->count();
		//活动
		$actCount = DB::table('activitys')->where('cid',$cid)->where('type',1)->count();
		//项目团队
		$xmjl = DB::table('admin_users')->where('pid',$cid)->where('job',11)->pluck('id');
		$xmjl = $xmjl->toArray();
		$buildCount = DB::table('build_case')->whereIn('uid',$xmjl)->count() + DB::table('arts')->where('cid',$cid)->count();
		$data['residenceCount'] = $residenceCount;
		$data['designerCount'] = $designerCount;
		$data['actCount'] = $actCount;
		$data['buildCount'] = $buildCount;
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
						// if (empty($v->url)) {
						// 	$v->url = 'http://www.homeeyes.cn/app/3DShow/index.html?type=1&case_id='.$v->id;
						// }
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
			if ($v->panorama) {
				$v->panorama = $this->duotu($v->panorama);
			}
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
			// if ($v->panorama) {
			// 	$v->panorama = $this->duotu($v->panorama);
			// }
			// if (empty($v->url)) {
			// 	$v->url = 'http://www.homeeyes.cn/app/3DShow/index.html?type=1&case_id='.$v->id;
			// }
		}
		return response()->json(['error'=>0,'data'=>$cases]);
	}

	//案例详情
	public function case_detail(Request $request){
		$case_id = $request->input('case_id');
		$cases = DB::table('cases')->where('id',$case_id)->first();
		if ($cases->photo) {
			$cases->photo = $this->upload.$cases->photo;
		}
		$cases->author = DB::table('admin_users')->where('id',$cases->uid)->value('name');
		// if ($cases->panorama) {
		// 	$cases->panorama = $this->duotu($cases->panorama);
		// }
	
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
			// $build_cases[$k]->keting = $this->duotu($v->keting);
			// $build_cases[$k]->woshi = $this->duotu($v->woshi);
			// $build_cases[$k]->weishengjian = $this->duotu($v->weishengjian);
			// $build_cases[$k]->chufang = $this->duotu($v->chufang);
			// $build_cases[$k]->shuidianshigong = $this->duotu($v->shuidianshigong);
			// $build_cases[$k]->qiqianggongyi = $this->duotu($v->qiqianggongyi);
			// $build_cases[$k]->mugonggongyi = $this->duotu($v->mugonggongyi);
			// $build_cases[$k]->youqigongyi = $this->duotu($v->youqigongyi);

		}
		$arts = DB::table('arts')->where('uids','like','%"'.$uid.'"%')->orderby('sort','desc')->get();
		foreach ($arts as $k => $v) {
			$arts[$k]->images = $this->duotu($v->images);
			$arts[$k]->url = 'http://www.homeeyes.cn/app/3DShow/index.html?type=0&case_id='.$v->id;
		}
		$data['builder'] = $builder;
		$data['arts'] = $arts;
		$data['build_cases'] = $build_cases;
		return response()->json(['error'=>0,'data'=>$data]);

	}

	//获取工艺详情 
	public function artDetail(Request $request){
		$art_id = $request->input('case_id');
		$type = $request->input('type',0);//0是工艺1是案例
		if ($type == 0) {
			$art = DB::table('arts')->where('id',$art_id)->first();
			$art->images = $this->duotu($art->images);
			return response()->json(['error'=>0,'data'=>$art]);
		}else{
			$case = DB::table('cases')->where('id',$art_id)->first();
			$case->images = $this->duotu($case->panorama);
			return response()->json(['error'=>0,'data'=>$case]);
		}
		
	}

	//获取施工案例详情
	public function getBuildDetail(Request $request){
		$bid = $request->input('bid');
		$build_cases = DB::table('build_case')->where('id',$bid)->first();
		$build_cases->builder = DB::table('admin_users')->where('id',$build_cases->uid)->value('name');
		$build_cases->builder_avatar = $this->upload.DB::table('admin_users')->where('id',$build_cases->uid)->value('avatar');
			
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

	//公司统计
	public function companyRecord(Request $request){
		 $cid = $request->input('cid');
		 $uid = $request->input('uid');
		 $data['comeraCount'] =  DB::table('camera')->where('cid',$cid)->count();
		 $data['projectCount'] = DB::table('project')->where('z_uid',$cid)->count();
		 $data['staffCount'] =  DB::table('admin_users')->where('pid',$cid)->count();
		 $data['userCount'] =   DB::table('user')->where('cid',$cid)->where('is_copy',0)->count();
		 $data['invitation'] =   $cid + 1000;
		 $month = $this->getMonth(date('Y-m-d'));
		 // return $month;
		 $data['newCustomer'] = DB::table('user')->where('cid',$cid)->whereBetween('addtime',$month)->count();
		 for ($i=0; $i < 7; $i++) { 
	        $date[$i]['day'] = date('Y-m-d', strtotime('-'.$i.' days'));
	    	}
	    	$date = array_reverse($date);
	     foreach ($date as $k => $v) {
	     	$date[$k]['count'] = DB::table('record')->where('day',$v['day'])->count();
	     }
	     $data['alive'] = $date;

	     $project = DB::table('project')->where('leader_id',$uid)->orWhere('project_us','like','%"'.$uid.'"%')->orderBy('state','asc')->get();
	     $project = $project->toArray();
	     // dd($project);
	     $own = [];
	     $own['projectCount'] = count($project);
	     $i=0;$j=0;$x=0;
	     foreach ($project as $k => $v) {
	     	if ($v->state < 2) {
	     		$i ++;
	     	}
	     	if ($v->state == 2 ) {
	     		$j ++;
	     	}
	     	if ($v->state == 3) {
	     		$x ++;
	     	}
	     }
	     $own['notStartCount'] = $i;
	     $own['onStartCount'] = $j;
	     $own['endtStartCount'] = $x;
	     $alldata['companydata'] = $data;
	     $alldata['own'] = $own;
		 return response()->json(['error'=>0,'data'=>$alldata]);
	}


	//多图处理
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

 	function getMonth($date){
	    $firstday = date("Y-m-01",strtotime($date));
	    $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));
	    return array($firstday,$lastday);
	}
	//月度报表
	public function getMonthRecord(Request $request){
		$cid = $request->input('cid');
		//直播数据--
		//1.直播总时间
		//2.直播时间最多的前三个设备和时间
		//3.观看直播最长的三个人
		//4.比上个月相比的直播时长

		//项目数据--
		//1.本月新增项目
		//2.本月开始的项目
		//3.本月完成的项目

		//用户数据--
		//1.本月新增用户
		
		//消费数据--
		//.本月消费金额
	}

}