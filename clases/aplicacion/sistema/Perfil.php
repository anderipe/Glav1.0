<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Auditoria.php';
require_once 'ModuloPerfil.php';

/**
 * Clase que representa un perfil de usuario. Un perfil de usuario es un
 * conjunto logico de modulos que puede ser asignado a un usuario y es la base
 * del sub-sistema de permisos y autorizacion del sistema.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Perfil
    extends ClaseBase{

    protected $idPerfil=0;

    protected $nombre='';

    protected $estado=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idPerfil', 0);
        $this->setPropiedad('nombre', '');
        $this->setPropiedad('estado', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idperfil='.$id))
                throw new AppException('No existe perfil con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idPerfil))
            throw new AppException('El perfil ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from perfil where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un perfil para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idPerfil', (int)$resultados->get()->idperfil, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->setPropiedad('estado', (int)$resultados->get()->estado, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->nombre))
            throw new AppException('El nombre del perfil es obligatorio',
                (object)array($this->getNombreJson('nombre')=>'El nombre del modulo es obligatorio'));

        if(empty($this->idPerfil)){
            $sql='insert INTO perfil
                (idperfil, nombre, estado)
                values(null,\''.mysql_real_escape_string($this->nombre).'\','.$this->estado.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idPerfil', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
            $modificacion->addDescripcion($this->getTextoParaAuditoria('estado'));
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idPerfil);
            if($this->estaModificada('nombre')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
                $cambios[]='nombre=\''.mysql_real_escape_string($this->nombre).'\'';
                $this->marcarNoModificada('nombre');
            }

            if($this->estaModificada('estado')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('estado'));
                $cambios[]='estado='.$this->estado;
                $this->marcarNoModificada('estado');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update perfil set $update where idperfil=".$this->idPerfil;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }
        $this->modificado=false;
        return $this->idPerfil;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idPerfil)){
            throw new AppException('El perfil no existe',
                (object)array($this->getNombreJson('idperfil')=>'El perfil no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El perfil no puede ser borrado, este ha sido utilizado');

        $sql='delete from perfil where idperfil='.$this->idPerfil;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idPerfil);
        $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
        $modificacion->guardarObjeto($auditoria);
    }

    public function estaAsignado(){
        $sql='select count(idusuario) total from perfilusuario where idperfil='.$this->idPerfil;
        $resultado=$this->conexion->consultar($sql);

        return (int)$resultado->get(0)->total;
    }

    public function getIdPerfil(){
        return $this->idPerfil;
    }

    public function getNombre(){
        return $this->nombre;
    }

    public function setNombre($nombre){
        $valor=(string)$nombre;
        if(mb_strlen($valor)>64)
            throw new AppException('El nombre puede tener maximo 64 caracteres',
                (object)array($this->getNombreJson('nombre')=>'El nombre puede tener maximo 64 caracteres'));

        return $this->setPropiedad('nombre', $valor);
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

    public function agregarModulo(Modulo $modulo, Auditoria $auditoria){
        $modulosHijos=$modulo->getModulosHijo('modulo');
        if(count($modulosHijos)==0){
            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($this->idPerfil);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
            $modificacion->addDescripcion('+ modulo= '.$modulo->getTextoParaAuditoria('idModulo').', '.$modulo->getTextoParaAuditoria('nombre'));
            $modificacion->guardarObjeto($auditoria);

            $moduloPerfil=new ModuloPerfil();
            $moduloPerfil->setPerfil($this);
            $moduloPerfil->setModulo($modulo);
            $moduloPerfil->guardarObjeto($auditoria);
        }else{
            foreach ($modulosHijos as $moduloHijo){
                $moduloPerfil=new ModuloPerfil();
                if(!$moduloPerfil->cargarPorModuloPerfil($this, $moduloHijo))
                    $this->agregarModulo($moduloHijo, $auditoria);
            }
        }
    }

    public function quitarModulo(Modulo $modulo, Auditoria $auditoria){
        $modulosHijos=$modulo->getModulosHijo('modulo');
        if(count($modulosHijos)==0){
            $moduloPerfil=new ModuloPerfil();
            if($moduloPerfil->cargarPorModuloPerfil($this, $modulo)){
                $modificacion= new Modificacion();
                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->addDescripcionId($this->idPerfil);
                $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
                $modificacion->addDescripcion('- modulo= '.$modulo->getTextoParaAuditoria('idModulo').', '.$modulo->getTextoParaAuditoria('nombre'));
                $modificacion->guardarObjeto($auditoria);

                $moduloPerfil->borrarObjeto($auditoria);
            }
        }else{
            foreach ($modulosHijos as $moduloHijo){
                $this->quitarModulo($moduloHijo, $auditoria);
            }
        }
    }

    public static function getPerfiles($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON || $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from perfil order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados->getRegistros());
            else
                return (array)$resultados->getRegistros();
        }else{
            $objetos=array();
            $sql='select idperfil from perfil order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Perfil($resultados->get()->idperfil);

            return $objetos;
        }
    }

    public function haSidoUtilizado() {
        $sql='select idperfilusuario from perfilusuario where idperfil='.$this->idPerfil.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        $sql='select idmoduloperfil from moduloperfil where idperfil='.$this->idPerfil.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>
