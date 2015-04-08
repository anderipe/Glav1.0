<?php
/**
 * @package co.org.lavado
 * @subpackage io
 */
/**
 * Clase que representa un conjunto de registros obtenidos desde una
 * base de datos residente en un servidor MySQL.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2010/09/15
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
 * Clase que representa un conjunto de registros obtenidos desde una
 * base de datos residente en un servidor MySQL.
 * <p>
 * @access public
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2010/09/15
 * @version 1.0
 * @package co.org.lavado
 * @subpackage io
 */
class RecordSetMySQL
    extends RecordSet{
    /**
     * Constante que define el tipo de recurso que la clase puede manipular.
     *
     * @access public
     * @var string
     */
    const TIPO_RECURSO = "mysql result";

    /**
     * Constructor de la clase.
     *
     * Crea un record set a aprtir de un recurso de resultados devuelto por una
     * conexion a una base de datos MySQL.
     *
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @param  resource $recurso Recurso devuelto por una consulta a base de
     * datos MySQL
     *
     */
    public function __construct($recurso){
        parent::__construct();

        if(!is_resource($recurso))
            throw new Exception("El parametro recibido no es un recurso");

        if(get_resource_type($recurso)!=RecordSetMySQL::TIPO_RECURSO)
            throw new Exception("El recurso recibido no es de
                tipo ".RecordSetMySQL::TIPO_RECURSO);

        $this->cantidadCampos=mysql_num_fields($recurso);
        for($i=0; $i<$this->cantidadCampos; $i++){
            $this->campos[]=mysql_fetch_field($recurso);
            //=$descripcion;
        }

        $this->cantidad=mysql_num_rows($recurso);
        if($this->cantidad>0){
            $this->numeroCampos=mysql_num_fields ($recurso);
            if($this->numeroCampos===FALSE)
                throw new Exception("No fue posible contar el numero de compos
                    del record set");
        }
        $i=0;
        for($i=0; $i<$this->cantidad; $i++){
            $this->registros[$i]=mysql_fetch_object($recurso);

            for($k=0; $k<$this->cantidadCampos; $k++){
                if($this->campos[$k]->type=='int'){
                    $nombreCampo=$this->campos[$k]->name;
                    $this->registros[$i]->$nombreCampo=(int)$this->registros[$i]->$nombreCampo;
                    continue;
                }

                if($this->campos[$k]->type=='float'){
                    $nombreCampo=$this->campos[$k]->name;
                    $this->registros[$i]->$nombreCampo=(float)$this->registros[$i]->$nombreCampo;
                    continue;
                }

                if($this->campos[$k]->type=='double'){
                    $nombreCampo=$this->campos[$k]->name;
                    $this->registros[$i]->$nombreCampo=(double)$this->registros[$i]->$nombreCampo;
                    continue;
                }

                if($this->campos[$k]->type=='boolean'){
                    $nombreCampo=$this->campos[$k]->name;
                    $this->registros[$i]->$nombreCampo=(boolean)$this->registros[$i]->$nombreCampo;
                    continue;
                }

                if($this->campos[$k]->type=='bool'){
                    $nombreCampo=$this->campos[$k]->name;
                    $this->registros[$i]->$nombreCampo=(bool)$this->registros[$i]->$nombreCampo;
                    continue;
                }
            }
        }
    }
}
?>