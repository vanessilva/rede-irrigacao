<?php

include "./conexao.php";

// Definindo valores padrão
$nome_planta = '';
$minimo = 0;
$maximo = 100;

$node_ip = '';
$param = @addslashes($_GET['id']);

//if ($id > 0 || $node_ip != NULL) {
$sql = "SELECT id, nome_planta, node_ip, minimo, maximo from no_sensores WHERE id = ? OR node_ip = ?";
$query = $conn->prepare($sql);
$query->bind_param('is', $param, $param);
$query->execute();
$query->bind_result($id, $nome_planta, $b_node_ip, $minimo, $maximo);
$query->fetch();
if ($b_node_ip == null) {
    $node_ip = $param;
} else {
    $node_ip = $b_node_ip;
}
//}
?>


<div class="modal fade" id="cadastro" tabindex="-1" role="dialog" aria-labelledby="cadastro" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= $id > 0 ? 'no_atualizar.php' : 'no_criar.php' ?>" method="post">
                <div class="modal-header">
                    <h2 class="modal-title" id="modal_label">Cadastro de canteiro<?= ($id ? ": #$id" : '') ?></h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="id" value="<?= $id ?>">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="node_name">Nome:</label>
                            <input type="text" class="form-control" name="nome_planta" required="true" placeholder="" value="<?= $nome_planta ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="ip">Endereço IP:</label>
                            <input type="text" class="form-control" name="node_ip" required="true" placeholder="0.0.0.0" value="<?= $node_ip ?>">
                        </div>
                    </div>


                </div>
                <hr>
                <div class="modal-body">
                    <h3 class="modal-title">Regras</h3>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                           <!-- <div class="input-group-text">
                               <input id="minimo" name="minimo" type="checkbox" aria-label="Ativar valor mínimo" <?= $minimo ?  'checked="checked"' : '' ?>>
                            </div>-->
                            <span class="input-group-text">
                                Irrigar quando a umidade do solo
                                ficar abaixo de
                            </span>
                        </div>
                        <input type="text" class="form-control input-sm" name="minimo" aria-label="Valor mínimo em porcentagem" value="<?= $minimo ?>">
                        <div class="input-group-append">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <strong>&rdsh;</strong>
                            </div>
                            <span class="input-group-text">
                                Se ficou abaixo do mínimo,
                                irrigar até atingir
                            </span>
                        </div>
                        <input type="text" class="form-control input-sm" name="maximo" aria-label="Valor máximo em porcentagem" value="<?= $maximo ?>">
                        <div class="input-group-append">
                            <span class="input-group-text">%</span>
                        </div>
                        <script>

                        </script>
                    </div>


                    <!--<div class="form-row">
                        <div class="form-group col-md-6">


                            <label for="minimo">Mínimo:</label>
                            <input type="number" class="form-control" name="minimo" required="true" placeholder="" value="<?= $minimo ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="maximo">Máximo:</label>
                            <input type="number" class="form-control" name="maximo" required="true" placeholder="" value="<?= $maximo ?>">
                        </div>
                    </div>-->
                </div>
                <div class="modal-footer">
                    <?php if ($id) : ?><button onclick="$('#alerta_excluir').modal();" type="button" class="btn btn-danger">Excluir</button><?php endif; ?>
                    <button type="submit" class="btn btn-success">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php if ($id > 0) : ?>


    <div class="modal fade" id="alerta_excluir" tabindex="-1" role="dialog" aria-labelledby="alerta_excluir" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <form action="no_excluir.php" method="post">
                    <div class="modal-header">
                        <h2 class="modal-title">Atenção</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="node_ip" value="<?= $node_ip ?>">

                    <div class="modal-body">
                        <p>Tem certeza que deseja excluir esse canteiro?</p>
                        <p>Os registros podem ser mantidos, a menos que você deseja excluí-los também.</p>
                    </div>
                    <div class="modal-footer">
                        <button name="x" type="submit" class="btn btn-danger" value="no">Excluir apenas o cadastro do canteiro</button>
                        <button name="x" type="submit" class="btn btn-danger" value="tudo">Excluir o canteiro e os todos os seus registros</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
