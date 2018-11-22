<?php

namespace App\Admin\Controllers;

use App\Share;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class ShareController extends Controller
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

            $content->header('分享转发');
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

            $content->header('分享转发');
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

            $content->header('分享转发');
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
        return Admin::grid(Share::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->title('标题');
            $grid->text('内容')->display(function($text){
                return str_limit($text, 30, '...');
            });
            $grid->url('连接')->display(function($url){
                return "<a href='$url'>$url</a>";
            });
            $grid->imageurl('图片')->display(function($v){
                 return "<img src = '"."http://47.97.109.9/upload/"."$v' class='btn'>
                 <script>
                    $(function(){
                        $('.btn').on('click',function(){
                            $('img').toggleClass('title');
                        })
                    })
                    </script>
                 ";
            });
            $grid->type('类型')->display(function($type){
                switch ($type) {
                    case 0:
                        return '首页';
                        break;
                    case 0:
                        return '其他';
                        break;
                }
            });
            $states = [
                'on'  => ['value' => 1, 'text' => '允许', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
            ];
            $grid->status('状态')->switch($states);

            $grid->created_at('添加时间');
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
        return Admin::form(Share::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('title','标题');
            $form->textarea('text','内容');
            $form->url('url','链接');
            $form->image('imageurl','图片');
            $states = [
                'on'  => ['value' => 1, 'text' => '允许', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
            ];

            $form->switch('status','状态')->states($states)->default('on');
            $data = [0=>'官网',1=>'案例',2=>'设计师',3=>'施工进度',4=>'直播',5=>'下载'];
            $form->select('type','类型')->options($data);
            $form->hidden('created_at', 'Created At');
            $form->hidden('updated_at', 'Updated At');
        });
    }
}
