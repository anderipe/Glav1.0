<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Variable.php';
    require_once 'Auxiliar.php';

/**
 * Clase controladora del modulo de aseguramiento de la base de datos
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazAseguramiento
        extends InterfazBase{

        const FIRMAR_TABLA=101;
        const SUBIR_P12=102;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=$this->getInt('accion');
            switch($accion){

                case InterfazAseguramiento::FIRMAR_TABLA:{
                    function __autoload($class_name) {
                        require_once $class_name . '.php';
                    }

                    $totalRegistro=0;
                    $tabla=$this->getString('tabla');
                    $indice=$this->getInt('indice');

                    $campoId='id'.$tabla;
                    $clase=Clase::crearPorTabla($tabla);
                    $nombreClase=$clase->getNombre();
                    do{
                        $sql='select '.$campoId.' as id from '.$tabla.' order by '.$campoId.' limit 1000 offset '.$totalRegistro;
                        $resultados=$this->conexion->consultar($sql);
                        $totalRetornados=$resultados->getCantidad();
                        $totalRegistro+=$totalRetornados;
                        while($resultados->irASiguiente()){
                            $creacionObjeto='return new '.$nombreClase.'('.$resultados->get()->id.');';
                            $objeto=eval($creacionObjeto);
                            $hash=$objeto->calcularHash();
                            $sql='update '.$tabla.' set hash=\''.$hash.'\' where '.$campoId.'='.$resultados->get()->id;
                            $this->conexion->ejecutar($sql);
                            $firma=$objeto->calcularFirma();
                            $sql='update '.$tabla.' set firma=\''.$firma.'\' where '.$campoId.'='.$resultados->get()->id;
                            $this->conexion->ejecutar($sql);
                        }
                    }while($totalRetornados>0);


                    $this->retorno->msg='';
                    $this->retorno->tabla=$tabla;
                    $this->retorno->indice=$indice;
                    $this->retorno->registros=$totalRegistro;
                    $this->retorno->firmado=1;

                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazAseguramiento::SUBIR_P12:{
                    $password=$this->getString('password');
                    $verificacion1=$this->getInt('verificacion1');
                    $verificacion2=$this->getInt('verificacion2');
                    $verificacion3=$this->getInt('verificacion3');

                    if(empty($verificacion1) || empty($verificacion2) || empty($verificacion3))
                        throw new Exception('Antes de proceder debe leer y aceptar los terminos de funcionamiento');


                    if(!isset($_FILES['archivoP12']))
                        throw new Exception('Debe proporcionar un archivo p12 o pfx que contenga la llave privada para el firmado de registros y el certificado de llave publica para verificacion');

                    if ($_FILES['archivoP12']['error'] > 0)
                        throw new Exception('Debe proporcionar un archivo p12 o pfx que contenga la llave privada para el firmado de registros y el certificado de llave publica para verificacion');

                    if(empty($password))
                        throw new AppException('La contraseña del p12 es obligatoria',
                            (object)array('password'=>'La contraseña del p12 es obligatoria'));


                    $p12=file_get_contents($_FILES["archivoP12"]["tmp_name"]);
                    unlink($_FILES["archivoP12"]["tmp_name"]);

                    $certificados=array();
                    if(openssl_pkcs12_read($p12, $certificados, $password)!=1)
                        throw new Exception('No se ha posido acceder al archivo p12. Es posible que la contraseña sea incorrecta o que el archivo tenga un formato invalido');

                    $password=Auxiliar::generarPassword(16);
                    $iv=Auxiliar::generarRandomDecimal(16);

                    $llaveEncriptada=openssl_encrypt ($certificados["pkey"], 'aes-128-cbc', $password, false, $iv);
                    if(!$llaveEncriptada)
                        throw new Exception('No fue posible encriptar la llave privada');

                    $certificado=new Variable(13);
                    $certificado->setValor($certificados['cert']);

                    $llavePrivada=new Variable(14);
                    $llavePrivada->setValor($llaveEncriptada);

                    $clave=new Variable(15);
                    $clave->setValor($password);

                    $sal=new Variable(16);
                    $sal->setValor($iv);

                    $usuario=new Usuario(FrameWork::getIdUsuario());
                    $modulo=Modulo::crearPorClase('siadno.view.administracion.local.auditoria.aseguramiento');

                    $auditoria=new Auditoria();
                    $auditoria->setUsuario($usuario);
                    $auditoria->setModulo($modulo);
                    $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));

                    $this->conexion->ejecutar('begin;');
                    $auditoria->guardarObjeto(null);
                    $certificado->guardarObjeto($auditoria);
                    $llavePrivada->guardarObjeto($auditoria);
                    $clave->guardarObjeto($auditoria);
                    $sal->guardarObjeto($auditoria);
                    $this->conexion->ejecutar('commit;');

                    /**
                     * Ejecutamos una prueba completa de firmado
                     */
                    $datos='datos de prueba para firmar';
                    $firma=FrameWork::firmarDatos($datos);
                    //$this->retorno->msg='La lonfitud de la firma es '.  mb_strlen($firma);
                    if(FrameWork::verificarFirma($datos, $firma)!=1)
                        throw new Exception('Error verificando la firma generada');

                    FrameWork::liberarLlaves();

                    $this->retorno->msg='';
                    echo json_encode($this->retorno);
                    break;
                }

                default:{
                    $sql = 'SELECT distinct TABLE_NAME as tabla
                        FROM information_schema.COLUMNS
                        WHERE
                        TABLE_SCHEMA like \'lavado\'
                        and
                        TABLE_NAME<>\'secuenciaimpresion\'
                        ORDER BY
                        TABLE_NAME';

                    $resultados=$this->conexion->consultar($sql);

                    $numeroTablas=$resultados->getCantidad();
                    $tablas=$resultados->getRegistros();
                    for($i=0; $i<$numeroTablas; $i++){
                        $sql='select count(*) as cantidad from '.$tablas[$i]->tabla;
                        $resultados=$this->conexion->consultar($sql);
                        $tablas[$i]->registros=(int)$resultados->get(0)->cantidad;
                        $tablas[$i]->firmado=-1;
                    }
                    $this->retorno->success=true;
                    $this->retorno->msg="";
                    $this->retorno->data=$tablas;
                    $this->retorno->total=count($this->retorno->data);
                    echo json_encode($this->retorno);
                }
            }
        }
    }
    new InterfazAseguramiento(new ArrayObject(array_merge($_POST, $_GET)));
?>