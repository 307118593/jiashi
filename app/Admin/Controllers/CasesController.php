<?php

namespace App\Admin\Controllers;

use App\Cases;
use App\User;
use App\Residence;
use App\Staff;
use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class CasesController extends Controller
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

            $content->header('项目案例');
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

            $content->header('项目案例');
            $content->description('编辑');

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

            $content->header('项目案例');
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
        return Admin::grid(Cases::class, function (Grid $grid) {
            $userid = admin::user()->id;
            $role = getRole($userid);//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $pid = admin::user()->pid;
            $grid->model()->orderBy('id','desc');
            $cid = 0;
            if ($role != 1) {
                if ($role == 2) {
                    $cid = $userid;
                }else{
                    $cid = $pid;
                }
                $grid->model()->where('cid',$cid);
            }
            $grid->title('标题');
            $grid->column('admin_users.name','作者');
            // $grid->house('户型');
            $grid->area('面积');
            $grid->style('装修风格');
            $grid->url('链接地址');
            $grid->address('地址');
            $grid->column('residence.name','所属楼盘');
            

            if ($role == 1 || $role == 2 || $role == 4 ) {
                $grid->sort('排序')->label();
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
            $grid->disableRowSelector();
            $grid->actions(function ($actions) {
                $actions->disableView();
            });
            // $grid->tools(function ($tools) {
            //     $tools->batch(function ($batch) {
            //         $batch->disableDelete();
            //     });
            // });
            $grid->filter(function($filter) use($role,$cid){
               
                $filter->disableIdFilter();
                $filter->like('title','案例名称');
                $filter->equal('admin_users.name','作者');
                 if ($role == 1) {
                    $filter->equal('rid','所属楼盘')->select(Residence::all()->pluck('name', 'id'));
                }else{
                    $filter->equal('rid','所属楼盘')->select(Residence::all()->where('cid',$cid)->pluck('name', 'id'));
                }
                if ($role == 1) {
                    $filter->equal('cid','所属公司')->select(Staff::all()->where('pid',0)->pluck('name', 'id'));
                }
                
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
        return Admin::form(Cases::class, function (Form $form) {
            $userid = admin::user()->id;
            $role = getRole($userid);//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $pid = admin::user()->pid;
            $job = admin::user()->job;
            $cid = $userid;
            if ($role != 2) {
                $cid = $pid;
            }

            $form->hidden('id', 'ID');
            $form->text('title','标题')->setwidth(3);
            $form->number('area','面积')->default(100);
            //获取该公司案例下的风格
           
            $style = $this->getStyle($cid,$role);
            // $form->text('style','装修风格')->setwidth(2)->help('如:中式,欧式');
            $form->tags('style','装修风格')->help('你可以自定义添加标签:输入文字按回车键成为一个标签.')->setWidth(5)->options($style);
            $form->select('type','装修类型')->options([0=>'全包',1=>'半包'])->default('1')->setWidth(2);
            // $form->currency('price','预算金额/万')->symbol('￥');
            $form->image('photo','封面图')->move('anli')->setwidth(5)->uniqueName();
            $form->url('url','链接地址')->help('如果有案例地址可直接跳转到链接地址;');
            // $form->multipleImage('panorama','全景图')->removable()->move('anli')->uniqueName()->help('你也可以上传多张全景图;');
            $form->text('address','地址');
            $form->hidden('cid','公司')->default($cid);
            $form->hidden('addtime','时间')->default(time());
            $where = [];
            $rwhere = [];
            if ($role != 1 ) {
                $where = ['pid'=>$cid];
                $rwhere = ['cid'=>$cid];
            }
            if ($job == 3) {
                $form->hidden('uid','作者')->default($userid);
            }else{
                
                $form->select('uid','作者')->options(DB::table('admin_users')->where('job',3)->where($where)->pluck('name','id'))->setwidth(2);
                $form->number('sort','排序权重')->help('数字越大越靠前.');
            }
            $form->select('rid','所属楼盘')->options(DB::table('residence')->where($rwhere)->pluck('name','id'))->setwidth(2);
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
            
            
        });
    }

    protected function getStyle($cid,$role){
     if ($role == 1) {
            $where = [];
        }else{
            $where =['cid'=>$cid];
        }
        $allstyle = DB::table('cases')->where($where)->groupBy('style')->whereNotNull('style')->pluck('style');
        $style = "";
        foreach ($allstyle as $k => $v) {
            $style .= $v.",";
        }
        $style = rtrim($style,",");
        $style = explode(",",$style);
        return $style;
    }
  
}
