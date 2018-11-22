<?php

namespace App\Admin\Controllers;

use App\User;
use App\Staff;
use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
class UserController extends Controller
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

            $content->header('客户管理');
            $content->description('列表');
             $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $pid = admin::user()->pid;
            $userid = admin::user()->id;
           if ($role == 2 || $role == 4) {
                if ($role == 2) {
                    $int = 1000 + $userid;
                }
                if ($role == 4) {
                    $int = 1000 + $pid;
                }
                // $content->body('<div class="alert alert-info alert-dismissible" role="alert" style="width:40%">
                //   <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                //   <strong><span style="font-size:18px">公司邀请码:'.$int.'...</span></strong>用户在注册时或个人中心输入邀请码,可以自动成为公司的客户.
                // </div>');
                $content->withInfo('公司邀请码:'.$int, '用户在注册时或个人中心输入邀请码,可以自动成为公司的客户.');
            }
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

            $content->header('客户管理');
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

            $content->header('客户管理');
            $content->description('新怎');

            $content->body($this->form());
        });
        $q = $request->get('q');
        // return User::where('phone', 'like', "%$q%")->paginate(null, ['phone', 'phone as text']);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(User::class, function (Grid $grid) {
            $grid->model()->orderBy('id','desc')->where('is_copy',0);

            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            if ($role == 2) {
                $grid->model()->where('cid',$userid);
            }elseif($role == 3){
                $grid->model()->where('bywho',$userid);
            }elseif($role == 4){
                $grid->model()->where('cid',$pid);
            }

            // $grid->id('ID')->sortable();
            // $grid->headurl('头像')->image(45,45);
            $grid->phone('手机号');
            $grid->name('姓名');
            $grid->address('地址');
            // $grid->bywho('接手人')->display(function($bywho){
            //     return DB::table('admin_users')->where('id',$bywho)->value('name').'<br>'.DB::table('admin_users')->where('id',$bywho)->value('username');
            // });
            if ($role == 1) {
                $grid->cid('所属公司')->display(function($cid){
                    return DB::table('admin_users')->where('id',$cid)->value('name');
                });
                $grid->column('oauth.type','第三方登录')->display(function($type){
                    return $type==1?'微信':'无';
                });
                
            }
            $grid->addtime('注册时间');
            $grid->uptime('最后登录')->sortable();
            if ($role == 3) {
                $grid->actions(function ($actions) {
                    $actions->disableDelete();
                    $actions->disableView();
                    $actions->disableEdit();
                });
            }else{
                $grid->column('转为员工')->display(function(){
                    $res = DB::table('admin_users')->where('username',$this->phone)->first();
                    if ($res) {
                        return '已有员工账号';
                    }
                    return "<button type='button' class='btn btn-danger btn-sm' onclick=\"firm('$this->id','$this->name')\">转为员工</button>
                    <script type='text/javascript'>
                         function firm(id,name){
                            
                                    confirm('客户名为\"'+name+'\"!', \"该操作会将客户的账号和密码转为员工,次记录会保留.\", function (isConfirm) {
                                        if (isConfirm) {
                                            var data = {id:id};
                                            $.ajax({
                                              url:\"http://47.97.109.9/api/changestaff\",
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
            $grid->actions(function ($actions) {
                    $actions->disableView();
                });
            $grid->disableRowSelector();
            $grid->filter(function($filter){
                $filter->disableIdFilter();
                $filter->equal('phone', '手机号');
                $filter->like('name', '昵称');
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
        return Admin::form(User::class, function (Form $form) use($id){
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            if ($userid != 1 && $pid > 0) {
                $userid  = $pid;
            }
            $form->hidden('id', 'ID');
            $form->text('name','客户姓名')->setWidth(3);
            $form->mobile('phone','手机号')->options(['mask' => '99999999999'])->help('客户初始登陆密码为手机号后六位,务必提醒客户登陆APP修改登录密码');
            // if ($id == 0) {
            //     $password = \Hash::make('111111');
            //     ->default($password);//默认密码
            //  }
            $form->hidden('password');
            $form->text('address','地址')->setWidth(4);
            if ($role != 1) {
                $form->hidden('cid', '公司')->default($userid);
                if ($role == 2 || $role == 4) {
                    $data = DB::table('admin_users')->where('pid',$userid)->whereBetween('job',[1,9])->select('id','name','job')->get();
                    $staff = [];
                    foreach ($data as $k => $v) {
                        switch ($v->job) {
                            case 1:$data[$k]->job = '销售总监';break;
                            case 2:$data[$k]->job = '客户经理';break;
                            case 3:$data[$k]->job = '设计师';break;
                            case 4:$data[$k]->job = '客服';break;
                        }
                        $staff[$v->id] = $v->name.'--'.$v->job;
                        
                    }
                    $form->select('bywho','选择接手人')->options($staff)->setwidth(3);
                }
            }else{

                $form->select('cid','选择公司')->options(Staff::all()->where('pid',0)->pluck('name', 'id'));
            }
            $form->hidden('addtime', 'Created At')->default(date('Y-m-d H:i:s'));
            // $form->hidden('uptime', 'Updated At');
            $form->saving(function(Form $form) {
                if (!$id = $form->model()->id) {
                // $form->phone = substr($form->phone,-6);
                $form->password = \Hash::make(substr($form->phone,-6));
                    

                    $res = DB::table('user')->where('phone',$form->phone)->first();
                    if ($res) {
                        $error = new MessageBag([
                            'title'   => '警告',
                            'message' => '该用户已经存在,请联系管理员操作.'.$id,
                        ]);

                        return back()->with(compact('error'));
                    }
                }
                
            });

        });
    }
}
