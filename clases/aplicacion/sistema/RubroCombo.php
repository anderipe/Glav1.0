<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Combo.php';
require_once 'Rubro.php';
require_once 'TipoAutomotor.php';

/**
 * Clase que representa un rubro agrupado en un combo de servicios
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class RubroCombo
    extends ClaseBase{

    protected $idRubroCombo=0;

    protected $idCombo=0;

    protected $idRubro=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idRubroCombo', 0);
        $this->setPropiedad('idCombo', 0);
        $this->setPropiedad('idRubro', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idrubrocombo='.$id))
                throw new AppException('No existe rubro con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idRubroCombo))
            throw new AppException('El rubrocombo ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from rubrocombo where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una rubrocombo para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idRubroCombo', (int)$resultados->get()->idrubrocombo, true);
        $this->setPropiedad('idRubro', (int)$resultados->get()->idrubro, true);
        $this->setPropiedad('idCombo', (int)$resultados->get()->idcombo, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idRubroCombo)){
            throw new AppException('El rubrocombo no existe',
                (object)array($this->getNombreJson('idrubrocombo')=>'El rubrocombo no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El rubrocombo no puede ser borrada, esta ha sido utilizada');

        $sql='delete from rubrocombo where idrubrocombo='.$this->idRubroCombo;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idRubroCombo);
        $modificacion->guardarObjeto($auditoria);
    }

    public function guardarObjeto(Auditoria $auditoria=null){
        if(empty($this->idCombo))
            throw new AppException('El combo es obligatorio',
                (object)array($this->getNombreJson('idcombo')=>'El combo es obligatorio'));

        if(empty($this->idRubro))
            throw new AppException('El rubro es obligatorio',
                (object)array($this->getNombreJson('idrubro')=>'El rubro es obligatorio'));

        if(empty($this->idRubroCombo)){
            $rubro=new RubroCombo();

            $sql='insert INTO rubrocombo
                (idrubrocombo, idcombo, idrubro)
                values(null, '.$this->idCombo.', '.$this->idRubro.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idRubroCombo', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'   where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idRubroCombo);

            if($this->estaModificada('idCombo')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idCombo'));
                $cambios[]='idcombo='.$this->idCombo;
                $this->marcarNoModificada('idCombo');
            }

            if($this->estaModificada('idRubro')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idRubro'));
                $cambios[]='idrubro='.$this->idRubroidRubro;
                $this->marcarNoModificada('idTipoAutomotor');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update rubrocombo set $update where idrubrocombo=".$this->idRubroCombo;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idRubroCombo;
    }

    public function getIdRubroCombo(){
        return $this->idRubroCombo;
    }

    public function getIdCombo(){
        return $this->idCombo;
    }

    public function getCombo(){
        return new Combo($this->idCombo);
    }

    public function setCombo(Combo $combo){
        $valor=$combo->getIdCombo();
        if(empty($valor))
            throw new AppException('El combo no existe',
                (object)array($this->getNombreJson('idcombo')=>'El combo no existe'));

        return $this->setPropiedad('idCombo', $valor);
    }

    public function getIdRubro(){
        return $this->idRubro;
    }

    public function getRubro(){
        return new Rubro($this->idRubro);
    }

    public function setRubro(Rubro $rubro){
        $valor=$rubro->getIdRubro();
        if(empty($valor))
            throw new AppException('El rubro no existe',
                (object)array($this->getNombreJson('idrubro')=>'El rubro no existe'));

        return $this->setPropiedad('idRubro', $valor);
    }

    public static function getRubrosCombo(Combo $combo, $formato){
        $formato=(int)$formato;

        if($combo->getIdCombo()==0)
            throw new Exception('El combo no existe');

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select rubro.* from rubro join rubrocombo using (idcombo) where idcombo='.$combo->getIdCombo().' and rubro.estado=1 order by rubro.descripcion';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select rubro.idrubro from rubro join rubrocombo using (idcombo) where idcombo='.$combo->getIdCombo().' and rubro.estado=1 order by rubro.descripcion';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Rubro($resultados->get()->idrubro);

            return $objetos;
        }
    }


    public function haSidoUtilizado() {

        return false;
    }
}

?>