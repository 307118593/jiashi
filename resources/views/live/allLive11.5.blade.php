
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" >
    <meta name="renderer" content="webkit">
    <title>所有直播</title>
    <style>
    @if($count == 4)
        html,body{margin:0 auto;text-align: center;width: 100%;padding: 0}
        .myPlayer{width: 45%;margin:5px 5px;float: left;display: block}
    @else
         html,body{margin:0 auto;text-align: center;width: 100%;padding: 0}
        .myPlayer{width: 31%;margin:5px 5px;float: left;display: block;}
    @endif
    </style>
    <style type="text/css">
        #pull_right{
            text-align:center;
        }
        .pull-right {
            /*float: left!important;*/
        }
        .pagination {
            display: inline-block;
            padding-left: 0;
            margin: 20px 0;
            border-radius: 4px;
        }
        .pagination > li {
            display: inline;
        }
        .pagination > li > a,
        .pagination > li > span {
            position: relative;
            float: left;
            padding: 6px 12px;
            margin-left: -1px;
            line-height: 1.42857143;
            color: #428bca;
            text-decoration: none;
            background-color: #fff;
            border: 1px solid #ddd;
        }
        .pagination > li:first-child > a,
        .pagination > li:first-child > span {
            margin-left: 0;
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
        }
        .pagination > li:last-child > a,
        .pagination > li:last-child > span {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }
        .pagination > li > a:hover,
        .pagination > li > span:hover,
        .pagination > li > a:focus,
        .pagination > li > span:focus {
            color: #2a6496;
            background-color: #eee;
            border-color: #ddd;
        }
        .pagination > .active > a,
        .pagination > .active > span,
        .pagination > .active > a:hover,
        .pagination > .active > span:hover,
        .pagination > .active > a:focus,
        .pagination > .active > span:focus {
            z-index: 2;
            color: #fff;
            cursor: default;
            background-color: #428bca;
            border-color: #428bca;
        }
        .pagination > .disabled > span,
        .pagination > .disabled > span:hover,
        .pagination > .disabled > span:focus,
        .pagination > .disabled > a,
        .pagination > .disabled > a:hover,
        .pagination > .disabled > a:focus {
            color: #777;
            cursor: not-allowed;
            background-color: #fff;
            border-color: #ddd;
        }
        .clear{
            clear: both;
        }
        .button { /* 按钮美化 */
          width: 70px; /* 宽度 */
          height: 37px; /* 高度 */
          border-width: 0px; /* 边框宽度 */
          border-radius: 15px; /* 边框半径 */
          background: #428bca; /* 背景颜色 */
          cursor: pointer; /* 鼠标移入按钮范围时出现手势 */
          outline: none; /* 不显示轮廓线 */
          font-family: Microsoft YaHei; /* 设置字体 */
          color: white; /* 字体颜色 */
          font-size: 17px; /* 字体大小 */
          position:absolute;
          margin-top: 20px;
          margin-bottom: 20px;margin-left: 1rem;padding: 0.3333rem
        }
        .button:hover { /* 鼠标移入按钮范围时改变颜色 */
          background: #2AA4E7;
        }
	
    </style>
</head>
<script>
</script>
<body>
<script src="http://47.97.109.9/vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="http://open.ys7.com/sdk/js/1.3/ezuikit.js"></script>
<!-- <script src="./ezuikit.js"></script> -->
<div style="display: block;width: 100%; text-align: center;padding: 0;margin:auto;">
@foreach($camera as $k => $v)
<div class="myPlayer" >
<video id="myPlayer{{$k}}" poster="http://47.97.109.9/upload/weizaixian.png" name="{{$v->name}}" controls playsInline webkit-playsinline preload style="width:100%">
    <source src="{{$v->hls}}" type="rtmp/flv" />
    <source src="{{$v->rtmp}}" type="application/x-mpegURL" />

</video>
 <span>{{$v->name}}</span>
</div>

<script>
    var player{{$k}} = new EZUIPlayer("myPlayer{{$k}}");
    
    player{{$k}}.on('error', function(){
       // $("#myPlayer{{$k}}").attr("poster","http://47.97.109.9/upload/weizaixian.png");
       console.log('error');
   });
    player{{$k}}.on('play', function(){
       console.log('play');
   });
   player{{$k}}.on('pause', function(){
       console.log('pause');
       // $("#myPlayer{{$k}}").attr("poster","http://47.97.109.9/upload/weizaixian.png");
   });
   player{{$k}}.on('waiting', function(){
       console.log('waiting');
   });
</script>
@endforeach
</div>


<div class="clear"></div>
 <div id="pull_right" >
       <div class="pull-right" >
          {{ $camera->appends(['userid' => $uid ])->links() }}
          @if($count==4)
          <a href="/lives?userid={{$uid}}&count=9"><button class="button">九宫格</button></a>
          @else
          <a href="/lives?userid={{$uid}}&count=4"><button class="button">四宫格</button></a>
          @endif
       </div>
 </div>

<!-- <input type="hidden" value=" "> -->



</body>
</html>