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
    require_once 'Auxiliar.php';

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
    class InterfazRegistroResumenSemanal
        extends InterfazBase{

        const LISTAR_EMPLEADOS=101;
        const TRAER_RESUMEN=102;
        const GUARDAR_CAMPO=103;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                case InterfazRegistroResumenSemanal::LISTAR_EMPLEADOS:{
                    $this->listarEmpleados();
                    break;
                }

                case InterfazRegistroResumenSemanal::TRAER_RESUMEN:{
                    $this->traerResumen();
                    break;
                }

                case InterfazRegistroResumenSemanal::GUARDAR_CAMPO:{
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

            $custom_date = strtotime( date('Y-m-d', strtotime($fecha)) );
            $fecha = date('Y-m-d', strtotime('this week last monday', $custom_date));//a la fecha de inicio de semana

            $sql="update registrosemanal
                set ahorros=".$valor."
                where
                idempleado=".$idEmpleado." and fecha='". mysql_real_escape_string($fecha) ."'";
            $this->conexion->ejecutar($sql);

            $sql="update registrosemanal
                set cobro=semana-ahorros
                where
                idempleado=".$idEmpleado." and fecha='". mysql_real_escape_string($fecha) ."'";
            $this->conexion->ejecutar($sql);
            $this->retorno->msg='';
            echo json_encode($this->retorno);
        }

        public function traerResumen(){
            $fecha=$this->getString('fecha');

            $fechas=Auxiliar::getInformacionSemana($fecha);
            $custom_date = strtotime( date('Y-m-d', strtotime($fecha)) );
            $week_start = $fechas[0];
            $week_end = $fechas[1];

            $dias=array('Lunes    ','Martes   ', 'Miercoles', 'Jueves   ', 'Viernes  ', 'Sabado   ', 'Domingo  ');

            $datos=array();
            $datos[0]=new stdClass();
            $datos[1]=new stdClass();
            $datos[2]=new stdClass();
            $datos[3]=new stdClass();
            $datos[4]=new stdClass();
            $datos[5]=new stdClass();
            $datos[6]=new stdClass();
            $datos[7]=new stdClass();
            $datos[8]=new stdClass();
            $datos[9]=new stdClass();


            $datos[0]->d0='1';
            $datos[1]->d0='2';
            $datos[2]->d0='3';
            $datos[3]->d0='4';
            $datos[4]->d0='5';
            $datos[5]->d0='6';
            $datos[6]->d0='7';
            $datos[7]->d0='SEMANA';
            $datos[8]->d0='AHORROS';
            $datos[9]->d0='COBRO';

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

            $propiedadTotales='d'.$indiceTotales;
            $datos[0]->$propiedadTotales=0.0;
            $datos[1]->$propiedadTotales=0.0;
            $datos[2]->$propiedadTotales=0.0;
            $datos[3]->$propiedadTotales=0.0;
            $datos[4]->$propiedadTotales=0.0;
            $datos[5]->$propiedadTotales=0.0;
            $datos[6]->$propiedadTotales=0.0;
            $datos[7]->$propiedadTotales=0.0;
            $datos[8]->$propiedadTotales=0.0;
            $datos[9]->$propiedadTotales=0.0;


            foreach ($empleados->getRegistros() as $key => $empleado) {
                $propiedad='d'.($key+1);
                $datos[7]->$propiedad=0.0;
                $datos[8]->$propiedad=0.0;
                $datos[9]->$propiedad=0.0;

                $sql='select * from registrosemanal
                    where
                    idempleado='.$empleado->idempleado.'
                    and
                    fecha=\''.mysql_real_escape_string($week_start).'\'';
                $resultadosRegistro=$this->conexion->consultar($sql);

                $indice=0;
                $fechaProcesada=$week_start;
                while($indice<7){
                    $datos[$indice]->d0=$dias[$indice].' '.$fechaProcesada;
                    //$datos[$indice]->d0=$fechaProcesada;

                    $sql='select sum(registrodiario.saldo) as saldo
                        from
                        registrodiario
                        where
                        idempleado='.$empleado->idempleado.'
                        and
                        fecha=\''.mysql_real_escape_string($fechaProcesada).'\'';

                    $resultados=$this->conexion->consultar($sql);
                    $saldo=(float)$resultados->get(0)->saldo;
                    $datos[$indice]->$propiedad=$saldo;
                    $datos[7]->$propiedad+=$saldo;
                    $datos[$indice]->$propiedadTotales+=$saldo;

                    //if($fechaProcesada==$week_end)
                    //    break;
                    $fechaProcesada=date('Y-m-d', strtotime('+1 days', strtotime($fechaProcesada)));
                    $indice++;
                }

                if($resultadosRegistro->getCantidad()==0){
                    $sql='insert into registrosemanal
                        (idempleado, fecha, semana, ahorros, cobro)
                        values
                        ('.$empleado->idempleado.', \''.mysql_real_escape_string($week_start).'\', '.$datos[7]->$propiedad.', 0.0, '.$datos[7]->$propiedad.')';
                    $this->conexion->ejecutar($sql);

                    $sql='select * from registrosemanal
                        where
                        idempleado='.$empleado->idempleado.'
                        and
                        fecha=\''.mysql_real_escape_string($week_start).'\'';
                    $resultadosRegistro=$this->conexion->consultar($sql);

                }else{

                    if($resultadosRegistro->get(0)->semana!=$datos[7]->$propiedad){
                        $sql='update registrosemanal
                            set semana='.$datos[7]->$propiedad.'
                            where
                            idempleado='.$empleado->idempleado.'
                            and
                            fecha=\''.mysql_real_escape_string($week_start).'\'';
                        $this->conexion->ejecutar($sql);

                        $sql='update registrosemanal
                            set cobro=semana-ahorros
                            where
                            idempleado='.$empleado->idempleado.'
                            and
                            fecha=\''.mysql_real_escape_string($week_start).'\'';
                        $this->conexion->ejecutar($sql);

                        $sql='select * from registrosemanal
                            where
                            idempleado='.$empleado->idempleado.'
                            and
                            fecha=\''.mysql_real_escape_string($week_start).'\'';
                        $resultadosRegistro=$this->conexion->consultar($sql);
                    }
                }

                $datos[7]->$propiedad=$resultadosRegistro->get(0)->semana;
                $datos[8]->$propiedad=$resultadosRegistro->get(0)->ahorros;
                $datos[9]->$propiedad=$resultadosRegistro->get(0)->cobro;

                $datos[7]->$propiedadTotales+=$resultadosRegistro->get(0)->semana;
                $datos[8]->$propiedadTotales+=$resultadosRegistro->get(0)->ahorros;
                $datos[9]->$propiedadTotales+=$resultadosRegistro->get(0)->cobro;
            }

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

            $documentoImprimible=DocumentoImprimible::crearPorNombre('Resumen Semanal');
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

                $impresora->escribir('Dia', null, 20, STR_PAD_RIGHT);
                foreach($arrayNombres as $valor){
                    $impresora->escribir($valor->abreviatura, null, 15, STR_PAD_LEFT);
                }
                $impresora->escribir('Totales', null, 15, STR_PAD_LEFT);

                $impresora->nuevaLinea();
            };

            $impresora->agregarEncabezado($imprimirEncabezadoListado, $impresora, Impresora::SEGUNDO_ENCABEZADO);
            $impresora->imprimirEmcabezados(Impresora::SEGUNDO_ENCABEZADO);

            $impresora->caracteresPorLinea160();
            foreach ($datos as $registro) {
                $registro =  (array)$registro;
                for($i=0; $i<count($registro); $i++){
                    $dato=$registro['d'.$i];
                    if($i==0)
                        $impresora->escribir($dato, null, 20, STR_PAD_RIGHT);
                    else
                        $impresora->escribir('$'.number_format($dato*-1), null, 15, STR_PAD_LEFT);
                }
                $impresora->nuevaLinea();
            }

            $auditoria=new Auditoria();
            $auditoria->setUsuario($usuario);
            $auditoria->setModulo(Modulo::crearPorClase('siadno.view.servicios.otros.resumensemanal'));
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
    new InterfazRegistroResumenSemanal(new ArrayObject(array_merge($_POST, $_GET)));
?>