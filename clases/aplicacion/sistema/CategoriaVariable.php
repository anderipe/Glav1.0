<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';

/**
 * Clase que define la categoria de una variable de entorno
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class CategoriaVariable
    extends ClaseBase{

    protected $idCategoriaVariable=0;

    protected $nombre='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idCategoriaVariable', 0);
        $this->setPropiedad('nombre', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idcategoriavariable='.$id))
                throw new AppException('No existe categori de avariable con identificador '.$id);
    }

    public function getIdCategoriaVariable(){
        return $this->idCategoriaVariable;
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idCategoriaVariable))
            throw new AppException('La categoria de variable ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from categoriavariable where '.$string);

        if($resultados->getCantidad()==0){
            return false;
        }

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una categoria de variable para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idCategoriaVariable', (int)$resultados->get()->idcategoriavariable, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
    }

    public function haSidoUtilizado() {
        $sql='select idvariable from `variable` where idcategoriavariable='.$this->idCategoriaVariable.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>