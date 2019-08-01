<?php

/* @var $this yii\web\View */

use yii\helpers\Url;

?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Bienvenido/a!</h1>

        <p class="lead">Descargue el listado de jugadores haciendo click en el siguiente botón.</p>

        <p><a class="btn btn-lg btn-success" href="<?php echo Url::to(['site/export']) ?>">Descargar</a></p>
    </div>
    
</div>
