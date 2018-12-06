<?php

namespace App\Admin\Controllers;

use App\Staff;
use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Show;

class StaffController extends Controller
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
           
            $content->header('员工管理');
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

            $content->header('员工管理');
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

            $content->header('员工管理');
            $content->description('创建');

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
        return Admin::grid(Staff::class, function (Grid $grid) {
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $pid = admin::user()->pid;
            $userid = admin::user()->id;
            $job = admin::user()->job;
            $grid->model()->where('pid','>',0)->orderBy('pid','desc')->orderBy('sort','desc')->orderBy('id','desc');
            if ($pid == 0) {
                $grid->model()->where('pid',$userid);
            }
            if ($role == 4) {
                $grid->model()->where('pid',$pid);
                if ($job == 1) {
                    $grid->model()->whereBetween('job',[1,10]);
                }
                if ($job ==11) {
                    $grid->model()->whereBetween('job',[11,20]);
                }
            }
            $grid->username('登陆账号');
            $grid->name('姓名');
            $grid->job('岗位')->display(function($job){
                switch ($job) {
                    case 1:
                        return '销售总监';
                        break;
                    case 2:
                        return '客户经理';
                        break;
                    case 3:
                        return '设计师';
                        break;
                    case 4:
                        return '客服';
                        break;
                    case 10:
                        return '工程总监';
                        break;
                    case 11:
                        return '项目经理';
                        break;
                    case 12:
                        return '施工人员';
                        break;
                    case 13:
                        return '工程监理';
                        break;
                   
                }
            });
            $grid->pid('公司')->display(function($pid){
                return DB::table('admin_users')->where('id',$pid)->value('name');
            });
            if ($role != 3) {
                $grid->sort('设计师排序')->label();
                // $states = [
                //     'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                //     'off' => ['value' => 0, 'text' => '否', 'color' => 'default'],
                // ];
                // $grid->is_up('是否首页显示')->switch($states);
                $grid->column('转为客户(临时功能)')->display(function(){
                    $res = DB::table('user')->where('phone',$this->username)->first();
                    if ($res) {
                        return '已有客户账号账号';
                    }
                    return "<button type='button' class='btn btn-danger btn-sm' onclick=\"firm('$this->id','$this->name')\">转为客户</button>
                    <script type='text/javascript'>
                         function firm(id,name){
                            
                                    confirm('员工名为\"'+name+'\"!', \"该操作会将员工的账号和密码转为客户,此记录会被保留.\", function (isConfirm) {
                                        if (isConfirm) {
                                            var data = {id:id};
                                            $.ajax({
                                              url:\"http://47.97.109.9/api/changeuser\",
                                              data:data,
                                              dataType:\"json\",
                                              type:\"POST\",
                                              success:function(data){
                                                 if(data.error == 0){
                                                  location.reload(true);
                                                }
                                                if(data.code==1){
                                                  alert(\"操作失败\");
                                                }  
                                              }
                                            })
                                        } else {
                                        }
                                    }, {confirmButtonText: '确定', cancelButtonText: '取消', width: 400});
                                                
                           }
                    </script>
                        ";
                
                });
                echo '<link rel="stylesheet" href="http://47.97.109.9/css/BeAlert.css">
                    <script src="http://47.97.109.9/resources/js/BeAlert.js"></script>
                  ';
            }
            
            // $grid->created_at('添加时间');
            $grid->updated_at('上次登录');
            $grid->actions(function ($actions) {
                $actions->disableView();
            });


            // $grid->disableCreateButton();
            $grid->disableRowSelector();
            $grid->filter(function($filter){
                $filter->disableIdFilter();
                $filter->equal('username', '手机号/账号');
                $filter->like('name', '昵称');
                $filter->equal('job','职位')->radio([
                        // ''   => 'All',
                        1    => '销售总监',
                        10    => '工程总监',
                        3    => '设计师',
                        11    => '项目经理',
                    ]);
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id=0)
    {
        return Admin::form(Staff::class, function (Form $form) use($id){
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $form->hidden('id', 'ID');
            $form->hidden('pid')->default($userid);
            
            $password = \Hash::make('111111');
            $form->hidden('password')->default($password);//默认密码
            $form->mobile('username', '员工手机号')->options(['mask' => '99999999999'])->help('手机号作为账号登陆,初始密码111111')->rules(function ($form) {
                    // 如果不是编辑状态，则添加字段唯一验证
                    if (!$id = $form->model()->id) {
                        return 'unique:admin_users,username';
                    }

                });
           
            $form->text('name','姓名')->setWidth(2)->rules('required|min:1');
            $jobs = [1=>'销售总监',2=>'--客户经理',3=>'--设计师',4=>'--客服',10=>'工程总监',11=>'--项目经理',12=>'--施工人员',13=>'--工程监理'];
            $form->select('job','岗位')->options($jobs)->default(2)->setwidth(4)->help('如果岗位是设计师或项目经理,请提交以后完善信息,将展示在APP内');
            // if ($id > 0) {
            //     $thejob = DB::table('admin_users')->where('id',$id)->value('job');
            // }
            $jobid=0;
            if (request()->route()->parameters) {
                $jobid = DB::table('admin_users')->where('id',request()->route()->parameters)->value('job');
            }
            if ($jobid == 3) {//设计师
                $form->divide();
                // $form->image('avatar','头像')->setwidth(3)->uniqueName();
                $form->cropper('avatar','头像')->cRatio(450,600)->uniqueName();
                $form->image('background','自定义背景图')->setwidth(3)->uniqueName()->help('自定义背景图,建议尺寸长宽比2:1,最大不能超过1M..');
                $form->text('position','设计师职位')->setwidth(2)->help('如:首席设计师,助理设计师');
                // $form->select('sex','性别')->options([0=>'男',1=>'女'])->setwidth(2);
                $form->text('style','设计风格')->setwidth(2)->help('如:简约,现代');
                // $form->text('address','所在地')->setwidth(2);
                $form->slider('year','工龄/经验')->options(['max' => 25, 'min' => 2, 'step' => 1, 'postfix' => '年'])->setwidth(8);
                $form->textarea('honor','个人荣誉')->setwidth(6);
                $form->text('content','设计理念')->setwidth(6)->help('设计师的以上信息将会展示在APP里.');
                // $form->divide();
            }else if($jobid == 11){
                $form->divide();
                 // $form->image('avatar','头像')->setwidth(3)->uniqueName();
                $form->cropper('avatar','头像')->cRatio(450,600)->uniqueName();
                $form->slider('year','工龄/经验')->options(['max' => 25, 'min' => 2, 'step' => 1, 'postfix' => '年'])->setwidth(8);
                $form->number('build_number','施工项目个数');
                $form->radio('medal','荣誉')->options([0=>'金牌',1=>'银牌'])->default(1)->help('金牌项目经理优先排序.首页只显示金牌经理且最多显示五名');
                // $form->slider('star','星级')->options(['max' => 5, 'min' => 1, 'step' => 1, 'postfix' => '星'])->setwidth(4);
                $form->starRating('star','星级')->default(4);
                $form->rate('praise','好评率')->setwidth(1);
            }
               

            if ($role != 3) {
                $form->text('sort','排序权重')->setwidth(2)->help('数字越大首页顺序越靠前,只对岗位设计师和项目经理有效')->default(0);
                // $states = [
                //     'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                //     'off' => ['value' => 0, 'text' => '否', 'color' => 'default'],
                // ];
                // $form->switch('is_up', '是否客户共享')->states($states)->default(0)->help('该设计师显示在首页,首页最多显示五个设计师');
            }else{
                $form->hidden('sort','排序权重');
            }
            $form->saving(function(form $form){
                // dump($form->avatar);exit;
            });
            $form->saved(function(form $form){
                $newid = DB::table('admin_users')->select('id')->where('username',$form->username)->value('id');
                if ($form->job == 1 || $form->job == 10) {
                    DB::table('admin_role_users')->where(['user_id'=>$newid])->delete();
                    DB::table('admin_role_users')->insert(['user_id'=>$newid,'role_id'=>4]);//设置用户为总监权限
                }else{
                    DB::table('admin_role_users')->where(['user_id'=>$newid])->delete();
                    DB::table('admin_role_users')->insert(['user_id'=>$newid,'role_id'=>3]);//设置用户为员工权限
                }

                $res = DB::table('user')->where('phone',$form->username)->first();
                if (empty($res)) {
                    $data = [
                        'phone'=>$form->username,
                        'password'=>\Hash::make('111111'),
                        'name'=>$form->name,
                        'addtime'=>date('Y-m-d H:i:s'),
                        'is_copy'=>1,
                        'cid'=>$form->pid,
                    ];
                    DB::table('user')->insert($data);
                }
            });
            $form->hidden('created_at', 'Created At');
            $form->hidden('updated_at', 'Updated At');
             
    //          $show->panel()
    // ->tools(function ($tools) {
    //     $tools->disableEdit();
    //     $tools->disableList();
    //     $tools->disableDelete();
    // });;
        });
    }
}
