<?php include "./conexao.php"; ?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Sistema de controle de irrigação</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="bootstrap-4.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="main.css"><!-- -->
	<script src="jquery.js"></script>
	<script src="bootstrap-4.3.1/js/bootstrap.min.js"></script>
	<script src="moment.js"></script><!-- Necessário para chart.js -->
	<script src="chart.min.js"></script><!-- Biblioteca para gráficos -->
	<script src="main.js"></script><!-- Funções utilitárias -->
	
</head>

<body>
	<div id="main" class="container">

		<div class="row">
			<div class="col">
				<h1>Controle de irrigação</h1>
			</div>
			<div class="col col-auto col-md-auto" style="">
				<button type="button" class="btn btn-primary" onclick="window.location = window.location">
					Atualizar
				</button>
			</div>
		</div>

		<div class="row">
			<?php if ($resultado = $conn->query("SELECT nome_planta, minimo, maximo, n.id, 
													s1.higrometro, s1.umidade, s1.temperatura, s1.data_hora, 
													n.node_ip AS node_ip, 
													s1.node_ip AS s_node_ip
												FROM sensores s1 
												LEFT OUTER JOIN sensores s2 
													ON s1.node_ip = s2.node_ip AND (s1.data_hora < s2.data_hora) 
												LEFT OUTER JOIN no_sensores n 
													ON s1.node_ip = n.node_ip 
												WHERE s2.id IS NULL")) : ?>
				<script>

					// Este comando é invocado após a página ser totalmente carregada.
					// O que  isso faz é chamar a função atualizar a cada 4 segundos
					$(document).ready(function() {
						atualizar();
						setInterval(atualizar, 4000);
					});

					// Esta função atualiza os valores de sensores sem recarregar a página toda
					// Ela faz a leitura da página "leitura.json.php" e coloca nos campos específicos de cada nó
					function atualizar() {
						$.ajax({
							url: 'leitura.json.php',
							cache: false,
							success: function(info) {
								for (i = 0; i < info.basico.length; i++) {
									console.log((info.basico[i]));
									id = info.basico[i].id;
									node_ip = info.basico[i].node_ip == null ? info.basico[i].s_node_ip : info.basico[i].node_ip;
									row_id = 'div[data-ip="' + node_ip + '"]'
									$(row_id + ' .higrometro').html(info.basico[i].higrometro);
									$(row_id + ' .umidade').html(info.basico[i].umidade);
									$(row_id + ' .temperatura').html(info.basico[i].temperatura);

									data_registro = new Date(Date.parse(info.basico[i].data_hora));

									if (segundos(data_registro) > 900) {
										mensagem_tempo = '<span class="tempo-excedido badge badge-danger">' + timeSince(data_registro) + ' atrás</span>';
									} else {
										mensagem_tempo = '<span class="badge badge-light">' + timeSince(data_registro) + ' atrás</span>';
									}

									$(row_id + ' .data_hora').html(mensagem_tempo);

									if (info.basico[i].higrometro * 1 < info.basico[i].minimo * 1) {
										$(row_id + ' .higrometro').removeClass('badge-primary');
										$(row_id + ' .higrometro').addClass('badge-danger');
									} else {
										$(row_id + ' .higrometro').addClass('badge-primary');
										$(row_id + ' .higrometro').removeClass('badge-danger');
									}
								}
								for (j = 0; j < 3; j++) {
									$('.tempo-excedido').fadeTo('slow', 0.1).fadeTo('slow', 0.9);
								}
							}
						})
					}
										
					// Esta função atua na criação e edição do cadastro
					// A página 'cadastro.php' é invocada por ajax e inserida 
					// na div da página 'modal_container'
					function editar(id) {
						$.ajax({
							url: 'cadastro.php?id=' + id,
							success: function(html) {
								$("#modal_container").html(html);
								$('#cadastro').modal();
							}
						});
					}

				</script>



				<?php $i = 0; ?>
				<?php while ($linha = $resultado->fetch_object()) : ?>
					<?php $node_ip = ($linha->s_node_ip != null) ? $linha->s_node_ip : $linha->node_ip; ?>
					<div onclick="" class="col-sm node" data-ip="<?= $node_ip ?>">
						<div class="card-deck">
							<div class="card text-center">
								<div class="card-header">
									<div class="card-title">
										<h2>
											<a href="javascript:window.open('mostrar.php?id=<?= $linha->id ?>','','postwindow')">
												<?= $linha->nome_planta ? $linha->nome_planta : '<span class="badge badge-danger">??????</span>' ?>&nbsp;
											</a>
										</h2>
									</div>
								</div>
								<div class="card-body">
									<p>
										Umidade do Solo: <span class="badge badge-info higrometro">?</span><br>
										Umidade do Relativa do Ar: <span class="badge badge-light umidade">?</span><br>
										Temperatura do Ar: <span class="badge badge-light temperatura">?</span>
									</p>
								</div>
								<div class="card-footer">
									<div class="row">
										<div class="col col-6">
											<button title="Editar" onclick="editar('<?= $node_ip ?>')" class="icone editar">
												Editar
											</button>
										</div>
										<div class="col col-6">
											<button title="Mostrar gráfico" onclick="window.open('mostrar.php?id=<?= $linha->id ?>','','postwindow')" class="icone grafico">
												Mostrar gráfico
											</button>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<small class="text-muted data_hora">Aguardando dados...</small>
								</div>
							</div>

						</div>
					</div>
					<?php $i++; ?>
					<?php if ($i >= 3) : ?>
						<?php $i = 0; ?>
						<?= "\n<!-- Quebra de linha -->" . '</div><div class="row">' ?>
					<?php endif; ?>

				<?php endwhile; ?>

			<?php endif; ?>
		</div>
	</div>



	<!-- Modal -->
	<div id="modal_container">

	</div>

</body>

</html>
