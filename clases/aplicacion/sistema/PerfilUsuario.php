<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Perfil.php';
require_once 'Usuario.php';

/**
 * Clase que representa un perfil asignado a un usuario.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class PerfilUsuario
    extends ClaseBase {

    protected $idPerfilUsuario=0;

    protected $idUsuario=0;

    protected $idPerfil=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idPerfilUsuario', 0);
        $this->setPropiedad('idUsuario', 0);
        $this->setPropiedad('idPerfil', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idperfilusuario='.$id))
                throw new AppException('No existe perfil de usuario con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idPerfilUsuario))
            throw new AppException('El perfil de usuario ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from perfilusuario where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un perfil de usuario para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idPerfilUsuario', (int)$resultados->get()->idperfilusuario, true);
        $this->setPropiedad('idUsuario', (int)$resultados->get()->idusuario, true);
        $this->setPropiedad('idPerfil', (int)$resultados->get()->idperfil, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function cargarPorPerfilUsuario(Usuario $usuario, Perfil $perfil){
        $where='idusuario='.$usuario->getIdUsuario().' and idperfil='.$perfil->getIdPerfil();
        return $this->cargarObjeto($where);
    }

    public function getIdPerfilUsuario(){
        return $this->idPerfilUsuario;
    }

    public function getIdUsuario(){
        return $this->idUsuario;
    }

    /**
     *
     * @return \Usuario
     */
    public function getUsuario(){
        return new Usuario($this->idUsuario);
    }

    public function setUsuario(Usuario $usuario){
        $valor=$usuario->getIdUsuario();
        if(empty($valor))
            throw new AppException('El usuario no existe',
                (object)array($this->getNombreJson('idusuario')=>'El usuario no existe'));

        return $this->setPropiedad('idUsuario', $valor);
    }

    public function getIdPerfil(){
        return $this->idPerfil;
    }

    /**
     *
     * @return Perfil
     */
    public function getPerfil(){
        return new Perfil($this->idPerfil);
    }

    public function setPerfil(Perfil $perfil){
        $valor=$perfil->getIdPerfil();
        if(empty($valor))
            throw new AppException('El perfil no existe',
                (object)array($this->getNombreJson('idperfil')=>'El perfil no existe'));

        return $this->setPropiedad('idPerfil', $valor);
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idUsuario))
            throw new AppException('El usuario es un dato obligatorio',
                (object)array($this->getNombreJson('idusuario')=>'El usuario es un dato obligatorio'));

        if(empty($this->idPerfil))
            throw new AppException('El perfil es un dato obligatorio',
                (object)array($this->getNombreJson('idperfil')=>'El perfil es un dato obligatorio'));


        if(empty($this->idPerfilUsuario)){
            $usuario=new PerfilUsuario();
            $usuario->cargarPorPerfilUsuario(new Usuario($this->idUsuario), new Perfil($this->idPerfil));
            $idPerfilUsuario=$usuario->getIdPerfilUsuario();
            if(!empty($idPerfilUsuario))
                throw new AppException('El perfil ya esta asignado al usuario',
                    (object)array($this->getNombreJson('idperfil')=>'El perfil ya esta asignado al usuario'));

            $sql='insert INTO perfilusuario
                (idperfilusuario, idusuario, idperfil)
                values(null, '.$this->idUsuario.','.$this->idPerfil.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idPerfilUsuario', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('idUsuario'));
            $modificacion->addDescripcion($this->getTextoParaAuditoria('idPerfil'));
            $modificacion->guardarObjeto($auditoria);
        }else{
            throw new AppException('No se permite la modificacion de perfiles usuarios',
                (object)array($this->getNombreJson('idperfilusuario')=>'No se permite la modificacion de perfiles usuarios'));
        }

        $this->modificado=false;
        return $this->idPerfilUsuario;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idPerfilUsuario)){
            throw new AppException('El perfil de usuario no existe',
                (object)array($this->getNombreJson('idperfil')=>'El perfil de usuario no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El perfil de usuario no puede ser borrado, este ha sido utilizado');

        $sql='delete from perfilusuario where idperfilusuario='.$this->idPerfilUsuario;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idPerfilUsuario);
        $modificacion->guardarObjeto($auditoria);
    }

    public function haSidoUtilizado() {
        return false;
    }
}

?>
