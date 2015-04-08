<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'TipoAutomotor.php';

/**
 * Clase que representa un automotor.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Automotor
    extends ClaseBase {

    const BOGOTA=1;

    protected $idAutomotor=0;

    protected $idTipoAutomotor=0;


    protected $matricula='';

    protected $modelo='';

    protected $idBien=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idAutomotor', 0);
        $this->setPropiedad('idTipoAutomotor', 0);
        $this->setPropiedad('matricula', '');
        $this->setPropiedad('modelo', 0);
        $this->setPropiedad('idBien', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idautomotor='.$id))
                throw new AppException('No existe automotor con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idAutomotor))
            throw new AppException('El automotor ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from automotor where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un automotor para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idAutomotor', (int)$resultados->get()->idautomotor, true);
        $this->setPropiedad('idTipoAutomotor', (int)$resultados->get()->idtipoautomotor, true);
        $this->setPropiedad('matricula', (string)$resultados->get()->matricula, true);
        $this->setPropiedad('modelo', (int)$resultados->get()->modelo, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function crearXMatricula($matricula){
        $matricula=(string)$matricula;
        if(empty($matricula))
            throw new AppException('El matricula del automotor el obligatorio',
                (object)array($this->getNombreJson('matricula')=>'El matricula del automotor el obligatorio'));

        $where='matricula=\''.  mysql_real_escape_string($matricula).'\'';
        return $this->cargarObjeto($where);
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idTipoAutomotor))
            throw new AppException('El departamento es un dato obligatorio',
                (object)array($this->getNombreJson('idtipoautomotor')=>'El departamento es un dato obligatorio'));

        if(empty($this->matricula))
            throw new AppException('El matricula del automotor el obligatorio',
                (object)array($this->getNombreJson('matricula')=>'El matricula del automotor el obligatorio'));

        if(empty($this->idAutomotor)){
            $sql='insert INTO automotor
                (idautomotor, idtipoautomotor, matricula, modelo)
                values(null, '.$this->idTipoAutomotor.', \''.mysql_real_escape_string($this->matricula).'\','.(!empty($this->modelo)?$this->modelo:date('Y')).')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idAutomotor', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('matricula'));
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idAutomotor);

            if($this->estaModificada('idTipoAutomotor')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idTipoAutomotor'));
                $cambios[]='idtipoautomotor=\''.$this->idTipoAutomotor;
                $this->marcarNoModificada('idTipoAutomotor');
            }

            if($this->estaModificada('matricula')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('matricula'));
                $cambios[]='matricula=\''.mysql_real_escape_string($this->matricula).'\'';
                $this->marcarNoModificada('matricula');
            }

            if($this->estaModificada('modelo')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('modelo'));
                $cambios[]='modelo='.(!empty($this->modelo)?$this->modelo:date('Y'));
                $this->marcarNoModificada('modelo');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update automotor set $update where idautomotor=".$this->idAutomotor;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idAutomotor;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idAutomotor)){
            throw new AppException('El automotor no existe',
                (object)array($this->getNombreJson('idautomotor')=>'El automotor no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El automotor no puede ser borrado, este ha sido utilizado');

        $sql='delete from automotor where idautomotor='.$this->idAutomotor;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idAutomotor);
        $modificacion->guardarObjeto($auditoria);
    }

    public function getIdAutomotor(){
        return $this->idAutomotor;
    }

    public function setTipoAutomotor(TipoAutomotor $tipoAutomotor){
        $valor=$tipoAutomotor->getIdTipoAutomotor();
        if(empty($valor))
            throw new AppException('El tipoAutomotor no existe',
                (object)array($this->getNombreJson('idtipoautomotor')=>'El tipoAutomotor no existe'));

        return $this->setPropiedad('idTipoAutomotor', $valor);
    }

    public function getTipoAutomotor(){
        return new TipoAutomotor($this->idTipoAutomotor);
    }

    public function getIdTipoAutomotor(){
        return $this->idTipoAutomotor;
    }

    public function setMatricula($matricula){
        $valor=(string)$matricula;
        if(mb_strlen($valor)>256)
            throw new AppException('El matricula puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('matricula')=>'El matricula puede tener maximo 256 caracteres'));

        return $this->setPropiedad('matricula', $valor);
    }

    public function getMatricula(){
        return $this->matricula;
    }

    public function setModelo($modelo){
        $valor=(int)$modelo;
        if($valor>2020 || $valor<1900)
            throw new AppException('El modelo no es valido',
                (object)array($this->getNombreJson('modelo')=>'El modelo no es valido'));

        return $this->setPropiedad('modelo', $valor);
    }

    public function getModelo(){
        return $this->modelo;
    }

    public function haSidoUtilizado() {
        return false;
    }
}

?>
