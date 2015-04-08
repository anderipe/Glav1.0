<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Perfil.php';
    require_once 'Auditoria.php';

/**
 * Clase controladora del modulo de administracion perfiles de usuarios
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazPerfiles
        extends InterfazBase{

        const NUEVO_PERFIL=2001;
        const BORRAR_PERFIL=2002;
        const MODULOS_EN_PERFIL=2003;
        const MODULOS_NOEN_PERFIL=2004;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){

                case InterfazBase::$GUARDAR_DATOS:{
                    $actualizados=json_decode($this->args['actualizados']);
                    $usuario=new Usuario(FrameWork::getIdUsuario());
                    $modulo=Modulo::crearPorClase('siadno.view.mantenimiento.local.permisos.perfiles');
                    $nuevos=array();

                    $this->conexion->ejecutar('begin;');
                    foreach($actualizados as $registro){
                        $idPerfil=(int)$registro->idperfil;
                        if($idPerfil<=0)
                            $idPerfil=null;
                        $perfil=new Perfil($idPerfil);
                        $perfil->setNombre($registro->nombre);
                        $perfil->setEstado($registro->estado);

                        $auditoria=new Auditoria();
                        $auditoria->setUsuario($usuario);
                        $auditoria->setModulo($modulo);

                        if($idPerfil==null){
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                            $auditoria->guardarObjeto(null);
                            $objeto=new stdClass();
                            $idNuevoPerfil=$perfil->guardarObjeto($auditoria);
                            $objeto->idtemporal=(int)$registro->idperfil;
                            $objeto->idnuevo=$idNuevoPerfil;
                            $nuevos[]=$objeto;
                        }else{
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                            $auditoria->guardarObjeto(null);
                            $perfil->guardarObjeto($auditoria);
                        }
                    }
                    $this->conexion->ejecutar('commit;');
                    $this->retorno->msg="La informacion ha sido guardada";
                    $this->retorno->data=$nuevos;
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazBase::$BORRAR_DATOS:{
                    $perfil=new Perfil($this->args['id']);
                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.mantenimiento.local.permisos.perfiles'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $perfil->borrarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='El perfil ha sido eliminado';
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazPerfiles::NUEVO_PERFIL:{
                    $modulo=new Modulo($this->args['idmodulo']);
                    $perfil=new Perfil($this->args['idperfil']);

                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.mantenimiento.local.permisos.perfiles'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $perfil->agregarModulo($modulo, $auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazPerfiles::BORRAR_PERFIL:{
                    $modulo=new Modulo($this->args['idmodulo']);
                    $perfil=new Perfil($this->args['idperfil']);

                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.mantenimiento.local.permisos.perfiles'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $perfil->quitarModulo($modulo, $auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazPerfiles::MODULOS_EN_PERFIL:{
                    $idModulo=isset($this->args['idmodulo'])?(int)$this->args['idmodulo']:0;
                    $idPerfil=isset($this->args['idPerfil'])?(int)$this->args['idPerfil']:0;

                    $perfil=new Perfil($idPerfil);

                    $tableName=  FrameWork::getTmpName().'moduloenperfil';
                    $sql='drop table if EXISTS '.$tableName;
                    $resultados=$this->conexion->ejecutar($sql);

                    $sql='CREATE TEMPORARY TABLE IF NOT EXISTS '.$tableName.'
                        (select modulo.* from
                        modulo
                        join moduloperfil using (idmodulo)
                        where idperfil='.$perfil->getIdPerfil().')';
                    $resultados=$this->conexion->ejecutar($sql);

                    function agregarPadre($conexion, $tableName, $idModulo){
                        $idModulo=(int)$idModulo;
                        $sql='select idmodulopadre from modulo where idmodulo='.$idModulo;
                        $resultados=$conexion->consultar($sql);
                        if($resultados->getCantidad()==0)
                            return;

                        $idModuloPadre=(int)$resultados->get(0)->idmodulopadre;
                        if($idModuloPadre==$idModulo){
                            return;
                        }

                        $sql='select idmodulo from '.$tableName.' where idmodulo='.$idModuloPadre;
                        $resultados=$conexion->consultar($sql);
                        if($resultados->getCantidad()==0){
                            $sql='insert into '.$tableName.' (select * from modulo where idmodulo='.$idModuloPadre.')';
                            $resultados=$conexion->ejecutar($sql);
                            agregarPadre($conexion, $tableName, $idModuloPadre);
                        }
                    }

                    $sql='select idmodulo from '.$tableName.' order by orden';
                    $resultados=$this->conexion->consultar($sql);
                    while($resultados->irASiguiente()){
                        agregarPadre($this->conexion, $tableName, $resultados->get()->idmodulo);
                    }

                    $objetoJson=new stdClass();
                    $objetoJson->success=true;
                    $objetoJson->msg="todo bien";
                    $objetoJson->data=array();
                    if($idModulo==-1){
                        $moduloHijo=new stdClass();
                        $moduloHijo->idmodulo=1;
                        $moduloHijo->nombre='Perfil: '.$perfil->getNombre();
                        $moduloHijo->clase='';
                        $moduloHijo->iconcss='';
                        $moduloHijo->orden=1;
                        $objetoJson->data[]=$moduloHijo;
                    }else{
                        $moduloPadre=new Modulo($idModulo);
                        $modulosHijos=$moduloPadre->getModulosHijo($tableName);
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
                    break;
                }

                case InterfazPerfiles::MODULOS_NOEN_PERFIL:{
                    $idModulo=isset($this->args['idmodulo'])?(int)$this->args['idmodulo']:0;
                    $idPerfil=isset($this->args['idPerfil'])?(int)$this->args['idPerfil']:0;
                    $perfil=new Perfil($idPerfil);

                    $tableName=  FrameWork::getTmpName().'modulonoenperfil';
                    $sql='drop table if EXISTS '.$tableName;
                    $resultados=$this->conexion->ejecutar($sql);

                    $sql='CREATE TEMPORARY TABLE IF NOT EXISTS '.$tableName.'
                        (select m1.*
                        from modulo as m1
                        where
                        not exists(select 1 from modulo as m2 where m2.idmodulopadre=m1.idmodulo)
                        and
                        not exists(select 1 from moduloperfil where idperfil='.$perfil->getIdPerfil().' and moduloperfil.idmodulo=m1.idmodulo))';
                    $resultados=$this->conexion->ejecutar($sql);

                    function agregarPadre($conexion, $tableName, $idModulo){
                        $idModulo=(int)$idModulo;
                        $sql='select idmodulopadre from modulo where idmodulo='.$idModulo;
                        $resultados=$conexion->consultar($sql);
                        if($resultados->getCantidad()==0)
                            return;

                        $idModuloPadre=(int)$resultados->get(0)->idmodulopadre;
                        if($idModuloPadre==$idModulo){
                            return;
                        }

                        $sql='select idmodulo from '.$tableName.' where idmodulo='.$idModuloPadre;
                        $resultados=$conexion->consultar($sql);
                        if($resultados->getCantidad()==0){
                            $sql='insert into '.$tableName.' (select * from modulo where idmodulo='.$idModuloPadre.')';
                            $resultados=$conexion->ejecutar($sql);
                            agregarPadre($conexion, $tableName, $idModuloPadre);
                        }
                    }

                    $sql='select idmodulo from '.$tableName;
                    $resultados=$this->conexion->consultar($sql);
                    while($resultados->irASiguiente()){
                        agregarPadre($this->conexion, $tableName, $resultados->get()->idmodulo);
                    }

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
                        $modulosHijos=$moduloPadre->getModulosHijo($tableName);
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
                    break;
                }

                default:{
                    $objetoJson=new stdClass();
                    $objetoJson->success=true;
                    $objetoJson->msg="todo bien";
                    $objetoJson->data=Perfil::getPerfiles(RecordSet::FORMATO_OBJETO);
                    $objetoJson->total=count($objetoJson->data);
                    echo json_encode($objetoJson);
                }
            }
        }
    }
    new InterfazPerfiles(new ArrayObject(array_merge($_POST, $_GET)));
?>