<?php

namespace App\Admin\Controllers;

use App\Customer;
use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class CustomerController extends Controller
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

            $content->header('客户管理');
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
        return Admin::grid(Customer::class, function (Grid $grid) {
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            $grid->model()->orderBy('id','desc');
            if ($role == 2) {
                $grid->model()->where('z_uid',$userid);
            }elseif($role == 3){
                $grid->model()->where('z_uid',$pid)->where('bywho',$userid);
            }elseif($role == 4){
                $grid->model()->where('z_uid',$pid);
            }
            
            $grid->name('客户姓名');
            $grid->phone('手机号');
            $grid->bywho('接手人')->display(function($bywho){
                return DB::table('admin_users')->where('id',$bywho)->value('name').'<br>'.DB::table('admin_users')->where('id',$bywho)->value('username');
            });
            $grid->addtime('添加时间');
            // $grid->id('ID')->sortable();

            // $grid->created_at();
            // $grid->updated_at();
            $grid->disableRowSelector();
            $grid->actions(function ($actions) {
                // $actions->disableDelete();
                $actions->disableView();
                // $actions->disableEdit();
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
        return Admin::form(Customer::class, function (Form $form) {
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            if ($userid != 1 && $pid > 0) {
                $userid  = $pid;
            }
            $form->hidden('id', 'ID');
            $form->hidden('z_uid', 'gongsi')->default($userid);
            $form->text('name','客户姓名');
            $form->mobile('phone','手机号')->options(['mask' => '99999999999'])->rules(function ($form) {
                // 如果不是编辑状态，则添加字段唯一验证
                if (!$id = $form->model()->id) {
                    return 'unique:customer,phone';
                }

            });
            if ($role == 2 || $role == 4) {
                $data = DB::table('admin_users')->where('pid',$userid)->whereBetween('job',[1,9])->select('id','name','job')->get();
                $staff = [];
                foreach ($data as $k => $v) {
                    switch ($v->job) {
                        case 1:$data[$k]->job = '销售总监';break;
                        case 2:$data[$k]->job = '销售';break;
                        case 3:$data[$k]->job = '设计师';break;
                        case 4:$data[$k]->job = '客服';break;
                    }
                    $staff[$v->id] = $v->name.'--'.$v->job;
                    
                }
                $form->select('bywho','选择接手人')->options($staff);
            }
            $form->hidden('addtime', '添加时间')->default(date('Y-m-d H:i:s',time()));
            // $form->display('created_at', 'Created At');
            // $form->display('updated_at', 'Updated At');
        });
    }
}
