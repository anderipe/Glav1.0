<?php
/**
 * @package co.org.lavado.io
 * @subpackage print
 */

/**
 * Clase que define una secuencia de escape nativa enviada a la impresora. Las
 * secuencias de escapen varian segun el lenguaje, asi por ejemplo, la secuencia
 * de escape para establecer letra negrita en impresoras compatibles IBM/POS es
 * diferente a la usada por impresoras PCL compatibles.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
class SecuenciaImpresion {
    /*
     * El comando de la secuencia se debe interpretar como la solicitud de un
     * parametro.
     * @var int
     */
    const PARAMETRO=1000;

    /**
     * Array de numeros enteros que define la secuencia de caracteres que
     * conforman el comando. Por convencion el numero 0 (se interpreta) como
     * un parametro que debe ser provisto por el usuario
     * @var array
     * @access private
     */
    private $comandos=array();

    /**
     * Cadena de texto que representa la secuencia de control que sera enviada
     * a la impresora.
     *
     * Se obtiene de interpretar el array de comandos como una lista de codigos
     * ASCII. Cada caracter es concatenado en la variable "secuencia". Esta
     * secuencia es creada por el constructor de la clase siempre que la
     * secuencia no posea parametros. Si la secuencia posee parametros esta es
     * creada cada vez que se llama el metodo "get" de la clase
     * @var string
     * @access private
     */
    private $secuencia="";

    /**
     * Contructor de la clase
     *
     * Crea una secuencia de control de impresora a partir de una Lista de
     * numeros separados por espacio, coma o punto y coma que representan el
     * conjunto de comandos de que conforman la secuencia. Cada numero es
     * convertido a su representacion en ASCII cuando se da la orden de
     * ejecucion del comando.
     *
     * @param string $comandoEnBruto Lista de numeros separados por espacio,
     * coma o punto y coma que representan el conjunto de comandos que
     * conforman la secuencia. Por convencion el numero -1 (menos uno) se
     * interpreta como un parametro que debe ser provisto por el usuario.
     * @access public
     */
    public function  __construct($comandoEnBruto) {
        $comandoEnBruto= trim($comandoEnBruto);
        $this->comandos=explode(" ", $comandoEnBruto);

        $numeroComandos=count($this->comandos);
        for($i=0;$i<$numeroComandos;$i++){
            if(trim($this->comandos[$i])!=""){
                $this->comandos[$i]=(int)$this->comandos[$i];
            }else{
                array_splice($this->comandos, $i, 1, null);
                $numeroComandos--;
            }
        }

        $secuencia="";
        foreach($this->comandos as $valor){
            if($valor==SecuenciaImpresion::PARAMETRO){
                $secuencia="";
                break;
            }else{
                $secuencia.=chr($valor);
            }
        }

        $this->secuencia=$secuencia;
    }

    /**
     * Devuelve la secuencia de control como una cadena de caracteres.
     *
     * Es posible, y asi se espera, que no todos los caracteres de la cadena
     * sean imprimibles, como por ejemplo el caracter escape (Esc). Para las
     * las secuencias  que requieren uno o mas parametros, la funcion soporta
     * la inclusion de un numero arbitrario de parametros, si estos no son
     * proporcionados y la secuencia los requiere se lanza una excepcion.
     * @access public
     * @return string
     */
    public function get(){
        if($this->secuencia!="")
            return $this->secuencia;

        $numeroArgumento=0;
        $secuencia="";
        $argumentos = func_get_args();
        foreach($this->comandos as $clave=>$valor){
            if($valor==Secuencia::PARAMETRO){
                if(!isset($argumentos[$numeroArgumento]))
                    throw new Exception("No se proporciono el parametro numero ".($numeroArgumento+1)." de la secuencia");

                $secuencia.=trim($argumentos[$numeroArgumento]);
                $numeroArgumento++;
            }else{
                $secuencia.=chr($valor);
            }
        }

        return $secuencia;
    }
}
?>