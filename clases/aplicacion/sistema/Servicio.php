<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Usuario.php';
require_once 'EstadoServicio.php';
require_once 'Persona.php';
require_once 'Automotor.php';
require_once 'Empleado.php';


/**
 * Clase que representa un servicio.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Servicio
    extends ClaseBase {

    const BOGOTA=1;

    protected $idServicio=0;

    protected $idUsuario=0;

    protected $idEstadoServicio=0;

    protected $fechaRegistro='';

    protected $idPersona=0;

    protected $idAutomotor=0;

    protected $fechaEntrega='';

    protected $idEmpleado=0;

    protected $observaciones='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idServicio', 0);
        $this->setPropiedad('idUsuario', 0);
        $this->setPropiedad('idEstadoServicio', 0);
        $this->setPropiedad('fechaRegistro', '');
        $this->setPropiedad('idPersona', 0);
        $this->setPropiedad('idAutomotor', 0);
        $this->setPropiedad('fechaEntrega', '');
        $this->setPropiedad('idEmpleado', 0);
        $this->setPropiedad('observaciones', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idservicio='.$id))
                throw new AppException('No existe servicio con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idServicio))
            throw new AppException('El servicio ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from servicio where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un servicio para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idServicio', (int)$resultados->get()->idservicio, true);
        $this->setPropiedad('idUsuario', (int)$resultados->get()->idusuario, true);
        $this->setPropiedad('idEstadoServicio', (int)$resultados->get()->idestadoservicio, true);
        $this->setPropiedad('fechaRegistro', (string)$resultados->get()->fecharegistro, true);
        $this->setPropiedad('idPersona', (int)$resultados->get()->idpersona, true);
        $this->setPropiedad('idAutomotor', (int)$resultados->get()->idautomotor, true);
        $this->setPropiedad('fechaEntrega', (int)$resultados->get()->fechaentrega, true);
        $this->setPropiedad('idEmpleado', (int)$resultados->get()->idempleado, true);
        $this->setPropiedad('observaciones', (string)$resultados->get()->observaciones, true);

        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function getRubros(){
        $sql='select rubro.idrubro, rubro.porcentajeiva, rubro.descripcion, rubroservicio.cantidad, (rubroservicio.valor/rubroservicio.cantidad) as valorunitario, rubroservicio.total, (rubroservicio.iva/rubroservicio.cantidad) as iva
            from
            rubroservicio
            join rubro using(idrubro)
            where
            idservicio='.$this->idServicio;
        $resultados=$this->conexion->consultar($sql);
        return $resultados->getRegistros();
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idAutomotor))
            throw new AppException('El automotor es un dato obligatorio',
                (object)array($this->getNombreJson('idautomotor')=>'El automotor es un dato obligatorio'));

        if(empty($this->idUsuario))
            throw new AppException('El usuario es un dato obligatorio',
                (object)array($this->getNombreJson('idusuario')=>'El usuario es un dato obligatorio'));

        //if(empty($this->idEmpleado))
        //    throw new AppException('El empleado es un dato obligatorio',
        //        (object)array($this->getNombreJson('idempleado')=>'El empleado es un dato obligatorio'));

        if(empty($this->idEstadoServicio))
            throw new AppException('El estado es un dato obligatorio',
                (object)array($this->getNombreJson('idestadoservicio')=>'El estado es un dato obligatorio'));

        if(empty($this->fechaRegistro))
            throw new AppException('La fecha de registro es obligatoria',
                (object)array($this->getNombreJson('fecharegistro')=>'La fecha de registro es obligatoria'));

        if(empty($this->idServicio)){
            $sql='insert INTO servicio
                (idservicio, idusuario, idestadoservicio, fecharegistro, idpersona, idautomotor, fechaentrega, idempleado, observaciones)
                values(null, '.$this->idUsuario.', '.$this->idEstadoServicio.', \''.mysql_real_escape_string($this->fechaRegistro).'\', '.(!empty($this->idPersona)?$this->idPersona:'null').', '.$this->idAutomotor.', '.(!empty($this->fechaEntrega)?'\''.mysql_real_escape_string($this->fechaEntrega).'\'':'null') .', '.(!empty($this->idEmpleado)?$this->idEmpleado:'null').', \''.mysql_real_escape_string($this->observaciones).'\')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idServicio', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('fechaRegistro'));
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idServicio);

            if($this->estaModificada('idUsuario')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idUsuario'));
                $cambios[]='idusuario=\''.$this->idUsuario;
                $this->marcarNoModificada('idUsuario');
            }

            if($this->estaModificada('idEstadoServicio')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idEstadoServicio'));
                $cambios[]='idestadoservicio='.$this->idEstadoServicio;
                $this->marcarNoModificada('idEstadoServicio');
            }

            if($this->estaModificada('fechaRegistro')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('fechaRegistro'));
                $cambios[]='fecharegistro=\''.mysql_real_escape_string($this->fechaRegistro).'\'';
                $this->marcarNoModificada('fechaRegistro');
            }

            if($this->estaModificada('observaciones')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('observaciones'));
                $cambios[]='observaciones=\''.mysql_real_escape_string($this->observaciones).'\'';
                $this->marcarNoModificada('observaciones');
            }

            if($this->estaModificada('idPersona')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idPersona'));
                $cambios[]='idpersona='.(!empty($this->idPersona)?$this->idPersona:'null');
                $this->marcarNoModificada('idPersona');
            }

            if($this->estaModificada('idAutomotor')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idAutomotor'));
                $cambios[]='idautomotor='.$this->idAutomotor;
                $this->marcarNoModificada('idAutomotor');
            }

            if($this->estaModificada('fechaEntrega')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('fechaEntrega'));
                $cambios[]='fechaentrega='.(!empty($this->fechaEntrega)?'\''.mysql_real_escape_string($this->fechaEntrega).'\'':'null');
                $this->marcarNoModificada('fechaEntrega');
            }

            if($this->estaModificada('idEmpleado')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idEmpleado'));
                $cambios[]='idempleado='.(!empty($this->idEmpleado)?$this->idEmpleado:'null');
                $this->marcarNoModificada('idEmpleado');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update servicio set $update where idservicio=".$this->idServicio;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idServicio;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idServicio)){
            throw new AppException('El servicio no existe',
                (object)array($this->getNombreJson('idservicio')=>'El servicio no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El servicio no puede ser borrado, este ha sido utilizado');

        $sql='delete from servicio where idservicio='.$this->idServicio;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idServicio);
        $modificacion->guardarObjeto($auditoria);
    }

    public function getIdServicio(){
        return $this->idServicio;
    }

    /**
     *
     * @param Usuario $usuario
     * @return type
     * @throws AppException
     */
    public function setUsuario(Usuario $usuario){
        $valor=$usuario->getIdUsuario();
        if(empty($valor))
            throw new AppException('El usuario no existe',
                (object)array($this->getNombreJson('idusuario')=>'El usuario no existe'));

        return $this->setPropiedad('idUsuario', $valor);
    }

    /**
     *
     * @return \Usuario
     */
    public function getUsuario(){
        return new Usuario($this->idUsuario);
    }

    public function getIdUsuario(){
        return $this->idUsuario;
    }

    public function getObservaciones(){
        return $this->observaciones;
    }

    public function setObservaciones($observaciones){
        $valor=(string)$observaciones;
        if(mb_strlen($valor)>256)
            throw new AppException('La observaciones puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('observaciones')=>'La observaciones puede tener maximo 256 caracteres'));

        return $this->setPropiedad('observaciones', $valor);
    }

    /**
     *
     * @param EstadoServicio $estadoServicio
     * @return type
     * @throws AppException
     */
    public function setEstadoServicio(EstadoServicio $estadoServicio){
        $valor=$estadoServicio->getIdEstadoServicio();
        if(empty($valor))
            throw new AppException('El estado servicio no existe',
                (object)array($this->getNombreJson('idEstadoServicio')=>'El estado servicio no existe'));

        return $this->setPropiedad('idEstadoServicio', $valor);
    }

    /**
     *
     * @return \EstadoServicio
     */
    public function getEstadoServicio(){
        return new EstadoServicio($this->idEstadoServicio);
    }

    public function getIdEstadoServicio(){
        return $this->idEstadoServicio;
    }

    /**
     *
     * @return \DateTime
     */
    public function getFechaRegistro(){
        return new DateTime($this->fechaRegistro);
    }

    /**
     *
     * @param DateTime $fechaRegistro
     * @return type
     */
    public function setFechaRegistro(DateTime $fechaRegistro){
        return $this->setPropiedad('fechaRegistro', $fechaRegistro->format('Y-m-d h:i:s'));
    }

    /**
     *
     * @param Persona $persona
     * @return type
     * @throws AppException
     */
    public function setPersona(Persona $persona){
        $valor=$persona->getIdPersona();
        if(empty($valor))
            throw new AppException('El persona no existe',
                (object)array($this->getNombreJson('idPersona')=>'El persona no existe'));

        return $this->setPropiedad('idPersona', $valor);
    }

    /**
     *
     * @return \Persona
     */
    public function getPersona(){
        return new Persona($this->idPersona);
    }

    public function getIdPersona(){
        return $this->idPersona;
    }

    /**
     *
     * @param Automotor $automotor
     * @return type
     * @throws AppException
     */
    public function setAutomotor(Automotor $automotor){
        $valor=$automotor->getIdAutomotor();
        if(empty($valor))
            throw new AppException('El automotor no existe',
                (object)array($this->getNombreJson('idAutomotor')=>'El automotor no existe'));

        return $this->setPropiedad('idAutomotor', $valor);
    }

    /**
     *
     * @return \Automotor
     */
    public function getAutomotor(){
        return new Automotor($this->idAutomotor);
    }

    public function getIdAutomotor(){
        return $this->idAutomotor;
    }

    /**
     *
     * @return \DateTime
     */
    public function getFechaEntrega($comoDateTime=true){
        if($comoDateTime)
            return new DateTime($this->fechaEntrega);
        else
            return $this->fechaEntrega;
    }

    /**
     *
     * @param DateTime $fechaEntrega
     * @return type
     */
    public function setFechaEntrega(DateTime $fechaEntrega){
        return $this->setPropiedad('fechaEntrega', $fechaEntrega->format('Y-m-d h:i:s'));
    }


    /**
     *
     * @param Empleado $empleado
     * @return type
     * @throws AppException
     */
    public function setEmpleado(Empleado $empleado){
        $valor=$empleado->getIdEmpleado();
        if(empty($valor))
            throw new AppException('El empleado no existe',
                (object)array($this->getNombreJson('idEmpleado')=>'El empleado no existe'));

        return $this->setPropiedad('idEmpleado', $valor);
    }

    /**
     *
     * @return \Empleado
     */
    public function getEmpleado(){
        return new Empleado($this->idEmpleado);
    }

    public function getIdEmpleado(){
        return $this->idEmpleado;
    }


    public function haSidoUtilizado() {
        return false;
    }
}

?>
