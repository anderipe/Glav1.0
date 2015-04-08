<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Usuario.php';
    require_once 'Variable.php';
    require_once 'Auxiliar.php';

/**
 * Clase controladora del modulo de administracion de usuarios
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
class InterfazUsuarios
    extends InterfazBase{

    const AGREGAR_PERFIL=101;
    const QUITAR_PERFIL=102;
    const PERFILES_ASIGNADOS=104;
    const PERFILES_NO_ASIGNADOS=105;

    public function __construct(ArrayObject $args=NULL) {
        parent::__construct($args);

        $accion=isset($this->args['accion'])?$this->args['accion']:0;
        switch($accion){
            case interfazUsuarios::AGREGAR_PERFIL:{
                $this->agregarPerfil();
                break;
            }

            case interfazUsuarios::QUITAR_PERFIL:{
                $this->quitarPerfil();
                break;
            }
            case InterfazBase::$GUARDAR_DATOS:{
                $this->guardarDatos();
                break;
            }

            case InterfazBase::$BORRAR_DATOS:{
                $this->borrarDatos();
                break;
            }

            case interfazUsuarios::PERFILES_ASIGNADOS:{
                $usuario=new Usuario($this->args['idusuario']);
                $this->retorno->data=$usuario->getPerfiles(RecordSet::FORMATO_OBJETO);
                $this->retorno->total=count($this->retorno->data);
                echo json_encode($this->retorno);

                break;
            }

            case interfazUsuarios::PERFILES_NO_ASIGNADOS:{
                $usuario=new Usuario($this->args['idusuario']);
                $this->retorno->data=$usuario->getPerfilesNoAsignados(RecordSet::FORMATO_OBJETO);
                $this->retorno->total=count($this->retorno->data);
                echo json_encode($this->retorno);
                break;
            }

            default:{
                $this->traerDatos();
            }
        }
    }

    protected function borrarDatos(){
        $auditoria=new Auditoria();
        $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
        $auditoria->setModulo(Modulo::crearPorClase('siadno.view.mantenimiento.local.permisos.usuarios'));
        $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $usuario=new Usuario($this->args['idusuario']);

        $this->conexion->ejecutar('begin;');
        $auditoria->guardarObjeto(null);
        $usuario->borrarObjeto($auditoria);
        $this->conexion->ejecutar('commit;');

        $this->retorno->msg='La persona ya no es usuario del sistema';
        echo json_encode($this->retorno);
    }

    protected function guardarDatos(){
        $persona=new Persona($this->args['idpersona']);
        $persona->setTipoIdentificacion(new TipoIdentificacion($this->args['idtipoidentificacion']));
        $persona->setIdentificacion($this->args['identificacion']);
        $persona->setNombres($this->args['nombres'].'|'.$this->args['apellidos']);
        $persona->setDireccion($this->args['direccion']);
        $persona->setTelefonos($this->args['telefonos']);
        $persona->setEMail($this->args['email']);
        $persona->setNacionalidad(new Pais($this->args['idpais']));
        $persona->setSexo($this->args['sexo']);
        $persona->setFechaNacimiento(new DateTime($this->args['fechanacimiento']));

        $usuario=new usuario($this->args['idusuario']);
        $usuario->setEstado($this->getBool('estado'));
        $usuario->setLogin($this->args['login']);
        if($usuario->getPassword()!=$this->args['password']){
            $usuario->setPassword($this->args['password'], $this->args['password']);
        }

        $auditoria=new Auditoria();
        $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
        $auditoria->setModulo(Modulo::crearPorClase('siadno.view.mantenimiento.local.permisos.usuarios'));
        if($persona->getIdPersona()==0)
            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
        else
            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));

        $this->conexion->ejecutar('begin;');
        $auditoria->guardarObjeto(null);
        $persona->guardarObjeto($auditoria);
        $usuario->setPersona($persona);
        $usuario->guardarObjeto($auditoria);
        $this->conexion->ejecutar('commit;');
        $this->retorno->msg='Los datos del usuario han sido guardados';

        echo json_encode($this->retorno);
    }

    protected function traerDatos(){
        $idTipoIdentificacion=$this->args['idtipoidentificacion'];
        $identificacion=$this->args['identificacion'];

        $persona=new Persona();
        $persona->cargarPorIdentificacion(new TipoIdentificacion($idTipoIdentificacion), $identificacion);

        $usuario=new Usuario();
        $usuario->cargarPorPersona($persona);

        $this->retorno->data=$persona->getJson(true);
        if($persona->getIdTipoIdentificacion()==0 && $persona->getIdentificacion()==''){
            $this->retorno->data->idtipoidentificacion=(int)$idTipoIdentificacion;
            $this->retorno->data->identificacion=$identificacion;
            $this->retorno->data->fechanacimiento=date('Y-m-d');
            $this->retorno->data->sexo=1;
        }
        $nombres=  explode('|', $this->retorno->data->nombres);
        $this->retorno->data->nombres=$nombres[0];
        if(isset($nombres[1]))
            $this->retorno->data->apellidos=$nombres[1];
        else
            $this->retorno->data->apellidos='';
        $this->retorno->data->idusuario=$usuario->getIdUsuario();
        $this->retorno->data->estado=$usuario->getEstado();
        $this->retorno->data->login=$usuario->getLogin();
        $this->retorno->data->password=$usuario->getPassword();
        $this->retorno->data->password2=$usuario->getPassword();
        $this->retorno->msg='';
        echo json_encode($this->retorno);
    }

    protected function agregarPerfil(){
        $idUsuario=$this->args['idusuario'];
        $idPerfil=$this->args['idperfil'];
        $usuario=new Usuario($idUsuario);


        $auditoria=new Auditoria();
        $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
        $auditoria->setModulo(Modulo::crearPorClase('siadno.view.mantenimiento.local.permisos.usuarios'));
        $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));

        $this->conexion->ejecutar('begin;');
        $auditoria->guardarObjeto(null);
        $usuario->asignarPerfil(new Perfil($idPerfil), $auditoria);
        $this->conexion->ejecutar('commit;');

        $this->retorno->msg='';
        echo json_encode($this->retorno);
    }

    protected function quitarPerfil(){
        $idUsuario=$this->args['idusuario'];
        $idPerfil=$this->args['idperfil'];
        $usuario=new Usuario($idUsuario);


        $auditoria=new Auditoria();
        $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
        $auditoria->setModulo(Modulo::crearPorClase('siadno.view.mantenimiento.local.permisos.usuarios'));
        $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));

        $this->conexion->ejecutar('begin;');
        $auditoria->guardarObjeto(null);
        $usuario->quitarPerfil(new Perfil($idPerfil), $auditoria);
        $this->conexion->ejecutar('commit;');

        $this->retorno->msg='';
        echo json_encode($this->retorno);
    }
}

new InterfazUsuarios(new ArrayObject(array_merge($_POST, $_GET)));
?>