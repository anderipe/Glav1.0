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
    require_once 'Empleado.php';

/**
 * Clase controladora del modulo que genera un listado de empleados
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazListadoImpreados
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);

            $cantidadTotal=0;
            $registros=Empleado::consultar($cantidadTotal, $this->offSet, $this->limit);

            $this->retorno->data=$registros->getRegistros();
            $this->retorno->total=$cantidadTotal;

            if(!empty($this->imprimir) || !empty($this->excel) || !empty($this->pdf))
                $this->generarImpresion();

            echo json_encode($this->retorno);
        }

        private function generarImpresion(){
            $impresora=null;

            $usuario=new Usuario(FrameWork::getIdUsuario());
            $cantidadTotal=null;
            $registros=$registros=Empleado::consultar($cantidadTotal, null, null);

            $documentoImprimible=DocumentoImprimible::crearPorNombre('Informe de Empleados Basico');
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
                $impresora->escribir('Identificacion', null, 30, STR_PAD_RIGHT);
                $impresora->escribir('Nombres', null, 40, STR_PAD_RIGHT);
                $impresora->escribir('Direccion', null, 30, STR_PAD_RIGHT);
                $impresora->escribir('Telefonos', null, 30, STR_PAD_RIGHT);
                $impresora->escribir('Estado', null, 30, STR_PAD_RIGHT);
                $impresora->nuevaLinea();
            };

            $impresora->agregarEncabezado($imprimirEncabezadoListado, $impresora, Impresora::SEGUNDO_ENCABEZADO);
            $impresora->imprimirEmcabezados(Impresora::SEGUNDO_ENCABEZADO);

            //$documentoExcel->escribirFila('Fecha', 'Modulo', 'Objeto', 'Accion', 'Descripcion', 'Usuario');
            $impresora->caracteresPorLinea160();
            while($registros->irASiguiente()){
                $impresora->escribir($registros->get()->identificacion, null, 30, STR_PAD_RIGHT);
                $impresora->escribir($registros->get()->nombres, null, 40, STR_PAD_RIGHT);
                $impresora->escribir($registros->get()->direccion, null, 30, STR_PAD_RIGHT);
                $impresora->escribir($registros->get()->telefonos, null, 30, STR_PAD_RIGHT);
                $impresora->escribir($registros->get()->estado, null, 30, STR_PAD_RIGHT);
                $impresora->nuevaLinea();
            }

            $auditoria=new Auditoria();
            $auditoria->setUsuario($usuario);
            $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.local.informeempleados'));
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
    new InterfazListadoImpreados(new ArrayObject(array_merge($_POST, $_GET)));
?>