<?php

namespace App\Admin\Controllers;

use App\Camera_log;
use App\Camera;
use App\Staff;
use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class Camera_logController extends Controller
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

            $content->header('设备日志');
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

            $content->header('header');
            $content->description('description');

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

            $content->header('header');
            $content->description('description');

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
        return Admin::grid(Camera_log::class, function (Grid $grid) {
            $userid = admin::user()->id;
            $role = getRole($userid);//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $pid = admin::user()->pid;

            $grid->model()->orderBy('id','desc');
            if ($role != 1) {
                if ($role == 2) {
                    $cid = $userid;
                }else{
                    $cid = $pid;
                }
                $grid->model()->where('cid',$cid);
            }
            $grid->id('ID')->sortable();
            $grid->mac('设备标识');
            $grid->column('camera.name','设备名称');
            $grid->uid('观看人')->display(function($uid){
                return DB::table('user')->where('id',$uid)->value('name').'<br>'.DB::table('user')->where('id',$uid)->value('phone');
            });
           $grid->alivetime('当日观看时长/分钟')->display(function($alivetime){
                return round($alivetime/60,1);
            })->label('primary')->size('20px');
            $grid->closetime('最后退出')->display(function($closetime){
                return date("Y-m-d H:i",$closetime);
            });
            $grid->day('日期')->sortable()->label()->size('20px');

            $grid->disableCreateButton();
            $grid->disableRowSelector();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableView();
            });
            $grid->filter(function($filter){
                $filter->disableIdFilter();
                $filter->like('mac', '设备标识');
                $filter->equal('user.name', '用户名');
                $filter->equal('user.phone', '手机号');
                $filter->day('day', '日期');
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
        return Admin::form(Camera_log::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
