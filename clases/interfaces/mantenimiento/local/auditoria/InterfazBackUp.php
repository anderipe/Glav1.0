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
    class InterfazBackUps
        extends InterfazBase{

        const LISTAR_BACKUPS=101;
        const CREAR_BACKUPS=102;
        const BORRAR_BACKUPS=103;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=$this->getInt('accion');
            switch($accion){

                case InterfazBackUps::LISTAR_BACKUPS:{
                    $this->listarBackUps();
                    break;
                }

                case InterfazBackUps::CREAR_BACKUPS:{
                    $nombreArchivo="../../../../../backups/DumpFile_".date("Y_m_d_h_i_s").".sql";
                    $this->conexion->crearBackUp($nombreArchivo);
                    $this->listarBackUps();
                    break;
                }

                case InterfazBackUps::BORRAR_BACKUPS:{
                    $nombreArchivo=$this->getString("rutacompleta");
                    unlink($nombreArchivo);
                    //$this->conexion->crearBackUp($nombreArchivo);
                    //$this->crearBackUps();
                    $this->listarBackUps();
                    break;
                }

                default:{
                    echo json_encode($this->retorno);
                }
            }
        }

        protected function listarBackUps(){
            $directorio="../../../../../backups";
            @$recurso = opendir($directorio);
            if($recurso==FALSE)
                throw new Exception("No fue posible abrir el directorio de BACKUPS");

            $archivos=array();
            do{
                @$archivo=readdir($recurso);
                //echo 'leido: '.$archivo.'<br>';
                if($archivo!=FALSE && $archivo!="." && $archivo!=".."){
                    //echo "es dir?".$directorio.'/'.$archivo.": ";
                    if(is_file($directorio.'/'.$archivo)){
                        $o=new stdClass();
                        $o->nombrearchivo=$archivo;
                        $o->rutacompleta=$directorio.'/'.$archivo;
                        $archivos[]=$o;
                    }
                }
            }while($archivo!=FALSE);

            @closedir($recurso);

            $this->retorno->msg="";
            $this->retorno->data=$archivos;
            $this->retorno->total=count($archivos);
            echo json_encode($this->retorno);
        }
    }


    new InterfazBackUps(new ArrayObject(array_merge($_POST, $_GET)));
?>