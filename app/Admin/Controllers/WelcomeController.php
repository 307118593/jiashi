<?php

namespace App\Admin\Controllers;

use App\Welcome;
use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Show;
class WelcomeController extends Controller
{
    use ModelForm;

    public function show($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->body(Admin::show(Post::findOrFail($id), function (Show $show) {
                $show->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableList();
                    $tools->disableDelete();
                });;
            }));
        });
    }
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('欢迎页');
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

            $content->header('欢迎页');
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

            $content->header('欢迎页');
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
        return Admin::grid(Welcome::class, function (Grid $grid) {
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
            if ($cid != 0) {
                $count = DB::table('welcome_page')->where('cid',$cid)->count();
                if ($count > 0) {
                    $grid->disableCreateButton();
                }
                $grid->model()->where('cid',$cid);
            }
            $grid->model()->orderBy('sort','asc');
            $grid->sort('排序')->sortable();
            $grid->image('图片')->image();
            $grid->url('链接');
            
            $grid->disableFilter();
            $grid->disableExport();
            $grid->actions(function ($actions) {
                $actions->disableView();
            });
            $grid->disableRowSelector();

        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Welcome::class, function (Form $form) {
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            if ($role == 1) {
                $cid =0;
            }elseif($role == 2){
                $cid = $userid;
            }elseif($role == 4){
                $cid = $pid;
            }
            $form->hidden('id', 'ID');
            $form->hidden('cid', '公司id')->default($cid);
            $form->image('image','欢迎页')->move('welcome')->setWidth(4)->uniqueName();
            $form->text('url','链接')->help('如果图片与活动相关,可填上活动链接');
            $form->number('sort','排序')->help('排序越小越靠前,且最多只能传一张图片');
            // $form->display('created_at', 'Created At');
            // $form->display('updated_at', 'Updated At');
         
        });
    }
}
