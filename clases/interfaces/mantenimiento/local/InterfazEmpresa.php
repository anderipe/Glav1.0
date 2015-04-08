<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Personal.php';
    require_once 'Empresa.php';

/**
 * Clase controladora del modulo de administracion de los datos de la empresa
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
class interfazEmpresa
    extends InterfazBase{

    const GUARDAR_NOTARIA=101;
    const CARGAR_NOTARIA=102;
    const CARGAR_PERSONAL=103;
    const GUARDAR_PERSONAL=104;


    public function __construct(ArrayObject $args=NULL) {
        parent::__construct($args);

        $accion=isset($this->args['accion'])?$this->args['accion']:0;
        switch($accion){

            case interfazEmpresa::CARGAR_PERSONAL:{
                $this->cargarPersonal();
                break;
            }

            case interfazEmpresa::GUARDAR_PERSONAL:{
                $this->guardarPersonal();
                break;
            }

            case interfazEmpresa::CARGAR_NOTARIA:{
                $this->cargarEmpresa();
                break;
            }

            case interfazEmpresa::GUARDAR_NOTARIA: {
                $this->guardarEmpresa();
                break;
            }

            default:{
                $this->traerDatos();
            }
        }
    }

    protected function cargarPersonal(){
        $personal=new Personal();
        $personal->cargarPorCargo(new Cargo($this->args['idcargo']));
        $persona=$personal->getPersona();

        $this->retorno->data=(object)array_merge((array)$personal->getJson(true), (array)$persona->getJson(true));
        $this->retorno->data->nombres=$persona->getNombre();
        $this->retorno->data->apellidos=$persona->getApellido();

        unset($this->retorno->data->idcargo);
        $this->retorno->msg='';

        echo json_encode($this->retorno);
    }

    protected function guardarPersonal(){
        $cargo=new Cargo($this->args['idcargo']);
        $personal=new Personal();
        $personal->cargarPorCargo($cargo);

        $persona=new Persona();
        $persona->cargarPorIdentificacion(new TipoIdentificacion($this->args['idtipoidentificacion']), $this->args['identificacion']);
        $persona->setTipoIdentificacion(new TipoIdentificacion($this->args['idtipoidentificacion']));
        $persona->setIdentificacion($this->args['identificacion']);
        $persona->setNombres($this->args['nombres'].'|'.$this->args['apellidos']);
        $persona->setDireccion($this->args['direccion']);
        $persona->setTelefonos($this->args['telefonos']);
        $persona->setEMail($this->args['email']);
        $persona->setNacionalidad(new Pais($this->args['idpais']));
        $persona->setSexo($this->args['sexo']);
        $persona->setFechaNacimiento(new DateTime());

        $personal->setCargo($cargo);

        $auditoria=new Auditoria();
        $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
        $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.local.empresa'));
        $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));

        $this->conexion->ejecutar('begin;');
        $auditoria->guardarObjeto(null);
        $persona->guardarObjeto($auditoria);
        $personal->setPersona($persona);
        $personal->guardarObjeto($auditoria);
        $this->conexion->ejecutar('commit;');

        $this->retorno->msg='Los datos del personal han sido guardados ';

        echo json_encode($this->retorno);
    }

    protected function cargarEmpresa(){
        $empresa=Empresa::obtenerMiEmpresa();
        $persona=$empresa->getPersona();
        $this->retorno->data=(object)array_merge((array)$empresa->getJson(true), (array)$persona->getJson(true));

        $municipio=$empresa->getUbicacion();
        $departamento=$municipio->getDepartamento();
        $pais=$departamento->getPais();

        $this->retorno->data->digitoverificacion=' D.V-'.((int)$persona->getDigitoVerificacion());
        $this->retorno->data->idmunicipio=$municipio->getIdMunicipio();
        $this->retorno->data->iddepartamento=$departamento->getIdDepartamento();
        $this->retorno->data->idpais=$pais->getIdPais();
        $this->retorno->msg='';

        echo json_encode($this->retorno);
    }

    protected function guardarEmpresa(){
        $persona=new Persona($this->args['idpersona']);
        $persona->setTipoIdentificacion(new TipoIdentificacion($this->args['idtipoidentificacion']));
        $persona->setIdentificacion($this->args['identificacion']);
        $persona->setNombres($this->args['nombres']);
        $persona->setDireccion($this->args['direccion']);
        $persona->setTelefonos($this->args['telefonos']);
        $persona->setEMail($this->args['email']);
        $persona->setNacionalidad(new Pais(Pais::COLOMBIA));
        $persona->setSexo(0);
        $persona->setFechaNacimiento(new DateTime());

        $empresa=Empresa::obtenerMiEmpresa();
        $empresa->setNombreAbreviado($this->args['nombreabreviado']);
        $empresa->setUbicacion(new Municipio($this->args['idmunicipio']));

        $auditoria=new Auditoria();
        $auditoria->setUsuario(new Usuario(FrameWork::getIdUsuario()));
        $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.local.empresa'));
        $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));

        $this->conexion->ejecutar('begin;');
        $auditoria->guardarObjeto(null);
        $persona->guardarObjeto($auditoria);
        $empresa->setPersona($persona);
        $empresa->guardarObjeto($auditoria);
        $this->conexion->ejecutar('commit;');

        $this->retorno->msg='Los datos de la empresa han sido guardados ';

        echo json_encode($this->retorno);
    }

    protected function traerDatos(){
        $idTipoIdentificacion=$this->args['idtipoidentificacion'];
        $identificacion=$this->args['identificacion'];
        $prefijo=isset($this->args['prefijo'])?$this->args['prefijo']:'';

        $persona=new Persona();
        $persona->cargarPorIdentificacion(new TipoIdentificacion($idTipoIdentificacion), $identificacion);

        $this->retorno->data=$persona->getJson(true, $prefijo);

        $propiedad=$prefijo.'nombres';
        $this->retorno->data->$propiedad=$persona->getNombre();
        $propiedad=$prefijo.'apellidos';
        $this->retorno->data->$propiedad=$persona->getApellido();
        if($persona->getIdTipoIdentificacion()==0 && $persona->getIdentificacion()==''){
            $propiedad=$prefijo.'idtipoidentificacion';
            $this->retorno->data->$propiedad=(int)$idTipoIdentificacion;
            $propiedad=$prefijo.'identificacion';
            $this->retorno->data->$propiedad=$identificacion;
        }

        $this->retorno->msg='';
        echo json_encode($this->retorno);
    }
}

new interfazEmpresa(new ArrayObject(array_merge($_POST, $_GET)));
?>