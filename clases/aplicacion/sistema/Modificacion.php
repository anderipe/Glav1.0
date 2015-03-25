<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Auditoria.php';
require_once 'AccionAuditable.php';
require_once 'Clase.php';

/**
 * Clase que representa una modificacion en el sistema. Esta es una clase de
 * apoyo al subsistema de auditoria.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Modificacion
    extends ClaseBase{

    protected $idModificacion=0;

    protected $idAuditoria=0;

    protected $idAccionAuditable=0;

    protected $idClase=0;

    protected $descripcion='';

    protected $fecha='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idModificacion', 0);
        $this->setPropiedad('idAuditoria', 0);
        $this->setPropiedad('idAccionAuditable', 0);
        $this->setPropiedad('idClase', 0);
        $this->setPropiedad('descripcion', '');
        $this->setPropiedad('fecha', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idmodificacion='.$id))
                throw new AppException('No existe modificacion con identificador '.$id);
    }

    public function setAccionAuditable(AccionAuditable $accionAuditable){
        $valor=$accionAuditable->getIdAccionAuditable();
        if(empty($valor))
            throw new AppException('La accion auditable no existe',
                (object)array($this->getNombreJson('idaccionauditable')=>'La accion auditable no existe'));

        return $this->setPropiedad('idAccionAuditable', $valor);
    }

    public function setClase(Clase $clase){
        $valor=$clase->getIdClase();
        if(empty($valor))
            throw new AppException('La clase no existe',
                (object)array($this->getNombreJson('idclase')=>'La clase no existe'));

        return $this->setPropiedad('idClase', $valor);
    }

    public function setDescripcion($descripcion){
        $valor=(string)$descripcion;
//        if(mb_strlen($valor)>256)
//            throw new AppException('La descripcion de la modificacion debe tener no mas de 256 caracteres '.$valor,
//                (object)array($this->getNombreJson('descripcion')=>'La descripcion de la modificacion debe tener no mas de 256 caracteres '.$valor));

        return $this->setPropiedad('descripcion', $valor);
    }

    public function addDescripcion($descripcion){
        $valor=(string)$descripcion;
        $valor=$this->descripcion.'|'.$valor;
        return $this->setDescripcion($valor);
    }

    public function addDescripcionId($descripcion){
        $valor=(string)$descripcion;
        $valor='id='.$descripcion.': ';
        $valor=$this->descripcion.'|'.$valor;
        return $this->setDescripcion($valor);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idModificacion))
            throw new AppException('La modificacion ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from modificacion where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una modificacion para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idModificacion', (int)$resultados->get()->idmodificacion, true);
        $this->setPropiedad('idAuditoria', (int)$resultados->get()->idauditoria, true);
        $this->setPropiedad('idAccionAuditable', (int)$resultados->get()->idaccionauditable, true);
        $this->setPropiedad('idClase', (int)$resultados->get()->idclase, true);
        $this->setPropiedad('descripcion', (string)$resultados->get()->descripcion, true);
        $this->setPropiedad('fecha', (string)$resultados->get()->fecha, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        $this->idAuditoria=$auditoria->getIdAuditoria();

        if(!empty($this->idModificacion))
            throw new AppException('Los registros de auditoria no son modificables',
                (object)array($this->getNombreJson('idModificacion')=>'Los registros de auditoria no son modificables'));

        if(empty($this->idAuditoria))
            throw new AppException('No se ha proporcionado auditoria',
                (object)array($this->getNombreJson('idAuditoria')=>'No se ha proporcionado auditoria'));

        if(empty($this->idAccionAuditable))
            throw new AppException('No se ha proporcionado accion auditable',
                (object)array($this->getNombreJson('idAccionAuditable')=>'No se ha proporcionado accion auditable'));

        if(empty($this->idClase))
            throw new AppException('No se ha proporcionado clase',
                (object)array($this->getNombreJson('idClase')=>'No se ha proporcionado clase'));

        $sql='insert INTO modificacion
            (idmodificacion, idauditoria, idaccionauditable, idclase, descripcion, fecha)
            values
            (null, \''.$this->idAuditoria.'\', \''.$this->idAccionAuditable.'\', \''.$this->idClase.'\', \''.mysql_real_escape_string($this->descripcion).'\', current_timestamp)';
        $id=$this->conexion->ejecutar($sql);
        $this->idModificacion=$id;

        $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
        $this->conexion->ejecutar($sql);

        $this->modificado=false;
        return $this->idModificacion;
    }

    public function haSidoUtilizado() {
        return false;
    }
}

?>