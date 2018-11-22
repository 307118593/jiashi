<?php

namespace App\Admin\Controllers;

use App\Pics;
use DB;
use App\Admin\Extensions\Tools\GridView;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class PicsController extends Controller
{
    use ModelForm;
    // use HasResourceActions;
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        $i = \request('i',0);
        if ($i > 0) {
            admin_toastr("成功上传".$i."张图片!");
        }
        return Admin::content(function (Content $content) {

            $content->header('相册管理');
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

            $content->header('相册管理');
            $content->description('编辑');

            $content->body($this->form($id)->edit($id));
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

            $content->header('相册管理');
            $content->description('新建');

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
        return Admin::grid(Pics::class, function (Grid $grid) {
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            $grid->model()->orderBy('sort','desc');
            // $grid->id('ID')->sortable();
            if ($role != 1) {
                if ($role == 2) {
                    $cid = $userid;
                }else{
                    $cid = $pid;
                }
                $grid->model()->where('cid',$cid);

            }else{
                $grid->column('cid','公司')->display(function($cid){
                    return DB::table('admin_users')->where('id',$cid)->value('name');
                });
            }
            $grid->image('图片')->image();
            $grid->detail('描述');
            $grid->sort('排序');
            $grid->addtime('修改时间');
            $grid->tools(function ($tools) {
                $tools->append(new GridView());
            });
            if (Request::get('view') !== 'table') {
                $grid->setView('admin.grid.card');
            }
            // $grid->created_at();
            // $grid->updated_at();
            $grid->disableExport();
            $grid->disableFilter();
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
    protected function form($id=0)
    {
        return Admin::form(Pics::class, function (Form $form) use($id){
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            if ($role == 2) {
                $cid = $userid;
            }else{
                $cid = $pid;
            }
            $form->hidden('id', 'ID');
            $form->hidden('cid', 'gongsiid')->default($cid);
            if ($id == 0) {
                $form->multipleImage('image','添加图片')->removable();
            }else{
                $form->image('image','添加图片')->removable()->uniqueName();
                $form->number('sort','排序权重')->help('数字越大越靠前');
            }
            // $form->text('detail','添加图片描述')->setwidth(5);
            $form->hidden('addtime', '修改时间')->default(date('Y-m-d H:i:s'));
            $form->setAction($this->host.'api/setimages');
            $form->saved(function(Form $form){

            });
        });
    }
}
