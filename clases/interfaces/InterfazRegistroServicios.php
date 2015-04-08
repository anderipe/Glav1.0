<?php
/**
 * @package co.org.lavado
 * @subpackage interfaces
 */

    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Empleado.php';
    require_once 'Persona.php';
    require_once 'Automotor.php';
    require_once 'Servicio.php';
    require_once 'RubroServicio.php';
    require_once 'EstadoServicio.php';


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
    class InterfazRegistroServicios
        extends InterfazBase{

        const LISTAR_TIPOS_IDENTIFICACION=101;
        const LISTAR_TIPO_AUTOMOTOR=102;
        const LISTAR_MARCA_AUTOMOTOR=103;
        const LISTAR_ENCARGADOS=104;
        const LISTAR_COMBOS=105;
        const LISTAR_RUBROS=106;
        const CONSULTAR_COMBO=107;
        const REGISTRAR_SERVICIO=108;
        const TRAER_SERVICIO=109;
        const REGISTRAR_RUBROS=110;
        const TRAER_SERVICIOS=111;
        const LISTAR_ESTADOSSERVICIO=112;
        const LISTAR_SERVICIOS2=113;
        const TRAER_PERSONA=114;
        const GUARDAR_INF_EXTRA=115;
        const MOSTRAR_RESUMEN=116;
        const BORRAR_RUBRO=117;

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){

                case InterfazRegistroServicios::LISTAR_TIPOS_IDENTIFICACION:{
                    $this->listarTiposIdentificacion();
                    break;
                }

                case InterfazRegistroServicios::LISTAR_TIPO_AUTOMOTOR:{
                    $this->listarTiposAutomotor();
                    break;
                }

                case InterfazRegistroServicios::LISTAR_MARCA_AUTOMOTOR:{
                    $this->listarMarcasAutomotor();
                    break;
                }

                case InterfazRegistroServicios::LISTAR_ENCARGADOS:{
                    $this->listarEncargados();
                    break;
                }

                case InterfazRegistroServicios::LISTAR_COMBOS:{
                    $this->listarCombos();
                    break;
                }

                case InterfazRegistroServicios::LISTAR_RUBROS:{
                    $this->listarRubros();
                    break;
                }

                case InterfazRegistroServicios::CONSULTAR_COMBO:{
                    $this->consultarCombo();
                    break;
                }

                case InterfazRegistroServicios::REGISTRAR_SERVICIO:{
                    $this->registrarServicio();
                    break;
                }

                case InterfazRegistroServicios::REGISTRAR_RUBROS:{
                    $this->registrarRubros();
                    break;
                }

                case InterfazRegistroServicios::TRAER_SERVICIO:{
                    $this->traerServicio();
                    break;
                }

                case InterfazRegistroServicios::TRAER_SERVICIOS:{
                    $this->traerServicios();
                    break;
                }

                case InterfazRegistroServicios::LISTAR_ESTADOSSERVICIO:{
                    $this->listarEstadosServicio();
                    break;
                }

                case InterfazRegistroServicios::LISTAR_SERVICIOS2:{
                    $this->listarServicios2();
                    break;
                }

                case InterfazRegistroServicios::TRAER_PERSONA:{
                    $this->traerPersona();
                    break;
                }

                case InterfazRegistroServicios::GUARDAR_INF_EXTRA:{
                    $this->guardarInfExtra();
                    break;
                }

                case InterfazRegistroServicios::MOSTRAR_RESUMEN:{
                    $this->mostrarResumen();
                    break;
                }

                case InterfazRegistroServicios::BORRAR_RUBRO:{
                    $this->borrarRubro();
                    break;
                }

                default:{
                    $this->traerPersona();
                    break;
                }
            }
        }

        function borrarRubro(){
            $this->retorno->msg='';
            echo json_encode($this->retorno);
        }

        public function mostrarResumen(){
            $fechaInicial=$this->getString('fechainicial');
            $idEmpleado=$this->getString('idempleado');

            $sql="select matricula, servicio.idservicio, sum(total) as total, sum(total)*0.4 as valor
                from
                servicio
                join rubroservicio using(idservicio)
                join automotor using (idautomotor)
                where
                fecharegistro between '$fechaInicial 00:00:00' and '$fechaInicial 23:59:59'
                and
                idempleado=".$idEmpleado."
                and
                idestadoservicio<>".EstadoServicio::ANULADO."
                group by servicio.idservicio, matricula";

            $resultados=$this->conexion->consultar($sql);
            $this->retorno->data=$resultados->getRegistros();
            $this->retorno->sql=$sql;
            echo json_encode($this->retorno);
        }

        public function listarServicios2(){
            $fechaInicial=$this->getString('fechainicial');
            $fechaFinal=$this->getString('fechafinal');
            $idEstadoServicio=$this->getString('idestadoservicio');

            $sql="select servicio.idempleado, servicio.idestadoservicio, servicio.idservicio, servicio.idpersona, servicio.fecharegistro, servicio.fechaentrega, automotor.matricula, estadoservicio.nombre as estado, replace(persona.nombres, '|', ' ') as empleado, cliente.idtipoidentificacion, cliente.identificacion
                from
                servicio
                join automotor using (idautomotor)
                join estadoservicio using (idestadoservicio)
                left join empleado using (idempleado)
                left join persona on (empleado.idpersona=persona.idpersona)
                left join persona as cliente on (servicio.idpersona=cliente.idpersona)
                where
                fecharegistro between '$fechaInicial 00:00:00' and '$fechaFinal 23:59:59' ";

            if(!empty($idEstadoServicio))
                $sql.=" and idestadoservicio=".$idEstadoServicio;

            $sql.=" order by
                fecharegistro
                ";

            $resultados=$this->conexion->consultar($sql);
            $this->retorno->data=$resultados->getRegistros();

            foreach($this->retorno->data as $clave=>$valor){
                $fecha=$valor->fechaentrega;
                if(!empty($fecha)){
                    $fecha=explode(' ', $fecha);
                    $this->retorno->data[$clave]->fechaentrega=$fecha[0];
                    $this->retorno->data[$clave]->horaentrega=$fecha[1];
                }

            }
            echo json_encode($this->retorno);
        }

        public function traerServicio(){
            $idServicio=$this->getInt('idservicio');
            $servicio=new Servicio($idServicio);
            $automotor=$servicio->getAutomotor();

            unset($this->retorno->data->idservicio);
            $this->retorno->data=(object)array_merge((array) $servicio->getJson(true), (array) $automotor->getJson(true));
            $this->retorno->msg='';
            echo json_encode($this->retorno);
        }

        public function traerServicios(){
            $idServicio=$this->getInt('idservicio');
            $servicio=new Servicio($idServicio);
            $this->retorno->data=$servicio->getRubros();
            $idServicio=$servicio->getIdServicio();
            if(empty($idServicio))
                $this->retorno->msg='El servicio con identificador '.$idServicio.' no se encuentra registrado';
            else
                $this->retorno->msg='';
            echo json_encode($this->retorno);
        }

        public function registrarRubros(){
            $raw = file_get_contents("php://input");
            $data = json_decode($raw);
            if(!is_array($data))
                $data=array($data);

            $idServicio=$this->getInt('idservicio');
            $servicio=new Servicio($idServicio);

            $usuario=new Usuario(FrameWork::getIdUsuario());
            $auditoria=new Auditoria();
            $auditoria->setUsuario($usuario);
            $auditoria->setModulo(Modulo::crearPorClase('siadno.view.PanelRegistroServicios'));
            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
            $auditoria->guardarObjeto();

            $this->conexion->ejecutar('begin');
            foreach ($data as $o) {
                $rubro=new Rubro($o->idrubro);
                $rubroServicio=new RubroServicio();
                $rubroServicio->cargarRubroServicio($rubro, $servicio);
                $rubroServicio->setRubro($rubro);
                $rubroServicio->setServicio($servicio);
                $rubroServicio->setCantidad($o->cantidad);
                $rubroServicio->setValor($o->valorunitario*$o->cantidad);
                $rubroServicio->setIva($o->iva*$o->cantidad);
                $rubroServicio->setTotal($o->total);
                $rubroServicio->guardarObjeto($auditoria);
            }
            $this->conexion->ejecutar('commit');

            echo json_encode($this->retorno);
        }

        public function registrarServicio(){
            $this->retorno->msg='';
            $idServicio=$this->getInt('idservicio');
            $usuario=new Usuario(FrameWork::getIdUsuario());
            $auditoria=new Auditoria();
            $auditoria->setUsuario($usuario);
            $auditoria->setModulo(Modulo::crearPorClase('siadno.view.PanelRegistroServicios'));
            if(empty($idServicio))
                $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            else
                $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));

            $automotor=new Automotor();
            $automotor->crearXMatricula($this->getString('matricula'));
            $automotor->setTipoAutomotor(new TipoAutomotor($this->getInt('idtipoautomotor')));
            //$automotor->setModelo($this->getInt('modelo'));
            $automotor->setMatricula($this->getString('matricula'));

            $persona=null;
            $sql='select servicio.idpersona
                from
                servicio
                join automotor using (idautomotor)
                where
                automotor.matricula=\''.mysql_real_escape_string($automotor->getMatricula()).'\'
                and
                servicio.idpersona>0 and servicio.idpersona is not null

                order by servicio.fecharegistro desc limit 1';
            $resultados=$this->conexion->consultar($sql);
            if($resultados->getCantidad()>0){
                $persona=new Persona($resultados->get(0)->idpersona);
                $this->retorno->msg='El servicio ha sido registrado a nombre de '.$persona->getNombreCompleto();
            }

            $servicio=new Servicio($idServicio);
            $servicio->setUsuario($usuario);
            //$servicio->setEmpleado(new Empleado($this->getInt('idempleado')));
            if(!empty($persona))
                $servicio->setPersona ($persona);
            $servicio->setEstadoServicio(new EstadoServicio(EstadoServicio::PENDIENTE));
            $servicio->setFechaRegistro(new DateTime());
            $servicio->setObservaciones($this->getString('observaciones'));

            $this->conexion->ejecutar('begin;');
            $auditoria->guardarObjeto();
            $automotor->guardarObjeto($auditoria);
            $servicio->setAutomotor($automotor);
            $servicio->guardarObjeto($auditoria);
            $this->conexion->ejecutar('commit;');

            if(empty($this->retorno->msg))
                $this->retorno->msg='El servicio ha sido registrado';
            $this->retorno->idservicio=$servicio->getIdServicio();
            echo json_encode($this->retorno);
        }

        public function guardarInfExtra(){
            $idServicio=$this->getInt('idservicio');
            $usuario=new Usuario(FrameWork::getIdUsuario());
            $auditoria=new Auditoria();
            $auditoria->setUsuario($usuario);
            $auditoria->setModulo(Modulo::crearPorClase('siadno.view.PanelRegistroServicios'));
            $auditoria->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));

            $idTipoIdentificacion=$this->getInt('idtipoidentificacion');
            $identificacion=$this->getString('identificacion');

            $persona=null;
            if(!empty($identificacion)){
                $persona=new Persona();
                $municipio=new Municipio(1);
                $tipoIdentificacion=new TipoIdentificacion($idTipoIdentificacion);
                $persona->cargarPorIdentificacion($tipoIdentificacion, $identificacion);
                $persona->setNacionalidad($municipio->getDepartamento()->getPais());
                $persona->setTipoIdentificacion($tipoIdentificacion);
                $persona->setIdentificacion($identificacion);
                $persona->setNombres($this->getString('nombres').'|'.$this->getString('apellidos'));
                $persona->setDireccion($this->getString('direccion'));
                $persona->setTelefonos($this->getString('telefonos'));
                $persona->setEMail($this->getString('email'));
                $persona->setFechaNacimiento(new DateTime($this->getString('fechanacimiento')));
                $persona->setSexo($this->getInt('sexo'));
            }

            $this->retorno->idServicio=$idServicio;
            $servicio=new Servicio($idServicio);
            $estadoServicio=new EstadoServicio($this->getInt('idestadoservicio'));
            $servicio->setEstadoServicio($estadoServicio);
            $servicio->setEmpleado(new Empleado($this->getInt('idempleado')));

            $fechaEntregaActual=$servicio->getFechaEntrega(false);

            $fechaEntrega=$this->getString('fechaentrega');

            if($estadoServicio->getIdEstadoServicio()==EstadoServicio::TERMINADO &&  empty($fechaEntrega) && empty($fechaEntregaActual)){
                $fechaEntrega=date('Y-m-d');
                $horaEntrega=date('h:i');
                if(empty($horaEntrega))
                    $horaEntrega=date('h:i');
                $servicio->setFechaEntrega(new DateTime($fechaEntrega.' '.$horaEntrega));
            }else{
                if(!empty($fechaEntrega)){
                    $horaEntrega=date('h:i');
                    $servicio->setFechaEntrega(new DateTime($fechaEntrega.' '.$horaEntrega));
                }
            }

            $this->conexion->ejecutar('begin;');
            $auditoria->guardarObjeto();
            if(!empty($persona)){
                $persona->guardarObjeto($auditoria);
                $servicio->setPersona($persona);
            }
            $servicio->guardarObjeto($auditoria);
            $this->conexion->ejecutar('commit;');

            $this->retorno->msg='';
            echo json_encode($this->retorno);
        }

        public function listarTiposIdentificacion(){
            $this->retorno->msg='';
            $this->retorno->data=TipoIdentificacion::getTiposIdentificacion(RecordSet::FORMATO_OBJETO, true);
            $objeto=new stdClass();
            $objeto->idtipoidentificacion=0;
            $objeto->abreviatura='-N.D-';
            $this->retorno->data[]=$objeto;
            echo json_encode($this->retorno);
        }

        public function listarTiposAutomotor(){
            $this->retorno->msg='';
            $this->retorno->data=  TipoAutomotor::getTiposAutomotor(RecordSet::FORMATO_OBJETO, true);
            echo json_encode($this->retorno);
        }

        public function listarEstadosServicio(){
            $this->retorno->msg='';
            $this->retorno->data=EstadoServicio::getEstadosServicio(RecordSet::FORMATO_OBJETO);
            $objeto=new stdClass();
            $objeto->idestadoservicio=0;
            $objeto->nombre='-Todos-';
            $this->retorno->data[]=$objeto;
            echo json_encode($this->retorno);
        }

        public function listarEncargados(){
            $this->retorno->msg='';
            $this->retorno->data= Empleado::getEmpleados();
            $objeto=new stdClass();
            $objeto->idempleado=0;
            $objeto->nombres='-N.D-';
            $this->retorno->data[]=$objeto;
            echo json_encode($this->retorno);
        }

        public function listarCombos(){
            $idTipoAutomotor=$this->getInt('idtipoautomotor');
            $tipoAutomotor=new TipoAutomotor($idTipoAutomotor);

            $this->retorno->msg='';
            $this->retorno->data=$tipoAutomotor->getCombos(RecordSet::FORMATO_OBJETO, true);
            $objeto=new stdClass();
            $objeto->idcombo=0;
            $objeto->descripcion='-N.D-';
            $this->retorno->data[]=$objeto;
            echo json_encode($this->retorno);
        }

        public function consultarCombo(){
            $idCombo=$this->getInt('idcombo');
            $combo=new Combo($idCombo);
            $this->retorno->msg='';
            $this->retorno->data=$combo->getRubros(RecordSet::FORMATO_OBJETO);
            echo json_encode($this->retorno);
        }

        public function listarRubros(){
            $idTipoAutomotor=$this->getInt('idtipoautomotor');
            $tipoAutomotor=new TipoAutomotor($idTipoAutomotor);

            $this->retorno->msg='';
            $this->retorno->data=$tipoAutomotor->getRubros(RecordSet::FORMATO_OBJETO, true, true, true);
            $objeto=new stdClass();
            $objeto->idrubro=0;
            $objeto->descripcion='-N.D-';
            $this->retorno->data[]=$objeto;
            echo json_encode($this->retorno);
         }

        public function traerPersona(){
            $idTipoIdentificacion=$this->getInt('idtipoidentificacion');
            $identificacion=$this->getInt('identificacion');

            $persona=new Persona();
            $tipoIdentificacion=new TipoIdentificacion($idTipoIdentificacion);
            $persona->cargarPorIdentificacion($tipoIdentificacion, $identificacion);

            $this->retorno->data=$persona->getJson(true);
            unset($this->retorno->data->idtipoidentificacion);
            unset($this->retorno->data->identificacion);
            if($persona->getIdTipoIdentificacion()==0 && $persona->getIdentificacion()==''){
                //$this->retorno->data->idtipoidentificacion=(int)$idTipoIdentificacion;
                //$this->retorno->data->identificacion=$identificacion;
                $this->retorno->data->fechanacimiento=date('Y-m-d');
                $this->retorno->data->sexo=1;
            }
            $nombres=  explode('|', $this->retorno->data->nombres);
            $this->retorno->data->nombres=$nombres[0];
            if(isset($nombres[1]))
                $this->retorno->data->apellidos=$nombres[1];
            else
                $this->retorno->data->apellidos='';

            if($tipoIdentificacion->getEsPersonaNatural()!=1)
                $this->retorno->data->digitoverificacion=' D.V-'.((int)$persona->getDigitoVerificacion());

            $this->retorno->msg='';
            echo json_encode($this->retorno);
        }
    }
    new InterfazRegistroServicios(new ArrayObject(array_merge($_POST, $_GET)));
?>