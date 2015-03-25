<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'EstadoServicio.php';
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
    class InterfazServiciosResumenVehiculo
        extends InterfazBase{

        const TRAER_RESUMEN=101;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){

                case InterfazServiciosResumenVehiculo::TRAER_RESUMEN:{
                    $this->traerResumen();
                    break;
                }

                default:{
                    echo json_encode($this->retorno);
                    break;
                }
            }
        }

        public function traerResumen(){
            $fechaInicial=$this->getString('fechainicial');
            $fechaFinal=$this->getString('fechafinal');

            $sql="select * from tipoautomotor where idtipoautomotor<>0 order by descripcion";
            $resultadosAutomotores=$this->conexion->consultar($sql);

            $registros=array();

            while($resultadosAutomotores->irASiguiente()){
                $registro=new stdClass();
                $registro->tipovehiculo=$resultadosAutomotores->get()->descripcion;

                $sql="select count(*) as cantidad
                    from
                    servicio
                    join automotor using(idautomotor)
                    where
                    fecharegistro between '$fechaInicial 00:00:00' and '$fechaFinal 23:59:59'
                    and
                    idestadoservicio<>".EstadoServicio::ANULADO."
                    and
                    idtipoautomotor=".$resultadosAutomotores->get()->idtipoautomotor;
                $resultados=$this->conexion->consultar($sql);
                $registro->cantidad=(int)$resultados->get(0)->cantidad;

                $sql="select sum(rubroservicio.total) as total
                    from
                    rubroservicio
                    join servicio using (idservicio)
                    join automotor using(idautomotor)
                    where
                    fecharegistro between '$fechaInicial 00:00:00' and '$fechaFinal 23:59:59'
                    and
                    idestadoservicio<>".EstadoServicio::ANULADO."
                    and
                    idtipoautomotor=".$resultadosAutomotores->get()->idtipoautomotor;
                $resultados=$this->conexion->consultar($sql);
                $registro->total=(int)$resultados->get(0)->total;
                $registro->p40=$registro->total*(0.4);
                $registro->p60=$registro->total*(0.6);

                $registros[]=$registro;
            }

            $sql="select *
                from
                servicio
                join automotor using (idautomotor)
                join tipoautomotor using (idtipoautomotor)";

            $this->retorno->data=$registros;
            $this->retorno->total=count($registros);
            $this->retorno->msg='';

            if(!empty($this->imprimir) || !empty($this->excel) || !empty($this->pdf))
                $this->generarImpresion($registros);

            echo json_encode($this->retorno);
        }

        private function generarImpresion($datos){
            $impresora=null;

            $usuario=new Usuario(FrameWork::getIdUsuario());

            $documentoImprimible=DocumentoImprimible::crearPorNombre('Resumen por Vehiculos');
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
                $impresora->caracteresPorLinea80();
                $impresora->negrita();
                $impresora->subrayada();

                $impresora->escribir('Tipo Vehiculo', null, 20, STR_PAD_RIGHT);
                $impresora->escribir('Cantidad', null, 15, STR_PAD_LEFT);
                $impresora->escribir('Total', null, 15, STR_PAD_LEFT);
                $impresora->escribir('40%', null, 15, STR_PAD_LEFT);
                $impresora->escribir('60%', null, 15, STR_PAD_LEFT);

                $impresora->nuevaLinea();
            };

            $impresora->agregarEncabezado($imprimirEncabezadoListado, $impresora, Impresora::SEGUNDO_ENCABEZADO);
            $impresora->imprimirEmcabezados(Impresora::SEGUNDO_ENCABEZADO);

            //$documentoExcel->escribirFila('Fecha', 'Modulo', 'Objeto', 'Accion', 'Descripcion', 'Usuario');
            $impresora->caracteresPorLinea80();
            $totales=array('cantidad'=>0, 'total'=>0.0, 'p40'=>0.0, 'p60'=>0.0);
            foreach ($datos as $registro) {

                $impresora->escribir($registro->tipovehiculo, null, 20, STR_PAD_RIGHT);
                $impresora->escribir(number_format($registro->cantidad, 0), null, 15, STR_PAD_LEFT);
                $impresora->escribir(number_format($registro->total, 0), null, 15, STR_PAD_LEFT);
                $impresora->escribir(number_format($registro->p40, 0), null, 15, STR_PAD_LEFT);
                $impresora->escribir(number_format($registro->p60, 0), null, 15, STR_PAD_LEFT);

                $totales['cantidad']+=(int)$registro->cantidad;
                $totales['total']+=(double)$registro->total;
                $totales['p40']+=(double)$registro->p40;
                $totales['p60']+=(double)$registro->p60;

                $impresora->nuevaLinea();
            }

            $impresora->negrita(true);

            $impresora->escribir('Totales', null, 20, STR_PAD_RIGHT);
            $impresora->escribir(number_format($totales['cantidad'], 0), null, 15, STR_PAD_LEFT);
            $impresora->escribir(number_format($totales['total'], 0), null, 15, STR_PAD_LEFT);
            $impresora->escribir(number_format($totales['p40'], 0), null, 15, STR_PAD_LEFT);
            $impresora->escribir(number_format($totales['p60'], 0), null, 15, STR_PAD_LEFT);

            $impresora->negrita(false);

            $auditoria=new Auditoria();
            $auditoria->setUsuario($usuario);
            $auditoria->setModulo(Modulo::crearPorClase('siadno.view.servicios.otros.consolidadovehiculo'));
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
    new InterfazServiciosResumenVehiculo(new ArrayObject(array_merge($_POST, $_GET)));
?>