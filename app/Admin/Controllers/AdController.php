<?php

namespace App\Admin\Controllers;
use DB;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Collapse;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;
class AdController extends Controller
{
    public function index()
    {
        return Admin::content(function (Content $content) {
            $role = Admin::user()->roles[0]['id'];//获取权限.1管理员.2公司负责人.3普通员工.4总监
            $userid = admin::user()->id;
            $pid = admin::user()->pid;
            $z_uid = 0;
            if ($role == 2) {
                $z_uid = $userid;
            }elseif($role == 3 || $role == 4){
                $z_uid = $pid;
            }else{
                admin_toastr("暂无权限!");
                return redirect(admin_url('admin'));
            }
            $content->header('公司设置');
            if (\Request::get('res',0) == 1) {
                admin_toastr("修改成功!");
            }
            // $content->description('Description...');
            $content->Row(function (Row $row) use($z_uid,$role){
                    $row->column(12, function (Column $column) use($z_uid,$role){
                        $infos['image'] = $this->host.DB::table('admin_users')->where('id',$z_uid)->value('image'); 
                        $infos['logo'] = $this->host.DB::table('admin_users')->where('id',$z_uid)->value('logo'); 
                        $infos['address'] = DB::table('admin_users')->where('id',$z_uid)->value('address'); 
                        $infos['tel'] = DB::table('admin_users')->where('id',$z_uid)->value('tel'); 
                        $infos['sharetitle'] = DB::table('admin_users')->where('id',$z_uid)->value('sharetitle'); 
                        $infos['sharecontent'] = DB::table('admin_users')->where('id',$z_uid)->value('sharecontent'); 
                        $form = new Form($infos);
                        $form->action('setCompany');
                        $form->hidden('z_uid','公司主键')->default($z_uid);
                        $form->url('homeurl','公司官网')->default(DB::table('admin_users')->where('id',$z_uid)->value('homeurl'))->help('请带上http://或者https://,如:http://www.xxx.com');
                        $form->text('address', '公司地址')->setwidth(8);
                        $form->text('tel', '电话')->setwidth(6)->help('如有多个地址或电话..请用英文字符分号即";"分隔,如地址1;地址2..电话1;电话2..');
                        $form->image('logo','公司logo')->help('图片宽高比最好接近1:1,适配app');
                        $form->image('image','拦腰图片')->help('图片宽高比最好接近2:1,适配app');
                        $form->textarea('content', '公司简介')->default(DB::table('admin_users')->where('id',$z_uid)->value('content'))->help('简介不能超过220个字.');
                        $form->divide();
                        $form->text('sharetitle','分享标题');
                        $form->text('sharecontent','分享内容')->help('分享设置用户客户分享到微信的封面内容,可不填.');
                        $collapse = new Collapse();
                        // $image = DB::table('admin_users')->where('id',$z_uid)->value('image');
                        // $logo = DB::table('admin_users')->where('id',$z_uid)->value('logo');
                        // if ($image) {
                        //     $collapse->add('LOGO/拦腰广告图','<img style="max-width:600px;margin:0 100px" src="'.$this->host.$logo.'"><img style="max-width:600px; margin:0 100px" src="'.$this->host.$image.'">');
                        // }
                        $collapse->add('设置公司简介', $form);
                        $form1 = new Form();
                        $form1->action('setimages');
                        $form1->hidden('z_uid','公司主键')->default($z_uid);
                        $form1->multipleImage('pics','公司相册');
                        // echo $collapse->render();
                        // $collapse->add('上传图片', $form1);
                        $column->append($collapse);
                    });
                  
            });
   
        });
    }


     //设置公司简介
    public function setCompany(Request $request){
        $z_uid = $request->input('z_uid');
        $homeurl = $request->input('homeurl');
        $content = $request->input('content');
        $address = $request->input('address');
        $tel = $request->input('tel');
        $file = $request->file('image');
        $logo = $request->file('logo');
        $data = [];
        if ($file) {
            $img = Image::make($file);  
            $ex = $file->getClientOriginalExtension();
            $name = $z_uid.".".$ex;
            $path = 'upload/company/'.$name;
            $img->resize(600, 300);
            $img->save($path);
            $data['image'] = $path;
        }
        if ($logo) {
            $img = Image::make($logo);  
            $ex = $logo->getClientOriginalExtension();
            $name = $z_uid."_logo.".$ex;
            $path = 'upload/company/'.$name;
            $img->resize(400, 400);
            $img->save($path);
            $data['logo'] = $path;
        }
        // if ($content) {
            $data['content'] = $content;
        // }
        // if ($homeurl) {
            $data['homeurl'] = $homeurl;
            $data['address'] = $address;
            $data['tel'] = $tel;
            $data['sharetitle'] = $sharetitle;
            $data['sharecontent'] = $sharecontent;
        // }
        $res = DB::table('admin_users')->where('id',$z_uid)->update($data);
        return Redirect('admin/ad?res='.$res);
        
    }
}
