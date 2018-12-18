<?php

namespace App\Admin\Controllers;

use App\Staff;
use Admin;
use App\Build_case;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Controllers\ModelForm;
use DB;
class Build_caseController
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
            ->header('施工案例')
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
            ->header('Detail')
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
            ->header('施工案例')
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
            ->header('施工案例')
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
        $grid = new Grid(new Build_case);
        $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
        $userid = admin::user()->id;
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
        $grid->title('标题/地址');
        $grid->column('admin_users.name','作者');
        $grid->area('面积');
        $grid->style('施工风格');
        $grid->build_time('施工周期/月')->label('info');
        if ($role == 1 || $role == 2 || $role == 4 ) {
             $states = [
                'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
            ];
            $grid->is_up('公司首页显示')->switch($states);
        }
        if ($role == 1) {
            $states = [
                'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
            ];
            $grid->is_appup('APP首页显示')->switch($states);
        }
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->filter(function($filter) use($role){
            $filter->disableIdFilter();
            $filter->like('title','案例名称');
            $filter->equal('admin_users.name','作者');
            if ($role == 1) {
                $filter->equal('cid','所属公司')->select(Staff::all()->where('pid',0)->pluck('name', 'id'));
            }
        });
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
        $show = new Show(Build_case::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Build_case);
        $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            $job = admin::user()->job;
            $cid = $userid;
            if ($role != 2) {
                $cid = $pid;
            }
            $form->tab('基本信息', function ($form) use($cid,$userid,$pid,$job,$role){
                $form->hidden('id', 'ID');
                $form->text('title','标题')->setwidth(3);
                $form->number('area','面积')->default(100);
                $form->select('type','装修类型')->options([0=>'全包',1=>'半包'])->default('1')->setWidth(2);
                // $form->currency('price','金额/万')->symbol('￥');
                $form->number('build_time','施工周期/月')->default(3);
                $form->image('photo','封面图')->move('anli')->setwidth(3)->uniqueName();
                $form->tags('style','施工标签')->help('你可以选择标签来突出优点,也可以自定义添加标签:输入文字按回车键成为一个标签.最多可以添加4个标签')->setWidth(5);
                // $form->slider('star','项目星级')->options(['max' => 5, 'min' => 1, 'step' => 1, 'postfix' => '星'])->setwidth(4);


                $form->starRating('star','星级')->default(4);


                
                $form->hidden('cid','公司')->default($cid);
                $form->hidden('addtime','时间')->default(time());
                $where = [];
                if ($role != 1 ) {
                    $where = ['pid'=>$cid];
                }
                if ($job == 3) {
                    $form->hidden('uid','作者')->default($userid);
                }else{
                    
                    $form->select('uid','作者')->options(DB::table('admin_users')->where('job',11)->where($where)->pluck('name','id'))->setwidth(2);
                    $form->number('sort','排序权重')->help('数字越大越靠前.');
                }
                if ($role == 1 || $role == 2 || $role == 4 ) {
                    $states = [
                        'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
                    ];

                    $form->switch('is_up','公司首页显示')->states($states)->help('首页最多显示10个案例.');
                }
                if ($role == 1) {
                    $states = [
                        'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
                    ];

                    $form->switch('is_appup','APP首页显示')->states($states);
                }
            })->tab('图片材料',function($form){
                // $form->hasMany('build_images','添加材料', function (Form\NestedForm $form) {
                //     $form->text('build_images.title','名称');
                //     $form->multipleImage('build_images.images','图片材料')->removable()->move('build_case')->uniqueName();
                //     // $form->image('images','图片材料')->removable()->move('build_case')->uniqueName();
                // });
                $form->multipleImage('keting','客厅')->removable()->move('keting')->uniqueName();
                $form->multipleImage('woshi','卧室')->removable()->move('woshi')->uniqueName();
                $form->multipleImage('weishengjian','卫生间')->removable()->move('weishengjian')->uniqueName();
                $form->multipleImage('chufang','厨房')->removable()->move('chufang')->uniqueName();
                $form->divide();
                $form->multipleImage('shuidianshigong','水电施工')->removable()->move('shuidianshigong')->uniqueName();
                $form->multipleImage('qiqianggongyi','砌墙工艺')->removable()->move('qiqianggongyi')->uniqueName();
                $form->multipleImage('mugonggongyi','木工工艺')->removable()->move('mugonggongyi')->uniqueName();
                $form->multipleImage('youqigongyi','油漆工艺')->removable()->move('youqigongyi')->uniqueName();

            });

            // $form->saving(function(Form $form){
            //     dump(request());exit;
            // });

        return $form;
       

    }
}
