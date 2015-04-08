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
    class InterfazRegistroResumenDiario
        extends InterfazBase{

        const LISTAR_EMPLEADOS=101;
        const TRAER_RESUMEN=102;
        const GUARDAR_CAMPO=103;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                case InterfazRegistroResumenDiario::LISTAR_EMPLEADOS:{
                    $this->listarEmpleados();
                    break;
                }

                case InterfazRegistroResumenDiario::TRAER_RESUMEN:{
                    $this->traerResumen();
                    break;
                }

                case InterfazRegistroResumenDiario::GUARDAR_CAMPO:{
                    $this->guardarCampo();
                    break;
                }

                default:{
                    echo json_encode($this->retorno);
                    break;
                }
            }
        }

        public function guardarCampo(){
            $idEmpleado=$this->getInt('idempleado');
            $fecha=$this->getString('fecha');
            $valor=$this->getDouble('valor');
            $campo=$this->getInt('campo');

            if($campo==3)
                $set='prestamo='.$valor;
            if($campo==4)
                $set='otros='.$valor;
            if($campo==6)
                $set='noentregado='.$valor;

            $sql="update registrodiario
                set $set
                where
                idempleado=".$idEmpleado." and fecha='". mysql_real_escape_string($fecha) ."'";
            $this->conexion->ejecutar($sql);

            $sql="update registrodiario
                set saldo=empleado-prestamo-otros, entregado=facturado-noentregado
                where
                idempleado=".$idEmpleado." and fecha='". mysql_real_escape_string($fecha) ."'";
            $this->conexion->ejecutar($sql);
            $this->retorno->msg='';
            echo json_encode($this->retorno);
        }

        public function traerResumen(){
            $fecha=$this->getString('fecha');
            $datos=array();
            $datos[0]=new stdClass();
            $datos[1]=new stdClass();
            $datos[2]=new stdClass();
            $datos[3]=new stdClass();
            $datos[4]=new stdClass();
            $datos[5]=new stdClass();
            $datos[6]=new stdClass();
            $datos[7]=new stdClass();

            $datos[0]->d0='FACTURADO.';
            $datos[1]->d0='40%';
            $datos[2]->d0='60%';
            $datos[3]->d0='PRESTAMOS';
            $datos[4]->d0='OTROS';
            $datos[5]->d0='SALDO OPERARIO';
            $datos[6]->d0='SIN ENTREGAR';
            $datos[7]->d0='ENTREGADO X OPER';


            $sql='select persona.nombres, empleado.idempleado
                from
                empleado
                join persona using (idpersona)
                where
                empleado.estado=true
                ';
            $empleados=$this->conexion->consultar($sql);
            $numEmpleados=$empleados->getCantidad();
            $indiceTotales=$numEmpleados+1;

            $totalFinal=0.0;
            $valorempleadoFinal=0.0;
            $valorempleadorFinal=0.0;
            $prestamosFinal=0.0;
            $otrosFinal=0.0;
            $saldoFinal=0.0;
            $sinentregarFinal=0.0;
            $entregadoFinal=0.0;

            foreach ($empleados->getRegistros() as $key => $empleado) {
                $propiedad='d'.($key+1);
                $total=0.0;
                $valorempleado=0.0;
                $valorempleador=0.0;
                $prestamos=0.0;
                $otros=0.0;
                $saldo=0.0;
                $sinentregar=0.0;
                $entregado=0.0;

                $sql='select * from registrodiario
                    where
                    idempleado='.$empleado->idempleado.'
                    and
                    fecha=\''.mysql_real_escape_string($fecha).'\'';
                $resultadosRegistro=$this->conexion->consultar($sql);

                $sql='select sum(rubroservicio.total) as total from
                servicio
                join
                rubroservicio using(idservicio)
                where
                idestadoservicio<>'.EstadoServicio::ANULADO.'
                and
                idempleado='.$empleado->idempleado.'
                and
                fecharegistro between \''.mysql_real_escape_string($fecha.' 00:00:00').'\' and \''.mysql_real_escape_string($fecha.' 23:59:59').'\'
                ';
                $this->retorno->sql=$sql;
                $resultados=$this->conexion->consultar($sql);

                $total=(float)$resultados->get(0)->total;
                $valorempleado=(float)$total*0.4;
                $valorempleador=(float)$total*0.6;
                $prestamos=(float)0.0;
                $otros=(float)0.0;
                $saldo=$valorempleado-$prestamos-$otros;
                $sinentregar=(float)0.0;
                $entregado=$total-$sinentregar;


                if($resultadosRegistro->getCantidad()==0){
                    $sql='insert into registrodiario
                        (idempleado, fecha, facturado, empleado, empleador, prestamo, otros, saldo, noentregado, entregado)
                        values
                        ('.$empleado->idempleado.', \''.mysql_real_escape_string($fecha).'\', '.$total.', '.$valorempleado.', '.$valorempleador.', '.$prestamos.', '.$otros.', '.$saldo.', '.$sinentregar.', '.$entregado.');
                        ';
                    $this->conexion->ejecutar($sql);
                }else{
                    if($total!=$resultadosRegistro->get(0)->facturado){
                        $sql='update registrodiario
                            set
                            facturado='.$total.',
                            empleado='.$valorempleado.',
                            empleador='.$valorempleador.'
                            where
                            idempleado='.$empleado->idempleado.'
                            and
                            fecha=\''.mysql_real_escape_string($fecha).'\'';
                        $this->conexion->ejecutar($sql);

                        $sql="update registrodiario
                            set saldo=empleado-prestamo-otros, entregado=facturado-noentregado
                            where
                            idempleado=".$empleado->idempleado." and fecha='". mysql_real_escape_string($fecha) ."'";
                        $this->conexion->ejecutar($sql);

                        $sql='select * from registrodiario
                            where
                            idempleado='.$empleado->idempleado.'
                            and
                            fecha=\''.mysql_real_escape_string($fecha).'\'';
                        $resultadosRegistro=$this->conexion->consultar($sql);
                    }
                    $total=(float)$resultadosRegistro->get(0)->facturado;
                    $valorempleado=(float)$resultadosRegistro->get(0)->empleado;
                    $valorempleador=(float)$resultadosRegistro->get(0)->empleador;
                    $prestamos=(float)$resultadosRegistro->get(0)->prestamo;
                    $otros=(float)$resultadosRegistro->get(0)->otros;
                    $saldo=(float)$resultadosRegistro->get(0)->saldo;
                    $sinentregar=(float)$resultadosRegistro->get(0)->noentregado;
                    $entregado=(float)$resultadosRegistro->get(0)->entregado;
                }

                $datos[0]->$propiedad=(float)$total;
                $datos[1]->$propiedad=(float)$valorempleado;
                $datos[2]->$propiedad=(float)$valorempleador;
                $datos[3]->$propiedad=(float)$prestamos;
                $datos[4]->$propiedad=(float)$otros;
                $datos[5]->$propiedad=(float)$saldo;
                $datos[6]->$propiedad=(float)$sinentregar;
                $datos[7]->$propiedad=(float)$entregado;

                $totalFinal+=$datos[0]->$propiedad;
                $valorempleadoFinal+=$datos[1]->$propiedad;
                $valorempleadorFinal+=$datos[2]->$propiedad;
                $prestamosFinal+=$datos[3]->$propiedad;
                $otrosFinal+=$datos[4]->$propiedad;
                $saldoFinal+=$datos[5]->$propiedad;
                $sinentregarFinal+=$datos[6]->$propiedad;
                $entregadoFinal+=$datos[7]->$propiedad;
            }

            $propiedad='d'.$indiceTotales;
            $datos[0]->$propiedad=(float)$totalFinal;
            $datos[1]->$propiedad=(float)$valorempleadoFinal;
            $datos[2]->$propiedad=(float)$valorempleadorFinal;
            $datos[3]->$propiedad=(float)$prestamosFinal;
            $datos[4]->$propiedad=(float)$otrosFinal;
            $datos[5]->$propiedad=(float)$saldoFinal;
            $datos[6]->$propiedad=(float)$sinentregarFinal;
            $datos[7]->$propiedad=(float)$entregadoFinal;


            $this->retorno->data=$datos;
            $this->retorno->msg='';

            if(!empty($this->imprimir) || !empty($this->excel) || !empty($this->pdf))
                $this->generarImpresion($datos);

            echo json_encode($this->retorno);
        }

        public function listarEmpleados(){
            $sql='select persona.nombres, empleado.idempleado
                from
                empleado
                join persona using (idpersona)
                where
                empleado.estado=true
                ';
            $resultados=$this->conexion->consultar($sql);

            $this->retorno->data=$resultados->getRegistros();

            foreach ($this->retorno->data as $key => $value) {
                $nombres= Auxiliar::mb_str_replace('|', ' ', $value->nombres);
                $nombres=explode(' ', $nombres);
                //if(isset($nombres[0]))
                //    $nombres[0]=  mb_substr($nombres[0], 0, 2);

                if(isset($nombres[1]))
                    $nombres[1]=  mb_substr($nombres[1], 0, 1);

                if(isset($nombres[2]))
                    $nombres[2]=  mb_substr($nombres[2], 0, 1);

                if(isset($nombres[3]))
                    $nombres[3]=  mb_substr($nombres[3], 0, 1);

                $this->retorno->data[$key]->abreviatura=  implode('.', $nombres);
            }

            $this->retorno->msg='';
            echo json_encode($this->retorno);
        }

        private function generarImpresion($datos){
            $impresora=null;

            $usuario=new Usuario(FrameWork::getIdUsuario());

            $sql='select persona.nombres, empleado.idempleado
                from
                empleado
                join persona using (idpersona)
                where
                empleado.estado=true
                ';
            $resultados=$this->conexion->consultar($sql);

            global $arrayNombres;
            $arrayNombres=$resultados->getRegistros();
            foreach ($arrayNombres as $key => $value) {
                $nombres= Auxiliar::mb_str_replace('|', ' ', $value->nombres);
                $nombres=explode(' ', $nombres);
                //if(isset($nombres[0]))
                //    $nombres[0]=  mb_substr($nombres[0], 0, 2);

                if(isset($nombres[1]))
                    $nombres[1]=  mb_substr($nombres[1], 0, 1);

                if(isset($nombres[2]))
                    $nombres[2]=  mb_substr($nombres[2], 0, 1);

                if(isset($nombres[3]))
                    $nombres[3]=  mb_substr($nombres[3], 0, 1);

                $arrayNombres[$key]->abreviatura=  implode('.', $nombres);
            }
            //$registros=$registros=Empleado::consultar($cantidadTotal, null, null);

            $documentoImprimible=DocumentoImprimible::crearPorNombre('Resumen Diario');
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

                $impresora->escribir('Concepto', null, 20, STR_PAD_RIGHT);
                foreach($arrayNombres as $valor){
                    $impresora->escribir($valor->abreviatura, null, 15, STR_PAD_LEFT);
                }
                $impresora->escribir('Totales', null, 15, STR_PAD_LEFT);

                $impresora->nuevaLinea();
            };

            $impresora->agregarEncabezado($imprimirEncabezadoListado, $impresora, Impresora::SEGUNDO_ENCABEZADO);
            $impresora->imprimirEmcabezados(Impresora::SEGUNDO_ENCABEZADO);

            //$documentoExcel->escribirFila('Fecha', 'Modulo', 'Objeto', 'Accion', 'Descripcion', 'Usuario');
            $impresora->caracteresPorLinea160();
            $totales=array();
            foreach ($datos as $registro) {
                $registro =  (array)$registro;
                for($i=0; $i<count($registro); $i++){
                    $dato=$registro['d'.$i];

                    if(!isset($totales['d'.$i]))
                        $totales['d'.$i]=0.0;

                    $totales['d'.$i]+=($dato*1.0);

                    if($i==0)
                        $impresora->escribir($dato, null, 20, STR_PAD_RIGHT);
                    else
                        $impresora->escribir('$'.number_format($dato), null, 15, STR_PAD_LEFT);

                }
                $impresora->nuevaLinea();
            }

            $impresora->negrita(true);
            for($i=0; $i<count($registro); $i++){
                $dato=$totales['d'.$i];
                if($i==0)
                    $impresora->escribir('Totales', null, 20, STR_PAD_RIGHT);
                else
                    $impresora->escribir('$'.number_format($dato), null, 15, STR_PAD_LEFT);

            }

            $impresora->negrita(false);
//            while($registros->irASiguiente()){
//                $impresora->escribir($registros->get()->nombres, null, 40, STR_PAD_RIGHT);
//                $impresora->escribir($registros->get()->direccion, null, 40, STR_PAD_RIGHT);
//                $impresora->escribir($registros->get()->telefonos, null, 40, STR_PAD_RIGHT);
//                $impresora->escribir($registros->get()->estado, null, 40, STR_PAD_RIGHT);
//                $impresora->nuevaLinea();
//            }

            $auditoria=new Auditoria();
            $auditoria->setUsuario($usuario);
            $auditoria->setModulo(Modulo::crearPorClase('siadno.view.servicios.otros.resumendiario'));
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
    new InterfazRegistroResumenDiario(new ArrayObject(array_merge($_POST, $_GET)));
?>