<?php

namespace App\Admin\Controllers;

use App\Camera;
use App\User;
use App\Staff;
use DB;
use App\Admin\Extensions\Tools\Cameras;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\MessageBag;
class CameraController extends Controller
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

            $content->header('设备管理');
            $content->description('列表');
            // $content->body('<button type="button" class="btn btn-primary btn-lg active">Primary button</button>');
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

            $content->header('设备管理');
            $content->description('修改');

            $content->body($this->form()->edit($id));
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

            $content->header('设备管理');
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
        return Admin::grid(Camera::class, function (Grid $grid) {
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
             if (in_array(Request::get('cameras'), ['0', '1'])) {
                if (Request::get('cameras') == 0) {//0未分配,1已分配
                    $grid->model()->where('uid',0)->where('pro_id',0);
                }else{
                    // $grid->model()->whereRaw('uid > 0 or pro_id > 0 ');
                    $grid->model()->whereRaw('(uid> ? or pro_id > ?)', [0,0]);
                    // $grid->model()->where('uid','>',0)->orWhere('pro_id','>',0);
                    // $grid->model()->Where('pro_id','>',0);
                }
            }
            $grid->model()->orderBy('id','desc');
            if ($role == 2) {
                $grid->model()->where('cid',$userid);
            }
            if ($role == 4) {
                $grid->model()->where('cid',$pid);
            }
            if ($role == 1) {
                $grid->id('id','ID')->sortable();
            }
            // $grid->id('ID')->sortable();
            $grid->mac('设备标识');
            $grid->name('别名');

            $grid->uid('绑定客户')->display(function($uid){
                return DB::table('user')->where('id',$uid)->value('name').'<br>'.DB::table('user')->where('id',$uid)->value('phone');
            });
            $grid->column('分享用户')->display(function(){
                $count = DB::table('camera_auth')->where('mac',$this->mac)->whereNotNull('uid')->count();
                return "<span style=''>人数:".$count."人</span><br><a style='color:#CC3300;' href='/admin/camera_auth?mac=$this->mac'>点击查看</a>";
            });
            
            if ($role == 1) {
                // $grid->cid('所属公司')->display(function($cid){
                //     return DB::table('admin_users')->where('id',$cid)->value('name');
                // });
                 $grid->column('admin_users.name','所属公司');
            }
            $grid->column('project.name','绑定工地');
            // $grid->account('账号');
            // $grid->pwd('密码');
            // $grid->is_share('共享')->display(function($is_share){
            //     return $is_share==0?'否':'<p style="color:green">共享</p>';
            // });
                 // <script src="http://47.97.109.9/resources/js/jquery1-1.9.1.js"></script><script src="http://47.97.109.9/resources/js/jquery-3.1.1.min.js"></script>

            
            /*
      function firm(id){
        var data = {id:id};
        $.ajax({
          url:"http://47.97.109.9/api/jiechubangding",
          data:data,
          dataType:"json",
          type:"POST",
          success:function(data){
             if(data.error == 0){
              location.reload(true);
            }
            if(data.code==1){
              alert("操作失败");
            }  
          }
        })
    }
    var mes = '确定要将设备名为\"'+name+'\"解除绑定吗?';
                        if(confirm(mes)){

                        }
            */
             
            $grid->addtime('新建时间');
            if (in_array(Request::get('cameras'), ['1'])) {
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
                $grid->column('观看直播')->display(function(){
                    return "<button type='button' class='btn btn-success btn-sm' onclick=\"live('$this->mac')\">点击观看</button>
                        <script>function live(mac){
                            window.open('/ysLive?mac='+mac,'查询','height=700,width=1300,top=250,left=250,toolbar=no,menubar=no,scrollbars=yes, resizable=no,location=no, status=no') ;
                        }</script>
                    ";
                });
                $grid->column('解除绑定')->display(function(){
                 
                    if ($this->uid>0 || $this->pro_id>0) {
                    return "<button type='button' class='btn btn-danger btn-sm' onclick=\"firm('$this->id','$this->name')\">解除绑定</button>
                    <script type='text/javascript'>
                         function firm(id,name){
                            
                                    confirm('设备名为\"'+name+'\"!', \"该操作将会设备初始化,包括子用户.\", function (isConfirm) {
                                        if (isConfirm) {
                                            var data = {id:id};
                                            $.ajax({
                                              url:\"http://47.97.109.9/api/jiechubangding\",
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
                
                    }
                });
            }
            echo '<link rel="stylesheet" href="http://47.97.109.9/css/BeAlert.css">
                    <script src="http://47.97.109.9/resources/js/BeAlert.js"></script>
                  ';
            $grid->disableExport();
            // if ($role != 1) {
            $grid->disableCreateButton();
                $grid->actions(function ($actions) {
                    $actions->disableDelete();$actions->disableView();
                    // $actions->disableEdit();
                });
            // }
            $grid->disableRowSelector();
            // $grid->actions(function ($actions) {
            //     // prepend一个操作
            //     $camera = $actions->row;
            //     if ($camera['uid']>0 || $camera['pro_id']>0) {
            //         $actions->prepend('<button type="button" class="btn btn-danger btn-xs">解绑</button><span>  ..</span>');
            //     }
                
            // });
            $grid->tools(function ($tools) use($role,$userid){
                $tools->append(new Cameras());
                $userid = time().$userid;
                if (Request::get('cameras') == 1) {
                    $tools->append('<a href="/lives/?userid='.$userid.'" target="_blank"><button type="button" class="btn  btn-info glyphicon glyphicon-camera btn-sm">观看直播</button></a>');
                }
                if ($role ==1 ) {
                    $tools->append('<button type="button"  data-loading-text="Loading..."  class="btn btn-danger btn-xs daoru">一键导入</button>
                        <script>
                  

                        $(".daoru").click(function(){
                            confirm("该操作会将萤石设备导入", "可能会花费较长时间.请不要刷新页面", function (isConfirm) {
                            if (isConfirm) {
                                $.ajax({
                                  url:"http://47.97.109.9/api/daoru",
                                  dataType:"json",
                                  type:"POST",
                                  success:function(data){
                                     if(data.error == 0){
                                      location.reload(true);
                                    }
                                    if(data.code==1){
                                      alert("操作失败");
                                    }  
                                  }
                                })
                            } else {
                            }
                        }, {confirmButtonText: "确定", cancelButtonText: "取消", width: 400});
                       });
                        </script>
                    ');
                }

                
            });
           

        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        // $mac = request::get('mac',0);
        // echo $mac;
        return Admin::form(Camera::class, function (Form $form){
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;

            $form->hidden('id', 'ID');
            $form->text('mac','设备序列号*')->setwidth(3)->rules(function ($form) {
                // 如果不是编辑状态，则添加字段唯一验证
                if (!$id = $form->model()->id) {
                    return 'unique:camera,mac';
                }

            });//C15769474
            // $form->text('code','设备六位验证码')->setwidth(3);//XIRIVB
            $form->text('name','设备名称*')->setwidth(3)->help('长度不大于20字，不能包含特殊字符');
            if ($role == 1) {
                $form->select('cid','选择公司')->options(Staff::all()->where('pid',0)->pluck('name', 'id'))->setwidth(4);
            }else{
                $form->hidden('cid', '公司')->default($userid);
            }
            if ($userid == 1) {
                $where = [];
            }else{
                if ($role == 2) {
                    $where = ['cid'=>$userid];
                }else{
                    $where = ['cid'=>$pid];
                }
            }
            $phones = DB::table('user')->whereNotNull('phone')->where($where)->select('id','phone','name')->get();
            foreach ($phones as $k => $v) {
                $data[$v->id] = $v->phone.'--'.$v->name;
            }
            $form->select('uid','绑定客户手机号')->options($data)->setwidth(3)->help('绑定了客户之后,客户可直接访问app查看直播');
            if ($userid == 1) {
                $where1 = [];
            }else{
                if ($role == 2) {
                    $where1 = ['z_uid'=>$userid];
                }else{
                    $where1 = ['z_uid'=>$pid];
                }
            }
            if ($userid == 1) {
                $form->dateRange('begintime', 'endtime', '租赁时间范围');
                $form->currency('money','押金金额')->symbol('￥');
            }
            $form->select('pro_id','请选择工地')->options(DB::table('project')->where($where1)->pluck('name','id'))->setwidth(5);
            $states = [
                'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '否', 'color' => 'default'],
            ];
            $form->switch('staff_share', '是否员工共享')->states($states)->default(0)->help('所有员工都可查看和操作设备');
            $statess = [
                'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '否', 'color' => 'default'],
            ];
            $form->switch('user_share', '是否客户共享')->states($statess)->default(0)->help('所有客户都可查看和操作设备');
            $form->hidden('addtime','添加时间')->default(date('Y-m-d H:i:s'));
            $form->saving(function(Form $form){
                $accessToken = $this->get_accessToken();
                if ($form->mac && $form->name) {
                    $ret = $this->vpost('https://open.ys7.com/api/lapp/device/name/update','accessToken='.$accessToken.'&deviceSerial='.$form->mac.'&deviceName='.$form->name);
                    $ret = json_decode($ret);
                    if ($ret->code != 200) {
                        $error = new MessageBag([
                            'title'   => '错误',
                            'message' => '修改名称失败.错误码:'.$ret->code,
                        ]);
                        return back()->with(compact('error'));
                    }
                }
                
            });
        });
    }
}
