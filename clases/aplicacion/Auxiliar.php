<?php
/**
 * @package co.org.lavado
 * @subpackage app
 */

/**
 * Clase que agrupa algunos metodos auxiliares
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage app
 */
class Auxiliar {
    public static function generarPassword($longitud) {
        $longitud=(int)$longitud;
        $chars = "abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        srand((double)microtime()*1000000);
        $pass = '' ;

        do{
            $num = rand() % mb_strlen($chars)+1;
            $tmp = mb_substr($chars, $num, 1);
            $pass .= $tmp;
        }while(mb_strlen($pass)<$longitud);

        return $pass;
    }

    public static function generarRandomAlfanumerico($longitud) {
        $longitud=(int)$longitud;
        $chars = "abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        srand((double)microtime()*1000000);
        $pass = '' ;

        do{
            $num = rand() % mb_strlen($chars)+1;
            $tmp = mb_substr($chars, $num, 1);
            $pass .= $tmp;
        }while(mb_strlen($pass)<$longitud);

        return trim($pass);;
    }

    public static function generarRandomHexadecimal($longitud) {
        $longitud=(int)$longitud;
        $chars = "ABCDEF0123456789";
        srand((double)microtime()*1000000);
        $pass = '' ;

        do{
            $num = rand() % mb_strlen($chars)+1;
            $tmp = mb_substr($chars, $num, 1);
            $pass .= $tmp;
        }while(mb_strlen($pass)<$longitud);

        return trim($pass);
    }

    public static function generarRandomDecimal($longitud) {
        $longitud=(int)$longitud;
        $chars = "0123456789";
        srand((double)microtime()*1000000);
        $pass = '' ;

        do{
            $num = rand() % mb_strlen($chars)+1;
            $tmp = mb_substr($chars, $num, 1);
            $pass .= $tmp;
        }while(mb_strlen($pass)<$longitud);

        return trim($pass);
    }

    public static function mb_str_ireplace($co, $naCo, $wCzym) {
        $wCzymM = mb_strtolower($wCzym);
        $coM    = mb_strtolower($co);
        $offset = 0;

        while(!is_bool($poz = mb_strpos($wCzymM, $coM, $offset))) {
            $offset = $poz + mb_strlen($naCo);
            $wCzym = mb_substr($wCzym, 0, $poz). $naCo .mb_substr($wCzym, $poz+mb_strlen($co));
            $wCzymM = mb_strtolower($wCzym);
        }

        return $wCzym;
    }

    public static function mb_str_replace($co, $naCo, $wCzym) {
        $wCzymM = $wCzym;
        $coM    = $co;
        $offset = 0;

        while(!is_bool($poz = mb_strpos($wCzymM, $coM, $offset))) {
            $offset = $poz + mb_strlen($naCo);
            $wCzym = mb_substr($wCzym, 0, $poz). $naCo .mb_substr($wCzym, $poz+mb_strlen($co));
            $wCzymM = $wCzym;
        }

        return $wCzym;
    }

    public static function mb_str_pad ($input, $pad_length, $pad_string, $pad_style, $encoding="UTF-8") {
        return str_pad($input, strlen($input)-mb_strlen($input,$encoding)+$pad_length, $pad_string, $pad_style);
    }

    public static function mb_trim($string, $charlist='\\\\s', $ltrim=true, $rtrim=true){
        $both_ends = $ltrim && $rtrim;

        $char_class_inner = preg_replace(
            array( '/[\^\-\]\\\]/S', '/\\\{4}/S' ),
            array( '\\\\\\0', '\\' ),
            $charlist
        );

        $work_horse = '[' . $char_class_inner . ']+';
        $ltrim && $left_pattern = '^' . $work_horse;
        $rtrim && $right_pattern = $work_horse . '$';

        if($both_ends)
        {
            $pattern_middle = $left_pattern . '|' . $right_pattern;
        }
        elseif($ltrim)
        {
            $pattern_middle = $left_pattern;
        }
        else
        {
            $pattern_middle = $right_pattern;
        }

        return preg_replace("/$pattern_middle/usSD", '', $string);
    }

    public static function sys_get_temp_dir(){
        // sys_get_temp_dir is only available since PHP 5.2.1
        // http://php.net/manual/en/function.sys-get-temp-dir.php#94119

        if ( !function_exists('sys_get_temp_dir')) {
            if ($temp = getenv('TMP') ) {
                if (file_exists($temp)) { return realpath($temp); }
            }
            if ($temp = getenv('TEMP') ) {
                if (file_exists($temp)) { return realpath($temp); }
            }
            if ($temp = getenv('TMPDIR') ) {
                if (file_exists($temp)) { return realpath($temp); }
            }

            // trick for creating a file in system's temporary dir
            // without knowing the path of the system's temporary dir
            $temp = tempnam(__FILE__, '');
            if (file_exists($temp)) {
                unlink($temp);
                return realpath(dirname($temp));
            }

            return null;
        }

        // use ordinary built-in PHP function
        //	There should be no problem with the 5.2.4 Suhosin realpath() bug, because this line should only
        //		be called if we're running 5.2.1 or earlier
        return realpath(sys_get_temp_dir());
    }

    /**
     *
     * @param string $fecha
     * @return array
     */
    public static function getInformacionSemana($fecha){

        $numeroDeSemana = date('W', strtotime($fecha));
        $anio = date('Y', strtotime($fecha));
        $date_string = $anio . 'W' . sprintf('%02d', $numeroDeSemana);
        $return[0] = date('Y-m-d', strtotime($date_string));
        $return[1] = date('Y-m-d', strtotime($date_string . '7'));

        return $return;
    }
}

?>
