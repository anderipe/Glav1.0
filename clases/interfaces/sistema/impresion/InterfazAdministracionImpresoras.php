<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
require_once 'Usuario.php';
require_once 'Impresora.php';
require_once 'Auditoria.php';
require_once 'Impresion.php';

/**
 * Clase controladora del modulo que administra las impresoras del usuario
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
class InterfazAdministracionImpresoras
    extends InterfazBase{

    const TRAER_IMPRESIONES=101;
    const IMPRIMIR=1001;

    public function __construct(ArrayObject $args=NULL) {
        parent::__construct($args);
        $accion=isset($this->args['accion'])?$this->args['accion']:0;
        switch($accion){
            case InterfazAdministracionImpresoras::IMPRIMIR:{
                $this->imprimir();
                break;
            }

            case InterfazAdministracionImpresoras::TRAER_IMPRESIONES:{
                $this->traerImpresiones();
                break;
            }

            case InterfazBase::$GUARDAR_DATOS:{
                $this->guardarDatos();
                break;
            }

            default:{
                $this->traerDatos();
            }
        }
    }

    protected function traerImpresiones(){
        $usuario=new Usuario(FrameWork::getIdUsuario());
        $this->retorno->data=$usuario->getImpresiones(RecordSet::FORMATO_OBJETO);
        $this->retorno->msg='';
        echo json_encode($this->retorno);
    }

    protected function traerDatos(){
        $usuario=new Usuario(FrameWork::getIdUsuario());
        $this->retorno->data=$usuario->getImpresoras(RecordSet::FORMATO_OBJETO);
        $this->retorno->msg='';
        echo json_encode($this->retorno);
    }

    protected function guardarDatos(){
        $usuario=new usuario(FrameWork::getIdUsuario());
        $auditoria=new Auditoria();
        $auditoria->setUsuario($usuario);
        $auditoria->setModulo(Modulo::crearPorClase('siadno.view.sistema.misistema.impresion.administracionimpresoras'));

        $actualizados=json_decode($this->args['actualizados']);
        $nuevos=array();
        $this->conexion->ejecutar('begin;');
        foreach($actualizados as $registro){
            $idImpresora=(int)$registro->idimpresora;
            if($idImpresora<=0)
                $idImpresora=null;

            $tipoImpresora=new TipoImpresora($registro->idtipoimpresora);

            $impresoraBuscada=new Impresora();
            $impresoraBuscada->cargarPorUsuario($usuario, $tipoImpresora);
            $idImpresoraBuscada=$impresoraBuscada->getIdImpresora();

            $impresora=new Impresora($idImpresora);
            $impresora->setNombre($registro->nombre);
            $impresora->setUsuario($usuario);
            $impresora->setLenguajeImpresion(new LenguajeImpresion($registro->idlenguajeimpresion));
            $impresora->setTipoImpresora($tipoImpresora);
            $impresora->setOffSetX($registro->offsetx);
            $impresora->setOffSetY($registro->offsety);

            if($idImpresora==null){
                $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                $auditoria->guardarObjeto(null);
                $objeto=new stdClass();
                $idNuevaImpresora=$impresora->guardarObjeto($auditoria);
                $objeto->idtemporal=$registro->idimpresora;
                $objeto->idnuevo=$idNuevaImpresora;
                $nuevos[]=$objeto;
            }else{
                $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $auditoria->guardarObjeto(null);
                $impresora->guardarObjeto($auditoria);
            }

            if(!empty($idImpresoraBuscada) && $idImpresoraBuscada!=$impresora->getIdImpresora()){
                $impresoraBuscada->borrarObjeto($auditoria);
            }
        }
        $this->conexion->ejecutar('commit;');
        $this->retorno->msg="Los datos de la impresora han sido guardados";
        $this->retorno->data=$nuevos;
        echo json_encode($this->retorno);
    }

    protected function imprimir(){
        $idImpresion=isset($this->args['idimpresion'])?$this->args['idimpresion']:0;
        $impresion=null;
        if(!empty($idImpresion)){
            $impresion=new Impresion($idImpresion);
        }else{
            $usuario=new Usuario(FrameWork::getIdUsuario());
            $impresiones=$usuario->getUltimaImpresion(RecordSet::FORMATO_CLASE);
            if(!empty($impresiones))
                $impresion=$impresiones[0];
        }

        if(!empty($impresion)){
            if($impresion->getIdTipoImpresion()==TipoImpresion::EXCEL){
                $dateTime= new DateTime();
                header('Content-Type: application/xlsx');
                header('Content-Disposition: attachment;Filename=documento_'.$dateTime->format('Y-m-d-h-i-s').'.xlsx');
                echo base64_decode($impresion->getContenido());
            }elseif($impresion->getIdTipoImpresion()==TipoImpresion::PDF){
                $dateTime= new DateTime();
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment;Filename=documento_'.$dateTime->format('Y-m-d-h-i-s').'.pdf');
                echo base64_decode($impresion->getContenido());
            }else{
                echo $impresion->getContenido();
            }
        }else{
            echo '';
        }
    }
}

new InterfazAdministracionImpresoras(new ArrayObject(array_merge($_POST, $_GET)));
/**
 * Cuidado de no dejar ningun caracter extra al final
 */
?>