<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';

/**
 * Clase que representa aun accion auditable en el sistema. Para ver cuales son
 * las acciones auditables consulte las porpiedades y constantes de esta clase
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class AccionAuditable
    extends ClaseBase{

    const Insercion=1;
    const Modificacion=2;
    const Eliminacion=3;
    const Expedicion=4;
    const IngresoAlSistema=5;
    const CierreDelSistema=6;
    const IMPRESION=7;

    protected $idAccionAuditable=0;

    protected $nombre='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idAccionAuditable', 0);
        $this->setPropiedad('nombre', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idaccionauditable='.$id))
                throw new AppException('No existe accion auditable con identificador '.$id);
    }

    public function getIdAccionAuditable(){
        return $this->idAccionAuditable;
    }

    public function getNombre(){
        return $this->nombre;
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idAccionAuditable))
            throw new AppException('La accio auditable ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from accionauditable where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una accion auditable para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idAccionAuditable', (int)$resultados->get()->idaccionauditable, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {

    }

    public static function getAccionesAuditables($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from
                accionauditable
                order by nombre ';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idaccionauditable from
                accionauditable
                order by nombre ';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new AccionAuditable($resultados->get()->idaccionauditable);

            return $objetos;
        }
    }

    public function haSidoUtilizado() {
        $sql='select idauditoria from auditoria where idaccionauditable='.$this->idAccionAuditable.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        $sql='select idmodificacion from modificacion where idaccionauditable='.$this->idAccionAuditable.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>