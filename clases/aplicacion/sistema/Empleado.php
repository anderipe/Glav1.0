<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Persona.php';

/**
 * Clase que representa un soldado razo en la empresa. Un empleado quien hace
 * el lavado de carros y presta los servicios en general.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Empleado
    extends ClaseBase{

    /**
     *
     * @var int
     */
    protected $idEmpleado=0;

    /**
     *
     * @var int
     */
    protected $idPersona=0;

    /**
     *
     * @var int
     */
    protected $estado=0;

    /**
     *
     * @var string
     */
    public $observaciones='';


    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idEmpleado', (int)0, true);
        $this->setPropiedad('idPersona', (int)0, true);
        $this->setPropiedad('estado', (int)0, true);
        $this->setPropiedad('observaciones', '', true);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idempleado='.$id))
                throw new AppException('No existe empleado con identificador '.$id);
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idEmpleado)){
            throw new AppException('El empleado no existe',
                (object)array($this->getNombreJson('idempleado')=>'El empleado no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El empleado no puede ser borrado, este ha sido utilizado');

        $sql='delete from empleado where idempleado='.$this->idEmpleado;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idEmpleado);
        $modificacion->guardarObjeto($auditoria);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idEmpleado))
            throw new AppException('El empleado ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from empleado where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una empleado para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idEmpleado', (int)$resultados->get()->idempleado, true);
        $this->setPropiedad('idPersona', (int)$resultados->get()->idpersona, true);
        $this->setPropiedad('estado', (int)$resultados->get()->estado, true);
        $this->setPropiedad('observaciones', $resultados->get()->observaciones, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    /**
     *
     * @return string
     */
    public function getObservaciones(){
        return $this->observaciones;
    }

    /**
     *
     * @param string $observaciones
     * @return string
     */
    public function setObservaciones($observaciones){
        return $this->setPropiedad('observaciones', $observaciones);
    }

    /**
     *
     * @return int
     */
    public function getEstado(){
        return $this->estado;
    }

    /**
     *
     * @param int $estado
     * @return int
     * @throws AppException
     */
    public function setEstado($estado){
        $valor=(int)$estado;
        if($valor<0 ||$valor>1)
            throw new AppException('Valor no valido para el estado',
                (object)array($this->getNombreJson('estado')=>'Valor no valido para el estado'));

        return $this->setPropiedad('estado', $valor);
    }

    /**
     *
     * @return int
     */
    public function getIdEmpleado(){
        return $this->idEmpleado;
    }

    /**
     *
     * @return int
     */
    public function getIdPersona(){
        return $this->idPersona;
    }

    /**
     *
     * @return \Persona
     */
    public function getPersona(){
        return new Persona($this->idPersona);
    }

    /**
     *
     * @param Persona $persona
     * @return int
     * @throws AppException
     */
    public function setPersona(Persona $persona){
        $valor=$persona->getIdPersona();
        if(empty($valor))
            throw new AppException('La persona no existe',
                (object)array($this->getNombreJson('idpersona')=>'La persona no existe'));

        return $this->setPropiedad('idPersona', $valor);
    }

    public function guardarObjeto(Auditoria $auditoria=null){
        if(empty($this->idPersona))
            throw new AppException('La persona es un dato obligatorio',
                (object)array($this->getNombreJson('idpersona')=>'La persona es un dato obligatorio'));

        if(empty($this->idEmpleado)){
            $empleado=new Empleado();

            $sql='insert INTO empleado
                (idempleado, idpersona, estado, observaciones)
                values(null, '.$this->idPersona.', '.$this->estado.', \''.mysql_real_escape_string($this->observaciones).'\')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idEmpleado', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idEmpleado);

            if($this->estaModificada('idPersona')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idPersona'));
                $cambios[]='idpersona='.$this->idPersona;
                $this->marcarNoModificada('idPersona');
            }

            if($this->estaModificada('estado')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('estado'));
                $cambios[]='estado='.$this->estado.'';
                $this->marcarNoModificada('estado');
            }

            if($this->estaModificada('observaciones')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('observaciones'));
                $cambios[]='observaciones=\''.mysql_real_escape_string($this->observaciones).'\'';
                $this->marcarNoModificada('observaciones');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update empleado set $update where idempleado=".$this->idEmpleado;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idEmpleado;
    }

    public function cargarPorPersona(Persona $persona){
        $where='idpersona='.$persona->getIdPersona();
        return $this->cargarObjeto($where);
    }

    public function haSidoUtilizado() {
        $sql="select * from servicio where idempleado=".$this->idEmpleado.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }

    public static function consultar(&$cantidadTotal=null, $offSet=null, $limit=null){
        if($cantidadTotal!==null){
            $sql='select count(*) as total from empleado';

            $registros=FrameWork::getConexion()->consultar($sql);
            $cantidadTotal=(int)$registros->get(0)->total;
        }

        $sql='select identificacion, direccion, telefonos, replace(nombres, \'|\', \' \') as nombres, empleado.estado
            from
            empleado
            join persona using (idpersona)
            order by persona.nombres' ;


        if($limit!==null){
            $limit=(int)$limit;
            $sql.=' limit '.$limit;
        }

        if($offSet!==null){
            $offSet=(int)$offSet;
            $sql.=' offset '.$offSet;
        }

        return FrameWork::getConexion()->consultar($sql);
    }

    public static function getEmpleados($estado=true){
        $sql='select replace(persona.nombres, \'|\', \' \') as nombres, idempleado
            from
            empleado
            join persona using (idpersona)
            where
            empleado.estado='.(($estado==true)?'true':'false').'
            order by persona.nombres';
        $resultados=FrameWork::getConexion()->consultar($sql);
        return $resultados->getRegistros();
    }
}

?>