<?php

namespace App\Admin\Controllers;

use App\Staff;
use Admin;
use App\Activitys;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Controllers\ModelForm;
class ActivitysController
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
            ->header('活动管理')
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
            ->header('活动管理')
            ->description('详情')
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
            ->header('活动管理')
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
            ->header('活动管理')
            ->description('创建')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Activitys);
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
        
        $grid->title('标题');
        $grid->image('首图')->image();
        $grid->type('类型')->display(function($type){
            return $type == 0?'跳转到图片':'<p style="color:#FF3333">跳转到外链</p>';
        });
        $states = [
            'on'  => ['value' => 0, 'text' => '使用', 'color' => 'success'],
            'off' => ['value' => 1, 'text' => '停用', 'color' => 'default'],
        ];
        $grid->state('状态')->switch($states);
        // $grid->state('State');
        $grid->sort('排序')->label();
        if ($role == 1) {
            $grid->column('admin_users.name','所属公司');
        }
        $grid->addtime('修改时间');
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
        $show = new Show(Activitys::findOrFail($id));
        $show->id('Id');
        
        $show->cid('Cid');
        $show->title('Title');
        $show->image('Image');
        $show->type('Type');
        $show->sort('Sort');
        $show->state('State');
        $show->addtime('Addtime');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        // return Admin::user();
        $form = new Form(new Activitys);
        $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
        $userid = admin::user()->id;
        $pid = admin::user()->pid;


        
        $form->text('title', '活动标题*')->setwidth(3);
        $form->text('desc', '简介*')->setwidth(5)->help('简单介绍活动内容,,字数不能超过30字.');
        $form->image('image', '活动首图')->resize(400,200)->help('请上传宽高比为2:1的图片适配App,大小不能超过1M')->setwidth(4)->uniqueName()->rules('max:1024');
        $form->radio('type', '类型')->options(['0' => '跳转到图片', '1'=> '跳转到外链'])->default('1')->help('如果是跳转到图片,请上传活动主图;如果过时跳转到外链,请填写链接地址.');
        $form->image('longimage', '活动主图')->help('请上传长图的适配App,大小不能超过2M')->setwidth(4)->uniqueName()->rules('dimensions:max_width=1600|max:2048;');
        $form->text('url','外链地址');
        $states = [
            'on'  => ['value' => 0, 'text' => '使用', 'color' => 'success'],
            'off' => ['value' => 1, 'text' => '停用', 'color' => 'default'],
        ];

        $form->switch('state', '状态')->states($states)->default(0);
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
        $form->hidden('addtime', 'Addtime')->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
