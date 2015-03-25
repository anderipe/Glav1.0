<?php
/**
 * @package co.org.lavado
 * @subpackage io
 */

/**
 * Clase obstracta que representa una conexion a una fuente de datos
 * de origen no especifica.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage io
 */

/**
 * Clase obstracta que representa un conjunto de registros obtenidos desde una
 * fuente de datos.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 */
require_once('RecordSet.php');

/**
 * Clase obstracta que representa una conexion a una fuente de datos
 * no especifica.
 *
 * @abstract
 * @access public
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage io
 */

abstract class Conexion{
    /**
     * Recurso de conexion nativo del manejador de base de datos.
     *
     * @access protected
     * @var resource
     */
    protected $conexion = null;
    /**
     * Ultimo conjunto de registros accedido por la conexion a la fuente de
     * datos.
     *
     * @access protected
     * @var RecordSet
     */
    protected $ultimosRegistros = null;

    /**
     * Nombre textual de la base de datos
     * @var string
     */
    protected $baseDeDatos=null;

    /**
     *
     * @var string
     */
    protected $usuario=null;

    /**
     *
     * @var string
     */
    protected $contrasena=null;

    /**
     *
     * @var string
     */
    protected $host=null;

    /**
     *
     * @var string
     */
    protected $puerto=null;

    /**
     * Contructor de la clase.
     * Inicializa los atributos de la clase.
     *
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     */
    public function __construct(){
        $this->conexion=null;
        $this->ultimosRegistros=null;
        $this->baseDeDatos="No Definida";
    }

    /**
     * Devuelve el nombre de la base de datos
     * @return string
     */
    public function getBaseDeDatos(){
        return $this->baseDeDatos;
    }

    /**
     * Obtiene el ultimo conjunto de datos accedido por la conexion a la fuente
     * datos.
     *
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @return RecordSet
     */
    public function getUltimosRegistros(){
        return $this->ultimosRegistros;
    }

    /**
     * Ejecuta una consultaSQL sobre la fuente de datos.
     * La sentencia ejecutada debe correponder a un SELECT segun el standar
     * SQL. Para ejecucion de inserciones, actualizaciones y eliminaciones de
     * registros se debe usar el metodo Ejecutar de la clase.
     *
     * @abstract
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @param  string $sql Sentencia SQL que define la consulta a la fuente de
     * datos.
     * <p>
     * @return RecordSet
     */
    public abstract function consultar($sql);

    /**
     * Ejecuta una insercion, actualizacion o borrado de registros sobre la
     * de datos.
     *
     * @abstract
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @param  string $sql Sentencia SQL que define la ejecucion. Esta debe
     * corresponder un UPDATE, INSERT o DELETE segun el standar SQL.
     *
     * @return int Numero de registros afectados por la sentencia SQL
     */
    public abstract function ejecutar($sql);

    /**
     * Obtiene el recurso de conexion nativo de la base de datos
     * @return resource
     */
    public function getConexion(){
        return $this->conexion;
    }

}
?>