<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Auditoria.php';
    require_once 'TipoRubro.php';

/**
 * Clase controladora del modulo de administracion de tipos de rubros
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazTiposRubro
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                case InterfazBase::$GUARDAR_DATOS:{
                    $actualizados=json_decode($this->args['actualizados']);
                    $usuario=new Usuario(FrameWork::getIdUsuario());
                    $modulo=Modulo::crearPorClase('siadno.view.administracion.liquidacion.tiposrubro');
                    $nuevos=array();

                    $this->conexion->ejecutar('begin;');
                    foreach($actualizados as $registro){
                        $idTipoRubro=(int)$registro->idtiporubro;
                        if($idTipoRubro<=0)
                            $idTipoRubro=null;
                        $tipoRubro=new TipoRubro($idTipoRubro);
                        $tipoRubro->setDescripcion($registro->descripcion);
                        $tipoRubro->setEstado($registro->estado);

                        $auditoria=new Auditoria();
                        $auditoria->setUsuario($usuario);
                        $auditoria->setModulo($modulo);

                        if($idTipoRubro==null){
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                            $auditoria->guardarObjeto(null);
                            $objeto=new stdClass();
                            $idNuevoTipoRubro=$tipoRubro->guardarObjeto($auditoria);
                            $objeto->idtemporal=(int)$registro->idtiporubro;
                            $objeto->idnuevo=$idNuevoTipoRubro;
                            $nuevos[]=$objeto;
                        }else{
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                            $auditoria->guardarObjeto(null);
                            $tipoRubro->guardarObjeto($auditoria);
                        }
                    }
                    $this->conexion->ejecutar('commit;');
                    $this->retorno->msg='';
                    $this->retorno->data=$nuevos;
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazBase::$BORRAR_DATOS:{
                    $tipoRubro=new TipoRubro($this->args['id']);
                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.liquidacion.tiposrubro'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $tipoRubro->borrarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                default:{
                    $this->retorno->success=true;
                    $this->retorno->msg='';
                    $this->retorno->data=TipoRubro::getTiposRubro(RecordSet::FORMATO_OBJETO);
                    echo json_encode($this->retorno);
                }
            }
        }
    }
    new InterfazTiposRubro(new ArrayObject(array_merge($_POST, $_GET)));
?>