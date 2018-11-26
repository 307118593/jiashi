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
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            $cid = 0;
            if ($role == 2) {
                $cid = $userid;
            }elseif($role == 3 || $role == 4){
                $cid = $pid;
            }else{
                admin_toastr("暂无权限!");
                return redirect(admin_url('admin'));
            }
            $content->header('首页');
            $content->description('数据统计');
            if ($role == 2 || $role == 4){//负责人或总监
                $content->row(function(Row $row) use($role,$cid){
                    //设备数量->已分配->未分配->设备使用率->使用率
                    $comeraCount =  DB::table('camera')->where('cid',$cid)->count();
                    $fenpeiCount =  DB::table('camera')->where('cid',$cid)->whereRaw('(uid> ? or pro_id > ?)', [0,0])->count();
                    $meiCount =  DB::table('camera')->where('cid',$cid)->where('uid',0)->where('pro_id',0)->count();
                    $shiyonglv = 0;
                    if ($comeraCount != 0) {
                        $shiyonglv = $fenpeiCount/$comeraCount * 100 ;
                    }
                    $CAMERA = new InfoBox('已分配:'.$fenpeiCount.'台,未分配:'.$meiCount.'台', 'fa-cogs', 'aqua', '/admin/camera', '设备数量:'.$comeraCount.',   使用率:'.round($shiyonglv,1).'%');

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
                    $zongjianCount = DB::table('admin_users')->where('pid',$cid)->whereIn('job',[1,11])->count();
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
                
            $content->row(function(Row $row) use($role,$cid){
                for ($i=0; $i < 7; $i++) { 
                    $date[$i] = date('m-d', strtotime('-'.$i.' days'));
                }
                // return $date;
                $charjs = new Box('近一周用户在线时长', view('admin.chartjs.line',compact('cc','date')));
                $row->column(6, $charjs);
            });
        });
    }
}
