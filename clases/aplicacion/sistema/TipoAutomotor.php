<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Combo.php';
require_once 'Rubro.php';

/**
 * Clase que representa un tipo de automotor, como motocicleta, camioneta, etc
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class TipoAutomotor
    extends ClaseBase {

    protected $idTipoAutomotor=0;

    protected $descripcion='';

    protected $estado=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idTipoAutomotor', 0);
        $this->setPropiedad('descripcion', '');
        $this->setPropiedad('estado', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idtipoautomotor='.$id))
                throw new AppException('No existe tipoautomotor con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idTipoAutomotor))
            throw new AppException('El tipoautomotor ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from tipoautomotor where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un tipoautomotor para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idTipoAutomotor', (int)$resultados->get()->idtipoautomotor, true);
        $this->setPropiedad('descripcion', (string)$resultados->get()->descripcion, true);
        $this->setPropiedad('estado', (int)$resultados->get()->estado, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idTipoAutomotor)){
            throw new AppException('El tipo de automotor no existe',
                (object)array($this->getNombreJson('idTipoAutomotor')=>'El tipo de automotor no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El tipo de automotor no puede ser borrado, este ha sido utilizado');

        $sql='delete from tipoautomotor where idtipoautomotor='.$this->idTipoAutomotor;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idTipoAutomotor);
        $modificacion->guardarObjeto($auditoria);
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->descripcion))
            throw new AppException('La descripcion es obligatoria',
                (object)array($this->getNombreJson('descripcion')=>'La descripcion de la resolcion es obligatoria'));

        if(empty($this->idTipoAutomotor)){
            $sql='insert INTO tipoautomotor
                (idtipoautomotor, descripcion, estado)
                values(null, \''.mysql_real_escape_string($this->descripcion).'\','.$this->estado.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idTipoAutomotor', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('descripcion'));
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idTipoAutomotor);

            if($this->estaModificada('descripcion')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('descripcion'));
                $cambios[]='descripcion=\''.mysql_real_escape_string($this->descripcion).'\'';
                $this->marcarNoModificada('descripcion');
            }

            if($this->estaModificada('estado')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('estado'));
                $cambios[]='estado='.$this->estado;
                $this->marcarNoModificada('estado');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';

                $update=implode(',', $cambios);
                $sql="update tipoautomotor set $update where idtipoautomotor=".$this->idTipoAutomotor;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idTipoAutomotor;
    }

    public function getIdTipoAutomotor(){
        return $this->idTipoAutomotor;
    }

    public function setDescripcion($descripcion){
        $valor=(string)$descripcion;
        if(mb_strlen($valor)>256)
            throw new AppException('La descripcion puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('descripcion')=>'La descripcion puede tener maximo 256 caracteres'));

        return $this->setPropiedad('descripcion', $valor);
    }

    public function getDescripcion(){
        return $this->descripcion;
    }

    public function getEstado(){
        return $this->estado;
    }

    public function setEstado($estado){
        $valor=(int)$estado;
        if($valor<0 ||$valor>1)
            throw new AppException('Valor no valido para el estado',
                (object)array($this->getNombreJson('estado')=>'Valor no valido para el estado'));

        return $this->setPropiedad('estado', $valor);
    }

    public static function getTiposAutomotor($formato, $soloActivos=false){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from tipoautomotor ';

            if($soloActivos)
                $sql.=' where estado=1 ';

            $sql.='order by descripcion';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idtipoautomotor from tipoautomotor ';

            if($soloActivos)
                $sql.=' where estado=1 ';

            $sql.=' order by descripcion';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new TipoAutomotor($resultados->get()->idtipoautomotor);

            return $objetos;
        }
    }

    public function getCombos($formato, $soloActivos=false){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from combo
                where
                idtipoautomotor='.$this->idTipoAutomotor;

            if($soloActivos)
                $sql.=' and estado=1 ';

            $sql.='order by descripcion';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idcombo from combo
                where
                idtipoautomotor='.$this->idTipoAutomotor;

            if($soloActivos)
                $sql.=' where estado=1 ';

            $sql.=' order by descripcion';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Combo($resultados->get()->idcombo);

            return $objetos;
        }
    }

    public function getRubros($formato, $soloActivos=false, $soloVisibles=false, $noDefinidosTambien=false){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select rubro.idrubro, rubro.descripcion, rubro.porcentajeiva, rubro.valorunitario from rubro
                where
                (idtipoautomotor='.$this->idTipoAutomotor;

            if($noDefinidosTambien)
                $sql.=' or idtipoautomotor=0';
            $sql.=')';

            if($soloActivos)
                $sql.=' and estado=1 ';

            if($soloVisibles)
                $sql.=' and visible=1 ';

            $sql.='order by descripcion';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select rubro.idrubro from rubro
                where
                and
                (idtipoautomotor='.$this->idTipoAutomotor;

            if($noDefinidosTambien)
                $sql.=' or idtipoautomotor=0';
            $sql.=')';

            if($soloActivos)
                $sql.=' and estado=1 ';

            if($soloVisibles)
                $sql.=' and visible=1 ';

            $sql.='order by descripcion';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Rubro($resultados->get()->idrubro);

            return $objetos;
        }
    }

    public function haSidoUtilizado() {
        $sql='select idautomotor from automotor where idtipoautomotor='.$this->idTipoAutomotor.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>
