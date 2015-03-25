<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

require_once '/media/www/lavado/clases/Framework.php';
$miFramework=new FrameWork();
require_once 'Auditoria.php';

/**
 * Interfaz base de la cual derivan todos los controladores de interfaces del
 * sistema. Esta interfaz base agrupa propiedades y funcionalidades comunes
 * a todas las interfaces controladoras.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
class InterfazBase {
    /**
     * Referencia a un framework global de la aplicacion
     * @var FrameWork
     */
    protected $framework;

    /**
     * Conexion a la base de datos del sistema
     * @var ConexionMySQL
     */
    protected $conexion;

    /**
     * Array de asociativo de argumentos recibidos en la interfaz
     * @var ArrayObject
     */
    protected $args;

    /**
     * Objeto que contiene los datos de retorno de la interfaz php hacia el
     * cliente
     * @var stdClass
     */
    protected $retorno;

    /**
     * Vale 1 si la interfaz debe imprimir en matriz de punto o pcl
     * @var int
     */
    protected $imprimir;

    /**
     * Vale 1 si la interfaz debe crear un documento de excel
     * @var int
     */
    protected $excel;

    /**
     * Vale 1 si la interfaz debe crear un documento pdf
     * @var int
     */
    protected $pdf;

    /**
     * Parametro que define si la interfaz es un informe que debe hacer una
     * consulta limitada. Este es el numero de registros a devolver
     * @var int
     */
    protected $limit;

    /**
     * Parametro que define si la interfaz es un informe que debe hacer una
     * consulta limitada. Este es el offset de los registros a ser consultados
     * @var int
     */
    protected $offSet;

    /**
     * Define un punto de entrada o proceso a ejecutar en la interfaz. La orden
     * es traer datos
     * @var int
     */
    public static $TRAER_DATOS = 0;

    /**
     * Define un punto de entrada o proceso a ejecutar en la interfaz. La orden
     * es guardar datos
     * @var int
     */
    public static $GUARDAR_DATOS = 1;

    /**
     * Define un punto de entrada o proceso a ejecutar en la interfaz. La orden
     * es borrar datos
     * @var int
     */
    public static $BORRAR_DATOS = 2;

    /**
     * Define un punto de entrada o proceso a ejecutar en la interfaz. La orden
     * es buscar datos
     * @var int
     */
    public static $BUSCAR_DATOS = 3 ;

    /**
     * Define un punto de entrada o proceso a ejecutar en la interfaz. La orden
     * es in activar
     * @var int
     */
    public static $INACTIVAR = 4 ;

    /**
     * Define un punto de entrada o proceso a ejecutar en la interfaz. La orden
     * es ir al primer registro
     * @var int
     */
    public static $IR_PRIMERO =5  ;

    /**
     * Define un punto de entrada o proceso a ejecutar en la interfaz. La orden
     * es ir al registro anterior
     * @var int
     */
    public static $IR_ANTERIOR =6  ;

    /**
     * Define un punto de entrada o proceso a ejecutar en la interfaz. La orden
     * es ir al registro siguiente
     * @var int
     */
    public static $IR_SIGUIENTE =7  ;

    /**
     * Define un punto de entrada o proceso a ejecutar en la interfaz. La orden
     * es ir al ultimo registro
     * @var int
     */
    public static $IR_ULTIMO =8  ;

    /**
     * Define un punto de entrada o proceso a ejecutar en la interfaz. La orden
     * es buscar un dato por identificacion
     * @var int
     */
    public static $BUSCAR_POR_IDENTIFICACION =9;

    /**
     * Define un punto de entrada o proceso a ejecutar en la interfaz. La orden
     * es crear un registro nuevo
     * @var int
     */
    public static $NUEVO=10;

    /**
     * COntructor de la clase, crea el manejador de la interfaz.
     * @param ArrayObject $args Generalmente agrupa los parametros recibidos por
     * GET y por POST
     */
    public function __construct(ArrayObject $args=NULL){
        $this->args=$args;
        $this->limit=isset($this->args['limit'])?$this->args['limit']:0;
        $this->offSet=isset($this->args['start'])?$this->args['start']:0;
        $this->imprimir=isset($this->args['imprimir'])?(int)$this->args['imprimir']:0;
        $this->excel=isset($this->args['excel'])?(int)$this->args['excel']:0;
        $this->pdf=isset($this->args['pdf'])?(int)$this->args['pdf']:0;
        //$this->framework=new FrameWork('siadno');
        $this->conexion=FrameWork::getConexion();
        $this->retorno= new stdClass();
        $this->retorno->success=true;
        $this->retorno->msg=get_class($this). " ha terminado con exito";
    }

    /**
     * Obtiene el parametro get o post que se pide como un dato de tipo entero
     * @param string $nombreParametro
     * @return int Devuelve 0 si el parametro no existe
     */
    public function getInt($nombreParametro){
        return isset($this->args[$nombreParametro])?(int)$this->args[$nombreParametro]:0;
    }

    /**
     * Obtiene el parametro get o post que se pide como un dato de tipo decimal
     * @param string $nombreParametro
     * @return double Devuelve 0.0 si el parametro no existe
     */
    public function getDouble($nombreParametro){
        return isset($this->args[$nombreParametro])?(double)$this->args[$nombreParametro]:0.0;
    }

    /**
     * Obtiene el parametro get o post que se pide como un dato de tipo boolean.
     * Se entiende por true si el valor del paranetro es 1, 'true' o 't',
     * cualquier otro valor es interpretado como false
     * @param string $nombreParametro
     * @return boolean Devuelve false si el parametro no existe
     */
    public function getBool($nombreParametro){
        if(!isset($this->args[$nombreParametro]))
            return false;

        $param=mb_strtolower(Auxiliar::mb_trim($this->args[$nombreParametro]));
        if($param==='true' || $param==='t' || (int)$param==1)
            return true;
        else
            return false;
    }

    /**
     * Obtiene el parametro get o post que se pide como un dato de tipo string
     * @param string $nombreParametro
     * @return string Devuelve '' si el parametro no existe
     */
    public function getString($nombreParametro){
        return isset($this->args[$nombreParametro])?Auxiliar::mb_trim($this->args[$nombreParametro]):'';
    }
}

?>
