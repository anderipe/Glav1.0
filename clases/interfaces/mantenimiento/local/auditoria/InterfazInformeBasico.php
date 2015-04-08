<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Impresora.php';
    require_once 'ImpresoraPDF.php';
    //require_once 'ImpresoraExcel.php';
    require_once 'Informe.php';
    require_once 'Auditoria.php';

/**
 * Clase controladora del modulo de informe basico de auditoria
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado
 * @subpackage interfaces
 */
    class InterfazInformeBasico
        extends InterfazBase{

        private $fechaInicial='';
        private $fechaFinal='';
        private $idUsuario=0;
        private $idModulo=0;
        private $idClase=0;
        private $descripcion='';

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $this->fechaInicial=isset($this->args['desde'])?$this->args['desde']:'';
            $this->fechaFinal=isset($this->args['hasta'])?$this->args['hasta']:'';
            $this->idUsuario=isset($this->args['idusuario'])?$this->args['idusuario']:0;
            $this->idModulo=isset($this->args['idmodulo'])?$this->args['idmodulo']:0;
            $this->idClase=isset($this->args['idclase'])?$this->args['idclase']:0;
            $this->descripcion=isset($this->args['descripcion'])?$this->args['descripcion']:'';

            $cantidadTotal=0;
            $registros=Auditoria::consultar($this->fechaInicial, $this->fechaFinal, $this->idUsuario, $this->idModulo, $this->idClase, $this->descripcion, $cantidadTotal, $this->offSet, $this->limit);

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
            $registros=$registros=Auditoria::consultar($this->fechaInicial, $this->fechaFinal, $this->idUsuario, $this->idModulo, $this->idClase, $this->descripcion, $cantidadTotal, null, null);

            $documentoImprimible=DocumentoImprimible::crearPorNombre('Informe de Auditoria Basico');
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
                $impresora->escribir('Fecha', null, 20, STR_PAD_RIGHT);
                $impresora->escribir('Modulo', null, 40, STR_PAD_RIGHT);
                $impresora->escribir('Objeto', null, 15, STR_PAD_RIGHT);
                $impresora->escribir('Accion', null, 15, STR_PAD_RIGHT);
                $impresora->escribir('Descripcion', null, 55, STR_PAD_RIGHT);
                $impresora->escribir('Usuario', null, 15, STR_PAD_RIGHT);
                $impresora->nuevaLinea();
            };

            $impresora->agregarEncabezado($imprimirEncabezadoListado, $impresora, Impresora::SEGUNDO_ENCABEZADO);
            $impresora->imprimirEmcabezados(Impresora::SEGUNDO_ENCABEZADO);

            //$documentoExcel->escribirFila('Fecha', 'Modulo', 'Objeto', 'Accion', 'Descripcion', 'Usuario');

            $modulo=array();
            $clase=array();
            $accion=array();
            $persona=array();
            $impresora->caracteresPorLinea160();
            while($registros->irASiguiente()){
                $impresora->escribir($registros->get()->fechaauditoria, null, 20, STR_PAD_RIGHT);

                $idModulo=$registros->get()->idmodulo;
                if(!isset($modulo[(string)$idModulo])){
                    $modulo[(string)$idModulo]=new Modulo($idModulo);
                    $modulo[(string)$idModulo]=$modulo[(string)$idModulo]->getClase();
                }
                $nombreModulo=mb_substr($modulo[(string)$idModulo], 12);
                $impresora->escribir($nombreModulo, 38, 40, STR_PAD_RIGHT);

                $idClase=$registros->get()->idclase;
                if(!isset($clase[(string)$idClase])){
                    $clase[(string)$idClase]=new Clase($idClase);
                    $clase[(string)$idClase]=$clase[(string)$idClase]->getNombre();
                }
                $impresora->escribir($clase[(string)$idClase], 13, 15, STR_PAD_RIGHT);

                $idAccion=$registros->get()->idaccionauditable;
                if(!isset($accion[(string)$idAccion])){
                    $accion[(string)$idAccion]=new AccionAuditable($idAccion);
                    $accion[(string)$idAccion]=$accion[(string)$idAccion]->getNombre();
                }
                $impresora->escribir($accion[(string)$idAccion], 13, 15, STR_PAD_RIGHT);

                $impresora->escribir($registros->get()->descripcion, 53, 55, STR_PAD_RIGHT);

                $idUsuario=$registros->get()->idusuario;
                if(!isset($persona[(string)$idUsuario])){
                    $usuarioTmp=new Usuario($idUsuario);
                    $personaTmp=$usuarioTmp->getPersona();
                    $persona[(string)$idUsuario]=$personaTmp->getApellido().' '.$personaTmp->getNombre();
                }
                $impresora->escribir($persona[(string)$idUsuario], 15, 15, STR_PAD_RIGHT);

                $impresora->nuevaLinea();
            }

            $auditoria=new Auditoria();
            $auditoria->setUsuario($usuario);
            $auditoria->setModulo(Modulo::crearPorClase('siadno.view.administracion.local.auditoria.informe'));
            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::IMPRESION));

            $impresion->setContenido($impresora);
            $this->conexion->ejecutar('begin;');
            $auditoria->guardarObjeto(null);
            $impresion->guardarObjeto($auditoria);
            $this->conexion->ejecutar('commit;');

            $this->retorno->impresion=$impresion->getIdImpresion();
            //$dateTime=new DateTime();
            //$this->retorno->archivo='c:\\tmp\\prueba '.$dateTime->format('Y-m-d-h-i-s').'.txt';
            $this->retorno->archivo='c:\\tmp\\prueba.prnt';
            $this->retorno->msg='';
        }
    }
    new InterfazInformeBasico(new ArrayObject(array_merge($_POST, $_GET)));
?>