<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Departamento.php';

/**
 * Clase que representa un municipio.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Municipio
    extends ClaseBase {

    const BOGOTA=1;

    protected $idMunicipio=0;

    protected $idDepartamento=0;

    protected $nombre='';

    protected $gentilicio='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idMunicipio', 0);
        $this->setPropiedad('idDepartamento', 0);
        $this->setPropiedad('nombre', '');
        $this->setPropiedad('gentilicio', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idmunicipio='.$id))
                throw new AppException('No existe municipio con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idMunicipio))
            throw new AppException('El municipio ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from municipio where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un municipio para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idMunicipio', (int)$resultados->get()->idmunicipio, true);
        $this->setPropiedad('idDepartamento', (int)$resultados->get()->iddepartamento, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->setPropiedad('gentilicio', (string)$resultados->get()->gentilicio, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idDepartamento))
            throw new AppException('El departamento es un dato obligatorio',
                (object)array($this->getNombreJson('iddepartamento')=>'El departamento es un dato obligatorio'));

        if(empty($this->nombre))
            throw new AppException('El nombre del municipio el obligatorio',
                (object)array($this->getNombreJson('nombre')=>'El nombre del municipio el obligatorio'));

        if(empty($this->idMunicipio)){
            $sql='insert INTO municipio
                (idmunicipio, iddepartamento , nombre, gentilicio)
                values(null, '.$this->idDepartamento.', \''.mysql_real_escape_string($this->nombre).'\',\''.mysql_real_escape_string($this->gentilicio).'\')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idMunicipio', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idMunicipio);

            if($this->estaModificada('nombre')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
                $cambios[]='nombre=\''.mysql_real_escape_string($this->nombre).'\'';
                $this->marcarNoModificada('nombre');
            }

            if($this->estaModificada('gentilicio')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('gentilicio'));
                $cambios[]='gentilicio=\''.mysql_real_escape_string($this->gentilicio).'\'';
                $this->marcarNoModificada('gentilicio');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update municipio set $update where idmunicipio=".$this->idDepartamento;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idMunicipio;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idMunicipio)){
            throw new AppException('El municipio no existe',
                (object)array($this->getNombreJson('idmunicipio')=>'El municipio no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El municipio no puede ser borrado, este ha sido utilizado');

        $sql='delete from municipio where idmunicipio='.$this->idMunicipio;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idMunicipio);
        $modificacion->guardarObjeto($auditoria);
    }

    public function getIdMunicipio(){
        return $this->idMunicipio;
    }

    public function setDepartamento(Departamento $departamento){
        $valor=$departamento->getIdDepartamento();
        if(empty($valor))
            throw new AppException('El departamento no existe',
                (object)array($this->getNombreJson('iddepartamento')=>'El departamento no existe'));

        return $this->setPropiedad('idDepartamento', $valor);
    }

    public function getDepartamento(){
        return new Departamento($this->idDepartamento);
    }

    public function getIdDepartamento(){
        return $this->idDepartamento;
    }

    public function setNombre($nombre){
        $valor=(string)$nombre;
        if(mb_strlen($valor)>256)
            throw new AppException('El nombre puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('nombre')=>'El nombre puede tener maximo 256 caracteres'));

        return $this->setPropiedad('nombre', $valor);
    }

    public function getNombre(){
        return $this->nombre;
    }

    public function setGentilicio($gentilicio){
        $valor=(string)$gentilicio;
        if(mb_strlen($valor)>256)
            throw new AppException('El gentilicio puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('gentilicio')=>'El gentilicio puede tener maximo 256 caracteres'));

        return $this->setPropiedad('gentilicio', $valor);
    }

    public function getGentilicio(){
        return $this->gentilicio;
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>
