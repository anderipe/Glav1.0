<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'TipoRubro.php';
require_once 'TipoAutomotor.php';

/**
 * Clase que representa un servicios o producto que puede ser cobrado por la
 * empresa.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Rubro
    extends ClaseBase{

    protected $idRubro=0;

    protected $idTipoRubro=0;

    protected $idTipoAutomotor=0;

    protected $descripcion='';

    protected $valorUnitario=0.0;

    protected $porcentajeIva=0.0;

    protected $estado=0;

    protected $visible=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idRubro', 0);
        $this->setPropiedad('idTipoRubro', 0);
        $this->setPropiedad('idTipoAutomotor', 0);
        $this->setPropiedad('descripcion', '');
        $this->setPropiedad('porcentajeIva', 0.0);
        $this->setPropiedad('valorUnitario', 0.0);
        $this->setPropiedad('estado', 0);
        $this->setPropiedad('visible', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idrubro='.$id))
                throw new AppException('No existe rubro con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idRubro))
            throw new AppException('El servicio ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from rubro where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una rubro para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idRubro', (int)$resultados->get()->idrubro, true);
        $this->setPropiedad('idTipoRubro', (int)$resultados->get()->idtiporubro, true);
        $this->setPropiedad('idTipoAutomotor', (int)$resultados->get()->idtipoautomotor, true);
        $this->setPropiedad('descripcion', (string)$resultados->get()->descripcion, true);
        $this->setPropiedad('porcentajeIva', (float)$resultados->get()->porcentajeiva, true);
        $this->setPropiedad('estado', (int)$resultados->get()->estado, true);
        $this->setPropiedad('visible', (int)$resultados->get()->visible, true);
        $this->setPropiedad('valorUnitario', (float)$resultados->get()->valorunitario, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idRubro)){
            throw new AppException('El servicio no existe',
                (object)array($this->getNombreJson('idrubro')=>'El servicio no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El servicio no puede ser borrado, este ha sido utilizado');

        $sql='delete from rubro where idrubro='.$this->idRubro;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idRubro);
        $modificacion->guardarObjeto($auditoria);
    }

    public function guardarObjeto(Auditoria $auditoria=null){
        if(empty($this->idTipoRubro))
            throw new AppException('El tipo de rubro es obligatorio',
                (object)array($this->getNombreJson('idtiporubro')=>'El tipo de rubro es obligatorio'));

        if(empty($this->descripcion))
            throw new AppException('La descripcion obligatoria',
                (object)array($this->getNombreJson('descripcion')=>'La descripcion obligatoria'));

        if(empty($this->valorUnitario))
            throw new AppException('El valor unitario es obligatorio',
                (object)array($this->getNombreJson('valorunitario')=>'El valor unitario es obligatorio'));

        if(empty($this->idRubro)){
            $rubro=new Rubro();

            $sql='insert INTO rubro
                (idrubro, idtiporubro, descripcion, porcentajeiva, estado, valorunitario, visible, idtipoautomotor)
                values(null, '.$this->idTipoRubro.', \''.mysql_real_escape_string($this->descripcion).'\', '.$this->porcentajeIva.', '.$this->estado.', '.$this->valorUnitario.', '.$this->visible.', '.$this->idTipoAutomotor.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idRubro', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'   where '.$this->campoId.'='.$id;
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
            $modificacion->addDescripcionId($this->idRubro);

            if($this->estaModificada('idTipoRubro')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idTipoRubro'));
                $cambios[]='idtiporubro='.$this->idTipoRubro;
                $this->marcarNoModificada('idTipoRubro');
            }

            if($this->estaModificada('idTipoAutomotor')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idTipoAutomotor'));
                $cambios[]='idtipoautomotor='.$this->idTipoAutomotor;
                $this->marcarNoModificada('idTipoAutomotor');
            }

            if($this->estaModificada('descripcion')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('descripcion'));
                $cambios[]='descripcion=\''.mysql_real_escape_string($this->descripcion).'\'';
                $this->marcarNoModificada('descripcion');
            }

            if($this->estaModificada('porcentajeIva')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('porcentajeIva'));
                $cambios[]='porcentajeiva='.$this->porcentajeIva.'';
                $this->marcarNoModificada('porcentajeIva');
            }

            if($this->estaModificada('estado')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('estado'));
                $cambios[]='estado='.$this->estado.'';
                $this->marcarNoModificada('estado');
            }

            if($this->estaModificada('visible')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('visible'));
                $cambios[]='visible='.$this->visible.'';
                $this->marcarNoModificada('visible');
            }

            if($this->estaModificada('valorUnitario')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('valorUnitario'));
                $cambios[]='valorunitario='.$this->valorUnitario.'';
                $this->marcarNoModificada('valorUnitario');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update rubro set $update where idrubro=".$this->idRubro;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idRubro;
    }

    public function getIdRubro(){
        return $this->idRubro;
    }

    public function getIdTipoRubro(){
        return $this->idTipoRubro;
    }

    public function getTipoRubro(){
        return new TipoRubro($this->idTipoRubro);
    }

    public function setTipoRubro(TipoRubro $tipoRubro){
        $valor=$tipoRubro->getIdTipoRubro();
        if(empty($valor))
            throw new AppException('El tipo de descripcion no existe',
                (object)array($this->getNombreJson('idtiporubro')=>'El tipo de descripcion no existe'));

        return $this->setPropiedad('idTipoRubro', $valor);
    }

    public function getTipoAutomotor(){
        return new TipoAutomotor($this->idTipoAutomotor);
    }

    public function getIdTipoAutomotor(){
        return $this->idTipoAutomotor;
    }

    public function setTipoAutomotor(TipoAutomotor $tipoAutomotor){
        $valor=$tipoAutomotor->getIdTipoAutomotor();
        if(empty($valor))
            throw new AppException('El tipo de automotor no existe',
                (object)array($this->getNombreJson('idtipoautomotor')=>'El tipo de automotor no existe'));

        return $this->setPropiedad('idTipoAutomotor', $valor);
    }

    public function getDescripcion(){
        return $this->descripcion;
    }

    public function setDescripcion($descripcion){
        $valor=(string)$descripcion;
        if(mb_strlen($valor)>256)
            throw new AppException('La descripcion puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('descripcion')=>'La descripcion puede tener maximo 256 caracteres'));

        return $this->setPropiedad('descripcion', $valor);
    }

    public function getPorcentajeIva(){
        return $this->porcentajeIva;
    }

    public function setPorcentajeIva($porcentajeIva){
        $valor=(float)$porcentajeIva;
        if($valor<0)
            throw new AppException('El porcentaje de iva no puede ser menor a cero',
                (object)array($this->getNombreJson('porcentajeIva')=>'El porcentaje de iva no puede ser menor a cero'));

        return $this->setPropiedad('porcentajeIva', $valor);
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

    public function getVisible(){
        return $this->visible;
    }

    public function setVisible($visible){
        $valor=(int)$visible;
        if($valor<0 ||$valor>1)
            throw new AppException('Valor no valido para el visible',
                (object)array($this->getNombreJson('visible')=>'Valor no valido para el visible'));

        return $this->setPropiedad('visible', $valor);
    }

    public function getValorUnitario(){
        return $this->valorUnitario;
    }

    public function setValorUnitario($valorUnitario){
        $valor=(float)$valorUnitario;
        if($valor<=0)
            throw new AppException('El valor unitario debe ser mayor que cero',
                (object)array($this->getNombreJson('valorUnitario')=>'El valor unitario debe ser mayor que cero'));

        return $this->setPropiedad('valorUnitario', $valor);
    }

    public static function getRubros(TipoRubro $tipoRubro, $formato){
        $formato=(int)$formato;

        if($tipoRubro->getIdTipoRubro()==0)
            throw new Exception('El tipo de rubro no existe');

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from rubro where idtiporubro='.$tipoRubro->getIdTipoRubro().' order by descripcion';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idrubro from rubro where idtiporubro='.$tipoRubro->getIdTipoRubro().' order by descripcion';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Rubro($resultados->get()->idrubro);

            return $objetos;
        }
    }

    public function haSidoUtilizado() {
        $sql="select * from rubroservicio where idrubro=".$this->idRubro.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        $sql="select * from rubrocombo where idrubro=".$this->idRubro.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>