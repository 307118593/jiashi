<?php

namespace App\Admin\Controllers;

use App\Residence;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Staff;
use Admin;
class ResidenceController
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('楼盘分类')
            ->description('列表')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('楼盘分类')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('楼盘分类')
            ->description('修改')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('楼盘分类')
            ->description('新建')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Residence);
        $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
        $userid = admin::user()->id;
        $pid = admin::user()->pid;
        if ($role == 1) {
            $cid = 0;
            $grid->model()->orderBy('cid','desc');
        }elseif($role == 2){
            $cid = $userid;
        }elseif($role == 4){
            $cid = $pid;
        }
        if ($role != 1) {
            $grid->model()->where('cid',$cid);
        }
        
        $grid->id('ID')->sortable();
        $grid->name('标题');
        $grid->image('楼盘图片')->image();
        $grid->sort('排序')->label();
        if ($role == 1) {
            $grid->column('admin_users.name','所属公司');
        }
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->disableRowSelector();
        $grid->disableExport();
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Residence::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Residence);
        $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
        $userid = admin::user()->id;
        $pid = admin::user()->pid;
        $form->text('name', '楼盘名称*')->setwidth(3);
        $form->image('image', '楼盘图片*')->help('请上传宽高比为2:1的图片适配App,大小不能超过1M')->resize(600,300)->setwidth(4)->uniqueName()->rules('max:1024');
        $form->number('sort', '排序权重')->help('权重越大.活动越靠前');
        if ($role == 1) {
            $form->select('cid', '选择公司')->options(Staff::all()->where('pid',0)->pluck('name','id'))->setwidth(3);
        }else{
            if ($role == 2) {
                $cid = $userid;
            }else{
                $cid = $pid;
            }
            $form->hidden('cid', '公司id')->default($cid);
        }
        return $form;
    }
}
