<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';

/**
 * Clase que representa un tipo de rubro, permitiendo clasificar lo servicios
 * que presta la empresa en tantas categorias como se deseen
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class TipoRubro
    extends ClaseBase {

    protected $idTipoRubro=0;

    protected $descripcion='';

    protected $estado=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idTipoRubro', 0);
        $this->setPropiedad('descripcion', '');
        $this->setPropiedad('estado', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idtiporubro='.$id))
                throw new AppException('No existe tiporubro con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idTipoRubro))
            throw new AppException('El tiporubro ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from tiporubro where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un tiporubro para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idTipoRubro', (int)$resultados->get()->idtiporubro, true);
        $this->setPropiedad('descripcion', (string)$resultados->get()->descripcion, true);
        $this->setPropiedad('estado', (int)$resultados->get()->estado, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idTipoRubro)){
            throw new AppException('El tipo de rubro no existe',
                (object)array($this->getNombreJson('idTipoRubro')=>'El tipo de rubro no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El tipo de rubro no puede ser borrado, este ha sido utilizado');

        $sql='delete from tiporubro where idtiporubro='.$this->idTipoRubro;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idTipoRubro);
        $modificacion->guardarObjeto($auditoria);
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->descripcion))
            throw new AppException('La descripcion de la resolcion es obligatoria',
                (object)array($this->getNombreJson('descripcion')=>'La descripcion de la resolcion es obligatoria'));

        if(empty($this->idTipoRubro)){
            $sql='insert INTO tiporubro
                (idtiporubro, descripcion, estado)
                values(null, \''.mysql_real_escape_string($this->descripcion).'\','.$this->estado.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idTipoRubro', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('descripcion'));
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idTipoRubro);

            if($this->estaModificada('descripcion')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('descripcion'));
                $cambios[]='descripcion=\''.mysql_real_escape_string($this->descripcion).'\'';
                $this->marcarNoModificada('descripcion');
            }

            if($this->estaModificada('estado')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('estado'));
                $cambios[]='estado='.$this->estado;
                $this->marcarNoModificada('estado');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update tiporubro set $update where idtiporubro=".$this->idTipoRubro;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idTipoRubro;
    }

    public function getIdTipoRubro(){
        return $this->idTipoRubro;
    }

    public function setDescripcion($descripcion){
        $valor=(string)$descripcion;
        if(mb_strlen($valor)>256)
            throw new AppException('La descripcion puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('descripcion')=>'La descripcion puede tener maximo 256 caracteres'));

        return $this->setPropiedad('descripcion', $valor);
    }

    public function getDescripcion(){
        return $this->descripcion;
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

    public static function getTiposRubro($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from tiporubro  where estado=1 order by descripcion';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idtiporubro from tiporubro  where estado=1 order by descripcion';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new TipoRubro($resultados->get()->idtiporubro);

            return $objetos;
        }
    }

    public function haSidoUtilizado() {
        $sql='select idrubro from rubro where idtiporubro='.$this->idTipoRubro.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>
