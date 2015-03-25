<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';

/**
 * Clase que representa el cargo de un funcionario en la empresa
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Cargo
    extends ClaseBase{

    protected $idCargo=0;

    protected $nombre='';

    protected $estado=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idCargo', 0);
        $this->setPropiedad('nombre', '');
        $this->setPropiedad('estado', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idcargo='.$id))
                throw new AppException('No existe cargo con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idCargo))
            throw new AppException('El cargo ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from cargo where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un cargo para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idCargo', (int)$resultados->get()->idcargo, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->setPropiedad('estado', (int)$resultados->get()->estado, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function getIdCargo(){
        return $this->idCargo;
    }

    public function getNombre(){
        return $this->nombre;
    }

    public function setNombre($nombre){
        $valor=(string)$nombre;
        if(mb_strlen($valor)>256)
            throw new AppException('El nombre puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('nombre')=>'El nombre puede tener maximo 256 caracteres'));

        return $this->setPropiedad('nombre', $valor);
    }

    public function getEstado(){
        return $this->estado;
    }

    public function setEstado($estado){
        $valor=(int)$estado;
        if($valor<0 ||$valor>1)
            throw new AppException('Valor no valido para el estado',
                (object)array($this->getNombreJson('estado')=>'Valor no valido para el estado'));

        return $this->setPropiedad('estado', $valor);
    }

    public static function getCargos($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from cargo order by nombre';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idcargo from cargo order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Cargo($resultados->get()->idcargo);

            return $objetos;
        }
    }

    public function haSidoUtilizado() {
        $sql='select idpersonal from personal where idcargo='.$this->idCargo.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idCargo)){
            throw new AppException('El cargo no existe',
                (object)array($this->getNombreJson('idCargo')=>'El cargo no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El cargo no puede ser borrado, este ha sido utilizado');

        $sql='delete from cargo where idcargo='.$this->idCargo;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idCargo);
        $modificacion->guardarObjeto($auditoria);
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->nombre))
            throw new AppException('El nombre  es obligatorio',
                (object)array($this->getNombreJson('nombre')=>'El nombre  es obligatorio'));

        if(empty($this->idCargo)){
            $sql='insert INTO cargo
                (idcargo, nombre, estado)
                values(null, \''.mysql_real_escape_string($this->nombre).'\','.$this->estado.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idCargo', (int)$id);

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
            $modificacion->addDescripcionId($this->idCargo);

            if($this->estaModificada('nombre')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
                $cambios[]='nombre=\''.mysql_real_escape_string($this->nombre).'\'';
                $this->marcarNoModificada('nombre');
            }

            if($this->estaModificada('estado')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('estado'));
                $cambios[]='estado='.$this->estado;
                $this->marcarNoModificada('estado');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update cargo set $update where idcargo=".$this->idCargo;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idCargo;
    }
}

?>