<?php
/**
 * @package co.org.lavado
 * @subpackage io
 */
/**
 * Clase obstracta que representa un conjunto de registros obtenidos desde una
 * fuente de datos.
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
 * @abstract
 * @access public
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage io
 */
abstract class RecordSet{

    /**
     * Constante que representa el primer registro en el record set.
     *
     * @access public
     * @var int
     */
    const REGISTRO_PRIMERO = -1;

    /**
     * Constante que representa el ultimo registro en el record set.
     * <p>
     * @access public
     * @var int
     */
    const REGISTRO_ULTIMO = -2;

    /**
     * Constante que representa el registro anterior al registro actual en el
     * record set.
     *
     * @access public
     * @var int
     */
    const REGISTRO_ANTERIOR = -3;

    /**
     * Constante que representa el registro siguiente al registro actual en el
     * record set.
     *
     * @access public
     * @var int
     */
    const REGISTRO_SIGUIENTE = -4;

    /**
     * Constante que define el registro actualmemte apuntado en el record
     * set.
     *
     * @access public
     * @var int
     */
    const REGISTRO_ACTUAL = -5;

    /**
     * Constante que define que los registros obtenidos desde el record set
     * seran devueltos como un objeto generico en donde cada propiedad
     * del objeto se corresponde con un campo del registro.
     */
    const FORMATO_CLASE = 0;

    /**
     * Constante que define que los registros obtenidos desde el record set
     * seran devueltos como un objeto generico en donde cada propiedad
     * del objeto se corresponde con un campo del registro.
     *
     * @access public
     * @var int
     */
    const FORMATO_OBJETO = 1;

    /**
     * Constante que define que los registros obtenidos desde el record set
     * seran devueltos como una cadena de caracteres en formato JSON
     * donde la cadena no posee las llaves de apertura y cierre del objeto.
     *
     * @access public
     * @var int
     */
    const FORMATO_JSON = 2;

    /**
     * Contante que define la orden de colocar llaves de inicio y cierre a una
     * cadena en formato JSON.
     *
     * @access public
     * @var int
     */
    const FORMATO_CON_LLAVES = 4;

    /**
     * Constante que define que los registros obtenidos desde el record set
     * seran devueltos como una cadena de caracteres en formato JSON
     * donde la cadena posee las llaves de apertura y cierre del objeto.
     *
     * @access public
     * @var int
     */
    const FORMATO_JSON_CON_LLAVES = 6;

    /**
     *
     */
    const FORMATO_OBJETO_APP = 8;

    /**
     * Array de objeto que representan el conjunto de registros obtenidos
     * de la fuente de datos. El array se encuentra en base cero.
     * <p>
     * @access protected
     * @var array
     */
    protected $registros = null;

    /**
     * Array que definen als propiedades de los campos ontenidos de la db
     * @var array
     */
    public $campos = null;

    /**
     * Numero de registros contenidos en el record set.
     *
     * @access protected
     * @var int
     */
    protected $cantidad = null;

    /**
     * Numero de campos contenidos en el record set.
     *
     * @access protected
     * @var int
     */
    protected $cantidadCampos = null;

    /**
     * Numero de campos en cada registro del record set.
     *
     * @access protected
     * @var int
     */
    protected $numeroCampos = null;

    /**
     * Atributo que hace las veces de apuntador a un registro y define el
     * registro actualmente apuntado por el record set.
     *
     * @access private
     * @var int
     */
    private $apuntador = null;

    /**
     * Constructor de que inicializa los atributos de la clase a sus valores por
     * defecto
     *
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     */
    public function __construct(){
        $this->registros=array();
        $this->cantidad=(int)0;
        $this->apuntador=(int)-1;
        $this->numeroCampos=(int)0;
    }

    /**
     * Obtiene los ultimos registros consultados
     * @return array
     */
    public function getRegistros(){
        return $this->registros;
    }

    /**
     * Mueve el apuntador de registro al anterior al primero, es decir
     * deja el apuntador del registro actual en -1.
     *
     * Cuando el record set se crea hace una llamada automatica a este
     * metodo pudiendo recorrerce inmediatamente de inicio a fin usando
     * el metodo irASiguiente dentro de un bucle de repeticion. Luego de
     * la primer llamada al metodo irASiguente es necesario volver a llamar
     * el metodo reiniciar para poder volver a recorrer el record set desde su
     * inicio.
     *
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     */
    public function reiniciar(){
        $this->apuntador=(int)-1;
    }

