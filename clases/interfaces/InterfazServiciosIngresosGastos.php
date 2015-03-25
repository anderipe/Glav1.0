<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';

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
    class InterfazServiciosIngresosGastos
        extends InterfazBase{

        const TRAER_RESUMEN=102;
        const GUARDAR_CAMPO=103;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                case InterfazServiciosIngresosGastos::TRAER_RESUMEN:{
                    $this->traerResumen();
                    break;
                }

                case InterfazServiciosIngresosGastos::GUARDAR_CAMPO:{
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
            $idTipoGasto=$this->getInt('idtipogasto');
            $fecha=$this->getString('fecha');
            $valor=$this->getDouble('valor');

            if($idTipoGasto<=5)
                throw new Exception('Esta causal no se puede modificar');

            $sql="update gastodiario set valor=$valor where fecha='$fecha' and idtipogasto=$idTipoGasto";
            $this->conexion->ejecutar($sql);

            $this->retorno->msg='';
            echo json_encode($this->retorno);
        }

        public function traerResumen(){
            $fecha=$this->getString('fecha');
            $ayer = date('Y-m-d', strtotime('-1 day', strtotime($fecha)));
            $gasto=$this->getBool('gasto');
            $data=array();

            if($gasto){
                $sql='select * from tipogasto where gasto=true order by descripcion';
            }else{
                $sql='select * from tipogasto where gasto=false order by descripcion';
            }

            $causales=$this->conexion->consultar($sql);
            while($causales->irASiguiente()){

                $causal=$causales->get();
                $valor=(float)0.0;
                if($causal->idtipogasto==1){//FACTURACION
                    $sql="select sum(facturado) as valor from registrodiario where fecha='".$fecha."'";
                    $resultados=$this->conexion->consultar($sql);
                    $valor=(float)$resultados->get(0)->valor;
                }
                if($causal->idtipogasto==2){//SIN ENTREGAR DE AYER
                    $sql="select sum(noentregado) as valor from registrodiario where fecha='".$ayer."'";
                    $resultados=$this->conexion->consultar($sql);
                    $valor=(float)$resultados->get(0)->valor;
                }
                if($causal->idtipogasto==3){//SIN ENTREGAR DE HOY
                    $sql="select sum(noentregado) as valor from registrodiario where fecha='".$fecha."'";
                    $resultados=$this->conexion->consultar($sql);
                    $valor=(float)$resultados->get(0)->valor;
                }
                if($causal->idtipogasto==4){//PRESTAMOS
                    $sql="select sum(prestamo) as valor from registrodiario where fecha='".$fecha."'";
                    $resultados=$this->conexion->consultar($sql);
                    $valor=(float)$resultados->get(0)->valor;
                }
                if($causal->idtipogasto==5){//OTROS
                    $sql="select sum(otros) as valor from registrodiario where fecha='".$fecha."'";
                    $resultados=$this->conexion->consultar($sql);
                    $valor=(float)$resultados->get(0)->valor;
                }

                $causal->valor=(float)$valor;


                $sql="select * from gastodiario where fecha='".$fecha."' and idtipogasto=".$causal->idtipogasto;
                $registro=$this->conexion->consultar($sql);

                if($registro->getCantidad()==0){
                    $sql="insert into gastodiario(idgastodiario, idtipogasto, fecha, valor) values (null, ".$causal->idtipogasto.", '".$fecha."', $valor)";
                    $this->conexion->ejecutar($sql);
                }else{
                    if($causal->idtipogasto>=1 && $causal->idtipogasto<=5 && $registro->get(0)->valor!=$valor){
                        $sql="update gastodiario set valor=$valor where idtipogasto=".$causal->idtipogasto." and fecha= '".$fecha."'";
                        $this->conexion->ejecutar($sql);
                    }else{
                        $causal->valor=(float)$registro->get(0)->valor;
                    }
                }

                $data[]=$causal;
            }

            $this->retorno->data=$data;
            $this->retorno->hoy=$fecha;
            $this->retorno->ayer=$ayer;
            $this->retorno->msg='';
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
    }
    new InterfazServiciosIngresosGastos(new ArrayObject(array_merge($_POST, $_GET)));
?>