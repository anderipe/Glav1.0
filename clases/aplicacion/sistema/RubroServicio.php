<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Servicio.php';
require_once 'Rubro.php';

/**
 * Clase que representa un rubro agrupado en un servicio de servicios
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class RubroServicio
    extends ClaseBase{

    protected $idRubroServicio=0;

    protected $idServicio=0;

    protected $idRubro=0;

    protected $cantidad=0;

    protected $valor=0;

    protected $iva=0;

    protected $total=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idRubroServicio', 0);
        $this->setPropiedad('idServicio', 0);
        $this->setPropiedad('idRubro', 0);
        $this->setPropiedad('cantidad', 0);
        $this->setPropiedad('valor', 0);
        $this->setPropiedad('iva', 0);
        $this->setPropiedad('total', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idrubroservicio='.$id))
                throw new AppException('No existe rubro con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idRubroServicio))
            throw new AppException('El rubroservicio ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from rubroservicio where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una rubroservicio para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idRubroServicio', (int)$resultados->get()->idrubroservicio, true);
        $this->setPropiedad('idRubro', (int)$resultados->get()->idrubro, true);
        $this->setPropiedad('idServicio', (int)$resultados->get()->idservicio, true);
        $this->setPropiedad('cantidad', (int)$resultados->get()->cantidad, true);
        $this->setPropiedad('valor', (float)$resultados->get()->valor, true);
        $this->setPropiedad('iva', (float)$resultados->get()->iva, true);
        $this->setPropiedad('total', (float)$resultados->get()->total, true);

        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function cargarRubroServicio(Rubro $rubro, Servicio $servicio){
        $where='idrubro='.$rubro->getIdRubro().' and idservicio='.$servicio->getIdServicio();
        return $this->cargarObjeto($where);
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idRubroServicio)){
            throw new AppException('El rubroservicio no existe',
                (object)array($this->getNombreJson('idrubroservicio')=>'El rubroservicio no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El rubroservicio no puede ser borrada, esta ha sido utilizada');

        $sql='delete from rubroservicio where idrubroservicio='.$this->idRubroServicio;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idRubroServicio);
        $modificacion->guardarObjeto($auditoria);
    }

    public function guardarObjeto(Auditoria $auditoria=null){
        if(empty($this->idServicio))
            throw new AppException('El servicio es obligatorio',
                (object)array($this->getNombreJson('idservicio')=>'El servicio es obligatorio'));

        if(empty($this->idRubro))
            throw new AppException('El rubro es obligatorio',
                (object)array($this->getNombreJson('idrubro')=>'El rubro es obligatorio'));

        if(empty($this->cantidad))
            throw new AppException('La cantidad es obligatoria',
                (object)array($this->getNombreJson('cantidad')=>'La cantidad es obligatoria'));

        if(empty($this->idRubroServicio)){
            $sql='insert INTO rubroservicio
                (idrubroservicio, idservicio, idrubro, cantidad, valor, iva, total)
                values(null, '.$this->idServicio.', '.$this->idRubro.', '.$this->cantidad.', '.$this->valor.', '.$this->iva.', '.$this->total.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idRubroServicio', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'   where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idRubroServicio);

            if($this->estaModificada('idServicio')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idServicio'));
                $cambios[]='idservicio='.$this->idServicio;
                $this->marcarNoModificada('idServicio');
            }

            if($this->estaModificada('idRubro')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idRubro'));
                $cambios[]='idrubro='.$this->idRubroidRubro;
                $this->marcarNoModificada('idTipoAutomotor');
            }

            if($this->estaModificada('cantidad')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('cantidad'));
                $cambios[]='cantidad='.$this->cantidad;
                $this->marcarNoModificada('cantidad');
            }

            if($this->estaModificada('valor')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('valor'));
                $cambios[]='valor='.$this->valor;
                $this->marcarNoModificada('valor');
            }

            if($this->estaModificada('iva')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('iva'));
                $cambios[]='iva='.$this->iva;
                $this->marcarNoModificada('iva');
            }
            if($this->estaModificada('total')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('total'));
                $cambios[]='total='.$this->total;
                $this->marcarNoModificada('total');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update rubroservicio set $update where idrubroservicio=".$this->idRubroServicio;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idRubroServicio;
    }

    public function getIdRubroServicio(){
        return $this->idRubroServicio;
    }

    public function getIdServicio(){
        return $this->idServicio;
    }

    public function getServicio(){
        return new Servicio($this->idServicio);
    }

    public function setServicio(Servicio $servicio){
        $valor=$servicio->getIdServicio();
        if(empty($valor))
            throw new AppException('El servicio no existe',
                (object)array($this->getNombreJson('idservicio')=>'El servicio no existe'));

        return $this->setPropiedad('idServicio', $valor);
    }

    public function getCatidad(){
        return $this->cantidad;
    }

    public function setCantidad($cantidad){
        $valor=(int)$cantidad;
        if($valor<0 || $valor>30000)
            throw new AppException('La cantidad no es valida',
                (object)array($this->getNombreJson('cantidad')=>'La cantidad no es valida'));

        return $this->setPropiedad('cantidad', $valor);
    }

    public function getValor(){
        return $this->valor;
    }

    public function setValor($valor){
        $valor=(float)$valor;
        if($valor<0 || $valor>50000000)
            throw new AppException('El valor no es valido',
                (object)array($this->getNombreJson('valor')=>'El valor no es valido'));

        return $this->setPropiedad('valor', $valor);
    }

    public function getIva(){
        return $this->iva;
    }

    public function setIva($iva){
        $valor=(float)$iva;
        if($valor<0 || $valor>50000000)
            throw new AppException('El Iva no es valido',
                (object)array($this->getNombreJson('iva')=>'El Iva no es valido'));

        return $this->setPropiedad('iva', $valor);
    }

    public function getTotal(){
        return $this->total;
    }

    public function setTotal($total){
        $valor=(float)$total;
        if($valor<0 || $valor>50000000)
            throw new AppException('El total no es valido',
                (object)array($this->getNombreJson('total')=>'El total no es valido'));

        return $this->setPropiedad('total', $valor);
    }

    public function getIdRubro(){
        return $this->idRubro;
    }

    public function getRubro(){
        return new Rubro($this->idRubro);
    }

    public function setRubro(Rubro $rubro){
        $valor=$rubro->getIdRubro();
        if(empty($valor))
            throw new AppException('El rubro no existe',
                (object)array($this->getNombreJson('idrubro')=>'El rubro no existe'));

        return $this->setPropiedad('idRubro', $valor);
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>