<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Auditoria.php';
    require_once 'TipoGasto.php';

/**
 * Clase controladora del modulo de administracion de tipos de gastos
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazTiposGasto
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                case InterfazBase::$GUARDAR_DATOS:{
                    $actualizados=json_decode($this->args['actualizados']);
                    $usuario=new Usuario(FrameWork::getIdUsuario());
                    $modulo=Modulo::crearPorClase('siadno.view.administracion.liquidacion.tiposgasto');
                    $nuevos=array();

                    $this->conexion->ejecutar('begin;');
                    foreach($actualizados as $registro){
                        $idTipoGasto=(int)$registro->idtipogasto;
                        if($idTipoGasto<=0)
                            $idTipoGasto=null;
                        $tipoGasto=new TipoGasto($idTipoGasto);
                        $tipoGasto->setDescripcion($registro->descripcion);
                        $tipoGasto->setGasto($registro->gasto);

                        $auditoria=new Auditoria();
                        $auditoria->setUsuario($usuario);
                        $auditoria->setModulo($modulo);

                        if($idTipoGasto==null){
                            $tipoGasto->setModificable(1);
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                            $auditoria->guardarObjeto(null);
                            $objeto=new stdClass();
                            $idNuevoTipoGasto=$tipoGasto->guardarObjeto($auditoria);
                            $objeto->idtemporal=(int)$registro->idtipogasto;
                            $objeto->idnuevo=$idNuevoTipoGasto;
                            $nuevos[]=$objeto;
                        }else{
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                            $auditoria->guardarObjeto(null);
                            $tipoGasto->guardarObjeto($auditoria);
                        }
                    }
                    $this->conexion->ejecutar('commit;');
                    $this->retorno->msg='';
                    $this->retorno->data=$nuevos;
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazBase::$BORRAR_DATOS:{
                    $tipoGasto=new TipoGasto($this->args['id']);
                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.liquidacion.tiposgasto'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $tipoGasto->borrarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                default:{
                    $this->retorno->success=true;
                    $this->retorno->msg='';
                    $this->retorno->data=TipoGasto::getTiposGastoTodos(RecordSet::FORMATO_OBJETO);
                    echo json_encode($this->retorno);
                }
            }
        }
    }
    new InterfazTiposGasto(new ArrayObject(array_merge($_POST, $_GET)));
?>