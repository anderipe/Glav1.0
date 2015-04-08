<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Auditoria.php';
    require_once 'TipoAutomotor.php';

/**
 * Clase controladora del modulo de administracion tipos de automotor
 * municipios
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazTiposAutomotor
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                case InterfazBase::$GUARDAR_DATOS:{
                    $actualizados=json_decode($this->args['actualizados']);
                    $usuario=new Usuario(FrameWork::getIdUsuario());
                    $modulo=Modulo::crearPorClase('siadno.view.administracion.otros.tiposautomotores');
                    $nuevos=array();

                    $this->conexion->ejecutar('begin;');
                    foreach($actualizados as $registro){
                        $idTipoAutomotor=(int)$registro->idtipoautomotor;
                        if($idTipoAutomotor<=0)
                            $idTipoAutomotor=null;
                        $tipoAutomotor=new TipoAutomotor($idTipoAutomotor);
                        $tipoAutomotor->setDescripcion($registro->descripcion);
                        $tipoAutomotor->setEstado($registro->estado);

                        $auditoria=new Auditoria();
                        $auditoria->setUsuario($usuario);
                        $auditoria->setModulo($modulo);

                        if($idTipoAutomotor==null){
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                            $auditoria->guardarObjeto(null);
                            $objeto=new stdClass();
                            $idNuevoTipoAutomotor=$tipoAutomotor->guardarObjeto($auditoria);
                            $objeto->idtemporal=(int)$registro->idtipoautomotor;
                            $objeto->idnuevo=$idNuevoTipoAutomotor;
                            $nuevos[]=$objeto;
                        }else{
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                            $auditoria->guardarObjeto(null);
                            $tipoAutomotor->guardarObjeto($auditoria);
                        }
                    }
                    $this->conexion->ejecutar('commit;');
                    $this->retorno->msg='';
                    $this->retorno->data=$nuevos;
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazBase::$BORRAR_DATOS:{
                    $tipoAutomotor=new TipoAutomotor($this->args['id']);
                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.otros.tiposautomotores'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $tipoAutomotor->borrarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                default:{
                    $this->retorno->success=true;
                    $this->retorno->msg='';
                    $this->retorno->data=TipoAutomotor::getTiposAutomotor(RecordSet::FORMATO_OBJETO);
                    echo json_encode($this->retorno);
                }
            }
        }
    }
    new InterfazTiposAutomotor(new ArrayObject(array_merge($_POST, $_GET)));
?>