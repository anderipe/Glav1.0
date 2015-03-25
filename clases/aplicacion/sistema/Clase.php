<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';

/**
 * Clase que define una clase de php usada en el sistema, se tiene por razones
 * de administracion y mantenimiento pues poseemos una tabla en la base de datos
 * que relaciona todas las clases usadas en el sistema y la tabla en la db
 * que mapea
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Clase
    extends ClaseBase{

    protected $idClase=0;

    protected $nombre='';

    protected $tabla='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idClase', 0);
        $this->setPropiedad('nombre', '');
        $this->setPropiedad('tabla', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idclase='.$id))
                throw new AppException('No existe clase con identificador '.$id);
    }

    public function getIdClase(){
        return $this->idClase;
    }

    public function getNombre(){
        return $this->nombre;
    }

    public function getTabla(){
        return $this->tabla;
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idClase))
            throw new AppException('La clase ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from clase where '.$string);

        if($resultados->getCantidad()==0){
            return false;
        }

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una clase para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idClase', (int)$resultados->get()->idclase, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->setPropiedad('tabla', (string)$resultados->get()->tabla, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;
        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {

    }

    public function cargarPorNombre($nombre){
        if(!$this->cargarObjeto('nombre=\''.mysql_real_escape_string($nombre).'\''))
                throw new AppException('No existe clase con nombre '.$nombre);
    }

    public function cargarPorTabla($tabla){
        if(!$this->cargarObjeto('tabla=\''.mysql_real_escape_string($tabla).'\''))
                throw new AppException('No existe clase con tabla '.$tabla);
    }

    public static function crearPorNombre($nombre){
        $clase=new Clase();
        $clase->cargarPorNombre($nombre);
        return $clase;
    }

    public static function crearPorTabla($tabla){
        $clase=new Clase();
        $clase->cargarPorTabla($tabla);
        return $clase;
    }

    public static function getClases($nombre, $formato){
        $formato=(int)$formato;
        $nombre=  mb_strtolower(trim($nombre));

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select clase.* from clase ';
            if(!empty($nombre))
                $sql.=' where lower(nombre) like \'%'.mysql_real_escape_string ($nombre).'%\'';
            $sql.=' order by clase.nombre';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select clase.idclase from clase ';
            if(!empty($nombre))
                $sql.=' where lower(nombre) like \'%'.mysql_real_escape_string ($nombre).'%\'';
            $sql.=' order by clase.nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Clase($resultados->get()->idclase);

            return $objetos;
        }
    }

    public function haSidoUtilizado() {
        return false;
    }
}

?>