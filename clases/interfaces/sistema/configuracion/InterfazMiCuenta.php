<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Usuario.php';
    require_once 'Variable.php';

/**
 * Clase controladora del modulo que administra los datos de la cuenta del usuario
 * que inicia sesion
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
class InterfazMiCuenta
    extends InterfazBase{

    public function __construct(ArrayObject $args=NULL) {
        parent::__construct($args);

        $accion=isset($this->args['accion'])?$this->args['accion']:0;
        switch($accion){

            case InterfazBase::$GUARDAR_DATOS:{
                $this->guardarDatos();
                break;
            }

            default:{
                $this->traerDatos();
            }
        }
    }

    protected function guardarDatos(){
        $usuario=new usuario($this->args['idusuario']);
        $auditoria=new Auditoria();
        $auditoria->setUsuario($usuario);
        $auditoria->setModulo(Modulo::crearPorClase('siadno.view.sistema.configuracion.micuenta'));
        $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));

        $usuario->setLogin($this->args['login']);
        $mensaje='Usuario: '.$usuario->getLogin();
        if($usuario->getPassword()!=$this->args['password']){
            $usuario->setPassword($this->args['password'], $this->args['password2']);
            $mensaje.=', Contraseña: '.$this->args['password'];
        }

        $this->conexion->ejecutar('begin;');
        $auditoria->guardarObjeto(null);
        $usuario->guardarObjeto($auditoria);
        $this->conexion->ejecutar('commit;');
        $this->retorno->msg='Los datos del usuario han sido guardados';

        echo json_encode($this->retorno);
    }

    protected function traerDatos(){
        $usuario=new Usuario(FrameWork::getIdUsuario());
        $persona=$usuario->getPersona();

        $tipoIdentificacion=$persona->getTipoIdentificacion();
        $this->retorno->data=$persona->getJson(true);

        $this->retorno->data->idtipoidentificacion=$tipoIdentificacion->getAbreviatura();
        $nombres=  explode('|', $this->retorno->data->nombres);
        $this->retorno->data->nombres=$nombres[0];
        if(isset($nombres[1]))
            $this->retorno->data->apellidos=$nombres[1];
        else
            $this->retorno->data->apellidos='';

        $this->retorno->data->idusuario=$usuario->getIdUsuario();
        $this->retorno->data->estado=$usuario->getEstado();

        $pais=$persona->getNacionalidad();
        $this->retorno->data->idpais=$pais->getNacionalidad();

        $this->retorno->data->login=$usuario->getLogin();
        $this->retorno->data->password=$usuario->getPassword();
        $this->retorno->data->password2=$usuario->getPassword();

        $this->retorno->msg='';
        echo json_encode($this->retorno);
    }
}

new InterfazMiCuenta(new ArrayObject(array_merge($_POST, $_GET)));
?>