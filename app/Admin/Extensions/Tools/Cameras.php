<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class Cameras extends AbstractTool
{
    protected function script()
    {
        $url = Request::fullUrlWithQuery(['cameras' => '_cameras_']);

        return <<<EOT

$('input:radio.cameras').change(function () {

    var url = "$url".replace('_cameras_', $(this).val());

    $.pjax({container:'#pjax-container', url: url });

});

EOT;
    }

    public function render()
    {
        Admin::script($this->script());

        $options = [
            '-1'   => '所有',
            '0'     => '未分配',
            '1'     => '已分配',
        ];

        return view('admin.tools.cameras', compact('options'));
    }
}