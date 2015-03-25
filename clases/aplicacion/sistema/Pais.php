<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Pais.php';
require_once 'Departamento.php';

/**
 * Clase que representa un pais.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Pais
    extends ClaseBase {

    const COLOMBIA=1;

    protected $idPais=0;

    protected $nombre='';

    protected $nacionalidad='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idPais', 0);
        $this->setPropiedad('nombre', '');
        $this->setPropiedad('nacionalidad', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idpais='.$id))
                throw new AppException('No existe pais con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idPais))
            throw new AppException('El pais ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from pais where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un pais para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idPais', (int)$resultados->get()->idpais, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->setPropiedad('nacionalidad', (string)$resultados->get()->nacionalidad, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->nombre))
            throw new AppException('El nombre del pais el obligatorio',
                (object)array($this->getNombreJson('nombre')=>'El nombre del pais el obligatorio'));

        if(empty($this->nacionalidad))
            throw new AppException('La nacionalidad del pais el obligatorio',
                (object)array($this->getNombreJson('nacionalidad')=>'La nacionalidad del pais el obligatorio'));

        if(empty($this->idPais)){
            $sql='insert INTO pais
                (idpais, nombre, nacionalidad)
                values(null, \''.mysql_real_escape_string($this->nombre).'\',\''.mysql_real_escape_string($this->nacionalidad).'\')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idPais', (int)$id);

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
            $modificacion->addDescripcionId($this->idPais);

            if($this->estaModificada('nombre')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
                $cambios[]='nombre=\''.mysql_real_escape_string($this->nombre).'\'';
                $this->marcarNoModificada('nombre');
            }

            if($this->estaModificada('nacionalidad')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('nacionalidad'));
                $cambios[]='nacionalidad=\''.mysql_real_escape_string($this->nacionalidad).'\'';
                $this->marcarNoModificada('nacionalidad');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update pais set $update where idpais=".$this->idPais;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idPais;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idPais)){
            throw new AppException('El pais no existe',
                (object)array($this->getNombreJson('idpais')=>'El pais no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El pais no puede ser borrado, este ha sido utilizado');

        $sql='delete from pais where idpais='.$this->idPais;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idPais);
        $modificacion->guardarObjeto($auditoria);
    }

    public function getIdPais(){
        return $this->idPais;
    }

    public static function getPaises($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from pais order by nombre';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idpais from pais order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Pais($resultados->get()->idpais);

            return $objetos;
        }
    }

    public function getDepartamentos($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from departamento where idpais='.$this->idPais.' order by nombre';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select iddepartamento from departamento where idpais='.$this->idPais.' order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Departamento($resultados->get()->iddepartamento);

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

    public function setNacionalidad($nacionalidad){
        $valor=(string)$nacionalidad;
        if(mb_strlen($valor)>256)
            throw new AppException('La nacionalidad puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('nacionalidad')=>'La nacionalidad puede tener maximo 256 caracteres'));

        return $this->setPropiedad('nacionalidad', $valor);
    }

    public function getNacionalidad(){
        return $this->nacionalidad;
    }

    public function haSidoUtilizado() {
        $sql='select iddepartamento from departamento where idpais='.$this->idPais.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>
