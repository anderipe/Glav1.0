<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Persona.php';
require_once 'Municipio.php';

/**
 * Clase que hace abtraccion de los datos generales de la empresa.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Empresa
    extends ClaseBase{

    protected $idEmpresa=0;

    protected $idPersona=0;

    protected $idMunicipio=0;

    protected $idCategoriaEmpresa=0;

    protected $nombreAbreviado='';

    protected $codigoSuper='';

    protected $subsidiada=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idEmpresa', 0);
        $this->setPropiedad('idPersona', 0);
        $this->setPropiedad('idMunicipio', 0);
        $this->setPropiedad('nombreAbreviado', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idempresa='.$id))
                throw new AppException('No existe empresa con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idEmpresa))
            throw new AppException('La empresa ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from empresa where '.$string);

        if($resultados->getCantidad()==0){
            return false;
        }

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una empresa para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idEmpresa', (int)$resultados->get()->idempresa, true);
        $this->setPropiedad('idPersona', (int)$resultados->get()->idpersona, true);
        $this->setPropiedad('idMunicipio', (int)$resultados->get()->idmunicipio, true);
        $this->setPropiedad('nombreAbreviado', (string)$resultados->get()->nombreabreviado, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function getIdPersona(){
        return $this->idPersona;
    }

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

    public function getIdMunicipio(){
        return $this->idMunicipio;
    }

    public function getUbicacion(){
        return new Municipio($this->idMunicipio);
    }

    public function setUbicacion(Municipio $municipio){
        $valor=$municipio->getIdMunicipio();
        if(empty($valor))
            throw new AppException('el municipio no existe',
                (object)array($this->getNombreJson('idmunicipio')=>'el municipio no existe'));

        return $this->setPropiedad('idMunicipio', $valor);
    }

    public function setNombreAbreviado($nombre){
        $valor=(string)$nombre;
        if(mb_strlen($valor)>256)
            throw new AppException('El nombre abreviado puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('nombreabreviado')=>'El nombre abreviado puede tener maximo 256 caracteres'));

        return $this->setPropiedad('nombreAbreviado', $valor);
    }

    public function getNombreAbreviado(){
        return $this->nombreAbreviado;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idPersona))
            throw new AppException('La persona juridica vinculada a la empresa es obligatoria',
                (object)array($this->getNombreJson('idpersona')=>'La persona juridica vinculada a la empresa es obligatoria'));

        if(empty($this->idMunicipio))
            throw new AppException('El municipio de ubicacion de la empresa es obligatorio',
                (object)array($this->getNombreJson('idmunicipio')=>'El municipio de ubicacion de la empresa es obligatorio'));

        if(empty($this->nombreAbreviado))
            throw new AppException('El nombre abreviado de la empresa es obligatorio',
                (object)array($this->getNombreJson('nombreabreviado')=>'El nombre abreviado de la empresa es obligatorio'));

        if(empty($this->idEmpresa)){
            $sql='insert INTO empresa
                (idempresa, idpersona, idmunicipio, nombreabreviado)
                values(null, '.$this->idPersona.', '.$this->idMunicipio.', \''.mysql_real_escape_string($this->nombreAbreviado).'\')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idEmpresa', (int)$id);

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
            $modificacion->addDescripcionId($this->idPersona);

            if($this->estaModificada('idPersona')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idPersona'));
                $cambios[]='idpersona='.$this->idPersona;
                $this->marcarNoModificada('idPersona');
            }

            if($this->estaModificada('idMunicipio')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idMunicipio'));
                $cambios[]='idmunicipio='.$this->idMunicipio;
                $this->marcarNoModificada('idMunicipio');
            }

            if($this->estaModificada('nombreAbreviado')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('nombreAbreviado'));
                $cambios[]='nombreabreviado=\''.mysql_real_escape_string($this->nombreAbreviado).'\'';
                $this->marcarNoModificada('nombreAbreviado');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update empresa set $update where idempresa=".$this->idEmpresa;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idEmpresa;
    }

    /**
     *
     * @return \Empresa
     */
    public static function obtenerMiEmpresa(){
        $sql='select idempresa from empresa limit 1';
        $resultados=  FrameWork::getConexion()->consultar($sql);
        if($resultados->getCantidad()>0)
            $empresa=new Empresa($resultados->get(0)->idempresa);
        else
            $empresa=new Empresa();

        return $empresa;
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>