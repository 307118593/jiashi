<?php

namespace App\Admin\Controllers;

use App\Apply;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ApplyController
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
            ->header('申请试用')
            ->description('列表')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('申请试用')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('申请试用')
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
            ->header('申请试用')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Apply);
        $grid->id('id');
        $grid->name('名字');
        $grid->phone('联系方式');
        $grid->company('公司名称');
        $grid->address('地址');
        $grid->addtime('时间');
        $grid->is_deal('处理')->display(function($value){
            if ($value == 0) {
                return "<button type='button' class='btn btn-danger btn-xs' onclick=\"chuli('$this->id','$value')\">确认处理</button>
                <script type='text/javascript'>
                         function chuli(id,is_deal){
                                var data = {'id':id,'is_deal':is_deal};
                                 console.log(data);
                                $.ajax({
                                  url:\"http://47.97.109.9/api/chuli\",
                                  data:data,
                                  dataType:\"json\",
                                  type:\"POST\",
                                  success:function(){
                                      location.reload(true);
                                  }
                                })     
                           }
                    </script>";
            }else{
                return "<button type='button' class='btn btn-success btn-xs' onclick=\"chuli('$this->id','$value')\">已处理</button>
                <script type='text/javascript'>
                     function chuli(id,is_deal){
                            var data = {id:id,is_deal:is_deal};
                            console.log(data);
                            $.ajax({
                              url:\"http://47.97.109.9/api/chuli\",
                              data:data,
                              dataType:\"json\",
                              type:\"POST\",
                              success:function(data){
                                location.reload(true);
                              }
                            })     
                       }
                    </script>";
            }
            
        });
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('name','姓名');
            $filter->like('phone','手机号');
          
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Apply::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Apply);
        $form->text('name','姓名')->setWidth(2);
        $form->text('company','公司名')->setWidth(3);
        $form->display('phone','联系方式')->setWidth(3);
        $form->text('address','地址')->setWidth(6);


        return $form;
    }

    
}
