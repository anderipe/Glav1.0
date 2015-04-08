<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Perfil.php';
require_once 'Modulo.php';

/**
 * Clase que representa un modulo incluido en un perfil de usuario.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class ModuloPerfil
    extends ClaseBase{

    protected $idModuloPerfil=0;

    protected $idPerfil=0;

    protected $idModulo=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idModuloPerfil', 0);
        $this->setPropiedad('idModulo', 0);
        $this->setPropiedad('idPerfil', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idmoduloperfil='.$id))
                throw new AppException('No existe perfil de modulo con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idModuloPerfil))
            throw new AppException('El modulo de perfil ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from moduloperfil where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un modulo de perfil para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idModuloPerfil', (int)$resultados->get()->idmoduloperfil, true);
        $this->setPropiedad('idModulo', (int)$resultados->get()->idmodulo, true);
        $this->setPropiedad('idPerfil', (int)$resultados->get()->idperfil, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function cargarPorModuloPerfil(Perfil $perfil, Modulo $modulo){
        $where='idperfil='.$perfil->getIdPerfil().' and idmodulo='.$modulo->getIdModulo();
        return $this->cargarObjeto($where);
    }

    public function setModulo(Modulo $modulo){
        $valor=$modulo->getIdModulo();
        if(empty($valor))
            throw new AppException('El modulo no existe',
                (object)array($this->getNombreJson('idmodulo')=>'El modulo no existe'));

        return $this->setPropiedad('idModulo', $valor);
    }

    public function setPerfil(Perfil $perfil){
        $valor=$perfil->getIdPerfil();
        if(empty($valor))
            throw new AppException('El perfil no existe',
                (object)array($this->getNombreJson('idperfil')=>'El perfil no existe'));

        return $this->setPropiedad('idPerfil', $valor);
    }

    public function getModulo(){
        return new Modulo($this->idModulo);
    }

    public function getPerfil(){
        return new Perfil($this->idPerfil);
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(!empty($this->idModuloPerfil))
            throw new AppException('No se permiten modificaciones sobre un moduloperfil, solo puede agregar y eliminar',
                (object)array($this->getNombreJson('idoduloperfil')=>'No se permiten modificaciones sobre un moduloperfil, solo puede agregar y eliminar'));

        if(empty($this->idModulo))
            throw new AppException('El modulo es obligatorio',
                (object)array($this->getNombreJson('idmodulo')=>'El modulo es obligatorio'));

        if(empty($this->idPerfil))
            throw new AppException('El perfil es obligatorio',
                (object)array($this->getNombreJson('idperfil')=>'El perfil es obligatorio'));

        $moduloPerfil=new ModuloPerfil();
        if($moduloPerfil->cargarPorModuloPerfil($this->getPerfil(), $this->getModulo()))
            throw new AppException('Ya existe el modulo en el perfil',
                (object)array($this->getNombreJson('idmoduloperfil')=>'Ya existe el modulo en el perfil'));

        $sql='insert INTO moduloperfil
                (idmoduloperfil, idmodulo, idperfil)
                values(null,'.$this->idModulo.','.$this->idPerfil.')';
        $id=$this->conexion->ejecutar($sql);
        $this->setPropiedad('idModuloPerfil', (int)$id);

        $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

        $modulo=$this->getModulo();
        $perfil=$this->getPerfil();

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($id);
        $modificacion->addDescripcion('perfil= '.$perfil->getTextoParaAuditoria('idPerfil').', '.$perfil->getTextoParaAuditoria('nombre'));
        $modificacion->addDescripcion('modulo= '.$modulo->getTextoParaAuditoria('idModulo').', '.$modulo->getTextoParaAuditoria('nombre'));
        $modificacion->guardarObjeto($auditoria);

        $this->modificado=false;
        return $this->idModuloPerfil;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idModuloPerfil)){
            throw new AppException('El modeulo de perfil no existe',
                (object)array($this->getNombreJson('idModuloPerfil')=>'El modeulo de perfil no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El modulo de perfil no puede ser borrado, este ha sido utilizado');

        $sql='delete from moduloperfil where idmoduloperfil='.$this->idModuloPerfil;
        $this->conexion->ejecutar($sql);

        $modulo=$this->getModulo();
        $perfil=$this->getPerfil();

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idModuloPerfil);
        $modificacion->addDescripcion('perfil= '.$perfil->getTextoParaAuditoria('idPerfil').', '.$perfil->getTextoParaAuditoria('nombre'));
        $modificacion->addDescripcion('modulo= '.$modulo->getTextoParaAuditoria('idModulo').', '.$modulo->getTextoParaAuditoria('nombre'));
        $modificacion->guardarObjeto($auditoria);
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>
