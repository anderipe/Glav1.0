<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';

/**
 * Clase controladora del modulo deverificacion de integridad
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazIntegridad
        extends InterfazBase{

        const VERIFICAR_HASH=101;
        const VERIFICAR_FIRMA=102;
        const VERIFICAR_HASHYFIRMA=103;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=$this->getInt('accion');

            switch($accion){
                case InterfazIntegridad::VERIFICAR_HASH:{
                    function __autoload($class_name) {
                        require_once $class_name . '.php';
                    }
                    $totalRegistro=0;

                    $tabla=$this->getString('tabla');
                    $indice=$this->getInt('indice');

                    $campoId='id'.$tabla;
                    $clase=Clase::crearPorTabla($tabla);
                    $nombreClase=$clase->getNombre();
                    //require_once $nombreClase.".php";
                    $resultadoVerificacion=1;
                    $totalIncorrectos=0;
                    $totalCorrectos=0;
                    $errores=array();
                    do{
                        $sql='select '.$campoId.' as id from '.$tabla.' order by '.$campoId.' limit 1000 offset '.$totalRegistro;
                        $resultados=$this->conexion->consultar($sql);
                        $totalRetornados=$resultados->getCantidad();
                        $totalRegistro+=$totalRetornados;
                        while($resultados->irASiguiente()){
                            $creacionObjeto='return new '.$nombreClase.'('.$resultados->get()->id.');';
                            $objeto=eval($creacionObjeto);
                            if($objeto->verificarHash()){
                                $totalCorrectos++;
                            }else{
                                $resultadoVerificacion=0;
                                $totalIncorrectos++;
                                $objetoError=new stdClass();
                                $objetoError->id=$resultados->get()->id;
                                $objetoError->error='El hash calculado difiere del guardado';
                                break 2;

                                $errores[]=$objetoError;
                            }
//                            $hash=$objeto->calcularHash();
//                            $sql='update '.$tabla.' set hash=\''.$hash.'\' where '.$campoId.'='.$resultados->get()->id;
//                            $this->conexion->ejecutar($sql);
//                            $firma=$objeto->calcularFirma();
//                            $sql='update '.$tabla.' set firma=\''.$firma.'\' where '.$campoId.'='.$resultados->get()->id;
//                            $this->conexion->ejecutar($sql);
                            //echo "$creacionObjeto:".  var_dump($objeto);
                            //echo "<br>";
                        }
                    }while($totalRetornados>0);


                    $this->retorno->msg='';
                    $this->retorno->data=$errores;
                    $this->retorno->tabla=$tabla;
                    $this->retorno->indice=$indice;
                    $this->retorno->registros=$totalRegistro;
                    $this->retorno->correctos=$totalCorrectos;
                    $this->retorno->incorrectos=$totalIncorrectos;
                    $this->retorno->verificacionhash=$resultadoVerificacion;

                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazIntegridad::VERIFICAR_FIRMA:{
                    function __autoload($class_name) {
                        require_once $class_name . '.php';
                    }
                    $totalRegistro=0;

                    $tabla=$this->getString('tabla');
                    $indice=$this->getInt('indice');

                    $campoId='id'.$tabla;
                    $clase=Clase::crearPorTabla($tabla);
                    $nombreClase=$clase->getNombre();
                    //require_once $nombreClase.".php";
                    $resultadoVerificacion=1;
                    $totalIncorrectos=0;
                    $totalCorrectos=0;
                    $errores=array();
                    do{
                        $sql='select '.$campoId.' as id from '.$tabla.' order by '.$campoId.' limit 1000 offset '.$totalRegistro;
                        $resultados=$this->conexion->consultar($sql);
                        $totalRetornados=$resultados->getCantidad();
                        $totalRegistro+=$totalRetornados;
                        while($resultados->irASiguiente()){
                            $creacionObjeto='return new '.$nombreClase.'('.$resultados->get()->id.');';
                            $objeto=eval($creacionObjeto);
                            if($objeto->verificarFirma()){
                                $totalCorrectos++;
                            }else{
                                $resultadoVerificacion=0;
                                $totalIncorrectos++;
                                $objetoError=new stdClass();
                                $objetoError->id=$resultados->get()->id;
                                $objetoError->error='La verificacion de firma ha sido incorrecta';
                                break 2;

                                $errores[]=$objetoError;
                            }
                        }
                    }while($totalRetornados>0);


                    $this->retorno->msg='';
                    $this->retorno->data=$errores;
                    $this->retorno->tabla=$tabla;
                    $this->retorno->indice=$indice;
                    $this->retorno->registros=$totalRegistro;
                    $this->retorno->correctos=$totalCorrectos;
                    $this->retorno->incorrectos=$totalIncorrectos;
                    $this->retorno->verificacionfirma=$resultadoVerificacion;

                    echo json_encode($this->retorno);
                    break;
                }

                case InterfazIntegridad::VERIFICAR_HASHYFIRMA:{
                    function __autoload($class_name) {
                        require_once $class_name . '.php';
                    }
                    $totalRegistro=0;

                    $tabla=$this->getString('tabla');
                    $indice=$this->getInt('indice');

                    $campoId='id'.$tabla;
                    $clase=Clase::crearPorTabla($tabla);
                    $nombreClase=$clase->getNombre();
                    //require_once $nombreClase.".php";
                    $resultadoVerificacionHash=1;
                    $resultadoVerificacionFirma=1;
                    $totalIncorrectos=0;
                    $totalCorrectos=0;
                    $errores=array();
                    do{
                        $sql='select '.$campoId.' as id from '.$tabla.' order by '.$campoId.' limit 1000 offset '.$totalRegistro;
                        $resultados=$this->conexion->consultar($sql);
                        $totalRetornados=$resultados->getCantidad();
                        $totalRegistro+=$totalRetornados;
                        while($resultados->irASiguiente()){
                            $creacionObjeto='return new '.$nombreClase.'('.$resultados->get()->id.');';
                            $objeto=eval($creacionObjeto);
                            $objetoError=null;
                            if($objeto->verificarHash()){
                                $totalCorrectos++;
                            }else{
                                $resultadoVerificacionHash=0;
                                $totalIncorrectos++;
                                $objetoError=new stdClass();
                                $objetoError->id=$resultados->get()->id;
                                $objetoError->error='El hash calculado difiere del guardado';

                            }

                            if($objeto->verificarFirma()){
                                $totalCorrectos++;
                            }else{
                                $resultadoVerificacionFirma=0;
                                $totalIncorrectos++;
                                if($objetoError==null){
                                    $objetoError=new stdClass();
                                    $objetoError->id=$resultados->get()->id;
                                    $objetoError->error='La verificacion de firma ha sido incorrecta';
                                }else{
                                    $objetoError->error='La verificacion de hash y firma han fallado';
                                }
                            }

                            if($objetoError!=null)
                                $errores[]=$objetoError;
//                            $hash=$objeto->calcularHash();
//                            $sql='update '.$tabla.' set hash=\''.$hash.'\' where '.$campoId.'='.$resultados->get()->id;
//                            $this->conexion->ejecutar($sql);
//                            $firma=$objeto->calcularFirma();
//                            $sql='update '.$tabla.' set firma=\''.$firma.'\' where '.$campoId.'='.$resultados->get()->id;
//                            $this->conexion->ejecutar($sql);
                            //echo "$creacionObjeto:".  var_dump($objeto);
                            //echo "<br>";
                        }
                    }while($totalRetornados>0);


                    $this->retorno->msg='';
                    $this->retorno->data=$errores;
                    $this->retorno->tabla=$tabla;
                    $this->retorno->indice=$indice;
                    $this->retorno->registros=$totalRegistro;
                    $this->retorno->correctos=$totalCorrectos;
                    $this->retorno->incorrectos=$totalIncorrectos;
                    $this->retorno->verificacionhash=$resultadoVerificacionHash;
                    $this->retorno->verificacionfirma=$resultadoVerificacionFirma;

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
                        $tablas[$i]->verificacionhash=-1;
                        $tablas[$i]->verificacionfirma=-1;
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
    new InterfazIntegridad(new ArrayObject(array_merge($_POST, $_GET)));
?>