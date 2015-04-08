<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Auditoria.php';
    require_once 'TipoIdentificacion.php';

/**
 * Clase controladora del modulo de administracion de tipos de identificacion
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazTiposIdentificacion
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                case InterfazBase::$GUARDAR_DATOS:{
                    $actualizados=json_decode($this->args['actualizados']);
                    $usuario=new Usuario(FrameWork::getIdUsuario());
                    $modulo=Modulo::crearPorClase('siadno.view.administracion.personas.tiposidentificacion');
                    $nuevos=array();

                    $this->conexion->ejecutar('begin;');
                    foreach($actualizados as $registro){
                        $idTipoIdentificacion=(int)$registro->idtipoidentificacion;
                        if($idTipoIdentificacion<=0)
                            $idTipoIdentificacion=null;
                        $tipoIdentificacion=new TipoIdentificacion($idTipoIdentificacion);
                        $tipoIdentificacion->setNombre($registro->nombre);
                        $tipoIdentificacion->setAbreviatura($registro->abreviatura);
                        $tipoIdentificacion->setEstado($registro->estado);
                        $tipoIdentificacion->setEsPersonaNatural($registro->espersonanatural);

                        $auditoria=new Auditoria();
                        $auditoria->setUsuario($usuario);
                        $auditoria->setModulo($modulo);

                        if($idTipoIdentificacion==null){
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                            $auditoria->guardarObjeto(null);
                            $objeto=new stdClass();
                            $idNuevoTipoIdentificacion=$tipoIdentificacion->guardarObjeto($auditoria);
                            $objeto->idtemporal=(int)$registro->idtipoidentificacion;
                            $objeto->idnuevo=$idNuevoTipoIdentificacion;
                            $nuevos[]=$objeto;
                        }else{
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                            $auditoria->guardarObjeto(null);
                            $tipoIdentificacion->guardarObjeto($auditoria);
                        }
                    }
                    $this->conexion->ejecutar('commit;');
                    $this->retorno->msg='';
                    $this->retorno->data=$nuevos;
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazBase::$BORRAR_DATOS:{
                    $tipoIdentificacion=new TipoIdentificacion($this->args['id']);
                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.personas.tiposidentificacion'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $tipoIdentificacion->borrarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                default:{
                    $this->retorno->success=true;
                    $this->retorno->msg='';
                    $this->retorno->data=TipoIdentificacion::getTiposIdentificacion(RecordSet::FORMATO_OBJETO);
                    echo json_encode($this->retorno);
                }
            }
        }
    }
    new InterfazTiposIdentificacion(new ArrayObject(array_merge($_POST, $_GET)));
?>