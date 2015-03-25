<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';

/**
 * Clase que representa un modulo en el sistema. UN modulo es una interfaz de
 * usuario independiente, generalmente accedida desde una opcion en el menu
 * del sistema.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Modulo
    extends ClaseBase{

    protected $idModulo=0;

    protected $idModuloPadre=0;

    protected $nombre='';

    protected $clase='';

    protected $iconCss='';

    protected $orden=0;

    /**
     *
     * @var Modulo
     */
    protected $moduloPadre=0;

    /**
     *
     * @var Modulo
     */
    protected $moduloHijo;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idModulo', 0);
        $this->setPropiedad('idModuloPadre', 0);
        $this->setPropiedad('nombre', '');
        $this->setPropiedad('clase', '');
        $this->setPropiedad('orden', 0);
        $this->setPropiedad('iconCss', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idmodulo='.$id))
                throw new AppException('No existe modulo con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idModulo))
            throw new AppException('El modulo ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from modulo where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un modulo para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idModulo', (int)$resultados->get()->idmodulo, true);
        $this->setPropiedad('idModuloPadre', (int)$resultados->get()->idmodulopadre, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->setPropiedad('clase', (string)$resultados->get()->clase, true);
        $this->setPropiedad('orden', (int)$resultados->get()->orden, true);
        $this->setPropiedad('iconCss', (string)$resultados->get()->iconcss, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idModuloPadre))
            throw new AppException('El modulo padre es obligatorio',
                (object)array($this->getNombreJson('idmodulopadre')=>'El modulo padre es obligatorio'));

        if(empty($this->nombre))
            throw new AppException('El nombre del modulo es obligatorio',
                (object)array($this->getNombreJson('nombre')=>'El nombre del modulo es obligatorio'));

        if(empty($this->idModulo)){
            $sql='insert INTO modulo
                (idmodulo, idmodulopadre, nombre, clase, orden, iconcss)
                values(null,'.$this->idModuloPadre.',\''.mysql_real_escape_string($this->nombre).'\',\''.mysql_real_escape_string($this->clase).'\','.$this->orden.',\''.mysql_real_escape_string($this->iconCss).'\')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idModulo', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $moduloPadre=$this->getModuloPadre();

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
            $modificacion->addDescripcion('padre='.$this->getTextoParaAuditoria('idModuloPadre').', '.$moduloPadre->getTextoParaAuditoria('nombre'));
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idModulo);
            if($this->estaModificada('idModuloPadre')){
                $moduloPadre=$this->getModuloPadre();
                $modificacion->addDescripcion('padre='.$this->getTextoParaAuditoria('idModuloPadre').', '.$moduloPadre->getTextoParaAuditoria('nombre'));
                $cambios[]='idmodulopadre='.$this->idModuloPadre;
                $this->marcarNoModificada('idModuloPadre');
            }

            if($this->estaModificada('nombre')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
                $cambios[]='nombre=\''.mysql_real_escape_string($this->nombre).'\'';
                $this->marcarNoModificada('nombre');
            }

            if($this->estaModificada('clase')){
                if(!empty($this->clase)){
                    if($this->tieneHijos()){
                        throw new AppException('El modulo tiene sub-modulos y no se puede asociar la ejecucion de una tarea',
                            (object)array($this->getNombreJson('idmodulo')=>'El modulo tiene sub-modulos y no se puede asociar la ejecucion de una tarea'));
                    }
                }
                $modificacion->addDescripcion($this->getTextoParaAuditoria('clase'));
                $cambios[]='clase=\''.mysql_real_escape_string($this->clase).'\'';
                $this->marcarNoModificada('clase');
            }

            if($this->estaModificada('iconCss')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('iconCss'));
                $cambios[]='iconcss=\''.mysql_real_escape_string($this->iconCss).'\'';
                $this->marcarNoModificada('iconCss');
            }

            if($this->estaModificada('orden')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('orden'));
                $cambios[]='orden='.$this->orden;
                $this->marcarNoModificada('orden');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update modulo set $update where idmodulo=".$this->idModulo;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $moduloPadre=$this->getModuloPadre();
        if($moduloPadre->getClase()!=''){
            $moduloPadre->setClase('');
            $auditoria->setDescripcionSiguienteAccionAuditable('Soy modulo padre, no debo ejecutar tarea');
            $moduloPadre->guardarObjeto($auditoria);
        }

        $sql='select idmodulo from modulo where idmodulopadre='.$this->idModuloPadre.' and orden='.$this->orden.' and idmodulo<>'.$this->idModulo;
        $resultados=$this->conexion->consultar($sql);
        while($resultados->irASiguiente()){
            $moduloConIgualOrden=new Modulo($resultados->get()->idmodulo);
            $moduloConIgualOrden->setOrden($moduloConIgualOrden->getOrden()+1);
            $moduloConIgualOrden->guardarObjeto($auditoria);
        }
        $this->modificado=false;
        return $this->idModulo;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if($this->tieneHijos()>0)
            throw new AppException('El modulo tiene hijos y no puede ser borrado. Elimine primero los hijos del modulo',
                (object)array($this->getNombreJson('idModulo')=>'El modulo tiene hijos y no puede ser borrado. Elimine primero los hijos del modulo'));

        if($this->estaEnPerfiles()>0)
            throw new AppException('El modulo se encuentra vinculado a algún perfil y no puede ser borrado. Elimine el modulo de todos los perfiles',
                (object)array($this->getNombreJson('idModulo')=>'El modulo se encuentra vinculado a algún perfil y no puede ser borrado. Elimine el modulo de todos los perfiles'));

        if($this->haSidoUtilizado())
            throw new AppException('El modulo no puede ser borrado, este ha sido utilizado');

        $sql='delete from modulo where idmodulo='.$this->idModulo;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idModulo);
        $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
        $modificacion->guardarObjeto($auditoria);
    }

    public function cargarPorClase($clase){
        if(!$this->cargarObjeto('clase=\''.mysql_real_escape_string($clase).'\''))
                throw new AppException('No existe modulo con clase '.$clase);
    }

    public static function crearPorClase($clase){
        $modulo=new Modulo;
        $modulo->cargarPorClase($clase);
        return $modulo;
    }

    public function tieneHijos(){
        $sql='select count(idmodulo) total from modulo where idmodulopadre='.$this->idModulo.'';
        $resultado=$this->conexion->consultar($sql);

        return (int)$resultado->get(0)->total;
    }

    public function estaEnPerfiles(){
        $sql='select count(idmodulo) total from moduloperfil where idmodulo='.$this->idModulo.'';
        $resultado=$this->conexion->consultar($sql);

        return (int)$resultado->get(0)->total;
    }

    public function getIdModulo(){
        return $this->idModulo;
    }

    public function getIdModuloPadre(){
        return $this->idModuloPadre;
    }

    public function setModuloPadre(Modulo $modulo){
        $valor=$modulo->getIdModulo();
        if(empty($valor))
            throw new AppException('El modulo padre no existe',
                (object)array($this->getNombreJson('idmodulopadre')=>'El modulo padre no existe'));

        return $this->setPropiedad('idModuloPadre', $valor);
    }

    /**
     *
     * @return \Modulo
     */
    public function getModuloPadre(){
        if($this->idModulo===$this->idModuloPadre)
            return $this;

        return new Modulo($this->idModuloPadre);
    }

    public function getClase(){
        return $this->clase;
    }

    public function setClase($clase){
        $valor=(string)$clase;
        if(mb_strlen($valor)>64)
            throw new AppException('La clase puede tener maximo 64 caracteres',
                (object)array($this->getNombreJson('clase')=>'La clase puede tener maximo 64 caracteres'));

        return $this->setPropiedad('clase', $valor);
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

    public function getOrden(){
        return $this->orden;
    }

    public function setOrden($orden){
        $valor=(int)$orden;
        if($valor<=0)
            throw new AppException('El orden debe ser mayor a cero',
                (object)array($this->getNombreJson('orden')=>'El orden debe ser mayor a cero'));

        return $this->setPropiedad('orden', $valor);
    }

    public function getIconCss(){
        return $this->iconCss;
    }

    public function setIconCss($iconCss){
        $valor=(string)$iconCss;
        if(mb_strlen($valor)>64)
            throw new AppException('El iconCss puede tener maximo 64 caracteres',
                (object)array($this->getNombreJson('iconcss')=>'El iconCss puede tener maximo 64 caracteres'));

        return $this->setPropiedad('iconCss', $valor);
    }

    /**
     *
     * @return \Modulo
     */
    public function getModulosHijo($tableName){
        $sql='select idmodulo from '.$tableName.' where idmodulopadre='.$this->idModulo. ' and idmodulo<>'.$this->idModulo.' order by orden';
        $resultados=$this->conexion->consultar($sql);
        $hijos=array();
        while($resultados->irASiguiente()){
            $hijos[]=new Modulo($resultados->get()->idmodulo);
        }
        return $hijos;
    }

    public function getUltimoOrdenHijo(){
        $sql='select max(orden) as ultimo from modulo where idmodulopadre='.$this->idModulo.' and idmodulo<>'.$this->idModulo;
        $resultado=$this->conexion->consultar($sql);
        return (int)$resultado->get(0)->ultimo;
    }

    /**
     *
     * @param type $tableName
     * @param string $tipoBoton Puede ser 'button' o 'menuitem'
     * @return null|\stdClass
     */
    public function getButton($tableName, $tipoBoton){
        $button=new stdClass();
        $button->xtype=$tipoBoton;
        $button->text=$this->nombre;
        $button->clase=$this->clase;
        //if(!empty($this->iconCss))
            $button->iconCls=$this->iconCss;

        $items=array();

        $modulosHijo=$this->getModulosHijo($tableName);
        foreach ($modulosHijo as $modulo) {
            $menu=$modulo->getButton($tableName, 'menuitem');
            if(!empty($menu))
                $items[]=$menu;
        }

        if(count($items)==0 && empty($button->clase))
            return null;

        if(count($items)!=0){
            $button->menu=new stdClass();
            $button->menu->xtype="menu";
            $button->menu->items=$items;
        }

        return $button;
    }

    public function getButtonGroup($tableName){
        $buttongroup=new stdClass();
        $buttongroup->xtype="buttongroup";
        $buttongroup->title=$this->nombre;
        $buttongroup->columns=10;//?
        $buttongroup->layout=new stdClass();
        $buttongroup->layout->columns=10;
        $buttongroup->layout->type="table";
        $buttongroup->items=array();

        $modulosHijo=$this->getModulosHijo($tableName);
        foreach ($modulosHijo as $modulo) {
            $button=$modulo->getButton($tableName, 'button');
            if(!empty($button))
                $buttongroup->items[]=$button;
        }

        if(count($buttongroup->items)==0)
            return null;

        return $buttongroup;
    }

    public function getToolBar($tableName){
        $toobar=new stdClass();
        $toobar->xtype="toolbar";
        $toobar->items=array();

        $modulosHijo=$this->getModulosHijo($tableName);
        foreach ($modulosHijo as $modulo) {
            $buttongroup=$modulo->getButtonGroup($tableName);
            if(!empty($buttongroup)){
                $toobar->items[]=$buttongroup;
            }
        }

        return $toobar;
    }

    public static function getModulos($clase, $formato){
        $formato=(int)$formato;
        $clase=  mb_strtolower(trim($clase));

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select idmodulo, clase, nombre from modulo as m
                where clase<>\'\' ';
            if(!empty($clase))
                $sql.=' and lower(clase) like \'%'.mysql_real_escape_string ($clase).'%\' ';
            $sql.='
                and not exists (select 1 from modulo where modulo.idmodulopadre=m.idmodulo)
                order by clase';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idmodulo from modulo as m
                where clase<>\'\' ';
            if(!empty($clase))
                $sql.=' and lower(clase) like \'%'.mysql_real_escape_string ($clase).'%\' ';
            $sql.='
                and not exists (select 1 from modulo where modulo.idmodulopadre=m.idmodulo)
                order by clase';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Modulo($resultados->get()->idmodulo);

            return $objetos;
        }
    }

    public function haSidoUtilizado() {
        $sql='select idauditoria from auditoria where idmodulo='.$this->idModulo.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        $sql='select idmoduloperfil from moduloperfil where idmodulo='.$this->idModulo.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        $sql='select idmodulo total from modulo where idmodulopadre='.$this->idModulo.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>