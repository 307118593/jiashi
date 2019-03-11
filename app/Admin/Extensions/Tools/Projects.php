<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class Projects extends AbstractTool
{
    protected function script()
    {
        $url = Request::fullUrlWithQuery(['project' => '_project_']);

        return <<<EOT

$('input:radio.project').change(function () {

    var url = "$url".replace('_project_', $(this).val());

    $.pjax({container:'#pjax-container', url: url });

});

EOT;
    }

    public function render()
    {
        Admin::script($this->script());

        $options = [
            '0'     => '工作',
            '1'     => '归档',
        ];

        return view('admin.tools.project', compact('options'));
    }
}