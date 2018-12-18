<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <title>
            @if($count==4)
              四宫格
            @elseif($count==6) 
              六宫格 
            @else 
              九宫格 
            @endif
    </title>
    <style>
        * { padding: 0; margin: 0; }
        .main {
              
            width: 100%;
            /*padding-bottom: 100%;*/
            padding-left: 0.5%;
        　　 padding-top: 0.5%;
        }
        .main .box {
            @if($count == 4)
            width: 48%;
            @else
            width: 31.5%;
            @endif
            /*padding-bottom: 17%;*/
            /*background-color: mediumpurple;*/
            float: left;
            margin:  0 0.5%;
            position: relative;   /*父元素给相对定位*/
        }
        .main .box .content {
    　　position: absolute;    /*子元素给绝对定位*/
    　　width: 100%;
　　　　　　 height: 100%;
    }
    </style>
        <style>
    @if($count == 4)
        html,body{margin:0 auto;text-align: center;width: 100%;padding: 0}
        /*.myPlayer{margin:5px 5px;float: left;display: block}*/
    @else
         html,body{margin:0 auto;text-align: center;width: 100%;padding: 0}
        /*.myPlayer{margin:5px 5px;float: left;display: block;}*/
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
    <script src="http://47.97.109.9/vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="http://open.ys7.com/sdk/js/1.3/ezuikit.js"></script>
</head>
<body>
  <input type="hidden" id="number" value="{{$number}}">
    <div class="main">
        @foreach($camera as $k => $v)
        <div class="box">
            <div class="content">
                    <div class="myPlayer" >
                        <video id="myPlayer{{$k}}" poster="http://47.97.109.9/upload/weizaixian.png" name="{{$v->name}}" controls playsInline webkit-playsinline preload style="width:100%">
                            <source src="{{$v->hls}}" type="rtmp/flv" />
                            <source src="{{$v->rtmp}}" type="application/x-mpegURL" />
                        </video>
                         <span>{{$v->name}}</span>
                    </div>
            </div>
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
          {{ $camera->appends(['userid' => $uid ,'count'=>$count])->links() }}
          @if($count==4)
          <a href="/lives?userid={{$uid}}&count=6"><button class="button">六宫格</button></a>
          <a href="/lives?userid={{$uid}}&count=9"><button class="button" style="margin-left: 100px">九宫格</button></a>
          @elseif($count==9)
          <a href="/lives?userid={{$uid}}&count=4"><button class="button">四宫格</button></a>
          <a href="/lives?userid={{$uid}}&count=6"><button class="button" style="margin-left: 100px">六宫格</button></a>
          @else
          <a href="/lives?userid={{$uid}}&count=4"><button class="button">四宫格</button></a>
          <a href="/lives?userid={{$uid}}&count=9"><button class="button" style="margin-left: 100px">九宫格</button></a>
          @endif
          

       </div>
 </div>
 <script type="text/javascript">
var count = 0;
var outTime=10;//分钟
window.setInterval(go, 1000);
function go() {
var number= $('#number').val();
        count++;
        console.log('count='+count);
        if (count == outTime*60) {
            // console.log('kk;'+number);
          // console.log('ddd;count');
          for (var i = 0; i < number; i++) {
            document.getElementById('myPlayer'+i).pause();
            
          }
          // $('#myPlayer{{$k}}').pause();
    }
}
var x ;
var y ;
//监听鼠标
document.onmousemove = function (event) {
    count = 0;
        
};
//监听键盘
document.onkeydown = function () {
        count = 0;
}; 
 </script>
</body>
</html>