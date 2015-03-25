<?php
/**
 * @package co.org.lavado.io
 * @subpackage print
 */
require_once 'ClaseBase.php';

/**
 * Clase que representa el estado en que se encuentra un trabajo de impresion
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
class EstadoImpresion
    extends ClaseBase{

    const SIN_IMPRIMIR=1;
    const IMPRIMIENDO=2;
    const IMPRESO=3;

    protected $idEstadoImpresion=0;

    protected $nombre='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idEstadoImpresion', 0);
        $this->setPropiedad('nombre', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idestadoimpresion='.$id))
                throw new AppException('No existe estado impresion con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idEstadoImpresion))
            throw new AppException('El estado impresion ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from estadoimpresion where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un estado impresion para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idEstadoImpresion', (int)$resultados->get()->idestadoimpresion, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        throw new AppException('Metodo no implementado', null);
    }

    public function getIdEstadoImpresion(){
        return $this->idEstadoImpresion;
    }

    public function getNombre(){
        return $this->nombre;
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>
