<?php

namespace App\Admin\Controllers;

use App\Camera;
use App\User;
use App\Staff;
use DB;
use App\Admin\Extensions\Tools\Cameras;
use App\Admin\Extensions\Tools\Fenpei;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\MessageBag;
use App\Admin\Extensions\ExcelExpoter;
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
            $userid = admin::user()->id;
            $role = getRole($userid);//获取权限.1管理员.2公司负责人.3普通员工.4总监
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
            if ($role == 5) {//代理商
                // $companyid = DB::table('admin_users')->where('did',$userid)->pluck('id');
                // $grid->model()->whereIn('cid',$companyid);
                $grid->model()->where('did',$userid);
                // $grid->id('id','ID')->sortable();
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
            
            if ($role == 1 || $role ==5) {
                // $grid->cid('所属公司')->display(function($cid){
                //     return DB::table('admin_users')->where('id',$cid)->value('name');
                // });
                if ($role == 1) {
                    $grid->column('代理商')->display(function(){
                        return DB::table('admin_users')->where('id',$this->did)->value('name');
                     });
                }
                 $grid->column('所属公司')->display(function(){
                    return DB::table('admin_users')->where('id',$this->cid)->value('name');
                 });
                 
            }
            $grid->column('绑定工地')->display(function(){
                return DB::table('project')->where('id',$this->pro_id)->value('name');
             });
            // $grid->addtime('新建时间');
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
                $statesss = [
                    'on'  => ['value' => 0, 'text' => '允许', 'color' => 'success'],
                    'off' => ['value' => 1, 'text' => '禁用', 'color' => 'default'],
                ];
                $grid->is_playback('回放')->switch($statesss);
                $grid->status('是否在线')->display(function($status){
                    // if ($this->status == 1) {
                    return $status == 0?"<span style='color:#999'>离线</span>":"<span style='color:green'>在线</span>";
                    // }
                });
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
                             var domain = window.location.host;
                            console.log(domain);
                            if(domain=='47.97.109.9'){//防止跨域退出登录
                                var host = 'www.homeeyes.cn';
                            }else{
                                var host = '47.97.109.9';
                            }
                                    confirm('设备名为\"'+name+'\"!', \"该操作将会设备初始化,包括子用户.\", function (isConfirm) {
                                        if (isConfirm) {
                                            var data = {id:id};
                                            $.ajax({
                                              url:\"http://\"+host+\"/api/jiechubangding\",
                                              data:data,
                                              dataType:\"json\",
                                              type:\"POST\",
                                              success:function(data){
                                                 if(data.error == 0){
                                                  $.pjax.reload('#pjax-container');
                                                  toastr.success('操作成功');
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
            // $grid->disableExport();
            // if ($role != 1) {
            $grid->disableCreateButton();
                $grid->actions(function ($actions) {
                    $actions->disableDelete();$actions->disableView();
                    // $actions->disableEdit();
                });
            // }
            if ($role != 1 ) {
                $grid->disableRowSelector();
            }
if ($role == 1) {
        $dids = DB::table('admin_role_users')->where('role_id',8)->pluck('user_id');
            $company = Staff::all()->where('pid',0)->whereNotIn('id',$dids)->pluck('name', 'id');
            $dali = Staff::all()->whereIn('id',$dids)->pluck('name', 'id');
            $comop = "<option value = 0>选择公司</option>";
            $daliop = "<option value = 0>选择代理商</option>";
            // dd($company);
            foreach ($company as $k => $v) {
                $comop .= "<option value =".$k.">".$v."</option>";
            }
            foreach ($dali as $k => $v) {
                $daliop .= "<option value =".$k.">".$v."</option>";
            }
echo "<div class='modal fade' id='createFileMModal' role='dialog' aria-labelledby='exampleModalLabel' aria-hidden='true'>
  <div class='modal-dialog' role='document'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title' id='createFileTitle'>请选择分配的公司或代理商</h5>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
          <span aria-hidden='true'>&times;</span>
        </button>
      </div>
      <div class='modal-body'>
        <form>
        <select id='com' class='form-control'>
            ".$comop."
        </select>
        </form>
      </div>
       <div class='modal-body'>
        <form>
        <select id='daili' class='form-control'>
            ".$daliop."
        </select>
        </form>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-primary' id='createFileSureBut'>确定</button>
      </div>
    </div>
  </div>
</div>
";
}
        
            $grid->tools(function ($tools) use($role,$userid){
                if ($role == 1) {
                    $tools->batch(function ($batch) {
                        $batch->disableDelete();
                        $batch->add('批量分配', new Fenpei());
                    });
                }
                
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
                                 var domain = window.location.host;
                            console.log(domain);
                            if(domain=="47.97.109.9"){//防止跨域退出登录
                                var host = "www.homeeyes.cn";
                            }else{
                                var host = "47.97.109.9";
                            }
                            if (isConfirm) {
                                $.ajax({
                                  url:"http://"+host+"/api/daoru",
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
           

            $grid->filter(function($filter) use($role){
                $filter->disableIdFilter();
                $filter->column(1/2, function ($filter) use($role) {
                    $filter->equal('mac', '设备序列号');
                    $filter->like('name', '设备名称');
                    if ($role == 1) {
                        $filter->equal('cid','所属公司')->select(Staff::all()->where('pid',0)->pluck('name', 'id'));
                    }
                    
                });
                $filter->column(1/2, function ($filter) {
                    $filter->like('user.name', '绑定的客户名称');
                    $filter->like('project.name', '绑定的工地名称');
                    
                });
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
            $userid = admin::user()->id;
            $role = getRole($userid);//获取权限.1管理员.2公司负责人.3普通员工.4总监
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
            if ($role == 1 || $role == 5) {
                
                if ($role == 1) {
                    $dids = DB::table('admin_role_users')->where('role_id',8)->pluck('user_id');
                    $form->select('cid','选择公司')->options(Staff::all()->where('pid',0)->whereNotIn('id',$dids)->pluck('name', 'id'))->setwidth(4);
                    $form->select('did','选择代理商')->options(Staff::all()->whereIn('id',$dids)->pluck('name', 'id'))->setwidth(4);
                }else{//代理商
                    $form->select('cid','选择公司')->options(Staff::all()->where('did',$userid)->pluck('name', 'id'))->setwidth(4);
                }
                

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
             if ($role == 5) {
                $companyid = DB::table('admin_users')->where('did',$userid)->pluck('id');
                $phones = DB::table('user')->whereNotNull('phone')->whereIn('cid',$companyid)->select('id','phone','name')->get();
            }
            $data = [];
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
            $pros = DB::table('project')->where($where1)->pluck('name','id');
            if ($role == 5) {
                $companyid = DB::table('admin_users')->where('did',$userid)->pluck('id');
                $phones = DB::table('user')->whereNotNull('phone')->whereIn('cid',$companyid)->select('id','phone','name')->get();
            }
            $form->select('pro_id','请选择工地')->options($pros)->setwidth(5);
            if ($userid == 1) {
                $form->dateRange('begintime', 'endtime', '租赁时间范围');
                $form->currency('money','押金金额')->symbol('￥');
            }

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
            $statesss = [
                'on'  => ['value' => 0, 'text' => '允许', 'color' => 'success'],
                'off' => ['value' => 1, 'text' => '禁用', 'color' => 'default'],
            ];
            $form->switch('is_playback', '是否允许客户查看回放')->states($statesss)->default(0);
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
