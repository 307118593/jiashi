<?php

namespace App\Admin\Controllers;

use App\Banner;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class BannerController extends Controller
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

            $content->header('轮播管理');
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

            $content->header('轮播');
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

            $content->header('轮播');
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
        return Admin::grid(Banner::class, function (Grid $grid) {
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $grid->model()->orderBy('sort','desc');
            if ($role == 1) {
               $grid->id('ID')->sortable();
               $cid = 0;
                // $grid->model()->where('cid',$cid);
            }else if ($role == 2 || $role == 4) {
                $cid = $userid;
                $grid->model()->where('cid',$cid);
            }else{
                return admin_toastr("暂无权限!");
            }

            $grid->title('标题');
            // $grid->href('跳转地址');
            // $grid->tag('类型')->display(function($tag){
            //     return $tag==1?'外部地址':'内部地址';
            // });
            $grid->image('图片')->image();
            $grid->sort('排序权重');
            $grid->updated_at('修改时间');
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
        return Admin::form(Banner::class, function (Form $form) {
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            if ($role == 1) {
               $cid = 0;
            }else if ($role == 2) {
                $cid = $userid;
            }
            $form->hidden('id', 'ID');
            $form->hidden('cid', '公司')->default($cid);
            $form->text('title','标题')->setWidth(2);
            // $form->text('href','跳转地址')->setWidth(4);
            $form->image('image','图片')->setWidth(4)->uniqueName();
            // $form->radio('tag','类型')->options(['0' => '内部地址', '1'=> '外部地址'])->default('0');
            $form->number('sort','排序权重')->help('权重越大越靠前.');
            $form->hidden('created_at', '创建时间');
            $form->hidden('updated_at', '修改时间');
        });
    }
}
