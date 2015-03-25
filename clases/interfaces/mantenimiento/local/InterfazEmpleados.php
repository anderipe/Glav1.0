<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Empleado.php';
    require_once 'Auxiliar.php';

/**
 * Clase controladora del modulo de administracion de empleados
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
class InterfazEmpleados
    extends InterfazBase{

    const AGREGAR_PERFIL=101;
    const QUITAR_PERFIL=102;
    const CAMBIAR_PASSWORD=103;

    public function __construct(ArrayObject $args=NULL) {
        parent::__construct($args);

        $accion=isset($this->args['accion'])?$this->args['accion']:0;
        switch($accion){
            case InterfazBase::$GUARDAR_DATOS:{
                $this->guardarDatos();
                break;
            }

            case InterfazBase::$BORRAR_DATOS:{
                $this->borrarDatos();
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
        $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.local.empleados'));
        $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $persona=new Persona($this->args['idpersona']);
        $empleado=new Empleado();
        $empleado->cargarPorPersona($persona);

        $this->conexion->ejecutar('begin;');
        $auditoria->guardarObjeto(null);
        $empleado->borrarObjeto($auditoria);
        $persona->borrarObjeto($auditoria);
        $this->conexion->ejecutar('commit;');

        $this->retorno->msg='El empleado ha sido eliminado del sistema';
        echo json_encode($this->retorno);
    }

    protected function guardarDatos(){
        $persona=new Persona($this->args['idpersona']);

        $tipoIdentificacion=new TipoIdentificacion($this->args['idtipoidentificacion']);
        $persona->setTipoIdentificacion($tipoIdentificacion);
        $persona->setIdentificacion($this->args['identificacion']);
        $persona->setDireccion($this->args['direccion']);
        $persona->setTelefonos($this->args['telefonos']);
        $persona->setEMail($this->args['email']);

        if($tipoIdentificacion->getEsPersonaNatural()==1){
            $nombres=trim($this->args['nombres']);
            $this->args['apellidos']=trim($this->args['apellidos']);
            if(!empty($this->args['apellidos']))
                $nombres.='|'.$this->args['apellidos'];

            $persona->setNombres($nombres);
            $persona->setFechaNacimiento(new DateTime($this->args['fechanacimiento']));
            $persona->setNacionalidad(new Pais($this->args['idpais']));
            $persona->setSexo($this->args['sexo']);
        }else{
            $persona->setNombres($this->args['nombres']);
            $persona->setFechaNacimiento(new DateTime('1500-01-01'));
            $persona->setNacionalidad(new Pais(1));
            $persona->setSexo(0);
        }

        $auditoria=new Auditoria();
        $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
        $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.local.empleados'));
        if($persona->getIdPersona()==0)
            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
        else
            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));

        $this->conexion->ejecutar('begin;');
        $auditoria->guardarObjeto(null);
        $persona->guardarObjeto($auditoria);
        $empleado=new Empleado();
        $empleado->cargarPorPersona($persona);
        $empleado->setPersona($persona);
        $empleado->setEstado($this->getBool('estado'));
        $empleado->setObservaciones($this->getString('observaciones'));
        $empleado->guardarObjeto($auditoria);
        $this->conexion->ejecutar('commit;');
        $this->retorno->msg='Los datos del empleado han sido guardados ';

        echo json_encode($this->retorno);
    }

    protected function traerDatos(){
        $idTipoIdentificacion=$this->args['idtipoidentificacion'];
        $identificacion=$this->args['identificacion'];

        $persona=new Persona();
        $tipoIdentificacion=new TipoIdentificacion($idTipoIdentificacion);
        $persona->cargarPorIdentificacion($tipoIdentificacion, $identificacion);

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

        if($tipoIdentificacion->getEsPersonaNatural()!=1)
            $this->retorno->data->digitoverificacion=' D.V-'.((int)$persona->getDigitoVerificacion());

        $empleado=new Empleado();
        $empleado->cargarPorPersona($persona);
        $this->retorno->data->estado=$empleado->getEstado();
        $this->retorno->data->observaciones=$empleado->getObservaciones();

        $this->retorno->msg='';
        echo json_encode($this->retorno);
    }
}

new InterfazEmpleados(new ArrayObject(array_merge($_POST, $_GET)));
?>