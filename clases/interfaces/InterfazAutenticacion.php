<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

require_once '../Framework.php';
//FrameWork::agregarIncludePath("/../../clases/");
FrameWork::agregarIncludePath('../../clases');
require_once 'AppException.php';

//require_once '../Framework.php';
//FrameWork::agregarIncludePath('/media/www/lavado/clases');
require_once 'AppException.php';
require_once 'ConexionMySQL.php';
require_once 'Auditoria.php';
FrameWork::mostrarErrores();

/**
 * La unica interfaz que no deriva de interfazBase, basicamente porque a este
 * punto no se ha iniciado sesion y las variables de sistema necesarias para
 * inciar un framework no estan definidas.
 *
 * Este es el unico lugar en donde se permite el uso de codigo extructurado
 * dado que el sistema de clases no se encuentra disponible sin la iniciacion
 * de framework
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
class InterfazAutenticacion{
    /**
     *
     * @param ArrayObject $args
     */
    public function __construct() {
        $loginUsuario=$_POST['login'];
        $passwordUsuario=$_POST['password'];
        $retorno= new stdClass();

        if(empty($loginUsuario))
            throw new AppException('El nombre de usuario es obligatorio',
                (object)array('login'=>'El nombre de usuario es obligatorio'));

        if(mb_strlen($loginUsuario)>32)
            throw new AppException('El nombre de usuario tiene maximo 32 caracteres',
                (object)array('login'=>'El nombre de usuario tiene maximo 32 caracteres'));

        if(empty($passwordUsuario))
            throw new AppException('La contraseña de usuario es obligatorio',
                (object)array('password'=>'La contraseña de usuario es obligatorio'));

        if(mb_strlen($passwordUsuario)>32)
            throw new AppException('La contraseña de usuario tiene maximo 32 caracteres',
                (object)array('password'=>'La contraseña de usuario tiene maximo 32 caracteres'));

        $passwordUsuario=sha1(md5($passwordUsuario));
        $conexion=new ConexionMySQL('lavado', 'root', '', 'localhost', '3306');
/*Yo modifique la siguiente linea**/
        $sql='select idusuario from usuario where login=\''.mysql_real_escape_string($loginUsuario).'\' and password=\''.mysql_real_escape_string($passwordUsuario).'\' and estado=1';
        $conexion->consultar($sql);
        $resultados=$conexion->consultar($sql);
        $conexion->cerrar();


        if($resultados->getCantidad()==0)
            throw new AppException('El nombre de usuario o su contraseña son incorrectos',
                (object)array('login'=>'Nombre de usuario o contraseña incorrectos', 'password'=>'Nombre de usuario o contraseña incorrectos'));

        

        FrameWork::iniciarSesion();
        $_SESSION['db_host']='localhost';
        $_SESSION['db_port']='3306';
        $_SESSION['db_name']='lavado';
        $_SESSION['db_user']='root';
        $_SESSION['db_password']='';
        $_SESSION['idUsuario']=(int)$resultados->get(0)->idusuario;



        /**
         * Creamos framework para poder usar la clase usuario
         */
        $miFramework=new FrameWork();
        $usuario=new Usuario(FrameWork::getIdUsuario());
        $accionAuditable= new AccionAuditable(AccionAuditable::IngresoAlSistema);
        $modulo=new Modulo(12);
        $auditoria= new Auditoria();
        $auditoria->setUsuario($usuario);
        $auditoria->setModulo($modulo);
        $auditoria->setAccionAuditable($accionAuditable);
        $auditoria->guardarObjeto(null);

        //echo 'llegue';exit();


        $retorno=new stdClass();
        $retorno->msg='';
        $retorno->success=true;
        echo json_encode($retorno);
        //include_once 'InterfazCrearMenu.php';
    }
}
new InterfazAutenticacion();
?>