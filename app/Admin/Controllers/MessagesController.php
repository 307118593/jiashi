<?php

namespace App\Admin\Controllers;

use App\Messages;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use DB;
use Umeng;
class MessagesController extends Controller
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

            $content->header('消息管理');
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

            $content->header('消息管理');
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

            $content->header('消息管理');
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
        return Admin::grid(Messages::class, function (Grid $grid) {
            $grid->model()->orderBy('id','desc');
            $grid->id('ID')->sortable();
            $grid->title('标题');
            $grid->content('内容');
            $grid->url('链接');
            $grid->addtime('创建时间');
            $grid->type('类型')->display(function($type){
                return $type==0?'本地消息':'推送消息';
            });
            $grid->senduser('发送人群')->display(function($senduser){
                if ($senduser == 0) {
                    return '全部';
                }else{
                    return DB::table('admin_users')->where('id',$senduser)->value('name');
                }
            });
            // $grid->created_at();
            // $grid->updated_at();
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
        return Admin::form(Messages::class, function (Form $form) {
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            if ($role == 1) {
                $cid = 0;
            }else if($role == 2){
                $cid = $userid;
            }else{
                $cid = $pid;
            }
            $form->hidden('id', 'ID');
            $form->hidden('cid', '公司id')->default($cid);
            $form->text('title','标题*')->setwidth(3);
            $form->textarea('content','内容*')->setwidth(5);
            $form->text('url','链接')->help('如果有活动海报链接.')->setwidth(5);
            $form->image('image','消息图片')->setWidth(4)->uniqueName();
            $form->radio('type','详细类型')->options([0=>'本地消息',1=>'推送消息']);
            if ($role == 1) {
                $data[0] = '全部';
                $com = DB::table('admin_users')->where('pid',0)->select('id','name')->get();
                foreach ($com as $k => $v) {
                    $data[$v->id] = $v->name;
                }
                // $data = array_merge($data,$com1);
                $form->select('senduser','发送人群')->options($data)->setwidth(3);
            }
            $form->hidden('addtime','修改时间')->default(date('Y-m-d H:i:s'));
            // $form->display('created_at', 'Created At');
            // $form->display('updated_at', 'Updated At');
            $form->saved(function(Form $form) use($role){
                if ($role == 1) {
                    $senduser = $form->senduser;
                }else{
                    $senduser = $form->cid;
                }
                if ($form->type == 0) {//本地消息
                    if ($senduser == 0) {
                        $user = DB::table('user')->pluck('id');
                    }else{
                        $user = DB::table('user')->where('cid',$senduser)->pluck('id');
                    }
                    foreach ($user as $k => $v) {
                        $data = [
                            'uid'=>$v,
                            'mid'=>$form->model()->id,
                            'sendtime'=>date('Y-m-d H:i:s'),
                        ];
                        DB::table('messages_user')->insert($data);
                    }
                } 
                if ($form->type == 1) {//推送
                        $after_open = 'go_app';
                        if ($form->url) {
                            $after_open = 'go_url';
                        }
                        $predefined = [
                            'ticker'=>'ticker',
                            "title"=>$form->title,
                            "text"=>$form->content,   
                            "after_open" => $after_open,
                        ];
                        if ($senduser == 0) {//广播
                            Umeng::android()->sendBroadcast($predefined);
                        }else{//组播
                            $filter = '{"where":{"and":[{"or":[{"tag":'.$senduser.'}]}]}}';
                            // $filter = ['where'=>['and'=>['or'=>['tag'=>$senduser]]]];
                            $extraField = array("test"=>"helloworld");
                            Umeng::android()->sendGroupcast($filter, $predefined,$extraField);
                        }
                  }  
                
                 
            });
        });
    }


  
}
