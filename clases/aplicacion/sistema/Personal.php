<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Persona.php';
require_once 'Cargo.php';

/**
 * Clase que representa una persona con un cargo especifico dentro de la empresa
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Personal
    extends ClaseBase{

    protected $idPersonal=0;

    protected $idPersona=0;

    protected $idCargo=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idPersonal', (int)0);
        $this->setPropiedad('idPersona', (int)0);
        $this->setPropiedad('idCargo', (int)0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idpersonal='.$id))
                throw new AppException('No existe personal con identificador '.$id);
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idPersonal)){
            throw new AppException('El personal no existe',
                (object)array($this->getNombreJson('idpersonal')=>'El personal no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El personal no puede ser borrado, este ha sido utilizado');

        $sql='delete from personal where idpersonal='.$this->idPersonal;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idPersonal);
        $modificacion->guardarObjeto($auditoria);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idPersonal))
            throw new AppException('El personal ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from personal where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una personal para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idPersonal', (int)$resultados->get()->idpersonal, true);
        $this->setPropiedad('idPersona', (int)$resultados->get()->idpersona, true);
        $this->setPropiedad('idCargo', (int)$resultados->get()->idcargo, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    /**
     *
     * @param Cargo $cargo
     * @return Personal
     */
    public function cargarPorCargo(Cargo $cargo){
        $where='idcargo='.$cargo->getIdCargo();
        return $this->cargarObjeto($where);
    }

    public function getIdPersonal(){
        return $this->idPersonal;
    }

    public function getIdPersona(){
        return $this->idPersona;
    }

    public function getIdCargo(){
        return $this->idCargo;
    }

    /**
     *
     * @return \Persona
     */
    public function getPersona(){
        return new Persona($this->idPersona);
    }

    public function setPersona(Persona $persona){
        $valor=$persona->getIdPersona();
        if(empty($valor))
            throw new AppException('La persona no existe',
                (object)array($this->getNombreJson('idpersona')=>'La persona no existe'));

        return $this->setPropiedad('idPersona', $valor);
    }

    public function getCargo(){
        return new Cargo($this->idCargo);
    }

    public function setCargo(Cargo $cargo){
        $valor=$cargo->getIdCargo();
        if(empty($valor))
            throw new AppException('El cargo no existe',
                (object)array($this->getNombreJson('idcargo')=>'El cargo no existe'));

        return $this->setPropiedad('idCargo', $valor);
    }

    public function guardarObjeto(Auditoria $auditoria=null){
        if(empty($this->idPersona))
            throw new AppException('La persona es un dato obligatorio',
                (object)array($this->getNombreJson('idpersona')=>'La persona es un dato obligatorio'));

        if(empty($this->idCargo))
            throw new AppException('El cargo es un dato obligatorio',
                (object)array($this->getNombreJson('idcargo')=>'El cargo es un dato obligatorio'));

        if(empty($this->idPersonal)){
            $personal=new Personal();
            $personal->cargarPorCargo($this->getCargo());
            $idPersonal=$personal->getIdPersonal();
            if(!empty($idPersonal))
                throw new AppException('El cargo seleccionado ya esta ocupado',
                    (object)array($this->getNombreJson('idpersonal')=>'El cargo seleccionado ya esta ocupado'));

            $sql='insert INTO personal
                (idpersonal, idpersona, idcargo)
                values(null, '.$this->idPersona.', '.$this->idCargo.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idPersonal', (int)$id);

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
            $modificacion->addDescripcionId($this->idPersonal);

            if($this->estaModificada('idPersona')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idPersona'));
                $cambios[]='idpersona='.$this->idPersona;
                $this->marcarNoModificada('idPersona');
            }

            if($this->estaModificada('idCargo')){
                $personal=new Personal();
                $personal->cargarPorCargo($this->getCargo());
                $idPersonal=$personal->getIdPersonal();
                if(!empty($idPersonal))
                    throw new AppException('El cargo seleccionado ya esta ocupado',
                        (object)array($this->getNombreJson('idpersonal')=>'El cargo seleccionado ya esta ocupado'));

                $modificacion->addDescripcion($this->getTextoParaAuditoria('idCargo'));
                $cambios[]='idcargo='.$this->idCargo;
                $this->marcarNoModificada('idCargo');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update personal set $update where idpersonal=".$this->idPersonal;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idPersonal;
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>