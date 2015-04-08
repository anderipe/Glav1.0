<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Modulo.php';
require_once 'Modificacion.php';
require_once 'AccionAuditable.php';
require_once 'Usuario.php';
require_once 'Clase.php';

/**
 * Clase que representa una registro de auditoria en el sistema. El subsistema
 * de auditoria integrado al sistema es bastante exaustivo y robusto.
 * Practicamente se tiene registro de todo cambios hecho en la base de datos
 * conservando rastreo antes y despues de la modificacion
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Auditoria
    extends ClaseBase{

    protected $idAuditoria=0;

    protected $idModulo=0;

    protected $idAccionAuditable=0;

    protected $idUsuario=0;

    protected $descripcion='';

    protected $descripcionSiguienteAccionAuditable='';

    protected $fecha='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idAuditoria', 0);
        $this->setPropiedad('idModulo', 0);
        $this->setPropiedad('idAccionAuditable', 0);
        $this->setPropiedad('idUsuario', 0);
        $this->setPropiedad('descripcion', '');
        $this->setPropiedad('fecha', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idauditoria='.$id))
                throw new AppException('No existe auditoria con identificador '.$id);
    }

    public function setModulo(Modulo $modulo){
        $valor=$modulo->getIdModulo();
        if(empty($valor))
            throw new AppException('El modulo no existe',
                (object)array($this->getNombreJson('idmodulo')=>'El modulo no existe'));

        return $this->setPropiedad('idModulo', $valor);
    }

    public function setAccionAuditable(AccionAuditable $accionAuditable){
        $valor=$accionAuditable->getIdAccionAuditable();
        if(empty($valor))
            throw new AppException('La accion auditable no existe',
                (object)array($this->getNombreJson('idaccionauditable')=>'La accion auditable no existe'));

        return $this->setPropiedad('idAccionAuditable', $valor);
    }

    public function setUsuario(Usuario $usuario){
        $valor=$usuario->getIdUsuario();
        if(empty($valor))
            throw new AppException('El usuario no existe',
                (object)array($this->getNombreJson('usuario')=>'El usuario no existe'));

        return $this->setPropiedad('idUsuario', $valor);
    }

    public function setDescripcion($descripcion){
        $valor=(string)$descripcion;
        if(mb_strlen($valor)>256)
            throw new AppException('La descripcion puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('descripcion')=>'La descripcion puede tener maximo 256 caracteres'));

        return $this->setPropiedad('descripcion', $valor);
    }

    public function addDescripcion($descripcion){
        $valor=(string)$descripcion;
        $valor=$this->descripcion.'|'.$valor;
        return $this->setDescripcion($valor);
    }

    public function setDescripcionSiguienteAccionAuditable($descripcion){
        $this->descripcionSiguienteAccionAuditable=$descripcion;
    }

    public function getDescripcionSiguienteAccionAuditable(){
        return $this->descripcionSiguienteAccionAuditable;
    }

    public function getIdAuditoria(){
        return $this->idAuditoria;
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idAuditoria))
            throw new AppException('La auditoria ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from auditoria where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una auditoria para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idAuditoria', (int)$resultados->get()->idauditoria, true);
        $this->setPropiedad('idModulo', (int)$resultados->get()->idmodulo, true);
        $this->setPropiedad('idAccionAuditable', (int)$resultados->get()->idaccionauditable, true);
        $this->setPropiedad('idUsuario', (int)$resultados->get()->idusuario, true);
        $this->setPropiedad('descripcion', (string)$resultados->get()->descripcion, true);
        $this->setPropiedad('fecha', (string)$resultados->get()->fecha, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(!empty($this->idAuditoria))
            throw new AppException('Los registros de auditoria no son modificables',
                (object)array($this->getNombreJson('idAuditoria')=>'Los registros de auditoria no son modificables'));

        if(empty($this->idModulo))
            throw new AppException('No se ha proporcionado modulo',
                (object)array($this->getNombreJson('idModulo')=>'No se ha proporcionado modulo'));

        if(empty($this->idAccionAuditable))
            throw new AppException('No se ha proporcionado accion auditable',
                (object)array($this->getNombreJson('idAccionAuditable')=>'No se ha proporcionado accion auditable'));

        if(empty($this->idUsuario))
            throw new AppException('No se ha proporcionado usuario',
                (object)array($this->getNombreJson('idUsuario')=>'No se ha proporcionado usuario'));

        if(empty($this->fecha)){
            $fecha=new DateTime(null);
            $this->fecha=$fecha->format('Y-m-d h:i:s');
        }

        $sql='insert INTO auditoria
            (idauditoria, idaccionauditable, idusuario, idmodulo, descripcion, fecha)
            values
            (null, '.$this->idAccionAuditable.', '.$this->idUsuario.', '.$this->idModulo.', \''.mysql_real_escape_string($this->descripcion).'\', \''.mysql_real_escape_string($this->fecha).'\')';

        $id=$this->conexion->ejecutar($sql);
        $this->setPropiedad('idAuditoria', (int)$id);

        $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
        $this->conexion->ejecutar($sql);

        $this->modificado=false;
        return $this->idAuditoria;
    }

    /**
     *
     * @param type $fechaInicial
     * @param type $fechaFinal
     * @param type $idUsuario
     * @param type $idModulo
     * @param type $idClase
     * @param type $descripcion
     * @param type $cantidadTotal
     * @param type $offset
     * @param type $limit
     * @return RecordSet
     */
    public static function consultar($fechaInicial, $fechaFinal, $idUsuario, $idModulo, $idClase, $descripcion, &$cantidadTotal=null, $offSet=null, $limit=null){
        $fechaInicial=trim($fechaInicial);
        $fechaFinal=trim($fechaFinal);
        $idUsuario=(int)$idUsuario;
        $idModulo=(int)$idModulo;
        $idClase=(int)$idClase;
        $descripcion=mb_strtolower(trim($descripcion));


        $where=array();
        if(!empty($fechaInicial))
            $where[]=' auditoria.fecha>=\''.mysql_real_escape_string ($fechaInicial).'\'';

        if(!empty($fechaFinal))
            $where[]=' auditoria.fecha<=\''.mysql_real_escape_string ($fechaFinal).'\'';

        if(!empty($idUsuario))
            $where[]=' auditoria.idusuario='.$idUsuario;

        if(!empty($idModulo))
            $where[]=' auditoria.idmodulo='.$idModulo;

        if(!empty($idClase))
            $where[]=' modificacion.idclase='.$idClase;

        if(!empty($descripcion))
            $where[]=' (lower(auditoria.descripcion) like \'%'.mysql_real_escape_string ($descripcion).'%\' or lower(modificacion.descripcion) like \'%'.mysql_real_escape_string ($descripcion).'%\')';

        if($cantidadTotal!==null){
            $sql='select count(*) as total from
                auditoria left join modificacion using (idauditoria) ';

            if(count($where)>0)
                $sql.=' where '.implode (' and ', $where);


            $registros=FrameWork::getConexion()->consultar($sql);
            $cantidadTotal=(int)$registros->get(0)->total;
        }

        $sql='select auditoria.idauditoria, auditoria.fecha as fechaauditoria, auditoria.idusuario, auditoria.idmodulo, modificacion.idaccionauditable, auditoria.idaccionauditable as idaccionauditableauditoria,  modificacion.descripcion, modificacion.idclase from
                auditoria left join modificacion using (idauditoria)' ;

            if(count($where)>0)
                $sql.=' where '.implode (' and ', $where);

        if($limit!==null){
            $limit=(int)$limit;
            $sql.=' limit '.$limit;
        }

        if($offSet!==null){
            $offSet=(int)$offSet;
            $sql.=' offset '.$offSet;
        }

        return FrameWork::getConexion()->consultar($sql);
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>