<?php

namespace App\Admin\Controllers;

use App\Camera_log;
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

            $grid->id('ID')->sortable();
            $grid->mac('设备标识');
            $grid->column('user.phone','所属管理员')->display(function($phone){
                return DB::table('user')->where('phone',$phone)->value('name').'<br>'.$phone;
            });
            $grid->is_admin('身份')->display(function($is_admin){
                return $is_admin == 0?'管理员':'子用户';
            });
            $grid->addtime('绑定时间');
            $grid->losetime('解绑时间');
            $grid->disableCreateButton();
            $grid->disableRowSelector();
            $grid->filter(function($filter){
                $filter->disableIdFilter();
                $filter->like('mac', '设备标识');
                $filter->equal('user.phone', '手机号');
                $filter->equal('身份')->select([0 => '管理员',1=>'子用户']);
            });
            $grid->actions(function ($actions) {
                $actions->disableView();
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
