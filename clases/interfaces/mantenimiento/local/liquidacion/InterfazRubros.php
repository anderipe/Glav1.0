<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Auditoria.php';
    require_once 'TipoRubro.php';
    require_once 'Rubro.php';

/**
 * Clase controladora del modulo de administracion de rubros
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazRubros
        extends InterfazBase{

        const LISTAR_TIPORUBROS=102;
        const LISTAR_TIPOAUTOMOTOR=103;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){

                case InterfazRubros::LISTAR_TIPOAUTOMOTOR:{
                    $this->retorno->data=  TipoAutomotor::getTiposAutomotor(RecordSet::FORMATO_OBJETO, true);
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazRubros::LISTAR_TIPORUBROS:{
                    $this->retorno->data=TipoRubro::getTiposRubro(RecordSet::FORMATO_OBJETO);
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazBase::$GUARDAR_DATOS:{
                    $actualizados=json_decode($this->args['actualizados']);
                    $usuario=new Usuario(FrameWork::getIdUsuario());
                    $modulo=Modulo::crearPorClase('siadno.view.administracion.liquidacion.rubros');
                    $nuevos=array();

                    $this->conexion->ejecutar('begin;');
                    foreach($actualizados as $registro){
                        $idRubro=(int)$registro->idrubro;
                        if($idRubro<=0)
                            $idRubro=null;
                        $rubro=new Rubro($idRubro);
                        $rubro->setDescripcion($registro->descripcion);
                        $rubro->setPorcentajeIva($registro->porcentajeiva);
                        $rubro->setValorUnitario($registro->valorunitario);
                        $rubro->setTipoRubro(new TipoRubro($registro->idtiporubro));
                        $rubro->setTipoAutomotor(new TipoAutomotor($registro->idtipoautomotor));

                        $rubro->setDescripcion($registro->descripcion);
                        $rubro->setEstado($registro->estado);
                        $rubro->setVisible($registro->visible);

                        $auditoria=new Auditoria();
                        $auditoria->setUsuario($usuario);
                        $auditoria->setModulo($modulo);

                        if($idRubro==null){
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                            $auditoria->guardarObjeto(null);
                            $objeto=new stdClass();
                            $idNuevoRubro=$rubro->guardarObjeto($auditoria);
                            $objeto->idtemporal=(int)$registro->idrubro;
                            $objeto->idnuevo=$idNuevoRubro;
                            $nuevos[]=$objeto;
                        }else{
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                            $auditoria->guardarObjeto(null);
                            $rubro->guardarObjeto($auditoria);
                        }
                    }
                    $this->conexion->ejecutar('commit;');
                    $this->retorno->msg='';
                    $this->retorno->data=$nuevos;
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazBase::$BORRAR_DATOS:{
                    $rubro=new Rubro($this->args['id']);
                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.liquidacion.rubros'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $rubro->borrarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                default:{
                    $tipoRubro=new TipoRubro($this->getInt('idtiporubro'));
                    $this->retorno->data=  Rubro::getRubros($tipoRubro, RecordSet::FORMATO_OBJETO);
                    echo json_encode($this->retorno);
                }
            }
        }
    }
    new InterfazRubros(new ArrayObject(array_merge($_POST, $_GET)));
?>