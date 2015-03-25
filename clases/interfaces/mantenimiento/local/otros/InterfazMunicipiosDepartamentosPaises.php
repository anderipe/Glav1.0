<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Auditoria.php';
    require_once 'Municipio.php';

/**
 * Clase controladora del modulo de administracion de paises, departamentos y
 * municipios
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazMunicipiosDepartamentosPaises
        extends InterfazBase{

        const GUARDAR_PAISES=102;
        const BORRAR_PAISES=103;

        const LISTAR_DEPARTAMENTOS=201;
        const GUARDAR_DEPARTAMENTOS=202;
        const BORRAR_DEPARTAMENTOS=203;

        const LISTAR_MUNICIPIOS=301;
        const GUARDAR_MUNICIPIOS=302;
        const BORRAR_MUNICIPIOS=303;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=$this->getInt('accion');
            switch($accion){

                case InterfazMunicipiosDepartamentosPaises::GUARDAR_PAISES:{
                    $actualizados=json_decode($this->args['actualizados']);
                    $usuario=new Usuario(FrameWork::getIdUsuario());
                    $modulo=Modulo::crearPorClase('siadno.view.administracion.otros.municipios');
                    $nuevos=array();

                    $this->conexion->ejecutar('begin;');
                    foreach($actualizados as $registro){
                        $idPais=(int)$registro->idpais;
                        if($idPais<=0)
                            $idPais=null;
                        $pais=new Pais($idPais);
                        $pais->setNombre($registro->nombre);
                        $pais->setNacionalidad($registro->nacionalidad);

                        $auditoria=new Auditoria();
                        $auditoria->setUsuario($usuario);
                        $auditoria->setModulo($modulo);

                        if($idPais==null){
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                            $auditoria->guardarObjeto(null);
                            $objeto=new stdClass();
                            $idNuevoPais=$pais->guardarObjeto($auditoria);
                            $objeto->idtemporal=(int)$registro->idpais;
                            $objeto->idnuevo=$idNuevoPais;
                            $nuevos[]=$objeto;

                            $departamento=new Departamento();
                            $departamento->setPais($pais);
                            $departamento->setNombre($pais->getNombre());
                            $departamento->setGentilicio($pais->getNacionalidad());
                            $departamento->guardarObjeto($auditoria);

                            $municipio=new Municipio();
                            $municipio->setDepartamento($departamento);
                            $municipio->setNombre($departamento->getNombre());
                            $municipio->setGentilicio($departamento->getGentilicio());
                            $municipio->guardarObjeto($auditoria);
                        }else{
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                            $auditoria->guardarObjeto(null);
                            $pais->guardarObjeto($auditoria);
                        }
                    }
                    $this->conexion->ejecutar('commit;');
                    $this->retorno->msg='';
                    $this->retorno->data=$nuevos;

                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazMunicipiosDepartamentosPaises::BORRAR_PAISES :{
                    $pais=new Pais($this->args['id']);
                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.otros.municipios'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $pais->borrarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazMunicipiosDepartamentosPaises::LISTAR_DEPARTAMENTOS:{
                    $idPais=$this->getInt('idpais');
                    $pais=new Pais($idPais);

                    $this->retorno->success=true;
                    $this->retorno->msg='';
                    $this->retorno->data=$pais->getDepartamentos(RecordSet::FORMATO_OBJETO);

                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazMunicipiosDepartamentosPaises::GUARDAR_DEPARTAMENTOS:{
                    $actualizados=json_decode($this->args['actualizados']);
                    $usuario=new Usuario(FrameWork::getIdUsuario());
                    $modulo=Modulo::crearPorClase('siadno.view.administracion.otros.municipios');
                    $nuevos=array();
                    $pais=null;
                    $this->conexion->ejecutar('begin;');
                    foreach($actualizados as $registro){
                        $idDepartamento=(int)$registro->iddepartamento;
                        if($idDepartamento<=0)
                            $idDepartamento=null;
                        $departamento=new Departamento($idDepartamento);
                        if($pais==null)
                            $pais=new Pais($registro->idpais);
                        $departamento->setPais($pais);
                        $departamento->setNombre($registro->nombre);
                        $departamento->setGentilicio($registro->gentilicio);

                        $auditoria=new Auditoria();
                        $auditoria->setUsuario($usuario);
                        $auditoria->setModulo($modulo);

                        if($idDepartamento==null){
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                            $auditoria->guardarObjeto(null);
                            $objeto=new stdClass();
                            $idNuevoDepartamento=$departamento->guardarObjeto($auditoria);
                            $objeto->idtemporal=(int)$registro->iddepartamento;
                            $objeto->idnuevo=$idNuevoDepartamento;
                            $nuevos[]=$objeto;

                            $municipio=new Municipio();
                            $municipio->setDepartamento($departamento);
                            $municipio->setNombre($departamento->getNombre());
                            $municipio->setGentilicio($departamento->getGentilicio());
                            $municipio->guardarObjeto($auditoria);
                        }else{
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                            $auditoria->guardarObjeto(null);
                            $departamento->guardarObjeto($auditoria);
                        }
                    }
                    $this->conexion->ejecutar('commit;');
                    $this->retorno->msg='';
                    $this->retorno->data=$nuevos;

                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazMunicipiosDepartamentosPaises::BORRAR_DEPARTAMENTOS:{
                    $departamento=new Departamento($this->args['id']);
                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.otros.municipios'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $departamento->borrarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazMunicipiosDepartamentosPaises::LISTAR_MUNICIPIOS:{
                    $idDepartamento=$this->getInt('iddepartamento');
                    $departamento=new Departamento($idDepartamento);

                    $this->retorno->success=true;
                    $this->retorno->msg='';
                    $this->retorno->data=$departamento->getMunicipios(RecordSet::FORMATO_OBJETO);

                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazMunicipiosDepartamentosPaises::GUARDAR_MUNICIPIOS:{
                    $actualizados=json_decode($this->args['actualizados']);
                    $usuario=new Usuario(FrameWork::getIdUsuario());
                    $modulo=Modulo::crearPorClase('siadno.view.administracion.otros.municipios');
                    $nuevos=array();
                    $departamento=null;
                    $this->conexion->ejecutar('begin;');
                    foreach($actualizados as $registro){
                        $idMunicipio=(int)$registro->idmunicipio;
                        if($idMunicipio<=0)
                            $idMunicipio=null;
                        $municipio=new Municipio($idMunicipio);
                        if($departamento==null)
                            $departamento=new Departamento($registro->iddepartamento);
                        $municipio->setDepartamento($departamento);
                        $municipio->setNombre($registro->nombre);
                        $municipio->setGentilicio($registro->gentilicio);

                        $auditoria=new Auditoria();
                        $auditoria->setUsuario($usuario);
                        $auditoria->setModulo($modulo);

                        if($idMunicipio==null){
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
                            $auditoria->guardarObjeto(null);
                            $objeto=new stdClass();
                            $idNuevoMunicipio=$municipio->guardarObjeto($auditoria);
                            $objeto->idtemporal=(int)$registro->idmunicipio;
                            $objeto->idnuevo=$idNuevoMunicipio;
                            $nuevos[]=$objeto;
                        }else{
                            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                            $auditoria->guardarObjeto(null);
                            $municipio->guardarObjeto($auditoria);
                        }
                    }
                    $this->conexion->ejecutar('commit;');
                    $this->retorno->msg='';
                    $this->retorno->data=$nuevos;

                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazMunicipiosDepartamentosPaises::BORRAR_MUNICIPIOS:{
                    $municipio=new Municipio($this->args['id']);
                    $auditoria=new Auditoria();
                    $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
                    $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.otros.municipios'));
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $municipio->borrarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                default:{
                    $this->retorno->success=true;
                    $this->retorno->msg='';
                    $this->retorno->data=Pais::getPaises(RecordSet::FORMATO_OBJETO);

                    echo json_encode($this->retorno);
                    break;
                    break;
                }
            }
        }
    }
    new InterfazMunicipiosDepartamentosPaises(new ArrayObject(array_merge($_POST, $_GET)));
?>