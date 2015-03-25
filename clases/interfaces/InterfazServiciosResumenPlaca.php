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
    class InterfazServiciosResumenPlaca
        extends InterfazBase{

        const TRAER_RESUMEN=101;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){

                case InterfazServiciosResumenPlaca::TRAER_RESUMEN:{
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
            $placa=$this->getString('placa');

            $sql="select servicio.idservicio, servicio.fecharegistro, servicio.fechaentrega, replace(persona.nombres, '|', ' ') as nombres, timediff(servicio.fechaentrega, servicio.fecharegistro) as tiempo
                from
                servicio
                join automotor using (idautomotor)
                left join empleado using (idempleado)
                left join persona on (persona.idpersona=empleado.idpersona)
                where
                automotor.matricula='".mysql_real_escape_string($placa)."'
                and
                idestadoservicio<>".EstadoServicio::ANULADO."
                order by
                servicio.fecharegistro";

            $resultadosAutomotores=$this->conexion->consultar($sql);

            $registros=array();

            while($resultadosAutomotores->irASiguiente()){
                $registro=new stdClass();
                $registro=$resultadosAutomotores->get();
                $idServicio=(int)$registro->idservicio;

                $sql="select sum(total) as total from rubroservicio where idservicio=".$idServicio;
                $resultados=$this->conexion->consultar($sql);
                $registro->total=(double)$resultados->get(0)->total;

                $sql="select rubro.descripcion from rubro join rubroservicio using (idrubro) where idservicio=".$idServicio;
                $resultados=$this->conexion->consultar($sql);
                $registro->servicios=array();
                while($resultados->irASiguiente()){
                    $registro->servicios[]=$resultados->get()->descripcion;
                }
                $registro->servicios=  implode(', ', $registro->servicios);

                $registros[]=$registro;
            }

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

            $documentoImprimible=DocumentoImprimible::crearPorNombre('Resumen por Matricula');
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
                $impresora->caracteresPorLinea160();
                $impresora->negrita();
                $impresora->subrayada();

                $impresora->escribir('Fecha y Hora', null, 20, STR_PAD_RIGHT);
                $impresora->escribir('Operario', null, 30, STR_PAD_RIGHT);
                $impresora->escribir('Servicios', null, 70, STR_PAD_RIGHT);
                $impresora->escribir('Costo', null, 10, STR_PAD_LEFT);
                $impresora->escribir('Salida', null, 20, STR_PAD_LEFT);
                $impresora->escribir('Tiempo', null, 10, STR_PAD_LEFT);

                $impresora->nuevaLinea();
            };

            $impresora->agregarEncabezado($imprimirEncabezadoListado, $impresora, Impresora::SEGUNDO_ENCABEZADO);
            $impresora->imprimirEmcabezados(Impresora::SEGUNDO_ENCABEZADO);

            //$documentoExcel->escribirFila('Fecha', 'Modulo', 'Objeto', 'Accion', 'Descripcion', 'Usuario');
            $impresora->caracteresPorLinea160();

            foreach ($datos as $registro) {

                $impresora->escribir($registro->fecharegistro, null, 20, STR_PAD_RIGHT);
                $impresora->escribir(mb_substr($registro->nombres, 0, 29) , null, 30, STR_PAD_RIGHT);
                $impresora->escribir(mb_substr($registro->servicios, 0, 69), null, 70, STR_PAD_RIGHT);
                $impresora->escribir(number_format($registro->total, 0), null, 10, STR_PAD_LEFT);
                $impresora->escribir($registro->fechaentrega, null, 20, STR_PAD_LEFT);
                $impresora->escribir($registro->tiempo, null, 10, STR_PAD_LEFT);

                $impresora->nuevaLinea();
            }


            $auditoria=new Auditoria();
            $auditoria->setUsuario($usuario);
            $auditoria->setModulo(Modulo::crearPorClase('siadno.view.servicios.otros.resumenplaca'));
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
    new InterfazServiciosResumenPlaca(new ArrayObject(array_merge($_POST, $_GET)));
?>