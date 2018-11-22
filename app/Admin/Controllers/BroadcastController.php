<?php

namespace App\Admin\Controllers;

use App\Broadcast;
use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class BroadcastController extends Controller
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
            session(['pro_id'=>$_GET['pro_id']]);
            $pro_id = session('pro_id');
            $content->header('项目播报--'.DB::table('project')->where('id',$pro_id)->value('name'));
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

            $pro_id = session('pro_id');
            $content->header('项目播报--'.DB::table('project')->where('id',$pro_id)->value('name'));
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

            $pro_id = session('pro_id');
            $content->header('项目播报--'.DB::table('project')->where('id',$pro_id)->value('name'));
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
        echo '<style>
                img{
                    width:140px;
                    height:auto;
                }
                .title{
                    width:800px;
                    height:auto;
                }
            </style>';
        return Admin::grid(Broadcast::class, function (Grid $grid) {
            $pro_id = session('pro_id');
            $grid->model()->where('pro_id',$pro_id)->orderBy('id','desc');
            $grid->content('播报内容');
            $grid->image('图片')->image();
            $grid->column('flow.name','所属进度');
            $grid->zan('点赞');
            $grid->addtime('添加时间');
            // $grid->id('ID')->sortable(); 

            // $grid->created_at();
            // $grid->updated_at();
            $grid->disableRowSelector();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableView();
                // $actions->disableEdit();
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
        return Admin::form(Broadcast::class, function (Form $form) {
            $pro_id = session('pro_id');
            $uid = admin::user()->id;
            $form->hidden('id', 'ID');
            $form->hidden('pro_id', '项目id')->default($pro_id);
            $form->hidden('uid', '作者')->default($uid);
            $form->text('content','内容');
            $form->multipleImage('image','图片')->move('bobao')->uniqueName()->removable();
            $flow = DB::table('flow')->where('pro_id',$pro_id)->select('id','name')->orderBy('sort','asc')->get();
            $option= [];
            foreach ($flow as $k => $v) {
                $option[$v->id] = $v->name;
            }
            $form->select('f_id','所属进度*')->options($option);
            $form->hidden('addtime', '添加时间')->default(date('Y-m-d H:i:s'));
            // $form->display('created_at', 'Created At');
            // $form->display('updated_at', 'Updated At');
            $form->saved(function(Form $form){
                $res = DB::table('broadcast')->where('pro_id',$form->pro_id)->first();
                if ($res) {
                    DB::table('flow')->where('id',$form->f_id)->update(['state'=>1]);
                    DB::table('project')->where('id',$form->pro_id)->update(['state'=>2]);
                }
            });
        });
    }
}
