<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Grid\Tools\BatchAction;

class Fenpei extends BatchAction
{
    protected $action;

    public function __construct($action = 1)
    {
        $this->action = $action;
    }

    public function script()
    {
        return <<<EOT
// function ShowCreateModal(title){
//         $("#createFileTitle").text(title);
//         $('#createFileMModal').modal('show');
//     }
//  // 关闭弹框， 获取输入值，然后执行逻辑
// $('{$this->getElementClass()}').click(function (){
//     $("#createFileMModal").modal("hide");
//     var inputFileName = $("#fileName").val();
//     console.log("input file name : " + inputFileName);
// });
 $("#createFileMModal").modal("hide");
$('{$this->getElementClass()}').on('click', function() {
        $('#createFileMModal').modal('show');
        var cid = $("#com").val();var did = $("#daili").val();
        console.log(cid);console.log(did);
   
});

$('#createFileSureBut').click(function (){
    $("#createFileMModal").modal("hide");
    var cid = $("#com").val();var did = $("#daili").val();
        console.log(cid);console.log(did);
        var domain = window.location.host;
        if(domain=="47.97.109.9"){//防止跨域退出登录
            var host = "www.homeeyes.cn";
        }else{
            var host = "47.97.109.9";
        }
         $.ajax({
            method: 'post',
            url: 'http://'+host+'/api/fenpei',
            data: {
                _token:LA.token,
                ids: selectedRows(),
                cid: cid,
                did: did,
                action: {$this->action}
            },
            success: function () {
                $.pjax.reload('#pjax-container');
                toastr.success('操作成功');
            }
        });
    
});

EOT;

    }

    //  public function render()
    // {
    //     Admin::script($this->script());

    //     $options = [
    //         '0'     => '工作',
    //         '1'     => '归档',
    //     ];

    //     return view('admin.tools.project', compact('options'));
    // }

}