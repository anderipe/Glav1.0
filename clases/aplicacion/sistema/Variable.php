<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'CategoriaVariable.php';

/**
 * Clase que representa una variable del sistema. Las variables del sistema son
 * fragmentos de informacion canstante que sirven para parametrizar el
 * comportamiento del sistema y en general deben ser entendidas como unidades
 * auxiliares de datos. UNa variable del sistema por ejemplo, puede definir
 * la ubicacion del certificado digital con que se garantiza la integridad de
 * los datos de la db
 * otros
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Variable
    extends ClaseBase{

    protected $idVariable=0;

    protected $idCategoriaVariable=0;

    protected $nombre='';

    protected $valor='';

    protected $deUsuario=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idVariable', (int)0);
        $this->setPropiedad('idCategoriaVariable', (int)0);
        $this->setPropiedad('nombre', '');
        $this->setPropiedad('valor', '');
        $this->setPropiedad('deUsuario', (int)0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idvariable='.$id))
                throw new AppException('No existe variable con identificador '.$id);
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idVariable)){
            throw new AppException('La variable no existe',
                (object)array($this->getNombreJson('idvariable')=>'La variable no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('La variable no puede ser borrada, esta ha sido utilizada');

        $sql='select idvariable from variableusuario where idvariable='.$this->idVariable;
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0){
            throw new AppException('La variable no puede ser borrada',
                (object)array($this->getNombreJson('idvariable')=>'La variable no puede ser borrada'));
        }

        $sql='delete from variable where idvariable='.$this->idVariable;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idVariable);
        $modificacion->guardarObjeto($auditoria);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idVariable))
            throw new AppException('La variable ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from variable where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una variable para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idVariable', (int)$resultados->get()->idvariable, true);
        $this->setPropiedad('idCategoriaVariable', (int)$resultados->get()->idcategoriavariable, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->setPropiedad('valor', (string)$resultados->get()->valor, true);
        $this->setPropiedad('deUsuario', (int)$resultados->get()->deusuario, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function getDeUsuario(){
        return $this->deusuario;
    }

    public function setDeUsuario($deUsuario){
        $valor=(int)$deUsuario;
        return $this->setPropiedad('deUsuario', $valor);
    }

    public function getIdVariable(){
        return $this->idVariable;
    }

    public function getIdCategoriaVariable(){
        return $this->idCategoriaVariable;
    }

    public function setNombre($nombre){
        $valor=(string)$nombre;
        if(mb_strlen($valor)<8 || mb_strlen($valor)>255)
            throw new AppException('El nombre de variable debe tener entre 8 y 255 caracteres',
                (object)array($this->getNombreJson('nombre')=>'El nombre de variable debe tener entre 8 y 255 caracteres'));

        return $this->setPropiedad('nombre', $valor);
    }

    public function setValor($valor){
        //$valor=(string)$valor;
        //if(mb_strlen($valor)<8 || mb_strlen($valor)>255)
        //    throw new AppException('El valor de variable debe tener entre 8 y 255 caracteres',
        //        (object)array($this->getNombreJson('valor')=>'El valor de variable debe tener entre 8 y 255 caracteres'));

        //$valor= sha1(md5($valor));
        return $this->setPropiedad('valor', $valor);
    }

    public function getValor(){
        return $this->valor;
    }

    /**
     *
     * @return \CategoriaVariable
     */
    public function getCategoriaVariable(){
        return new CategoriaVariable($this->idCategoriaVariable);
    }

    public function setCategoriaVariable(CategoriaVariable $categoriavariable){
        $valor=$categoriavariable->getIdCategoriaVariable();
        if(empty($valor))
            throw new AppException('La categoriavariable no existe',
                (object)array($this->getNombreJson('idcategoriavariable')=>'La categoriavariable no existe'));

        return $this->setPropiedad('idCategoriaVariable', $valor);
    }

    public function guardarObjeto(Auditoria $auditoria=null){
        if(empty($this->idCategoriaVariable))
            throw new AppException('La categoriavariable es un dato obligatorio',
                (object)array($this->getNombreJson('idcategoriavariable')=>'La categoriavariable es un dato obligatorio'));

        if(empty($this->nombre))
            throw new AppException('El nombre es obligatorio',
                (object)array($this->getNombreJson('nombre')=>'El nombre es obligatorio'));

        if(empty($this->valor))
            throw new AppException('El valor es obligatorio',
                (object)array($this->getNombreJson('valor')=>'El valor es obligatorio'));

        if(empty($this->idVariable)){

            $sql='insert INTO variable
                (idvariable, idcategoriavariable, deUsuario, nombre, valor)
                values(null, '.$this->idCategoriaVariable.', '.$this->deusuario.', \''.mysql_real_escape_string($this->nombre).'\', \''.mysql_real_escape_string($this->valor).'\')';
            $id=$this->conexion->ejecutar($sql);

            $this->setPropiedad('idVariable', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idCategoriaVariable);

            if($this->estaModificada('idCategoriaVariable')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idCategoriaVariable'));
                $cambios[]='idcategoriavariable='.$this->idCategoriaVariable;
                $this->marcarNoModificada('idCategoriaVariable');
            }

            if($this->estaModificada('deUsuario')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('deUsuario'));
                $cambios[]='deUsuario='.$this->deusuario;
                $this->marcarNoModificada('deUsuario');
            }

            if($this->estaModificada('nombre')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
                $cambios[]='nombre=\''.mysql_real_escape_string($this->nombre).'\'';
                $this->marcarNoModificada('nombre');
            }

            if($this->estaModificada('valor')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('valor'));
                $cambios[]='valor=\''.mysql_real_escape_string($this->valor).'\'';
                $this->marcarNoModificada('valor');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update variable set $update where idvariable=".$this->idVariable;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idVariable;
    }

    public function haSidoUtilizado() {
        $sql='select idvariableusuario from variableusuario where idvariable='.$this->idVariable.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>