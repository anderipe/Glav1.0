<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

require_once 'InterfazBase.php';
require_once 'Usuario.php';

/**
 * Interfaz encargada de crear el menu de usuario luego de la autenticacion.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
class InterfazCrearMenu
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
        $this->retorno->msg='';
        $this->retorno->success=true;
        $this->retorno->toolbar=$usuario->getMenu();
        echo json_encode($this->retorno);
    }
}
new InterfazCrearMenu(new ArrayObject($_POST));
?>