    /**
     * Obtiene el numero de registros en el record set.
     *
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @return int Cantidad de registro en el record set.
     */
    public function getCantidad(){
        return (int)$this->cantidad;
    }

    /**
     * Obtiene el numero campos en cada registro del record set.
     *
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @return int Numero campos en cada registro del record set.
     */
    public function getNumeroCampos(){
        return (int)$this->numeroCampos;
    }

    /**
     * Mueve el apuntador al siguiente registro disponible en el recordset.
     *
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @return boolean Devuelve TRUE si fue posible ubicar el apuntador en el
     * siguiente registro del record set. Devuelve FALSE en caso contrario.
     */
    public function irASiguiente(){
        if($this->apuntador<($this->cantidad-1)){
            $this->apuntador++;
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Obtiene un registro especifico del record set en el formato especificado.
     *
     * El metodo permite obtener un registro en la forma de un objeto donde
     * cada uno de los atributos del objeto corresponde con un campo del
     * registro. De manera alternativa permite obtener el registro mediante una
     * cadena de texto en formato JSON, este metodo es conveniente para
     * el intercambio de informacion asincronico sobre la WEB, por esta razon
     * cuando se obtiene el registro en formato JSON los valores de cada
     * campo del registro son codificados mediante la funcion de php
     * escapeJsonString($valor)
     *
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @param  int $posicion [opcional]<p>
     * Numero de registro que se desea obtener o constante que define un
     * registro especifico. El valor por defecto es la constante
     * REGISTRO_ACTUAL. Si el record set no posee registros o si el
     * numero de registro que se desea acceder no existe el metodo arroja aun
     * excepcion.
     *
     * @param  int $formato [opcional]<p>Define el formato en que se desea
     * obtener el registro. Su valor por defecto es la constante
     * FORMATO_OBJETO. Puede tomar uno de los valores FORMATO_OBJETO,
     * FORMATO_JSON o FORMATO_JSON_CON_LLAVES. Si no se recibe una de estas
     * constante el metodo arroja una excepcion.
     *
     * @return object Objeto o cadena de caracteres en formato JSON que describe
     * el registro solicitado.
     */
    public function get($posicion = RecordSet::REGISTRO_ACTUAL  ,
            $formato = RecordSet::FORMATO_OBJETO){

        $returnValue=null;
        $posicion=(int)$posicion;
        $formato=(int)$formato;

        if($this->cantidad==0)
            throw new Exception("El record set esta vacio, no se pueden
                obtener registros");

        if($posicion>=0){
            if($posicion>=$this->cantidad)
                throw new Exception("El registro solicitado esta fuera del
                    rango, maximo permitido:".($this->cantidad-1));

            if($formato & RecordSet::FORMATO_OBJETO)
                return $this->registros[$posicion];

            if($formato & RecordSet::FORMATO_JSON)
                return json_encode($this->registros[$posicion]);

        }else{
            switch ($posicion) {
                case RecordSet::REGISTRO_ACTUAL:{
                    if($this->apuntador<0
                            || $this->apuntador>=($this->cantidad))
                        throw new Exception("El apuntador no es valido");

                    return $this->get($this->apuntador, $formato);
                }
                case RecordSet::REGISTRO_PRIMERO:{
                    return $this->get(0, $formato);
                }
                case RecordSet::REGISTRO_ULTIMO:{
                    return $this->get($this->cantidad-1, $formato);
                }
                default:{
                    throw new Exception("La posicion indicada no es valida");
                    break;
                }
            }
        }

        throw new Exception("La posicion indicada no es valida");
    }

    /**
     * Obtiene una cadena de caracteres que representa todo el record set
     * en notacion JSON.
     *
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @return string La cadena es una array de registros, donde cada uno de
     * ellos es en si mismo una cadena en formato JSON.
     */
    public function obtenerJSON(){
        $this->reiniciar();
        return json_encode($this->registros);
    }
}
?>