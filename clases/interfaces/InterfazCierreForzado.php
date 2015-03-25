<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

//    require_once '/media/www/lavado/clases/Framework.php';
//    FrameWork::agregarIncludePath('/media/www/lavado/clases');
//    require_once 'AppException.php';
//    require_once 'ConexionMySQL.php';
//    require_once 'Auditoria.php';
//    FrameWork::mostrarErrores();
//    FrameWork::iniciarSesion();
//
//    $host=isset($_SESSION['db_host'])?$_SESSION['db_host']:'';
//    $puerto=isset($_SESSION['db_port'])?$_SESSION['db_port']:'';
//    $baseDeDatos=isset($_SESSION['db_name'])?$_SESSION['db_name']:'';
//    $usuario=isset($_SESSION['db_user'])?$_SESSION['db_user']:'';
//    $contrasena=isset($_SESSION['db_password'])?$_SESSION['db_password']:'';

    //session_destroy();
    //unset($_SESSION);

//    $retorno= new stdClass();
//    if(!empty($host) && !empty($puerto) && !empty($baseDeDatos) && !empty($usuario)){
//        $miFramework=new FrameWork();
//        $retorno->msg='';
//        $usuario=new Usuario(FrameWork::getIdUsuario());
//        $accionAuditable= new AccionAuditable(AccionAuditable::CierreDelSistema);
//        $modulo=new Modulo(7);
//        $auditoria= new Auditoria();
//        $auditoria->setUsuario($usuario);
//        $auditoria->setModulo($modulo);
//        $auditoria->setAccionAuditable($accionAuditable);
//        $auditoria->setDescripcion('Forzada');
//        $auditoria->guardarObjeto(null);
//    }
//
//    session_destroy();
//    unset($_SESSION);

    $retorno= new stdClass();
    $retorno->success=true;
    $retorno->msg='';
    echo json_encode($retorno);
?>