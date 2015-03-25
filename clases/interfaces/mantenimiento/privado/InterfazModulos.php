<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '../../../interfaces/InterfazBase.php';
    require_once 'Modulo.php';
    require_once 'Auditoria.php';

/**
 * Clase controladora del modulo que administra los modulos del sistema
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazModulos
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                case InterfazBase::$NUEVO:{
                    $modulo=new Modulo();
                    $moduloPadre=new Modulo($this->args['idmodulo']);
                    $modulo->setModuloPadre($moduloPadre);
                    $modulo->setClase('siadno.view.sistema.cierre');
                    $modulo->setNombre('--Nuevo--(por defecto, salida)');
                    $modulo->setIconCss('icon-brick');
                    $modulo->setOrden($moduloPadre->getUltimoOrdenHijo()+1);

                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.mantenimiento.privado.modulos'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $modulo->guardarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    $this->retorno->data=$modulo->getJson(true);
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazBase::$GUARDAR_DATOS:{
                    $modulo=new Modulo($this->args['idmodulo']);
                    $moduloPadre=new Modulo($this->args['idmodulopadre']);
                    $modulo->setModuloPadre($moduloPadre);
                    $modulo->setClase($this->args['clase']);
                    $modulo->setNombre($this->args['nombre']);
                    $modulo->setIconCss($this->args['iconcss']);
                    $modulo->setOrden($this->args['orden']);

                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.mantenimiento.privado.modulos'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $modulo->guardarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    $this->retorno->data=$modulo->getJson(true);
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazBase::$BORRAR_DATOS:{
                    $modulo=new Modulo($this->args['idmodulo']);
                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.mantenimiento.privado.modulos'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $modulo->borrarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                default:{
                    $idModulo=(int)$this->args['idmodulo'];

                    $objetoJson=new stdClass();
                    $objetoJson->success=true;
                    $objetoJson->msg="todo bien";
                    $objetoJson->data=array();
                    if($idModulo==-1){
                        $moduloHijo=new stdClass();
                        $moduloHijo->idmodulo=1;
                        $moduloHijo->nombre='S.I.A.D.N.O';
                        $moduloHijo->clase='';
                        $moduloHijo->iconcss='';
                        $moduloHijo->orden=1;
                        $objetoJson->data[]=$moduloHijo;
                    }else{
                        $moduloPadre=new Modulo($idModulo);
                        $modulosHijos=$moduloPadre->getModulosHijo('modulo');
                        foreach ($modulosHijos as $modulo){
                            $moduloHijo=$modulo->getJson(true);
                            $clase=$modulo->getClase();
                            if(!empty($clase))
                                $moduloHijo->leaf=true;
                            $objetoJson->data[]=$moduloHijo;
                        }
                    }
                    $objetoJson->total=count($objetoJson->data);
                    echo json_encode($objetoJson);
                }
            }
        }
    }
    new InterfazModulos(new ArrayObject(array_merge($_POST, $_GET)));
?>