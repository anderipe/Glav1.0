<?php
/**
 * @package co.org.lavado
 * @subpackage app
 */

/**
 * Clase que agrupa algunos metodos de validacion
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage app
 */
class Validador {
    public static function esEMail($email){
        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            return false;

        return true;
    }

    public static function digitoVerificacion($nit) {
        $nit=(int)$nit;
        if (empty($nit) || ($dim = strlen($nit)) > 15)
            return '';

        $primos = array(3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71);
        $zuma = $modulo = 0;
        for ($i = 0; $i < $dim; ++$i) {
            $ind=($dim - 1) - $i;
            $c = mb_substr($nit, $ind, 1);
            if (ord($c) < 48 || ord($c) > 57){
                return '';
            }
            $zuma += ($c * $primos[$i]);
        }
        $modulo = $zuma % 11;
        if ($modulo > 1){
            return (11 - $modulo);
        }
        return $modulo;
    }

    public static function esAlfabetico($valor){
        $permitidos = '|^[a-zA-Z ñÑáéíóúüç]*$|';

        if (preg_match($permitidos,$valor))
            return true; // Campo permitido
        else
            return false; // Error uno de los caracteres no hace parte de la expresión regular
    }
}

?>
