<?php
/**
 * @package co.org.lavado
 */

//function __autoload($class_name) {
//    require_once $class_name.'.php';
//}

/**
 * Clase que conforma un marco general de programacion para un proyecto php.
 * <p>
 * Este marco general define un conjunto de caracteristicas generales a la
 * aplicacion que son utilizadas por todas las clases del proyecto.
 * <p>
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2010/09/15
 * @version 1.0
 * @package co.org.lavado
 */

/**
 * Clase que conforma un marco general de programacion para un proyecto php.
 * <p>
 * Este marco general define un conjunto de caracteristicas generales a la
 * aplicacion que son utilizadas por todas las clases del proyecto.
 * <p>
 * @access public
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2010/09/15
 * @version 1.0
 * @package co.org.lavado
 */
class FrameWork{

    const LOCAL=0;
    const REMOTO=1;

    /**
     * Nombre del sitio al cual hace parte el marco de trabajo.
     * <p>
     * Este nombre de sitio debe ser el nombre de la caprta raiz en donde se
     * encuentra el archivo index.php que da la entrada al sitio web.
     * <p>
     * @access private
     * @static
     * @var String
     */
    private static $sitio = null;

    public static $servidor=null;

    public static $baseDeDatos=null;

    public static $usuario=null;

    public static $contrasena=null;

    public static $host=null;

    public static $puerto=null;

    /**
     * Ruta relativa hasta la raiz del sitio.
     * <p>
     * @access private
     * @static
     * @var String
     */
    private static $rootPath = "";

    /**
     * Conexion a la base de datos del sitio web al que pertenece el marco de
     * trabajo.
     * <p>
     * @access private
     * @static
     * @var Conexion
     */
    private static $conexion = null;

    /**
     *
     * @var int
     */
    private static $idUsuario=0;

    private static $idLlavePrivada=null;

    private static $idLlavePublica=null;

    public static function getIdLlavePrivada(){
        if(FrameWork::$idLlavePrivada===null){
            $llavePrivada=new Variable(14);
            $clave=new Variable(15);
            $sal=new Variable(16);

            $llaveEncriptada=$llavePrivada->getValor();

            if(empty($llaveEncriptada)){
                FrameWork::$idLlavePrivada=-1;
                return FrameWork::$idLlavePrivada;
            }

            $llaveDesEncriptada= openssl_decrypt($llaveEncriptada, 'aes-128-cbc', $clave->getValor(), false, $sal->getValor());
            if(!$llaveDesEncriptada)
                throw new Exception('No fue posible obtener la llave encriptada en la prueba de firmado');

            FrameWork::$idLlavePrivada = openssl_get_privatekey($llaveDesEncriptada);
            if(FrameWork::$idLlavePrivada===false){
                FrameWork::$idLlavePrivada=-1;
                throw new Exception('No fue posible obtener el identificador de la llave privada');
            }
        }

        return FrameWork::$idLlavePrivada;
    }

    public static function getIdLlavePublica(){
        if(FrameWork::$idLlavePublica===null){
            $certificado=new Variable(13);
            $llavePublica=$certificado->getValor();
            if(empty($llavePublica)){
                FrameWork::$idLlavePublica=-1;
                return;
            }
            FrameWork::$idLlavePublica = openssl_get_publickey($llavePublica);
            if(FrameWork::$idLlavePublica==false)
                throw new Exception('No fue posible obtener el identificador de la llave publica');
        }

        return FrameWork::$idLlavePublica;
    }

    public static function liberarLlaves(){
        if(!empty(FrameWork::$idLlavePublica) && FrameWork::$idLlavePublica!=-1)
            openssl_free_key(FrameWork::$idLlavePublica);

        if(!empty(FrameWork::$idLlavePrivada) && FrameWork::$idLlavePrivada!=-1)
            openssl_free_key(FrameWork::$idLlavePrivada);
    }

    public static function firmarDatos($datos){
        $llavePrivada=FrameWork::getIdLlavePrivada();
        if($llavePrivada==-1)
            return '';

        if(empty($datos))
            throw new Exception('No se puede firmar datos vacios');

        $firma=null;
        if(!openssl_sign($datos, $firma, $llavePrivada, OPENSSL_ALGO_SHA1))
            throw new Exception('No fue posible firmar los datos de prueba');

        return $firma;
    }

    public static function verificarFirma($datos, $firma){
        if(empty($datos))
            return -1;

        $llavePublica=FrameWork::getIdLlavePublica();
        if($llavePublica==-1)
            return -1;

        return openssl_verify($datos, $firma, $llavePublica);
    }

