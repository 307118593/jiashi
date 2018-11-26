<canvas id="myChart" width="400" height="200"></canvas>
<!-- @foreach($date as $k => $v)
<div>{{$k}} -- {{$v}}<div>
@endforeach -->
<script>
$(function () {
    var ctx = document.getElementById("myChart").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ["{{$date[6]}}","{{$date[5]}}", "{{$date[4]}}", "{{$date[3]}}", "{{$date[2]}}", "{{$date[1]}}", "{{$date[0]}}"],
            datasets: [{
                label: '分钟数',
                data: [55, 19, 3, 15, 25, 3,5],
                backgroundColor: [
                    'rgba(255, 99, 132, 0)',
                ],
                borderColor: [
                    // 'rgba(255,99,132,1)',
                    // 'rgba(54, 162, 235, 1)',
                    // 'rgba(255, 206, 86, 1)',
                    'rgba(60,179,113, 1)',
                    // 'rgba(153, 102, 255, 1)',
                    // 'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1,
                lineTension:0,
                pointRadius:5,
                pointBackgroundColor:'rgba(255 ,48, 48, 1)',
            },
            // {
            //     label: '小时数',
            //     data: [6, 5, 3, 15, 25, 3,5],
            //     backgroundColor: [
            //         'rgba(255, 99, 132, 0)',
            //     ],
            //     borderColor: [
            //         // 'rgba(255,99,132,1)',
            //         // 'rgba(54, 162, 235, 1)',
            //         // 'rgba(255, 206, 86, 1)',
            //         'rgba(60,179,113, 1)',
            //         // 'rgba(153, 102, 255, 1)',
            //         // 'rgba(255, 159, 64, 1)'
            //     ],
            //     borderWidth: 1,
            //     lineTension:0,
            //     pointRadius:5,
            //     pointBackgroundColor:'rgba(255 ,48, 48, 1)',
            // },
            ]
        },
        
        // options: {
        //     scales: {
        //         yAxes: [{
        //             ticks: {
        //                 beginAtZero:true
        //             }
        //         }]
        //     }
        // }
    });
});
</script>