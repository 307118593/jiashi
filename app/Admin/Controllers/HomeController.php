<?php

namespace App\Admin\Controllers;
use DB;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Collapse;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Form;
use Encore\Admin\Widgets\InfoBox;
class HomeController extends Controller
{
    public function index()
    {
        return Admin::content(function (Content $content) {
          //admin_toastr("统计暂未开放!");
          //return redirect(admin_url('admin'));
            // $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $content->header('首页');
            $content->description('数据统计');
            $userid = admin::user()->id;
            $role = getRole($userid);//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $pid = admin::user()->pid;
            $cid = 0;
            if ($role == 2) {
                $cid = $userid;
            }elseif($role == 3 || $role == 4){
                $cid = $pid;
            }elseif($role == 1){//管理员
                $content->row(function(Row $row) use($role,$cid){
                    //设备数量->已分配->未分配->设备使用率->使用率
                    $comeraCount =  DB::table('camera')->count();
                    $online =  DB::table('camera')->where('status',1)->count();
                    $fenpeiCount =  DB::table('camera')->whereRaw('(uid> ? or pro_id > ?)', [0,0])->count();
                    $meiCount =  DB::table('camera')->where('uid',0)->where('pro_id',0)->count();
                    $shiyonglv = 0;
                    if ($comeraCount != 0) {
                        $shiyonglv = $fenpeiCount/$comeraCount * 100 ;
                    }
                    $CAMERA = new InfoBox('已分配:'.$fenpeiCount.'台,未分配:'.$meiCount.'台,实时在线:'.$online.'台', 'fa-cogs', 'aqua', '/admin/camera', '设备数量:'.$comeraCount.',   使用率:'.round($shiyonglv,1).'%');

                    //工地数量->施工中数量->已完成数量->客户总数
                    $projectCount = DB::table('project')->count();
                    $project = DB::table('project')->select('id')->get();
                    $shigongzhong = 0;
                    foreach ($project as $k => $v) {
                        $wcjd = DB::table('flow')->where('state','>',0)->where('pro_id',$v->id)->count();
                        if ($wcjd > 0) {
                            $shigongzhong ++;
                        }
                    }
                    $yiwancheng = 0;
                    foreach ($project as $k => $v) {
                        $wcjd = DB::table('flow')->where('state','>',0)->where('pro_id',$v->id)->count();
                        $flows = DB::table('flow')->where('pro_id',$v->id)->count();
                        if ($wcjd == $flows && $flows > 0) {
                            $yiwancheng ++;
                        }
                    }
                    $userCount = DB::table('user')->where('is_copy',0)->count();
                    // $yaoqingma = $cid + 1000;
                    $PROJECT = new InfoBox('正在施工:'.$shigongzhong, 'fa-cogs', 'orange', '/admin/project', '工地数量:'.$projectCount);

                    //员工总数->总监->设计师
                    $staffCount = DB::table('admin_users')->count();
                    $zongjianCount = DB::table('admin_users')->whereIn('job',[1,10])->count();
                    $designCount = DB::table('admin_users')->where('job',3)->count();
                    $companyCount = DB::table('admin_users')->where('pid',0)->count();
                    $newUserConut = DB::table('user')->where('addtime','>',date('Y-m-d'))->count();
                    $STAFF = new InfoBox('员工数:'.$staffCount.'人', 'fa-cogs', 'green', '/admin/staff', '公司数量:'.$companyCount.'个');
                    $USER = new InfoBox('本月新增客户:'.$newUserConut, 'fa-cogs', 'purple', '/admin/user', '注册客户:'.$userCount.'人');
                    $row->column(3, $CAMERA);
                    $row->column(3, $PROJECT);
                    $row->column(3, $STAFF);
                    $row->column(3, $USER);
                });//第一行结束
            
            }
            
            if ($role == 5) {//代理商
                $content->row(function(Row $row) use($role,$userid){
                    //公司id
                    $companyid = DB::table('admin_users')->where('did',$userid)->pluck('id');
                    // dd($companyid);
                    //设备数量->已分配->未分配->设备使用率->使用率
                    $comeraCount =  DB::table('camera')->where('did',$userid)->count();
                    $online =  DB::table('camera')->where('did',$userid)->where('status',1)->count();
                    $fenpeiCount =  DB::table('camera')->whereRaw('(uid> ? or pro_id > ?)', [0,0])->where('did',$userid)->count();
                    $meiCount =  DB::table('camera')->where('uid',0)->where('pro_id',0)->where('did',$userid)->count();
                    $shiyonglv = 0;
                    if ($comeraCount != 0) {
                        $shiyonglv = $fenpeiCount/$comeraCount * 100 ;
                    }
                    $CAMERA = new InfoBox('已分配:'.$fenpeiCount.'台,未分配:'.$meiCount.'台,实时在线:'.$online.'台', 'fa-cogs', 'aqua', '/admin/camera', '设备数量:'.$comeraCount.',   使用率:'.round($shiyonglv,1).'%');

                    //工地数量->施工中数量->已完成数量->客户总数
                    $projectCount = DB::table('project')->whereIn('z_uid',$companyid)->count();
                    $project = DB::table('project')->select('id')->whereIn('z_uid',$companyid)->get();
                    $shigongzhong = 0;
                    foreach ($project as $k => $v) {
                        $wcjd = DB::table('flow')->where('state','>',0)->where('pro_id',$v->id)->count();
                        if ($wcjd > 0) {
                            $shigongzhong ++;
                        }
                    }
                    $yiwancheng = 0;
                    foreach ($project as $k => $v) {
                        $wcjd = DB::table('flow')->where('state','>',0)->where('pro_id',$v->id)->count();
                        $flows = DB::table('flow')->where('pro_id',$v->id)->count();
                        if ($wcjd == $flows && $flows > 0) {
                            $yiwancheng ++;
                        }
                    }
                    $userCount = DB::table('user')->whereIn('cid',$companyid)->where('is_copy',0)->count();
                    // $yaoqingma = $cid + 1000;
                    $PROJECT = new InfoBox('正在施工:'.$shigongzhong, 'fa-cogs', 'orange', '/admin/project', '工地数量:'.$projectCount);

                    //员工总数->总监->设计师
                    $staffCount = DB::table('admin_users')->whereIn('pid',$companyid)->count();
                    // $staffCount = DB::table('admin_users')->where('did',$userid)->pluck('id');
                    // $zongjianCount = DB::table('admin_users')->whereIn('job',[1,10])->count();
                    // $designCount = DB::table('admin_users')->where('job',3)->count();
                    $companyCount = DB::table('admin_users')->where('did',$userid)->count();
                    $newUserConut = DB::table('user')->where('addtime','>',date('Y-m-d'))->whereIn('cid',$companyid)->count();
                    $STAFF = new InfoBox('员工数:'.$staffCount.'人', 'fa-cogs', 'green', '/admin/staff', '公司数量:'.$companyCount.'个');
                    $USER = new InfoBox('本月新增客户:'.$newUserConut, 'fa-cogs', 'purple', '/admin/user', '注册客户:'.$userCount.'人');
                    $row->column(3, $CAMERA);
                    $row->column(3, $PROJECT);
                    $row->column(3, $STAFF);
                    $row->column(3, $USER);
                });//第一行结束
            }
            


            if ($role == 2 || $role == 4){//负责人或总监
                $content->row(function(Row $row) use($role,$cid){
                    //设备数量->已分配->未分配->设备使用率->使用率
                    $comeraCount =  DB::table('camera')->where('cid',$cid)->count();
                    $online =  DB::table('camera')->where('cid',$cid)->where('status',1)->count();
                    $fenpeiCount =  DB::table('camera')->where('cid',$cid)->whereRaw('(uid> ? or pro_id > ?)', [0,0])->count();
                    $meiCount =  DB::table('camera')->where('cid',$cid)->where('uid',0)->where('pro_id',0)->count();
                    $shiyonglv = 0;
                    if ($comeraCount != 0) {
                        $shiyonglv = $fenpeiCount/$comeraCount * 100 ;
                    }
                    $CAMERA = new InfoBox('已分配:'.$fenpeiCount.'台,未分配:'.$meiCount.'台,实时在线:'.$online.'台', 'fa-cogs', 'aqua', '/admin/camera', '设备数量:'.$comeraCount.',   使用率:'.round($shiyonglv,1).'%');

                    //工地数量->施工中数量->已完成数量->客户总数
                    $projectCount = DB::table('project')->where('z_uid',$cid)->count();
                    $project = DB::table('project')->where('z_uid',$cid)->select('id')->get();
                    $shigongzhong = 0;
                    foreach ($project as $k => $v) {
                        $wcjd = DB::table('flow')->where('state','>',0)->where('pro_id',$v->id)->count();
                        if ($wcjd > 0) {
                            $shigongzhong ++;
                        }
                    }
                    $yiwancheng = 0;
                    foreach ($project as $k => $v) {
                        $wcjd = DB::table('flow')->where('state','>',0)->where('pro_id',$v->id)->count();
                        $flows = DB::table('flow')->where('pro_id',$v->id)->count();
                        if ($wcjd == $flows && $flows > 0) {
                            $yiwancheng ++;
                        }
                    }
                    $userCount = DB::table('user')->where('cid',$cid)->where('is_copy',0)->count();
                    $yaoqingma = $cid + 1000;
                    $PROJECT = new InfoBox('正在施工:'.$shigongzhong, 'fa-cogs', 'orange', '/admin/project', '工地数量:'.$projectCount);

                    //员工总数->总监->设计师
                    $staffCount = DB::table('admin_users')->where('pid',$cid)->count();
                    $zongjianCount = DB::table('admin_users')->where('pid',$cid)->whereIn('job',[1,10])->count();
                    $designCount = DB::table('admin_users')->where('pid',$cid)->where('job',3)->count();
                    $STAFF = new InfoBox('设计师团队:'.$designCount.'人', 'fa-cogs', 'green', '/admin/staff', '员工数:'.$staffCount.'人,总监:'.$zongjianCount);
                    $USER = new InfoBox('公司邀请码:'.$yaoqingma, 'fa-cogs', 'purple', '/admin/user', '注册客户:'.$userCount.'人');
                    $row->column(3, $CAMERA);
                    $row->column(3, $PROJECT);
                    $row->column(3, $STAFF);
                    $row->column(3, $USER);
                });//第一行结束
                $content->row(function(Row $row) {
                    $tab = new Tab();
                    $tab->add('Pie', 'db2_field_display_size(stmt, column)');
                    $tab->add('Table', 'new Table()');
                    $tab->add('Text', '水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,水电费水电费第三方,');
                   
                    // echo $tab->render();
                    // $row->column(12, $tab);
                });
            }//负责人总监结束--
            // if ($cid == 2) {
                $content->row(function(Row $row) use($role,$cid,$userid){

                    $date = date('m-d', strtotime('-7 days'));
                    $day = date('Y-m-d', strtotime('-7 days'));
                    if ($role == 1) {//管理员
                        $camera = DB::table('camera')->select('mac','name')->get();
                    }else if($role == 5){//代理商
                        // $companyid = DB::table('admin_users')->where('did',$userid)->pluck('id');
                        $camera = DB::table('camera')->where('did',$userid)->select('mac','name')->get();
                    }else{
                        $camera = DB::table('camera')->where('cid',$cid)->select('mac','name')->get();
                    }
                    
                    foreach ($camera as $k => $v) {
                        $camera[$k]->alive = round(DB::table('camera_log')->where('mac',$v->mac)->where('day','>',$day)->sum('alivetime')/60,1);
                        if ($camera[$k]->alive  == 0) {
                            unset($camera[$k]);
                        }
                    }
                    $camera = $camera->toArray();
                    $camera = array_values($camera);
                    // dd($camera);
                    $pie = new Box('近一周设备观看比例', view('admin.chartjs.pie',compact('camera')));
                    $row->column(7, $pie);

                    for ($i=0; $i < 7; $i++) { 
                        $alive[$i]['date'] = date('m-d', strtotime('-'.$i.' days'));
                        $alive[$i]['day'] = date('Y-m-d', strtotime('-'.$i.' days'));
                    }
                    foreach ($alive as $k => $v) {
                        if ($role == 1) {//管理员
                            $alive[$k]['alive'] = round(DB::table('camera_log')->where('day',$v['day'])->sum('alivetime')/60,1);
                        }else if($role == 5){//代理商
                            $companyid = DB::table('admin_users')->where('did',$userid)->pluck('id');
                            $alive[$k]['alive'] = round(DB::table('camera_log')->whereIn('cid',$companyid)->where('day',$v['day'])->sum('alivetime')/60,1);
                        }else{
                            $alive[$k]['alive'] = round(DB::table('camera_log')->where('cid',$cid)->where('day',$v['day'])->sum('alivetime')/60,1);
                        }
                        // $alive[$k]['alive'] = round(DB::table('camera_log')->where('cid',$cid)->where('day',$v['day'])->sum('alivetime')/60,1);
                       
                    }
                    
                    // dd($alive);
                    $line = new Box('近一周设备观看总时长', view('admin.chartjs.line',compact('alive')));
                    $row->column(5, $line);


                
                });
            // }
                
        });
    }
}