    /**
     *
     * @return int
     */
    public static function getIdUsuario(){
        return FrameWork::$idUsuario;
    }

    public static function getTmpName(){
        if(empty(FrameWork::$idUsuario))
            throw new Exception('No esta definido el usuario');

        return 'u'.FrameWork::$idUsuario;
    }

    /**
     *
     * @param int $idUsuario
     */
    public static function setIdUsuario($idUsuario){
        FrameWork::$idUsuario=(int)$idUsuario;
        $_SESSION['idUsuario']=FrameWork::$idUsuario;
    }

    /**
     * Ontiene la ruta relativa hasta la raiz del sitio web.
     * <p>
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @return String Ruta relativa hasta la raiz del sitio web
     * <p>
     */
    public static function getRootPath(){
        return (string) FrameWork::$rootPath;
    }

    /**
     * Obtiene la raiz del sitio web
     * @return type
     */
    public static function getSitio(){
        return (string) FrameWork::$sitio;
    }

    /**
     * Obtiene la conexion a la base de datos del sitio web.
     * <p>
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @return ConexionMySQL Conexion a la base de datos del sitio web
     * <p>
     */
    public static function getConexion(){
        return FrameWork::$conexion;
    }

    /**
    * Este metodo se ejecuta siempre que sea lanzada una excepcion por el
    * sistema y esta no sea capturada explicitamente por el codigo fuente.
    * Devuelve la excepcion al cliente como una cadena en formato JSON.
    * <p>
    * @param AppException $excepcion Excepcion capturada por el sistema.
    * @access public
    * @static
    */
    public static function ManejarExcepcion(Exception $excepcion){
        $json= new stdClass();
        $json->success=false;
        $json->msg=$excepcion->getMessage();
        $json->archivo=$excepcion->getFile();
        $json->linea=$excepcion->getLine();
        if(get_class($excepcion)=="AppException")
            $json->errors=$excepcion->getErrores();

        die (json_encode($json));
    }

    /**
     * Captura errores de usuario de php y los convierte en excepciones.
     * <p>
     * Los errores capturados son de tipo warning, notice y error, los
     * errores de sintaxis no son cpturados por esta funcion debido a que
     * php no permite su captura.
     * <p>
     * @param int $errno Codigo de error
     * @param string $errstr Descripcion del error
     * @param string $errfile Archivo donde se genera el error
     * @param int $errline Linea de codigo donde se genera el error
     * @return boolean
     */
    public static function ManejarError($errno, $errstr, $errfile, $errline){
        throw new AppException($errstr.', archivo:'.$errfile.', linea: '.$errline.', numero:'.$errno);
    }

    /**
     *
     * Calculo en FrameWork::$rootPath la ruta relativa al sitio
     * @param type $sitio
     * @throws Exception
     */
    public static function crearRootPath(){
        FrameWork::$rootPath='/media/www/lavado/';
    }

    /**
     * Establece si se deben o no mostrar los errores y si el manejador de
     * errores debe ser cambiado
     *
     * Si se muestran los errores, se muestra todo menos los deprecados
     *
     * @param type $mostrarErrores
     * @param type $cambiarmanejadorDeErrores
     */
    public static function mostrarErrores($mostrarErrores=true,
            $cambiarmanejadorDeErrores=true){

        if($mostrarErrores==true){
            ini_set("display_errors" , 1);
            error_reporting(E_ALL & ~E_DEPRECATED);
        }

        if($cambiarmanejadorDeErrores==true){
            set_exception_handler(array("FrameWork", "ManejarExcepcion"));
            set_error_handler(array("FrameWork", "ManejarError"));
        }
    }

    /**
     * Inicializa sesion php
     * @param type $creacionSesion
     */
    public static function iniciarSesion($creacionSesion=true){
        $idSesion=session_id();
        if(empty($idSesion) && $creacionSesion)
            session_start();
    }

    /**
     * Establece la zona horaria por defecto
     * @param type $timeZone
     */
    public static function setTimezone($timeZone='America/Bogota'){
        date_default_timezone_set($timeZone);
    }

