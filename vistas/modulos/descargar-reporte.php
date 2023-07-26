<?php

require_once "../../controladores/ventas.controlador.php";
require_once "../../modelos/ventas.modelo.php";
require_once "../../controladores/clientes.controlador.php";
require_once "../../modelos/clientes.modelo.php";
require_once "../../controladores/usuarios.controlador.php";
require_once "../../modelos/usuarios.modelo.php";
require_once "../../controladores/productos.controlador.php";
require_once "../../modelos/productos.modelo.php";
require_once "../../controladores/categorias.controlador.php";
require_once "../../modelos/categorias.modelo.php";

// Permite descargar un archivo excel en una ruta dependiendo si es producto o venta
if (isset($_REQUEST['opcion'])) {
    switch ($_REQUEST['opcion']) {
        case "productos":
            $reporte = new ControladorProductos();
            $nombre_archivo = $reporte->ctrDescargarReporteZenodo();
            header("Location: ../../controladores/zenodo.controlador.php?opcion=productos&nombre=$nombre_archivo");
            break;
        case "ventas":
            $reporte = new ControladorVentas();
            $nombre_archivo = $reporte->ctrDescargarReporteZenodo();
            header("Location: ../../controladores/zenodo.controlador.php?opcion=ventas&nombre=$nombre_archivo");
            break;
    }
}

$reporte = new ControladorVentas();
$reporte->ctrDescargarReporte();
