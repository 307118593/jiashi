<?php

namespace App\Admin\Controllers;

use App\Staff;
use App\Arts;
use Admin;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use DB;
class ArtsController
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
            ->header('工艺展示')
            ->description('列表')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('工艺展示')
            ->description('展示')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('工艺展示')
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
            ->header('工艺展示')
            ->description('新增')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Arts);
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
        // $grid->id('Id');
        $grid->name('工艺名称');
        $grid->images('图片')->map(function ($path) {
            return 'http://47.97.109.9/upload/'. $path;
        })->image();
        $grid->uids('作者列表')->map(function ($uid) {
            return DB::table('admin_users')->where('id',$uid)->value('name');
        })->implode('<br>')->size('17px')->badge();
         $grid->sort('排序')->label();
        if ($role == 1) {
            $grid->column('admin_users.name','所属公司');
            
        }
        $grid->created_at('新建');
        $grid->updated_at('更新');

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->filter(function($filter) use($role){
            $filter->disableIdFilter();
            $filter->like('name','工艺名称');
            // $filter->equal('admin_users.name','作者');
            if ($role == 1) {
                $filter->equal('cid','所属公司')->select(Staff::all()->where('pid',0)->pluck('name', 'id'));
            }
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Arts::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->cid('Cid');
        $show->images('Images');
        $show->uids('Uids');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Arts);
        $userid = admin::user()->id;
        $role = getRole($userid);//获取权限.1管理员.2公司负责人.3普通员工.4总监
        $pid = admin::user()->pid;
        $cid = $userid;
        if ($role != 2) {
            $cid = $pid;
        }
        $form->text('name', '工艺名称')->setwidth(3);
       
        $form->multipleImage('images', '全景图片')->help('你可以上传不超过3张全景图图片.')->removable()->move('arts')->uniqueName();;
        if ($role == 1) {
            $staff1 = DB::table('admin_users')->where('job',11)->select('id','username','name')->get();  
        }else{
            $staff1 = DB::table('admin_users')->where('pid',$cid)->where('job',11)->select('id','username','name')->get();  
        } 
        $staff = [];
        foreach ($staff1 as $k => $v) {
            $staff[$v->id] = $v->name;
        }
        $form->multipleSelect('uids', '作者')->help('可以选择多个作者,工艺会展示在作者名下.')->options($staff);
        $form->number('sort','排序权重')->help('数字越大越靠前.');
        if ($role == 1) {
            $form->select('cid','选择公司')->options(Staff::all()->where('pid',0)->pluck('name', 'id'))->setwidth(4);
        }else{
            $form->hidden('cid', '公司')->default($cid);
        }
        return $form;
    }
}
