<?php
/**
 * @package co.org.lavado.io
 * @subpackage print
 */

require_once 'ClaseBase.php';

/**
 * Clase que representa un tipo de documento que puede ser impreso, como un
 * informe, una factura, un reporte, un listado, etc
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
class DocumentoImprimible
    extends ClaseBase{

    protected $idDocumentoImprimible=0;

    protected $nombre='';

    protected $descripcion='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idDocumentoImprimible', 0);
        $this->setPropiedad('nombre', '');
        $this->setPropiedad('descripcion', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('iddocumentoimprimible='.$id))
                throw new AppException('No existe documento imprimible con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idDocumentoImprimible))
            throw new AppException('El documento imprimible ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from documentoimprimible where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un documento imprimible para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idDocumentoImprimible', (int)$resultados->get()->iddocumentoimprimible, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->setPropiedad('descripcion', (string)$resultados->get()->descripcion, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function cargarPorNombre($nombre){
        $where='nombre=\''.mysql_real_escape_string($nombre).'\'';
        return $this->cargarObjeto($where);
    }

    public static function crearPorNombre($nombre){
        $documentoImprimible=new DocumentoImprimible();
        $documentoImprimible->cargarPorNombre($nombre);
        return $documentoImprimible;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        throw new AppException('Metodo no implementado', null);
    }

    public function getIdDocumentoImprimible(){
        return $this->idDocumentoImprimible;
    }

    public function getDescripcion(){
        return $this->descripcion;
    }

    public function getNombre(){
        return $this->nombre;
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>
