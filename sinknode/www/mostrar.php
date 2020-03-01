<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Gráfico</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="bootstrap-4.3.1/css/bootstrap.min.css">
    <script src="jquery.js"></script>
    <script src="bootstrap-4.3.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="main.css">
    <script src="moment.js"></script>
    <script src="chart.min.js"></script>
    <script>
        var ultimo_registro_hoje = null;
        var ultimo_registro_semana = null;
        var ultimo_registro_mes = null;
        // Este comando é invocado após a página ser totalmente carregada.
        // O que  isso faz é chamar a função atualizar a cada 4 segundos
        var meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
			'Julho','Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        $(document).ready(function() {
            $.ajax({
                url: 'grafico.json.php?id=<?= @$_GET['id'] ?>',
                cache: false,
                success: function(info) {
                    console.log(info, info.hoje.length);
                    for (i = 0; i < info.hoje.length; i++) {
                        console.log(info.hoje[i]);
                        document.title = info.hoje[i].nome_planta;
                        data_hora = new Date(info.hoje[i].data_hora);
                        config_hoje.data.labels.push(data_hora.getHours() + ':' + data_hora.getMinutes());
                        config_hoje.options.title.text = info.hoje[i].nome_planta + ' - Gráfico do dia';
                        config_hoje.data.datasets[0].data.push(info.hoje[i].temperatura);
                        config_hoje.data.datasets[1].data.push(info.hoje[i].higrometro);
                        config_hoje.data.datasets[2].data.push(info.hoje[i].umidade);
                        window.linha_grafico_hoje.update();
                        ultimo_registro_hoje = info.hoje[i];
                    }
                    for (i = 0; i < info.semana.length; i++) {
                        config_semana.options.title.text = info.hoje[i].nome_planta + ' - Gráfico da semana';
                        data_hora = new Date(info.semana[i].data_hora);
                        config_semana.data.labels.push(data_hora.getDate() + '/' + meses[data_hora.getMonth()] );
                        config_semana.data.datasets[0].data.push(info.semana[i].temperatura);
                        config_semana.data.datasets[1].data.push(info.semana[i].higrometro);
                        config_semana.data.datasets[2].data.push(info.semana[i].umidade);
                        window.linha_grafico_semana.update();
                        ultimo_registro_semana = info.semana[i];
                        console.log(ultimo_registro_semana)
                    }
                    for (i = 0; i < info.mes.length; i++) {
                        config_mes.options.title.text = info.hoje[i].nome_planta + ' - Gráfico do mês';
                        data_hora = new Date(info.mes[i].data_hora);
                        config_mes.data.labels.push(data_hora.getDate() + '/' + meses[data_hora.getMonth()] );
                        config_mes.data.datasets[0].data.push(info.mes[i].temperatura);
                        config_mes.data.datasets[1].data.push(info.mes[i].higrometro);
                        config_mes.data.datasets[2].data.push(info.mes[i].umidade);
                        window.linha_grafico_mes.update();
                        ultimo_registro_mes = info.mes[i];
                    }
                }
            });
            var ctx_hoje = document.getElementById('grafico-diario').getContext('2d');
            window.linha_grafico_hoje = new Chart(ctx_hoje, config_hoje);
            var ctx_semana = document.getElementById('grafico-semanal').getContext('2d');
            window.linha_grafico_semana = new Chart(ctx_semana, config_semana);
            var ctx_mes = document.getElementById('grafico-mensal').getContext('2d');
            window.linha_grafico_mes = new Chart(ctx_mes, config_mes);
            setInterval(atualizar, 4000);
        });
        function atualizar() {
            $.ajax({
                url: 'grafico.json.php?id=<?= @$_GET['id'] ?>',
                cache: false,
                success: function(info) {
                    var ultimo_atualizado_hoje = info.hoje[info.hoje.length - 1];
                    if (ultimo_atualizado_hoje.data_hora != ultimo_registro_hoje.data_hora) {
                        data_hora = new Date(ultimo_atualizado_hoje.data_hora);
                        config_hoje.data.labels.push(data_hora.getHours() + ':' + data_hora.getMinutes());
                        config_hoje.data.datasets[0].data.push(ultimo_atualizado_hoje.temperatura);
                        config_hoje.data.datasets[1].data.push(ultimo_atualizado_hoje.higrometro);
                        config_hoje.data.datasets[2].data.push(ultimo_atualizado_hoje.umidade);
                        ultimo_registro_hoje = ultimo_atualizado_hoje;
                        window.linha_grafico_hoje.update();
                    }
                }
            })
        }
    </script>
</head>

<body>
    <div id="main">
        <h1></h1>
        <div>
            <canvas id="grafico-diario"></canvas>
        </div>
        <div>
            <canvas id="grafico-semanal"></canvas>
        </div>
        <div>
            <canvas id="grafico-mensal"></canvas>
        </div>
    </div>
    <script>
        var config = {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Temperatura',
                    fill: 'boundary',
                    xAxisID: 'x-axis-1',
                    yAxisID: 'y-axis-1',
                    backgroundColor: 'red',
                    borderColor: '#ff000011',
                    data: [],
                }, {
                    label: 'Umidade do solo',
                    xAxisID: 'x-axis-1',
                    yAxisID: 'y-axis-2',
                    backgroundColor: 'blue',
                    borderColor: 'blue',
                    data: [],
                    fill: false,
                }, {
                    label: 'Umidade do ar',
                    xAxisID: 'x-axis-1',
                    yAxisID: 'y-axis-2',
                    backgroundColor: 'yellow',
                    borderColor: '#aaaa0022',
                    data: [],
                    fill: false,
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Alface'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        id: 'x-axis-1',
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Horário'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        id: 'y-axis-1',
                        scaleLabel: {
                            display: true,
                            labelString: 'Temperatura'
                        },
                        ticks: {
                            min: -15,
                            max: 50
                        }
                    }, {
                        //type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                        display: true,
                        position: 'right',
                        reverse: true,
                        id: 'y-axis-2',
                        scaleLabel: {
                            display: true,
                            labelString: '%'
                        },
                        ticks: {
                            min: 0,
                            max: 100,
                            stepSize: 5
                        },
                        // grid line settings
                        gridLines: {
                            drawOnChartArea: false, // only want the grid lines for one axis to show up
                        },
                    }]
                }
            }
        };
        let config_hoje = JSON.parse(JSON.stringify(config));
        let config_semana = JSON.parse(JSON.stringify(config));
        let config_mes = JSON.parse(JSON.stringify(config));
    </script>
</body>

</html>
