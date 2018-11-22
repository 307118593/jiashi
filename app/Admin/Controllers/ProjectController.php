<?php

namespace App\Admin\Controllers;

use App\Project;
use App\User;
use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Admin\Extensions\ExcelExpoter;
class ProjectController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            // echo session('pro_id');
            $content->header('项目管理');
            $content->description('列表');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('项目管理');
            $content->description('修改');

            $content->body($this->form($id)->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('项目管理');
            $content->description('新增');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Project::class, function (Grid $grid) {
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            // if ($userid != 1 && $pid ==0) {
            //     $grid->model()->where('z_uid',$userid)->orderBy('id','desc');
            // }else if($userid != 1 && $pid > 0){
            //     $grid->model()->where('z_uid',$pid)->orderBy('id','desc');
            // }
            $grid->model()->orderBy('id','desc');
            if ($role == 2) {
                $grid->model()->where('z_uid',$userid);
            }elseif($role == 3){
                $grid->model()->where('z_uid',$pid)->where('leader_id',$userid);
            }elseif($role == 4){
                $grid->model()->where('z_uid',$pid);
            }
            $grid->id('ID')->sortable();
            if ($userid != 1) {
                $grid->name('项目名称');
            }else{
                $grid->name('项目名称')->display(function($name){
                    return $name.'<br>('.DB::table('admin_users')->where('id',$this->z_uid)->value('name').')';
                });
            }
            
            $grid->column('业主')->display(function(){
                return DB::table('user')->where('id',$this->uid)->value('phone').'<br>'.DB::table('user')->where('id',$this->uid)->value('name');
            });
            $grid->column('负责人')->display(function(){
                $leader = DB::table('admin_users')->where('id',$this->leader_id)->select('username','name')->first();
                if ($leader) {
                    return $leader->name.'<br>'.$leader->username;
                } 
            });
            // $grid->project_us('项目成员')->display(function($project_us){
            //     foreach ($project_us as $k => $v) {
            //         $name[] = DB::table('admin_users')->where('id',$v)->value('name'); 
            //     }
            //     return $name;
            // })->implode('<br>')->badge();
            $grid->starttime_d('项目计划周期')->display(function($time){
                $endtime = date('Y-m-d',strtotime('+'.$this->month.' month',strtotime($time)));
                return str_limit($time, 10,'').'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;~<br>'.$endtime;
            });
            $grid->area('面积')->display(function($area){
                return $area;
            });
            $grid->type('半包全包')->display(function($type){
                return $type==0?'全包':'半包';
            });
            // $grid->state('装修状态')->display(function($state){
            //     switch ($state) {
            //         case 0:
            //             return '设计中';
            //             break;
            //         case 1:
            //             return '准备签单';
            //             break;
            //         case 2:
            //             return '施工中';
            //             break;
            //     }
            // });
            $grid->column('装修进度')->display(function(){
                $href = '<a style="color:#CC3300;" href="/admin/flow?pro_id='.$this->id.'">查看进度</a>';
                $wcjd = DB::table('flow')->where('state','>',0)->where('pro_id',$this->id)->count();
                if ($wcjd == 0) {
                    return '未开始<br>'.$href;
                }else{
                    $flows = DB::table('flow')->where('pro_id',$this->id)->count();
                  	if($flows == 0){
                        $jindu = 0;
                    }else{
                      $jindu = DB::table('flow')->where('state',2)->where('pro_id',$this->id)->count()/DB::table('flow')->where('pro_id',$this->id)->count()*100;

                    }
                    return '<span style="font-size:17px">'.ceil($jindu).'％</span><br>'.$href;
                }
            });
            $grid->column('进度播报')->display(function(){
                $href = "<a href=\"/admin/broadcast?pro_id=$this->id\"><button type='button' class='btn btn-success btn-sm'>查看</button></a>";
                return $href;
            });
            if ($role != 3) {
                $states = [
                    'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                    'off' => ['value' => 0, 'text' => '否', 'color' => 'default'],
                ];
                $grid->staff_share('是否员工共享')->switch($states);
                $statess = [
                    'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                    'off' => ['value' => 0, 'text' => '否', 'color' => 'default'],
                ];
                $grid->user_share('是否客户共享')->switch($statess);
            }
            // $grid->exporter(new ExcelExpoter());
            $filename="项目";

            $grid->exporter(new ExcelExpoter($grid,$filename));
            $grid->disableRowSelector();
            if ($userid == 1) {
                $grid->disableCreateButton();
            }
            $grid->actions(function ($actions) {
                $actions->disableDelete(); $actions->disableView();
                // $actions->disableEdit();
            });
            $grid->filter(function($filter){
                $filter->disableIdFilter();
                $filter->like('admin_users.name', '负责人');
                $filter->between('starttime_d', '项目开始时间')->datetime();

            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = 0)
    {
        return Admin::form(Project::class, function (Form $form) use($id){
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
           	if ($role == 2) {
           		$cid = $userid;
           	}else {
                $cid = $pid;
            }
            $form->tab('基本信息', function ($form) use($cid,$id,$role){
                $form->hidden('id', 'ID');
                // if ($userid == 1) {
                // 	$data = DB::table('admin_users')->where('pid',0)->select('id','name')->get();
                // 	foreach ($data as $k => $v) {
                // 		$company[$v->id] = $v->name;
                // 	}
                // 	$form->select('z_uid','所属装修公司')->options($company)->setWidth(3);
                // }else{
                // }
                $form->hidden('z_uid', '所属装修公司')->default($cid);
                $form->text('name','项目名称*')->help('如:申花壹号院5幢110室..')->setWidth(3)->rules('required|min:3');
                // $phones = DB::table('user')->select('id','phone','name')->get();
                // foreach ($phones as $k => $v) {
                //     $data[$v->id] = $v->phone.'--'.$v->name;
                // }
                $data = [];
                // $customer = DB::table('customer')->where('z_uid',$userid)->select('id','phone','name')->get();
                // foreach ($customer as $k => $v) {
                //     $data[$v->phone] = $v->phone.'--'.$v->name;
                // }
                if ($role == 1) {
                    $phones = DB::table('user')->select('id','phone','name')->whereNotNull('phone')->get();
                }else{
                    $phones = DB::table('user')->select('id','phone','name')->where('cid',$cid)->get();
                }
                
                foreach ($phones as $k => $v) {
                    $data[$v->id] = $v->phone.'--'.$v->name;
                }
                $form->select('uid','请输入业主手机号*')->options($data)->setWidth(3)->rules('required|min:1');
                if ($role == 1) {
                   $staff1 = DB::table('admin_users')->select('id','username','name')->get();  
                }else{
                   $staff1 = DB::table('admin_users')->where('pid',$cid)->select('id','username','name')->get();  
                }
                 
                
                
                foreach ($staff1 as $k => $v) {
                    $staff[$v->id] = $v->username.'--'.$v->name;
                }
                $form->select('leader_id','负责人*')->options($staff)->setWidth(3)->rules('required|min:1');
                $form->multipleSelect('project_us','项目成员')->options($staff)->setWidth(8)->help('负责人已经是项目成员.无需重复添加..');
                if ($role == 1) {
                    $ty1 = DB::table('user')->whereNotNull('phone')->select('id','phone','name')->get(); 
                }else{
                    $ty1 = DB::table('user')->where('cid',$cid)->select('id','phone','name')->get(); 
                }
                foreach ($ty1 as $k => $v) {
                    $ty[$v->id] = $v->phone.'--'.$v->name;
                }
                $form->multipleSelect('try_uid','体验人员')->options($ty)->setWidth(8)->help('添加体验人员可直接在用户客户端查看工地.');
                $form->date('starttime_d','计划开始日期*')->format('YYYY-MM-DD')->default(date('Y-m-d'));
                $form->number('month','项目周期/月*')->default(3)->rules('required|min:1');
                $form->number('area','面积/平方*')->rules('required|min:1');
                // $states = [
                //     'on'  => ['value' => 0, 'text' => '全包', 'color' => 'success'],
                //     'off' => ['value' => 1, 'text' => '半包', 'color' => 'danger'],
                // ];

                $form->select('type','装修类型')->options([0=>'全包',1=>'半包'])->default('1')->setWidth(2);
                // $form->select('state','装修状态')->options([0=>'设计中',1=>'准备签单',2=>'施工中'])->setWidth(2);
                $form->image('image','首图')->setWidth(3)->help('建议图片比例2:1')->uniqueName();
                // $form->multipleImage('pictures','轮播图')->removable();
                $temp = DB::table('flow_model')->where('z_uid',$cid)->groupBy('temp')->select('temp')->get();
                foreach ($temp as $k => $v) {
                    $temp[$k]->flow = DB::table('flow_model')->where('z_uid',$cid)->where('temp',$v->temp)->select('name')->orderBy('sort','asc')->get();
                }
                $flow = [];
                foreach ($temp as $k => $v) {
                    foreach ($v->flow as $kk => $vv) {
                        if ($kk == 0) {
                            $str = $vv->name;
                        }else{
                            $str .= '->'.$vv->name;
                        }
                    }
                    $flow[$v->temp] = '模板'.$v->temp.':'.$str;
                }
                $form->select('temp','选择流程模板')->options($flow)->help('流程模板在工地开始施工之后不可更改.但可在流程明细中修改具体的流程..');
                // $form->ignore(['temp']);
                if ($role != 3) {
                    $states = [
                        'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => '否', 'color' => 'default'],
                    ];
                    $form->switch('staff_share', '是否员工共享')->states($states)->default(0)->help('所有员工都可查看工地进度');
                    $statess = [
                        'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => '否', 'color' => 'default'],
                    ];
                    $form->switch('user_share', '是否客户共享')->states($statess)->default(0)->help('所有客户都可查看工地进度');
                }
                    
                $form->hidden('created_at', '添加时间');
                $form->hidden('updated_at', '更新时间');
            })->tab('项目材料',function($form){
                 $form->multipleImage('zxht','装修合同')->removable()->move('zxht')->uniqueName();
                 $form->multipleImage('ysqd','预算清单')->removable()->move('ysqd')->uniqueName();
                 $form->multipleImage('xmcl','项目材料')->removable()->move('xmcl')->uniqueName();
                 $form->multipleImage('xgt','效果图')->removable()->move('xgt')->uniqueName();
                 $form->multipleImage('yszp','验收照片')->removable()->move('yszp')->uniqueName();
                 $form->multipleImage('fkxx','收付款信息')->removable()->move('fkxx')->uniqueName();
            });
            // ->tab('设备绑定',function($form) use($id){
            //     // $form->display('备注')->with(function ($value) {
            //     //     return "sdfdsf";
            //     // });
            //     $uid = DB::table('project')->where('id',$id)->value('uid');
            //     // if ($id > 0 && $uid < 100000000) {
            //         $cameras = DB::table('camera')->where('uid',$uid)->select('mac','name')->get();
            //         $camera = [];
            //         if (!$cameras->isEmpty()) {
            //             foreach ($cameras as $k => $v) {
            //                $camera[$v->mac] = $v->name;
            //             }
            //         }
            //         $form->multipleSelect('cameras','请选择设备')->options($camera)->help('只有业主成为会员且项目新建完成之后才可绑定设备.');
                
            //     // }
                 
            //      // $form->text('uid','项目成员')->default($uid);
            // });


            $form->saving(function (Form $form) {
                $pro_id = $form->model()->id;
                $temp = $form->model()->temp;
                $z_uid = $form->model()->z_uid;
                $wcjd = DB::table('flow')->where('state','>',0)->where('pro_id',$pro_id)->count();
                if ($wcjd == 0) {
                    DB::table('flow')->where(['pro_id'=>$pro_id])->delete();
                    $flow = DB::table('flow_model')->where(['z_uid'=>$z_uid,'temp'=>$temp])->get();
                    foreach ($flow as $k => $v) {
                        $data = [
                            'pro_id'=>$pro_id,
                            'name'=>$v->name,
                            'sort'=>$v->sort,
                        ];
                        DB::table('flow')->insert($data);
                    }
                }

                
                
            });
            
            
        });
    }

    public function strToHtml($s){
    if ($s==null||$s.equals("")) return "";
    $s = $s.replaceAll("&","&");
    $s = $s.replaceAll("<","<");
    $s = $s.replaceAll(">",">");
    $s = $s.replaceAll(" "," ");
    //s = s.replaceAll("<br/>","/n");
    //s = s.replaceAll("'","'");
    return $s;
    }
}
