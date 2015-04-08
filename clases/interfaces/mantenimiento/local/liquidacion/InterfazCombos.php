<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Auditoria.php';
    require_once 'Combo.php';
    require_once 'RubroCombo.php';

/**
 * Clase controladora del modulo de administracion de combos de servicios
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazCombos
        extends InterfazBase{

        const LISTAR_TIPOAUTOMOTOR=101;

        const LISTAR_RUBROS=102;

        const ENLAZAR_RUBROS=103;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){

                case InterfazCombos::ENLAZAR_RUBROS:{
                    $actualizados=json_decode($this->args['actualizados']);

                    $idCombo=$actualizados[0]->idcombo;
                    $idRubro=$actualizados[0]->idrubro;
                    $seleccionado=$actualizados[0]->seleccionado;

                    $sql='select * from rubrocombo where idcombo='.$idCombo.' and idrubro='.$idRubro;
                    $resultados=$this->conexion->consultar($sql);
                    if($resultados->getCantidad()==0){
                        $this->retorno->cantidad=0;
                        if($seleccionado){
                            $rubroCombo=new RubroCombo();
                            $rubroCombo->setCombo(new Combo($idCombo));
                            $rubroCombo->setRubro(new Rubro($idRubro));

                            $auditoria=new Auditoria();
                            $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                            $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.liquidacion.combos'));
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                            $auditoria->guardarObjeto(null);
                            $rubroCombo->guardarObjeto($auditoria);
                            $this->retorno->guardado='SI';
                        }
                    }else{
                        $this->retorno->cantidad=1;
                        if(!$seleccionado){
                            $rubroCombo=new RubroCombo($resultados->get(0)->idrubrocombo);
                            $auditoria=new Auditoria();
                            $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                            $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.liquidacion.combos'));
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
                            $auditoria->guardarObjeto(null);
                            $rubroCombo->borrarObjeto($auditoria);
                            $this->retorno->borrado='SI';
                        }
                    }
                    //$this->retorno->data=  TipoAutomotor::getTiposAutomotor(RecordSet::FORMATO_OBJETO, true);
                    $this->retorno->seleccionado=$seleccionado;
                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazCombos::LISTAR_TIPOAUTOMOTOR:{
                    $this->retorno->data=  TipoAutomotor::getTiposAutomotor(RecordSet::FORMATO_OBJETO, true);
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazCombos::LISTAR_RUBROS:{
                    $combo=new Combo($this->getInt('idcombo'));

                    $sql='select if(rubrocombo.idrubrocombo is null, 0, 1) as seleccionado, rubro.idrubro, rubro.descripcion, rubrocombo.idrubrocombo, '.$combo->getIdCombo().' as idcombo
                        from
                        rubro
                        left join rubrocombo on (rubro.idrubro=rubrocombo.idrubro and idcombo='.$combo->getIdCombo().')
                        where
                        rubro.idtipoautomotor='.$combo->getIdTipoAutomotor().'
                        and
                        rubro.estado=1
			and 
			idtiporubro=1
                        order by seleccionado desc, rubro.descripcion';

                    $resultados=$this->conexion->consultar($sql);
                    $this->retorno->data=$resultados->getRegistros();
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazBase::$GUARDAR_DATOS:{
                    $actualizados=json_decode($this->args['actualizados']);
                    $usuario=new Usuario(FrameWork::getIdUsuario());
                    $modulo=Modulo::crearPorClase('siadno.view.administracion.liquidacion.combos');
                    $nuevos=array();

                    $this->conexion->ejecutar('begin;');
                    foreach($actualizados as $registro){
                        $idCombo=(int)$registro->idcombo;
                        if($idCombo<=0)
                            $idCombo=null;
                        $combo=new Combo($idCombo);
                        $combo->setDescripcion($registro->descripcion);
                        $combo->setTipoAutomotor(new TipoAutomotor($registro->idtipoautomotor));
                        $combo->setEstado($registro->estado);

                        $auditoria=new Auditoria();
                        $auditoria->setUsuario($usuario);
                        $auditoria->setModulo($modulo);

                        if($idCombo==null){
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                            $auditoria->guardarObjeto(null);
                            $objeto=new stdClass();
                            $idNuevoCombo=$combo->guardarObjeto($auditoria);
                            $objeto->idtemporal=(int)$registro->idcombo;
                            $objeto->idnuevo=$idNuevoCombo;
                            $nuevos[]=$objeto;
                        }else{
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                            $auditoria->guardarObjeto(null);
                            $combo->guardarObjeto($auditoria);
                        }
                    }
                    $this->conexion->ejecutar('commit;');
                    $this->retorno->msg='';
                    $this->retorno->data=$nuevos;
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazBase::$BORRAR_DATOS:{
                    $combo=new Combo($this->args['id']);
                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.liquidacion.combos'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $combo->borrarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                default:{
                    $this->retorno->success=true;
                    $this->retorno->msg='';
                    $this->retorno->data=Combo::getCombos(RecordSet::FORMATO_OBJETO);
                    echo json_encode($this->retorno);
                }
            }
        }
    }
    new InterfazCombos(new ArrayObject(array_merge($_POST, $_GET)));
?>
