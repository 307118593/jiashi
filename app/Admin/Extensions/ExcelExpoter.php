<?php

// namespace App\Admin\Extensions;

// use Encore\Admin\Grid\Exporters\AbstractExporter;
// use Maatwebsite\Excel\Facades\Excel;
// use App\Admin\Extensions\Carbon;
// class ExcelExpoter extends AbstractExporter
// {
//     public function export()
//     {
//         Excel::create('Filename', function($excel) {

//             $excel->sheet('Sheetname', function($sheet) {

//                 // 这段逻辑是从表格数据中取出需要导出的字段
//                 $rows = collect($this->getData())->map(function ($item) {
//                     return array_only($item, ['id', 'name', 'admin.name', 'rate', 'keywords']);
//                 });

//                 $sheet->rows($rows);

//             });

//         })->export('xls');
//     }

// }


namespace App\Admin\Extensions;

use Encore\Admin\Grid\Column;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExpoter extends AbstractExporter
{
    public $titles = [];

    public function __construct($grid, $name)
    {
        $this->grid = $grid;
        $this->tablename = $name;
    }

    public function export()
    {
        Excel::create($this->tablename . date('Y-m-d'), function ($excel) {

            $excel->setTitle('这是啥');
            $excel->setCreator('Creator这又是啥')
                ->setCompany('Maatwebsite又是什么鬼');
            $excel->setDescription('Description这个好像挺长，写点啥呢');
            $columns = $this->grid->columns();
            if (!empty($columns)) {
                foreach ($columns as $c) {
                    array_push($this->titles, $c->getLabel());
                }
            }
            $excel->sheet($this->tablename, function ($sheet) {
                $this->grid->build();
                // 这段逻辑是从表格数据中取出需要导出的字段
                $modelColumnNames = [];
                $titleNames = [];
                $this->grid->columns()->map(function (Column $column) use (&$modelColumnNames, &$titleNames) {
                    if ($column->getName() != '__row_selector__') {
                        array_push($modelColumnNames, $column->getName());
                        array_push($titleNames, $column->getLabel());
                    }
                });
                $modelColumnNames = array_unique($modelColumnNames);
                $titleNames = array_unique($titleNames);
                $sheet->rows([$titleNames]);

                $rows = $this->grid->rows();
                $datas = $rows->map(function ($item) use ($modelColumnNames) {
                    $row = array();
                    $model = $item->model();
                    foreach ($modelColumnNames as $key) {
                        $row[$key] = $this->cutstr_html($model[$key]);
                    }
                    return $row;                 
                });
                $sheet->rows($datas);
            });

        })->export('xls');
    }


    //去掉文本中的HTML标签
    public function cutstr_html($string, $length = 0, $ellipsis = '…')
    {
        $string = strip_tags($string);
        $string = preg_replace('/\n/is', '', $string);
        $string = preg_replace('/ |　/is', '', $string);
        $string = preg_replace('/&nbsp;/is', '', $string);
        $string = preg_replace('/<br \/>([\S]*?)<br \/>/','<p>$1<\/p>',$string);
        preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $string);
        if (is_array($string) && !empty($string[0])) {
            if (is_numeric($length) && $length) {
                $string = join('', array_slice($string[0], 0, $length)) . $ellipsis;
            } else {
                $string = implode('', $string[0]);
            }
        } else {
            $string = '';
        }
        return $string;
    }
}