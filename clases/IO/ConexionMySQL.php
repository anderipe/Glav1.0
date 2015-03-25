<?php
/**
 * @package co.org.lavado
 * @subpackage io
 */

/**
 * Clase que representa una conexion a una base de datos albergada
 * en un servidor de base de datos MySQL.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage io
 */

/**
 * Clase obstracta que representa una conexion a una fuente de datos
 * no especifica.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 */
require_once('Conexion.php');

/**
 * Clase que representa un conjunto de registros obtenidos desde una
 * base de datos residente en un servidor MySQL.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 */
require_once('RecordSetMySQL.php');

/**
 * Clase que representa una conexion a una base de datos albergada
 * en un servidor de base de datos MySQL.
 *
 * @access public
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage io
 */
class ConexionMySQL
    extends Conexion{
    /**
     * Crea una conexion a una fuente de datos MySQL iniciando una
     * transaccion.
     *
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @param  String $baseDeDatos Nombre de la base de datos a la cual se
     * pretende conectar
     * @param  String $usuario Nombre del usuario que tiene permiso de conexion
     * a la base de datos
     * @param  String $contrasena Contraseña del usuario que posee permiso de
     * conexion a la base de datos
     * @param  String $host Direccion IP o nombre del host que alberga el
     * servidor de bases de datos MySQL
     * @param  Integer $puerto [opcional]<p>
     * Numero del puerto por el cual se el servidor de bases de datos atiende
     * las petciones de conexion
     */
    public function __construct($baseDeDatos, $usuario, $contrasena, $host,
            $puerto=3306){
        parent::__construct();
        $this->conexion=mysql_connect($host.":".$puerto, $usuario, $contrasena,
                TRUE);
        mysql_set_charset('utf8', $this->conexion);
        //echo mysql_client_encoding ($this->conexion);
        if($this->conexion===FALSE)
            throw new Exception("No fue posible establecer conexion con la base
                el servidor MYSQL en $host:$puerto $usuario, $contrasena");

        if(mysql_select_db($baseDeDatos, $this->conexion)===FALSE)
            throw new Exception("No fue posible conectar con la base de datos
                $baseDeDatos");

        $this->baseDeDatos=$baseDeDatos;
        $this->usuario=$usuario;
        $this->contrasena=$contrasena;
        $this->host=$host;
        $this->puerto=$puerto;
    }

    /**
     * Destruye el objeto.
     *
     * Si la conexion no se ha cerrado de manera explicita, el destructor
     * cierra la conexion aplicando una descarte a todas las operaciones
     * realizadas durante la ultima transaccion.
     * <p>
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     */
    public function __destruct(){
        $this->cerrar(FALSE);
    }

    /**
     * Cierra la conexion a la fuente de datos permitiendo la manipulacion de
     * la transaccion en curso.
     *
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @param  Boolean $aplicarCambios [opcional]<p>
     * Especifica si los cambios hechos a la base de datos durante la conexion
     * deben ser aplicados aplicando un COMMIT o un ROLLBACK sobre la
     * transaccion abierta durante la conexion.
     */
    public function cerrar(){
        if($this->conexion!=null
                && mysql_stat($this->conexion)!=null){

            if(mysql_close($this->conexion)===FALSE)
                throw new Exception("No fue posible cerrar la conexion");
            $this->conexion=null;
        }
    }

    /**
     * Ejecuta una sentencia sql sobre la conexion a la base de datos
     * @return RecordSetMySQL
     */
    public function consultar($sql){
        $recordSet=null;
        $sql=trim($sql);
        //mysql_query exije que la sentencia SQL no tenga ; al final
        $sql=trim($sql, " ;");
        if(empty($sql))
            throw new Exception("La sentencia de consulta esta vacia");

        $resultado=mysql_query($sql, $this->conexion);
        if($resultado===FALSE)
            throw new Exception("No fue posible consultar: |$sql|");

        $recordSet= new RecordSetMySQL($resultado);
        if(mysql_free_result($resultado)===FALSE)
            throw new Exception("No fue posible liberar el recurso de
                resultados");
        return $recordSet;
    }

    /**
     * Ejecuta una sentencia de accion sobre la base de datos, las sentencias
     * de accion son insert, delete y update
     * @param string $sql
     * @return int Retorna el ultimo auto id generado por el servidor de base
     * de datos
     * @throws Exception
     */
    public function ejecutar($sql){
        $sql=trim($sql);
        if(empty($sql))
            throw new Exception("La sentencia de ejecucion esta vacia");

        $resultado=mysql_query ($sql, $this->conexion);
        if($resultado===FALSE)
            throw new Exception("No fue posible ejecutar $sql");

        return $this->getUltimoId();
    }

    /**
     * Obtiene el ultimo auto id generado por el servidor de bases de datos
     * @return int
     */
    public function getUltimoId(){
        return (int)mysql_insert_id($this->conexion);
    }

    /**
     *
     * @param string $nombreArchivo
     */
    public function crearBackUp($nombreArchivo){
        passthru("/usr/bin/mysqldump --opt --host=".$this->host." --user=".$this->usuario." --password=".$this->contrasena." ".$this->baseDeDatos." > $nombreArchivo");
    }
}
?>