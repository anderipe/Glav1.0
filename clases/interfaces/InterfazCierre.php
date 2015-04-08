<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

require_once 'InterfazBase.php';
require_once 'Auditoria.php';

/**
 * La unica interfaz que no deriva de interfazBase, basicamente porque a este
 * punto no se ha iniciado sesion y las variables de sistema necesarias para
 * inciar un framework no estan definidas.
 *
 * Este es el unico lugar en donde se permite el uso de codigo extructurado
 * dado que el sistema de clases no se encuentra disponible sin la iniciacion
 * de framework
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
class InterfazCierre
    extends InterfazBase{

    /*
    public function buttongroup($idmodulo){
        $idmodulo=(int)$idmodulo;
        $sql="select * from modulo where idmodulo=$idmodulo";
        $resultado=$this->conexion->consultar($sql);
        $boton=new stdClass();
        $boton->xtype="button";
        $boton->text=$resultado->get(0)->nombre;
    }
    */
    /**
     *
     * @param ArrayObject $args
     */
    public function __construct(ArrayObject $args=NULL) {
         parent::__construct();
        $usuario=new Usuario(FrameWork::getIdUsuario());
        $accionAuditable= new AccionAuditable(AccionAuditable::CierreDelSistema);
        $modulo=new Modulo(7);
        $auditoria= new Auditoria();
        $auditoria->setUsuario($usuario);
        $auditoria->setModulo($modulo);
        $auditoria->setAccionAuditable($accionAuditable);
        $auditoria->setDescripcion('');
        $auditoria->guardarObjeto(null);
        unset($_SESSION);
        session_destroy();
        $this->retorno->msg='';
        echo json_encode($this->retorno);
    }
}
new InterfazCierre(new ArrayObject($_POST));
?>