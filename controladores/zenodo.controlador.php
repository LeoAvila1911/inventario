<?php
/*=============================================
Componente controller que verifica la opcion seleccionada
por el usuario, ejecuta el modelo y enruta la navegacion de paginas.
=============================================*/

// Accede el modelo zenodo
require '../modelos/zenodo.php';

// Accede al archivo de configuraciones
include '../config.php';

// Obtiene los valores de la URL
$opcion = $_REQUEST['opcion'];
$nombre = $_REQUEST['nombre'];

// Lee las configuraciones por ambiente
$archivoEnv = "../application-{$entorno}.env";
$variablesEnv = parse_ini_file($archivoEnv);

// Accede a las variables de entorno por ambiente
$clientID = $variablesEnv['CLIENT_ID'];
$clientSecret = $variablesEnv['CLIENT_SECRET'];
$authURL = $variablesEnv['AUTH_URL'];
$depositURL = $variablesEnv['DEPOSIT_URL'];
$depositFileURL = $variablesEnv['DEPOSIT_FILE_URL'];
$depositPublishURL = $variablesEnv['DEPOSIT_PUBLISH_URL'];
$publish_authorization = $variablesEnv['PUBLISH_AUTHORIZATION'];

// Obtiene al apikey de Xenodo
$zenodo = new Zenodo();
$apiKey = $zenodo->obtenerApiKey($clientID, $clientSecret, $authURL);

// Crea un depósito nuevo y retorna el ID con que fue creado
$nombreArchivo = $opcion . " " . $nombre;
$depositID = $zenodo->crearDeposito($apiKey, $depositURL, $nombreArchivo);

// Carga un archivo excel en el depósito previamente creado
$zenodo->cargarArchivo($apiKey, $depositFileURL, $depositID, $opcion, $nombre);

// Permite publicar un depósito con el archivo adjunto
if ($publish_authorization) {
    $zenodo->publicarDeposito($apiKey, $depositPublishURL, $depositID);
}

// Redirige a la ventana luego de realizar las operaciones
switch ($opcion) {
    case "productos":
        header('Location: ../productos?zenodo=success');
        break;
    case "ventas":
        header('Location: ../ventas?zenodo=success');
        break;
}

// Verifica el estado de la conexión
//$estado = $zenodo->verificarConexion($apiKey, $depositURL);

// Permite eliminar depósitos que no han sido publicados
//$zenodo->eliminarDeposito($apiKey, $depositFileURL, 1221001);
