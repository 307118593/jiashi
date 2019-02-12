<canvas id="myChart" width="400" height="200"></canvas>

<script>
$(function () {
    var ctx = document.getElementById("myChart").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ["{{$alive[6]['date']}}","{{$alive[5]['date']}}", "{{$alive[4]['date']}}", "{{$alive[3]['date']}}", "{{$alive[2]['date']}}", "{{$alive[1]['date']}}", "{{$alive[0]['date']}}"],
            datasets: [{
                label: '分钟数',
                // data: [55, 19, 3, 15, 25, 3,5],
                data: ["{{$alive[6]['alive']}}","{{$alive[5]['alive']}}", "{{$alive[4]['alive']}}", "{{$alive[3]['alive']}}", "{{$alive[2]['alive']}}", "{{$alive[1]['alive']}}", "{{$alive[0]['alive']}}"],
                backgroundColor: [
                    'rgba(255, 99, 132, 0)',
                ],
                fill: false,
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