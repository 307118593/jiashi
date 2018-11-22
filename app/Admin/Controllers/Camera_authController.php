<?php

namespace App\Admin\Controllers;

use App\Camera_auth;
use App\User;
use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
// use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Request;

class Camera_authController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {   
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            if ($role == 3) {
                admin_toastr("暂无权限!");
                return redirect(admin_url('admin'));
            }
        return Admin::content(function (Content $content) {

            $content->header('分享用户');
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

            $content->header('分享用户');
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

            $content->header('分享用户');
            $content->description('添加');
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
        return Admin::grid(Camera_auth::class, function (Grid $grid) {
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            $mac = \request('mac');
            $grid->model()->orderBy('id','desc');
            if ($role == 2) {
                $grid->model()->where('cid',$userid);
            }
            if ($role == 4) {
                $grid->model()->where('cid',$pid);
            }
            if ($mac) {
                // session(['mac'=>$mac]);
                Request::session()->flash('mac',$mac);
                $grid->model()->where('mac',$mac);
            }
            // $grid->id('ID')->sortable();
            $grid->mac('设备标识');
            $grid->uid('分享用户')->display(function($uid){
                return DB::table('user')->where('id',$uid)->value('name').'<br>'.DB::table('user')->where('id',$uid)->value('phone');
            });
            // $grid->name('别名');
            $states = [
                'on'  => ['value' => 1, 'text' => '允许', 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => '禁用', 'color' => 'default'],
            ];
            $grid->allow('权限')->switch($states);
            $grid->addtime('添加时间');
            $grid->disableRowSelector();
            $grid->actions(function ($actions) {
                $actions->disableView();
            });
            // $grid->created_at();
            // $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id=0)
    {
        return Admin::form(Camera_auth::class, function (Form $form) use($id){
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            $where=[];
            if ($role == 2) {
                $where = ['cid'=>$userid];
            }else if ($role == 4) {
                $where = ['cid'=>$pid];
            }
            $mac = session('mac');
            // Session::forget('mac');
            // echo $mac;
            $form->hidden('id', 'ID');
            if ($id == 0 && empty($mac)) {
                $camera1 = DB::table('camera')->where($where)->select('name','mac')->get();
                foreach ($camera1 as $k => $v) {
                    $camera[$v->mac] = $v->mac.'--'.$v->name;
                }
                $form->select('mac','设备序列号*')->options($camera)->setwidth(4);
            }else{
                $form->display('mac','设备序列号*')->setwidth(2)->default($mac);
                $form->hidden('mac','设备序列号')->setwidth(2)->default($mac);
            }
            
            // $form->text('name','设备名称')->setwidth(3);

            $phones = DB::table('user')->whereNotNull('phone')->select('id','phone','name')->where($where)->get();
                foreach ($phones as $k => $v) {
                    $data[$v->id] = $v->phone.'--'.$v->name;
                }
            $form->select('uid','请输入子用户手机号')->options($data)->setwidth(4);
            // $form->select('uid','请输入子用户手机号*')->options(function ($id) {
            //     $user = User::find($id);

            //     if ($user) {
            //         return [$user->id => $user->phone];
            //     }
            // })->ajax('/admin/api/users')->setwidth(4)->help('直接在编辑框内输入手机号.系统匹配正确的手机号后选择.')->rules('required');
            $states = [
                'on'  => ['value' => 1, 'text' => '允许', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '禁用', 'color' => 'default'],
            ];
            $form->switch('allow','权限')->states($states)->default(1);
            $form->hidden('addtime', '时间')->default(date('Y-m-d H:i:s',time()));
            // $form->display('updated_at', 'Updated At');
            $form->saving(function(Form $form){
                $res = DB::table('camera_auth')->where('mac',$form->mac)->where('uid',$form->uid)->first();
                if ($res) {
                    $error = new MessageBag([
                        'title'   => '错误',
                        'message' => '该用户已经是该设备的子用户',
                    ]);

                    return back()->with(compact('error'));
                }
                $ret = DB::table('camera')->where('mac',$form->mac)->where('uid',$form->uid)->first();
                if ($ret) {
                    $error = new MessageBag([
                        'title'   => '错误',
                        'message' => '该用户已经是该设备的管理员,无需添加',
                    ]);

                    return back()->with(compact('error'));
                }
            });
            $form->saved(function(Form $form){
                $cid = DB::table('camera')->where('mac',$form->mac)->value('cid');
                DB::table('camera_auth')->where('mac',$form->mac)->update(['cid'=>$cid]);
            });
        });
    }

    public function users(Request $request)
    {
        $q = Request::get('q');

        return User::where('phone', 'like', "%$q%")->paginate(null, ['id', 'phone as text']);
    }

  
}
