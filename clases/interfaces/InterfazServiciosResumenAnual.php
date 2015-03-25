<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Impresora.php';
    require_once 'ImpresoraPDF.php';
    require_once 'ImpresoraExcel.php';
    require_once 'Informe.php';


/**
 * Clase controladora del modulo de administracion de movimientos creditos
 * para los empleados
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazServiciosResumenAnual
        extends InterfazBase{

        const LISTAR_GASTOS=101;
        const TRAER_RESUMEN=102;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                case InterfazServiciosResumenAnual::LISTAR_GASTOS:{
                    $this->listarGastos();
                    break;
                }

                case InterfazServiciosResumenAnual::TRAER_RESUMEN:{
                    $this->traerResumen();
                    break;
                }

                default:{
                    echo json_encode($this->retorno);
                    break;
                }
            }
        }

        public function listarGastos(){
            $sql='select * from tipogasto order by gasto';
            $resultados=$this->conexion->consultar($sql);

            $this->retorno->data=$resultados->getRegistros();

            $this->retorno->msg='';
            echo json_encode($this->retorno);
        }

        public function traerResumen(){
            $fechaInicial=$this->getString('fechainicial');
            $fechaFinal=$this->getString('fechafinal');

            $sql='select * from tipogasto order by gasto';
            $gastos=$this->conexion->consultar($sql);

            $custom_date = strtotime( date('Y-m-d', strtotime($fechaInicial)) );
            $anioInicial=(int)date('Y', $custom_date);
            $mesInicial=(int)date('m', $custom_date);

            $custom_date = strtotime( date('Y-m-d', strtotime($fechaFinal)) );
            $anioFinal=(int)date('Y', $custom_date);
            $mesFinal=(int)date('m', $custom_date);

            $datos=array();

            for($anio=$anioInicial; $anio<=$anioFinal; $anio++){
                if($anio==$anioInicial)
                    $mes=$mesInicial;
                else
                    $mes=1;

                if($anio==$anioFinal)
                    $mesf=$mesFinal;
                else
                    $mesf=12;


                for(; $mes<=$mesf; $mes++){
                    $o=new stdClass();
                    $o->fecha="$anio/".str_pad($mes, 2, "0", STR_PAD_LEFT);
                    $o->total=0.0;

                    $fechaInicio = "$anio-$mes-1";
                    $fechaFin = date('Y-m-t', strtotime($fechaInicio));

                    //$o->fecha=$fechaInicio."-".$fechaFin;

                    $gastos->reiniciar();
                    while($gastos->irASiguiente()){
                        $campo='id_gasto_'.$gastos->get()->idtipogasto;
                        $sql="select valor from gastodiario where idtipogasto=".$gastos->get()->idtipogasto." and fecha between '$fechaInicio 00:00:00' and '$fechaFin 23:59:59'";
                        $resultados=$this->conexion->consultar($sql);
                        $o->$campo=0.0;
                        if($resultados->getCantidad()!=0){
                            if($gastos->get()->gasto==1)
                                $o->$campo=(float)$resultados->get(0)->valor*-1.0;
                            else
                                $o->$campo=(float)$resultados->get(0)->valor*1.0;

                            $o->total+=$o->$campo;
                        }
                    }

                    $datos[]=$o;
                }
            }



            $this->retorno->fechaInicio=$fechaInicio;
            $this->retorno->fechaFin=$fechaFin;
            $this->retorno->data=$datos;
            $this->retorno->msg='';

            if(!empty($this->imprimir) || !empty($this->excel) || !empty($this->pdf))
                $this->generarImpresion($datos);
            echo json_encode($this->retorno);
        }

        private function generarImpresion($datos){
            $impresora=null;

            $usuario=new Usuario(FrameWork::getIdUsuario());

            $sql='select * from tipogasto order by gasto';
            $resultados=$this->conexion->consultar($sql);

            global $arrayNombres;
            $arrayNombres=$resultados->getRegistros();

            foreach ($arrayNombres as $key => $value) {
                $nombres='';
                $nombres= $value->descripcion;
                $nombres=explode(' ', $nombres);
                //if(isset($nombres[0]))
                //    $nombres[0]=  mb_substr($nombres[0], 0, 2);

                if(isset($nombres[1]))
                    $nombres[1]=  mb_substr($nombres[1], 0, 1);

                if(isset($nombres[2]))
                    $nombres[2]=  mb_substr($nombres[2], 0, 1);

                if(isset($nombres[3]))
                    $nombres[3]=  mb_substr($nombres[3], 0, 1);

                if(isset($nombres[4]))
                    $nombres[4]=  mb_substr($nombres[4], 0, 1);

                if(isset($nombres[5]))
                    $nombres[5]=  mb_substr($nombres[5], 0, 1);

                if(isset($nombres[6]))
                    $nombres[6]=  mb_substr($nombres[6], 0, 1);

                $arrayNombres[$key]->abreviatura=  implode('.', $nombres);
            }

            $documentoImprimible=DocumentoImprimible::crearPorNombre('Resumen Anual');
            $impresion=new Impresion();
            $impresion->cargarPorDocumento($usuario, $documentoImprimible);
            $impresion->setUsuario($usuario);
            $impresion->setDocumentoImprimible($documentoImprimible);
            $impresion->setComentarios('');
            $impresion->setEstadoImpresion(new EstadoImpresion(EstadoImpresion::SIN_IMPRIMIR));

            if($this->imprimir){
                $impresora=new Impresora();
                $impresora->cargarPorTipoImpresora($usuario, new TipoImpresora(TipoImpresora::GENERAL), true);
                $this->retorno->impresora=$impresora->getNombre();
            }elseif($this->pdf){
                $impresora=new ImpresoraPDF();
            }elseif($this->excel){
                $impresora=new ImpresoraExcel();
            }

            $impresora->setImpresion($impresion);
            $impresora->iniciarImpresion();
            $impresora->imprimirEmcabezados(Impresora::PRIMER_ENCABEZADO);

            $imprimirEncabezadoListado=function(ImpresoraBase $impresora){
                global $arrayNombres;
                $impresora->caracteresPorLinea160();
                $impresora->negrita();
                $impresora->subrayada();

                $impresora->escribir('Fecha', null, 12, STR_PAD_RIGHT);
                foreach($arrayNombres as $valor){
                    $impresora->escribir($valor->abreviatura, null, 15, STR_PAD_LEFT);
                }
                $impresora->escribir('NETO', null, 13, STR_PAD_LEFT);

                $impresora->nuevaLinea();
            };

            $impresora->agregarEncabezado($imprimirEncabezadoListado, $impresora, Impresora::SEGUNDO_ENCABEZADO);
            $impresora->imprimirEmcabezados(Impresora::SEGUNDO_ENCABEZADO);

            $impresora->caracteresPorLinea160();
            $totales=array();
            $totales['total']=0.0;
            foreach ($datos as $registro) {
                $primerColumna=true;
                $registro =  (array)$registro;
                $impresora->escribir($registro['fecha'], null, 12, STR_PAD_RIGHT);

                for($i=0; $i<count($arrayNombres); $i++){
                    $indice='id_gasto_'.$arrayNombres[$i]->idtipogasto;
                    $dato=$registro[$indice];
                    if(!isset($totales[$indice]))
                        $totales[$indice]=0.0;

                    $totales[$indice]+=($dato*1.0);
                    $impresora->escribir('$'.number_format($dato), null, 15, STR_PAD_LEFT);

                }

                $totales['total']+=$registro['total'];
                $impresora->escribir('$'.number_format($registro['total']), null, 13, STR_PAD_LEFT);
                $impresora->nuevaLinea();
            }

//            $impresora->negrita(true);
//            for($i=0; $i<count($registro); $i++){
//                $indice='';
//                $dato=$registro[$indice];
//                if($i==0)
//                    $impresora->escribir('Totales', null, 20, STR_PAD_RIGHT);
//                elseif($i==count($registro)-1)
//                    $impresora->escribir('$'.number_format($dato), null, 13, STR_PAD_LEFT);
//                else
//                    $impresora->escribir('$'.number_format($dato), null, 15, STR_PAD_LEFT);
//
//            }
//            $impresora->negrita(false);

            $impresora->negrita(true);
            $impresora->escribir('Totales', null, 12, STR_PAD_RIGHT);

            for($i=0; $i<count($arrayNombres); $i++){
                $indice='id_gasto_'.$arrayNombres[$i]->idtipogasto;
                $dato=$totales[$indice];
                $impresora->escribir('$'.number_format($dato), null, 15, STR_PAD_LEFT);

            }

            $impresora->escribir('$'.number_format($totales['total']), null, 13, STR_PAD_LEFT);
            $impresora->negrita(false);


            $auditoria=new Auditoria();
            $auditoria->setUsuario($usuario);
            $auditoria->setModulo(Modulo::crearPorClase('siadno.view.servicios.otros.resumenanual'));
            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::IMPRESION));

            $impresion->setContenido($impresora);
            $this->conexion->ejecutar('begin;');
            $auditoria->guardarObjeto(null);
            $impresion->guardarObjeto($auditoria);
            $this->conexion->ejecutar('commit;');

            $this->retorno->impresion=$impresion->getIdImpresion();
            //$dateTime=new DateTime();
            //$this->retorno->archivo='c:\\tmp\\prueba '.$dateTime->format('Y-m-d-h-i-s').'.txt';
            //$this->retorno->archivo='c:\\tmp\\prueba.prnt';
            $this->retorno->msg='';
        }
    }
    new InterfazServiciosResumenAnual(new ArrayObject(array_merge($_POST, $_GET)));
?>