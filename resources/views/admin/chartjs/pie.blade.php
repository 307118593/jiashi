<canvas id="pie" width="400" height="200"></canvas>
<script>
$(function () {
    var ctx = document.getElementById("pie").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels:[
                @foreach($camera as $v)
                    "{{$v->name}}",
                @endforeach
                ],
            datasets: [{
                label: '分钟数',
                // data: [55, 19, 3, 15, 25, 3,5],
                data: [
                    @foreach($camera as $v)
                        "{{$v->alive}}",
                    @endforeach
                ],
                backgroundColor: [
                    'rgba(255,100,97,1)',
                    'rgba(4,169,172,.8)',
                    'rgba(0,255,219,1)',
                    'rgba(5,124,131,.9)',
                    'rgba(154,116,71,0.3)',
                    'rgba(238,153,34,1)',
                    'rgba(45,221,5,0.2)',
                    'rgba(63,85,99,0.4)',
                    'rgba(219,40,95,0.8)',
                    'rgba(51,150,118,0.8)',
                    'rgba(177,217,149,0.8)',
                    'rgba(255, 255, 51,0.8)',
                    'rgba(255, 153, 102,0.8)',
                    'rgba(204, 204, 204,0.8)',
                    'rgba(153, 204, 51,0.8)',
                    'rgba(255, 105, 180,0.8)',
                    'rgba(245, 222, 179,0.8)',
                    'rgba(174, 238, 238,0.8)',
                    'rgba(64, 224, 208,0.8)',
                    'rgba(255,100,97,1)',
                    'rgba(4,169,172,.8)',
                    'rgba(0,255,219,1)',
                    'rgba(5,124,131,.9)',
                    'rgba(154,116,71,0.3)',
                    'rgba(238,153,34,1)',
                    'rgba(45,221,5,0.2)',
                    'rgba(63,85,99,0.4)',
                    'rgba(219,40,95,0.8)',
                    'rgba(51,150,118,0.8)',
                    'rgba(177,217,149,0.8)',
                    'rgba(255, 255, 51,0.8)',
                    'rgba(255, 153, 102,0.8)',
                    'rgba(204, 204, 204,0.8)',
                    'rgba(153, 204, 51,0.8)',
                    'rgba(255, 105, 180,0.8)',
                    'rgba(245, 222, 179,0.8)',
                    'rgba(174, 238, 238,0.8)',
                    'rgba(64, 224, 208,0.8)',
                    'rgba(255,100,97,1)',
                    'rgba(4,169,172,.8)',
                    'rgba(0,255,219,1)',
                    'rgba(5,124,131,.9)',
                    'rgba(154,116,71,0.3)',
                    'rgba(238,153,34,1)',
                    'rgba(45,221,5,0.2)',
                    'rgba(63,85,99,0.4)',
                    'rgba(219,40,95,0.8)',
                    'rgba(51,150,118,0.8)',
                    'rgba(177,217,149,0.8)',
                    'rgba(255, 255, 51,0.8)',
                    'rgba(255, 153, 102,0.8)',
                    'rgba(204, 204, 204,0.8)',
                    'rgba(153, 204, 51,0.8)',
                    'rgba(255, 105, 180,0.8)',
                    'rgba(245, 222, 179,0.8)',
                    'rgba(174, 238, 238,0.8)',
                    'rgba(64, 224, 208,0.8)',
                    
                ],
                // backgroundColor: [
                //         window.chartColors.red,
                //         window.chartColors.orange,
                //         window.chartColors.yellow,
                //         window.chartColors.green,
                //         window.chartColors.blue,
                //     ],
                // borderWidth: 1,
                // lineTension:0,
                // pointRadius:5,
                // pointBackgroundColor:'rgba(255 ,48, 48, 1)',
            },
   
            ]
        },
 
    });
});
</script>