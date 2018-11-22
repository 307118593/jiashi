<?php

namespace App\Admin\Controllers;

use App\Flow;
use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Tree;
class Flowcontroller extends Controller
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
            if (session('pro_id') && session('pro_id') != $_GET['pro_id']) {
                session(['pro_id'=>$_GET['pro_id']]);
            }else if(!session('pro_id')){
                session(['pro_id'=>$_GET['pro_id']]);
            }
            // session(['pro_id'=>$_GET['pro_id']]);
            $pro_id = session('pro_id');
            $content->header('流程--'.DB::table('project')->where('id',$pro_id)->value('name'));
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
            $content->header('流程--'.DB::table('project')->where('id',$pro_id)->value('name'));
            $content->description('编辑');

            // $content->body($this->form()->edit($id));
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

            $pro_id = session('pro_id');
            $content->header('流程--'.DB::table('project')->where('id',$pro_id)->value('name'));
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
        return Admin::grid(Flow::class, function (Grid $grid) {
            $pro_id = session('pro_id');
            $grid->model()->where('pro_id',$pro_id)->orderBy('sort','asc');
            $grid->column('顺序')->display(function() use($pro_id){
                $rank = Flow::where('pro_id',$pro_id)->where('sort','<=',$this->sort)->count();
                return $rank;
            })->badge();
            $grid->name('流程名称');
            $grid->state('状态')->display(function($state){
                switch ($state) {
                    case 0:
                        return '未开始';
                        break;
                    case 1:
                        return '进行中';
                        break;
                    case 2:
                        return '已完成';
                        break;
                }
            });
            // $grid->column('admin.name','交接人');
            $grid->starttime('开始时间');
            $grid->endtime('结束时间');


            $grid->disableRowSelector();
            $grid->actions(function ($actions) {
                $actions->disableDelete();$actions->disableView();
                // $actions->disableEdit();
            });
        });
      
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = 0)
    {
        return Admin::form(Flow::class, function (Form $form) use($id){
            $pro_id = session('pro_id');
            $form->hidden('id','ID');
            $form->hidden('pro_id','pro_id')->default($pro_id);
            $form->text('name','流程名称')->rules('required');
            $data = DB::table('flow')->where('pro_id',$pro_id)->select('sort','name')->orderBy('sort','asc')->get();
            $option[-100] = '第一';
            foreach ($data as $k => $v) {
                $option[$v->sort] = ($k+1).$v->name;
            }
            if ($id == 0) {
                $form->select('sort','添加到XX之后')->options($option);
              
            }else{
                $form->ignore(['sort']);
            }
            $form->select('state','状态')->options([0 => '未开始', 1 => '进行中', 2 => '已完成']);
            
            // echo $id;
            $form->tools(function (Form\Tools $tools) {
                $tools->disableListButton();
            });
            $form->saving(function (Form $form) use($data,$id){
                
            });
            $form->saved(function (Form $form) use($data,$id){
                // echo "
                //     <script>
                //         alert(".$form->id.");
                //     </script>
                // ";
                // return;
                // return $id;
                if ($form->id > 0) {
                    if ($form->state == 1) {
                    DB::table('flow')->where('id',$form->id)->update(['starttime'=>date('Y-m-d H:i:s',time())]);
                    }
                    if ($form->state == 2) {
                        DB::table('flow')->where('id',$form->id)->update(['endtime'=>date('Y-m-d H:i:s',time())]);
                    }
                    // exit;
                }else{
                    $id = $form->model()->id;
                    if ($form->sort == -100) {
                        $sort = DB::table('flow')->where('pro_id',$form->pro_id)->orderBy('sort','asc')->value('sort');
                        $sort = $sort - 0.01;
                        DB::table('flow')->where('id',$id)->update(['sort'=>$sort]);
                    }else{
                        // $sort = DB::table('flow')->where('pro_id',$form->pro_id)->where('sort',$form->sort)->value('sort');
                        $sort = $form->sort;
                        $sortmax = DB::table('flow')->where('pro_id',$form->pro_id)->orderBy('sort','desc')->value('sort');
                        if ($sortmax == $sort) {
                            $sort = $sort + 10;
                            DB::table('flow')->where('id',$id)->update(['sort'=>$sort]);
                            return;
                        }
                        foreach ($data as $k => $v) {
                            if ($v->sort - $sort > 0) {
                                $sort2 = $v->sort;
                                $sort = ($sort + $sort2)/2;
                                DB::table('flow')->where('id',$id)->update(['sort'=>$sort]);
                                break;
                            }
                        }
                        
                    }
                }
                    
                


               

            });
        });
    }
}
