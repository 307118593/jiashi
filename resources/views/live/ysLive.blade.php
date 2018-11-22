
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" >
    <meta name="renderer" content="webkit">
    <title>{{$data['name']}}</title>
    <style>
        body{margin:0 auto;text-align: center}
        #myPlayer{max-width: 1200px;width: 100%;margin:0 auto;}
    </style>
</head>
<script>
</script>
<body>
<script src="https://open.ys7.com/sdk/js/1.3/ezuikit.js"></script>
<!-- <script src="./ezuikit.js"></script> -->
@if(empty($data['rtmp']) )
<h7>设备不在线或设备不在直播中</h7>
@endif
<video id="myPlayer" poster="" controls playsInline webkit-playsinline autoplay>
    <source src="{{$data['hls']}}" type="rtmp/flv" />
    <source src="{{$data['rtmp']}}" type="application/x-mpegURL" />

</video>
<br>
<h7>请耐心等待数据接入,如长时间未打开.按F5刷新</h7>
<script>
    var player = new EZUIPlayer('myPlayer');
   player.on('error', function(){
       console.log('error');
   });
   player.on('play', function(){
       console.log('play');
   });
   player.on('pause', function(){
       console.log('pause');
   });
   player.on('waiting', function(){
       console.log('waiting');
   });


   // 日志
   // player.on('log', log);

   // function log(str){
   //     var div = document.createElement('DIV');
   //     div.innerHTML = (new Date()).Format('yyyy-MM-dd hh:mm:ss.S') + JSON.stringify(str);
   //     document.body.appendChild(div);
   // }


</script>
</body>
</html>