    /**
     * Lee y procesa el archivo de configuracion del sitio
     * @param string $archivoConfiguracion
     * @throws Exception
     */
    public static function procesarArchivoConfiguracion(){
        if(!isset($_SESSION['db_host']))
            throw new Exception('Servidor de datos no definido');
        FrameWork::$host=$_SESSION['db_host'];

        if(!isset($_SESSION['db_port']))
            throw new Exception('Puerto de conecion no definifo');
        FrameWork::$puerto=$_SESSION['db_port'];

        if(!isset($_SESSION['db_name']))
            throw new Exception('Base de datos no definida');
        FrameWork::$baseDeDatos=$_SESSION['db_name'];

        if(!isset($_SESSION['db_user']))
            throw new Exception('Usuario de datos no definido');
        FrameWork::$usuario=$_SESSION['db_user'];

        if(!isset($_SESSION['db_password']))
            throw new Exception('Clave de usuario de datos no definida');
        FrameWork::$contrasena=$_SESSION['db_password'];

        if(!isset($_SESSION['idUsuario']))
            throw new Exception('El usuario no esta definido');
        FrameWork::$idUsuario=$_SESSION['idUsuario'];
    }

    /**
     * Crea una conexion a la base de datos a partir del archivo de
     * configuracion
     * @param type $archivoConfiguracion
     * @return ConexionPostgreSQL
     */
    public static function crearConexion(){
        require_once('ConexionMySQL.php');
        $nuevaConexion=new ConexionMySQL(FrameWork::$baseDeDatos, FrameWork::$usuario, FrameWork::$contrasena, FrameWork::$host, FrameWork::$puerto);

        return $nuevaConexion;
    }

    /**
     * Agrega un directorio al include path de php.
     * <p>
     * Al incluir un directorio al include path de php no se hace necesario
     * especificar la ruta completa de un archivo al llamar las funciones
     * include o requery, basta con solo especificar el nombre del archivo
     * para que php lo busque en los directorios del include path. Esta funcion
     * agrega al include path el directorio especificado y todos los sub
     * contenidos en el.
     * <p>
     * @access public
     * @static
     * @author Universidad Cooperativa de Colombia - 2012
     * @param  String $directorio Directorio a agregar al include path
     * <p>
     */
    public static function agregarIncludePath($directorio){
        if(!is_dir($directorio))
            throw new Exception("El directorio $directorio no existe o no es
                    valido, no se puede agregar al INCLUDE_PATH");

        @$recurso = opendir($directorio);
        if($recurso==FALSE)
            throw new Exception("No fue posible abrir el directorio $directorio
                    para incluirlo al INCLUDE_PATH");

        set_include_path(get_include_path().PATH_SEPARATOR.$directorio);
        //echo "agregado: $directorio<br>";
        do{
            @$archivo=readdir($recurso);
            //echo 'leido: '.$archivo.'<br>';
            if($archivo!=FALSE && $archivo!="." && $archivo!=".."){
                //echo "es dir?".$directorio.'/'.$archivo.": ";
                if(is_dir($directorio.'/'.$archivo)){
                    //echo "Si<br>";
                    FrameWork::agregarIncludePath($directorio.'/'.$archivo);
                }else{
                    //echo "No<br>";
                }
            }
        }while($archivo!=FALSE);

        @closedir($recurso);
    }

    /**
     * Constructor de la clase.
     * <p>
     * Define algunas caracteristicas generales de sitio web creando asi un
     * marco de trabajo general para las clases usadas dentro del sitio web.
     * Entre las caracteristicas definidas estan la conexion general a la
     * base de datos, rutas relativas al sitio web, creación de una sesión de
     * trabajo php y otras caracteristicas.
     * <p>
     * @access public
     * @author Universidad Cooperativa de Colombia - 2012
     * @param  string $sitio Nombre del sitio.<p>
     * En general es el nombre de la carpeta principal del sitio web en donde
     * se encuentra el archivo index.* de entrada al sitio web. El nombre del
     * sitio es utilizado para calcular las rutas relativas a los demas
     * archivos necesarios para inclusion.
     * <p>
     * @param  string $archivoConfiguracion [opcional]<p>
     * Ruta al archivo de configuracion del sitio web. El archivo de
     * configuracion del sitio web define las caracteristicas de conexion a
     * la base de datos. Esta ruta debe ser relativa a la raiz del sitio.
     * Se soportan archivos de configuracion con las sintaxis de php.ini o
     * con la sintaxis del viejo archivos de configuracion de signo
     * <p>
     */
    public function __construct(){
        require_once 'AppException.php';
        FrameWork::setTimezone('America/Bogota');
        FrameWork::mostrarErrores(true, true);
        FrameWork::iniciarSesion(true);
        FrameWork::crearRootPath();
        FrameWork::agregarIncludePath("/media/www/lavado/clases/");
        //spl_autoload_register();

        FrameWork::procesarArchivoConfiguracion();
        FrameWork::$conexion=FrameWork::crearConexion();

        if(isset($_SESSION['idUsuario']))
            FrameWork::$idUsuario=(int)$_SESSION['idUsuario'];

    }
}
?>