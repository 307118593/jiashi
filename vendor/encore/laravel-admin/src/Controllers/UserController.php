<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Routing\Controller;
use Admin;
class UserController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        $userid = admin::user()->id;
        $role = getRole($userid);
        if ($role == 5) {//代理商
            return $content
                ->header('账户')
                ->description(trans('admin.list'))
                ->body($this->grid()->render());
        }
        return $content
            ->header(trans('admin.administrator'))
            ->description(trans('admin.list'))
            ->body($this->grid()->render());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header(trans('admin.administrator'))
            ->description(trans('admin.detail'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param $id
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
    
        return $content
            ->header(trans('admin.administrator'))
            ->description(trans('admin.edit'))
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create(Content $content)
    {

        return $content
            ->header(trans('admin.administrator'))
            ->description(trans('admin.create'))
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Administrator());
        $userid = admin::user()->id;
        $role = getRole($userid);
        if ($role == 5) {//代理商
            $grid->model()->where('did',$userid);
        }
        $grid->id('ID')->sortable();
        $grid->username(trans('admin.username'));
        if (\Request::get('pid')!=0) {
            $grid->column('邀请码')->display(function(){
                return $this->id+1000;
            });
        }
        $grid->name(trans('admin.name').'/邀请码')->display(function($name){
            $int = $this->id+1000;
            if ($this->pid == 0) {
               return $name."($int)";
            }else{
                return $name;
            }
        });
      
        $grid->roles(trans('admin.roles'))->pluck('name')->label();
        $grid->created_at(trans('admin.created_at'));
        $grid->updated_at(trans('admin.updated_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->getKey() == 1) {
                $actions->disableDelete();
            }
                $actions->disableView();
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });
            $grid->disableRowSelector();
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->equal('username', '手机号/账号');
            $filter->like('name', '昵称');
            $filter->equal('job','职位')->radio([
                // ''   => 'All',
                1    => '销售总监',
                10    => '工程总监',
                3    => '设计师',
                11    => '项目经理',
            ]);
            $filter->equal('pid','角色')->radio([
                // ''   => 'All',
                0    => '公司角色',
            ]);
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Administrator::findOrFail($id));

        $show->id('ID');
        $show->username(trans('admin.username'));
        $show->name(trans('admin.name'));
        $show->roles(trans('admin.roles'))->as(function ($roles) {
            return $roles->pluck('name');
        })->label();
        $show->permissions(trans('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
        $show->created_at(trans('admin.created_at'));
        $show->updated_at(trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $form = new Form(new Administrator());
        $userid = admin::user()->id;
        $role = getRole($userid);

        $form->display('id', 'ID');

        $form->text('username', trans('admin.username'))->rules('required');
        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);

        if ($role == 1) {//管理员
            $form->multipleSelect('roles', trans('admin.roles'))->options(Role::all()->whereIn('id',[2,3,4,5,6,7,8])->pluck('name', 'id'));
        }else{//代理商
            $form->multipleSelect('roles', trans('admin.roles'))->options(Role::all()->whereIn('id',[2,3,4,5,6,7])->pluck('name', 'id'));
        }
        if ($role == 1) {//管理员
            $form->multipleSelect('permissions', trans('admin.permissions'))->options(Permission::all()->whereIn('id',[6,7,9,11])->pluck('name', 'id'));
        }else{//代理商
            $form->multipleSelect('permissions', trans('admin.permissions'))->options(Permission::all()->whereIn('id',[6,7,9])->pluck('name', 'id'));
            $form->hidden('did','代理商id')->default($userid);
        }

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        $form->saving(function (Form $form) use($role,$userid){
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = bcrypt($form->password);
            }
            // if ($role == 5) {//代理商
            //     $form->did = $userid;
            // }
            // dump(($form));exit;
        });

        return $form;
    }
}
