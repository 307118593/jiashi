<?php

namespace App\Admin\Controllers;

use App\Flow_model;
use DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\MessageBag;
use Encore\Admin\Layout\Row;
use Encore\Admin\Layout\Column;
class Flow_modelController extends Controller
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
            $userid = admin::user()->id;
            if ($userid == 1 ) {
                admin_toastr("暂无权限!");
                return redirect(admin_url('admin'));
            }
            $content->header('流程模型');
            $content->description('列表');
            $temp = DB::table('flow_model')->where('z_uid',$userid)->groupBy('temp')->select('temp')->get();
            // $temp = DB::table('flow_model')->where('z_uid',2)->pluck('temp');
            // dd($temp);
            foreach ($temp as $k => $v) {
                $temp[$k]->flow = DB::table('flow_model')->where('z_uid',$userid)->where('temp',$v->temp)->select('name')->orderBy('sort','asc')->get();
            }
          
            foreach ($temp as $k => $v) {
                $content->row(function (Row $row) use($v){

                    $row->column(1, '流程模板'.$v->temp.':');

                    foreach ($v->flow as $kk => $vv) {
                        $row->column(1, ($kk+1).':'.$vv->name);
                    }
                });
            }
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

            $content->header('流程模型');
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

            $content->header('流程模型');
            $content->description('创建');

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
        return Admin::grid(Flow_model::class, function (Grid $grid) {
            $userid = admin::user()->id;
            if ($userid == 1 ) {
                admin_toastr("暂无权限!");
                return redirect(admin_url('admin'));
            }
            // $flow = DB::table('flow_model')->pluck('sort','name');
            $grid->model()->where('temp','>',0)->where('z_uid',$userid)->orderBy('temp','asc')->orderBy('sort','asc');
            $grid->name('流程名称')->display(function($name) use($userid){
                $rank = DB::table('flow_model')->where('z_uid',$userid)->where('temp',$this->temp)->where('sort','<=',$this->sort)->count();
                return "<span style=\"font-size:18px;background-color:red;border-radius:5px;padding:1px 5px\">".$rank."</span>".$name;
            });
            $grid->temp('所属模板')->label();
            $grid->sort('排序权重')->label('primary');
            $grid->disableExport();
            $grid->disableRowSelector();

            $grid->filter(function($filter) use($userid){
                $filter->disableIdFilter();
                $temp = DB::table('flow_model')->where('z_uid',$userid)->pluck('temp');
                $data=[];
                if ($temp) {
                    foreach ($temp as $k => $v) {
                        $data[$v] = '模板'.$v;
                    }
                }
                
                $filter->equal('temp','请选择模板')->select($data);
      
            });
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
        return Admin::form(Flow_model::class, function (Form $form) {
            $userid = admin::user()->id;
            $form->hidden('id', 'ID');
            $form->hidden('z_uid', '装修公司Id')->default($userid);
            $form->text('name','流程名称')->setWidth(2);
            // $form->select('dsfds','图标')->options();
            // $form->icon('icon');
            $form->number('sort','排序权重')->help('数字小排名靠前.且不能相同..');
            $form->number('temp','加入模板')->default(1)->help('模板号相同的为一组流程.默认从1开始的自然数');
            $form->saving(function(Form $form){
                    if ($form->temp < 1) {
                        $error = new MessageBag([
                            'title'   => '错误...',
                            'message' => '模板号应大于等于1',
                        ]);
                        return back()->with(compact('error'));
                   }
            });
            // $form->saved(function(Form $form) use($z_uid){
            //     $flow = DB::table('flow_model')->where('z_uid',$z_uid)->where('temp',$form->temp)->select('name')->orderBy('sort','asc')->get();
            //     // print_r($flow);
            //     foreach ($flow as $k => $v) {
            //         if ($k == 0) {
            //             $str = $v->name;
            //         }else{
            //             $str .= '=>'.$v->name;
            //         }
            //     }
            //     $temp = 0-$form->temp;
            //     $res = DB::table('flow_model')->where(['z_uid'=>$z_uid,'temp'=>$temp])->first();
            //     if (empty($res)) {
            //         $data = [
            //             'z_uid'=>$z_uid,
            //             'name'=>$str,
            //             'temp'=>$temp,
            //             'sort'=>-1,
            //         ];
            //         DB::table('flow_model')->insert($data);
            //     }else{
            //         DB::table('flow_model')->where(['z_uid'=>$z_uid,'temp'=>$temp])->update(['name'=>$str]);
            //     }
            // });
        });
    }
}
