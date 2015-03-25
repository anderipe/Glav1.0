<?php
/**
 * @package co.org.lavado.io
 * @subpackage print
 */

require_once 'ClaseBase.php';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Clase que define el tipo de impresora usada
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
class TipoImpresora
    extends ClaseBase {

    const GENERAL=1;
    const POS=2;
    const BARCODE=3;

    protected $idTipoImpresora=0;

    protected $nombre='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idTipoImpresora', 0);
        $this->setPropiedad('nombre', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idtipoimpresora='.$id))
                throw new AppException('No existe tipo de impresora con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idTipoImpresora))
            throw new AppException('El tipo de impresora ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from tipoimpresora where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un tipo de impresora para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idTipoImpresora', (int)$resultados->get()->idtipoimpresora, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        throw new AppException('Metodo no implementado', null);
    }

    public function getIdTipoImpresora(){
        return $this->idTipoImpresora;
    }

    public static function getTipoImpresoras($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from tipoimpresora order by nombre';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idtipoimpresora from tipoimpresora order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new TipoImpresora($resultados->get()->idtipoimpresora);

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
