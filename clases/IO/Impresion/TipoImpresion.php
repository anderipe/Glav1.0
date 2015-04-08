<?php
/**
 * @package co.org.lavado.io
 * @subpackage print
 */
require_once 'ClaseBase.php';

/**
 * Clase que define el tipo de impresion a ejecutar
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
class TipoImpresion
    extends ClaseBase {

    const FISICA=1;
    const PDF=2;
    const EXCEL=3;

    protected $idTipoImpresion=0;

    protected $nombre='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idTipoImpresion', 0);
        $this->setPropiedad('nombre', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idtipoimpresion='.$id))
                throw new AppException('No existe tipo de impresion con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idTipoImpresion))
            throw new AppException('El tipo de impresion ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from tipoimpresion where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un tipo de impresion para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idTipoImpresion', (int)$resultados->get()->idtipoimpresion, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        throw new AppException('Metodo no implementado', null);
    }

    public function getIdTipoImpresion(){
        return $this->idTipoImpresion;
    }

    public static function getTipoImpresiones($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from tipoimpresion order by nombre';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idtipoimpresion from tipoimpresion order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new TipoImpresion($resultados->get()->idtipoimpresion);

            return $objetos;
        }
    }

    public function getNombre(){
        return $this->nombre;
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>
