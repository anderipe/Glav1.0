<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'TipoAutomotor.php';
require_once 'Rubro.php';

/**
 * Clase que define un combo de servicios ofrecido por la empresa. Basicamente
 * se usa para reunir un conjunto de servicios individuales.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Combo
    extends ClaseBase{

    protected $idCombo=0;

    protected $idTipoAutomotor=0;

    protected $descripcion='';

    protected $estado=0;


    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idCombo', 0);
        $this->setPropiedad('idTipoAutomotor', 0);
        $this->setPropiedad('descripcion', '');
        $this->setPropiedad('estado', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idcombo='.$id))
                throw new AppException('No existe combo con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idCombo))
            throw new AppException('La combo ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from combo where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una combo para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idCombo', (int)$resultados->get()->idcombo, true);
        $this->setPropiedad('idTipoAutomotor', (int)$resultados->get()->idtipoautomotor, true);
        $this->setPropiedad('descripcion', (string)$resultados->get()->descripcion, true);
        $this->setPropiedad('estado', (int)$resultados->get()->estado, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idCombo)){
            throw new AppException('La combo no existe',
                (object)array($this->getNombreJson('idcombo')=>'La combo no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('La combo no puede ser borrada, esta ha sido utilizada');

        $sql='delete from combo where idcombo='.$this->idCombo;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idCombo);
        $modificacion->guardarObjeto($auditoria);
    }

    public function guardarObjeto(Auditoria $auditoria=null){
        if(empty($this->descripcion))
            throw new AppException('La descripcion obligatoria',
                (object)array($this->getNombreJson('descripcion')=>'La descripcion obligatoria'));

        if(empty($this->idCombo)){
            $combo=new Combo();

            $sql='insert INTO combo
                (idcombo, descripcion, estado, idtipoautomotor)
                values(null, \''.mysql_real_escape_string($this->descripcion).'\', '.$this->estado.', '.$this->idTipoAutomotor.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idCombo', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'   where '.$this->campoId.'='.$id;
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
            $modificacion->addDescripcionId($this->idCombo);

            if($this->estaModificada('idTipoAutomotor')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idTipoAutomotor'));
                $cambios[]='idtipoautomotor='.$this->idTipoAutomotor;
                $this->marcarNoModificada('idTipoAutomotor');
            }

            if($this->estaModificada('descripcion')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('descripcion'));
                $cambios[]='descripcion=\''.mysql_real_escape_string($this->descripcion).'\'';
                $this->marcarNoModificada('descripcion');
            }

            if($this->estaModificada('estado')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('estado'));
                $cambios[]='estado='.$this->estado.'';
                $this->marcarNoModificada('estado');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update combo set $update where idcombo=".$this->idCombo;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idCombo;
    }

    public function getIdCombo(){
        return $this->idCombo;
    }

    public function getIdTipoCombo(){
        return $this->idTipoCombo;
    }

    public function getTipoAutomotor(){
        return new TipoAutomotor($this->idTipoAutomotor);
    }

    public function getIdTipoAutomotor(){
        return $this->idTipoAutomotor;
    }

    public function setTipoAutomotor(TipoAutomotor $tipoAutomotor){
        $valor=$tipoAutomotor->getIdTipoAutomotor();
        if(empty($valor))
            throw new AppException('El tipo de automotor no existe',
                (object)array($this->getNombreJson('idtipoautomotor')=>'El tipo de automotor no existe'));

        return $this->setPropiedad('idTipoAutomotor', $valor);
    }

    public function getDescripcion(){
        return $this->descripcion;
    }

    public function setDescripcion($descripcion){
        $valor=(string)$descripcion;
        if(mb_strlen($valor)>256)
            throw new AppException('La descripcion puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('descripcion')=>'La descripcion puede tener maximo 256 caracteres'));

        return $this->setPropiedad('descripcion', $valor);
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

    public static function getCombos($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from combo order by idtipoautomotor, descripcion';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idcombo from combo order by idtipoautomotor, descripcion';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Combo($resultados->get()->idcombo);

            return $objetos;
        }
    }

    public function getRubros($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select rubro.idrubro, rubro.porcentajeiva, rubro.valorunitario, rubro.descripcion from rubro join rubrocombo using (idrubro) where idcombo='.$this->idCombo.' order by descripcion';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select rubro.idrubro from rubro join rubrocombo using (idrubro) where idcombo='.$this->idCombo.' order by descripcion';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Rubro($resultados->get()->idrubro);

            return $objetos;
        }
    }

    public function haSidoUtilizado() {
        $sql='select * from rubrocombo where idcombo='.$this->idCombo.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;
        return false;
    }
}

?>