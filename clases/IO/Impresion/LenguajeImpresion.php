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
 * Clase que representa un lenguaje de impresion, como el IBP/POS o el PCL
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
class LenguajeImpresion
    extends ClaseBase {

    protected $idLenguajeImpresion=0;

    protected $nombre='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idLenguajeImpresion', 0);
        $this->setPropiedad('nombre', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idlenguajeimpresion='.$id))
                throw new AppException('No existe lenguajede de impresion con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idLenguajeImpresion))
            throw new AppException('El lenguajede impresion ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from lenguajeimpresion where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un lenguaje de impresion para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idLenguajeImpresion', (int)$resultados->get()->idlenguajeimpresion, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        throw new AppException('Metodo no implementado', null);
    }

    public function getIdLenguajeImpresion(){
        return $this->idLenguajeImpresion;
    }

    public static function getLenguajeImpresiones($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from lenguajeimpresion order by nombre';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idlenguajeimpresion from lenguajeimpresion order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new LenguajeImpresion($resultados->get()->idlenguajeimpresion);

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
