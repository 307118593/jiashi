<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */
use Encore\Admin\Grid\Column;
use App\Admin\Extensions\Popover;
Encore\Admin\Form::forget(['map', 'editor']);
Column::extend('color', function ($value, $color) {
    return "<span style='color: $color'>$value</span>";
});
Column::extend('popover', Popover::class);
// Admin::css('http://47.97.109.9/resources/css/xcConfirm.css');
// Admin::js('http://47.97.109.9/resources/js/jquery-3.1.1.min.js');
// Admin::js('http://47.97.109.9/resources/js/BeAlert.js');