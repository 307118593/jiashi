<?php

namespace App\Admin\Controllers;

use Admin;
use App\Record;
use App\Staff;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class RecordController
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
            ->header('登陆记录')
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
            ->header('登陆记录')
            ->description('列表')
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
            ->header('登陆记录')
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
            ->header('登陆记录')
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
        $grid = new Grid(new Record);
            $userid = admin::user()->id;
            $role = getRole($userid);//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $pid = admin::user()->pid;
        $grid->model()->orderBy('id','desc')->where('alivetime','>',0);
        if($role == 2){
            $cid = $userid;
        }elseif($role == 4){
            $cid = $pid;
        }
        if ($role != 1) {
            $grid->model()->where('cid',$cid);
        }
        $grid->id('ID');
        $grid->column('user.name','用户名称');
        if ($role == 1) {
            $grid->column('admin_users.name','所属公司');
        }
        $grid->alivetime('在线时长/分钟')->display(function($alivetime){
            return round($alivetime/60,1);
        })->label('primary')->size('18px');
        $grid->endtime('最后退出')->display(function($endtime){
            return date("Y-m-d H:i",$endtime);
        });
        $grid->day('日期')->sortable()->label()->size('18px');

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->filter(function($filter) use($role){
            $filter->disableIdFilter();
            $filter->like('user.name','用户名称');
            $filter->equal('user.phone','用户手机号');
            $filter->day('day', '日期');
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
        $show = new Show(Record::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Record);



        return $form;
    }
}
