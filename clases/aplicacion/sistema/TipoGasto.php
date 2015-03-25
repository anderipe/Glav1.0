<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';

/**
 * Clase que representa un tipo de gasto, permitiendo clasificar lo servicios
 * que presta la empresa en tantas categorias como se deseen
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class TipoGasto
    extends ClaseBase {

    protected $idTipoGasto=0;

    protected $descripcion='';

    protected $gasto=0;

    protected $modificable=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idTipoGasto', 0);
        $this->setPropiedad('descripcion', '');
        $this->setPropiedad('gasto', 0);
        $this->setPropiedad('modificable', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idtipogasto='.$id))
                throw new AppException('No existe tipogasto con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idTipoGasto))
            throw new AppException('El tipogasto ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from tipogasto where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un tipogasto para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idTipoGasto', (int)$resultados->get()->idtipogasto, true);
        $this->setPropiedad('descripcion', (string)$resultados->get()->descripcion, true);
        $this->setPropiedad('gasto', (int)$resultados->get()->gasto, true);
        $this->setPropiedad('modificable', (int)$resultados->get()->modificable, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idTipoGasto)){
            throw new AppException('El tipo de gasto no existe',
                (object)array($this->getNombreJson('idTipoGasto')=>'El tipo de gasto no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El tipo de gasto no puede ser borrado, este ha sido utilizado');

        if($this->modificable==0)
            throw new AppException('El tipo de gasto no se puede borrar');

        $sql='delete from tipogasto where idtipogasto='.$this->idTipoGasto;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idTipoGasto);
        $modificacion->guardarObjeto($auditoria);
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->descripcion))
            throw new AppException('La descripcion de  es obligatoria',
                (object)array($this->getNombreJson('descripcion')=>'La descripcion de obligatoria'));

        if(empty($this->idTipoGasto)){
            $sql='insert INTO tipogasto
                (idtipogasto, descripcion, gasto, modificable)
                values(null, \''.mysql_real_escape_string($this->descripcion).'\','.$this->gasto.','.$this->modificable.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idTipoGasto', (int)$id);

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
            $modificacion->addDescripcionId($this->idTipoGasto);

            if($this->estaModificada('descripcion')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('descripcion'));
                $cambios[]='descripcion=\''.mysql_real_escape_string($this->descripcion).'\'';
                $this->marcarNoModificada('descripcion');
            }

            if($this->estaModificada('gasto')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('gasto'));
                $cambios[]='gasto='.$this->gasto;
                $this->marcarNoModificada('gasto');
            }
            if($this->estaModificada('modificable')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('modificable'));
                $cambios[]='modificable='.$this->modificable;
                $this->marcarNoModificada('modificable');
            }


            if(count($cambios)>0){
                if($this->modificable==0)
                    throw new AppException('El tipo de gasto no se puede modificar');

                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update tipogasto set $update where idtipogasto=".$this->idTipoGasto;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idTipoGasto;
    }

    public function getIdTipoGasto(){
        return $this->idTipoGasto;
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

    public function getGasto(){
        return $this->gasto;
    }

    public function setGasto($gasto){
        $valor=(int)$gasto;
        if($valor<0 ||$valor>1)
            throw new AppException('Valor no valido para el gasto',
                (object)array($this->getNombreJson('gasto')=>'Valor no valido para el gasto'));

        return $this->setPropiedad('gasto', $valor);
    }

    public function getModificable(){
        return $this->modificable;
    }

    public function setModificable($modificable){
        $valor=(int)$modificable;
        if($valor<0 ||$valor>1)
            throw new AppException('Valor no valido para el modificable',
                (object)array($this->getNombreJson('modificable')=>'Valor no valido para el modificable'));

        return $this->setPropiedad('modificable', $valor);
    }

    public static function getTiposGasto($gasto, $formato){
        $formato=(int)$formato;
        $gasto=(int)$gasto;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from tipogasto where gasto='.$gasto.' order by gasto, descripcion';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idtipogasto from tipogasto  where gasto='.$gasto.' order by gasto, descripcion';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new TipoGasto($resultados->get()->idtipogasto);

            return $objetos;
        }
    }

    public static function getTiposGastoTodos($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from tipogasto order by gasto, descripcion';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idtipogasto from tipogasto  order by gasto, descripcion';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new TipoGasto($resultados->get()->idtipogasto);

            return $objetos;
        }
    }

    public function haSidoUtilizado() {
        $sql="select * from gastodiario where idtipogasto=".$this->idTipoGasto.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>
