<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Pais.php';
require_once 'Departamento.php';
require_once 'Municipio.php';

/**
 * Clase que representa un departamento
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Departamento
    extends ClaseBase {

    protected $idDepartamento=0;

    protected $idPais=0;

    protected $nombre='';

    protected $gentilicio='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idDepartamento', 0);
        $this->setPropiedad('idPais', 0);
        $this->setPropiedad('nombre', '');
        $this->setPropiedad('gentilicio', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('iddepartamento='.$id))
                throw new AppException('No existe departamento con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idDepartamento))
            throw new AppException('El departamento ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from departamento where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un departamento para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idDepartamento', (int)$resultados->get()->iddepartamento, true);
        $this->setPropiedad('idPais', (int)$resultados->get()->idpais, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->setPropiedad('gentilicio', (string)$resultados->get()->gentilicio, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idPais))
            throw new AppException('El pais es un dato obligatorio',
                (object)array($this->getNombreJson('idpais')=>'El pais es un dato obligatorio'));

        if(empty($this->nombre))
            throw new AppException('El nombre del departamento el obligatorio',
                (object)array($this->getNombreJson('nombre')=>'El nombre del departamento el obligatorio'));

        if(empty($this->idDepartamento)){
            $sql='insert INTO departamento
                (iddepartamento, idpais , nombre, gentilicio)
                values(null, '.$this->idPais.', \''.mysql_real_escape_string($this->nombre).'\',\''.mysql_real_escape_string($this->gentilicio).'\')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idDepartamento', (int)$id);

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
            $modificacion->addDescripcionId($this->idDepartamento);

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
                $sql="update departamento set $update where iddepartamento=".$this->idDepartamento;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idDepartamento;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idDepartamento)){
            throw new AppException('El departamento no existe',
                (object)array($this->getNombreJson('iddepartamento')=>'El departamento no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El departamento no puede ser borrado, este ha sido utilizado');

        $sql='delete from departamento where iddepartamento='.$this->idDepartamento;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idDepartamento);
        $modificacion->guardarObjeto($auditoria);
    }

    /**
     *
     * @return \Pais
     */
    public function getPais(){
        return new Pais($this->idPais);
    }

    public function getIdDepartamento(){
        return $this->idDepartamento;
    }

    public function getMunicipios($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from municipio where iddepartamento='.$this->idDepartamento.' order by nombre';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idmunicipio from municipio where iddepartamento='.$this->idDepartamento.' order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Municipio($resultados->get()->idmunicipio);

            return $objetos;
        }
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

    public function setPais(Pais $pais){
        $valor=$pais->getIdPais();
        if(empty($valor))
            throw new AppException('El pais no existe',
                (object)array($this->getNombreJson('idpais')=>'El pais no existe'));

        return $this->setPropiedad('idPais', $valor);
    }

    public function getIdPais(){
        return $this->idPais;
    }

    public function haSidoUtilizado() {
        $sql='select idmunicipio from municipio where iddepartamento='.$this->idDepartamento.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }


}

?>
