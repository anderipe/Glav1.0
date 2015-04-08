<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Variable.php';
require_once 'Usuario.php';

/**
 * Clase que representa una variable del sistema especificamente asignada a un
 * usuario del sistema. Esto es util cuando cada usuario del sistema necesita
 * definir para si mismo un comportamiento particular del sistema
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class VariableUsuario
    extends ClaseBase {

    protected $idVariableUsuario=0;

    protected $idUsuario=0;

    protected $idVariable=0;

    protected $valor='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idVariableUsuario', 0);
        $this->setPropiedad('idUsuario', 0);
        $this->setPropiedad('idVariable', 0);
        $this->setPropiedad('valor', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idvariableusuario='.$id))
                throw new AppException('No existe variable de usuario con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idVariableUsuario))
            throw new AppException('La variable de usuario ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from variableusuario where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un variable de usuario para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idVariableUsuario', (int)$resultados->get()->idvariableusuario, true);
        $this->setPropiedad('idUsuario', (int)$resultados->get()->idusuario, true);
        $this->setPropiedad('idVariable', (int)$resultados->get()->idvariable, true);
        $this->setPropiedad('valor', (int)$resultados->get()->valor, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function cargarPorVariableUsuario(Usuario $usuario, Variable $variable){
        $where='idusuario='.$usuario->getIdUsuario().' and idvariable='.$variable->getIdVariable();
        return $this->cargarObjeto($where);
    }

    public function getIdVariableUsuario(){
        return $this->idVariableUsuario;
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

    public function getIdVariable(){
        return $this->idVariable;
    }

    /**
     *
     * @return Variable
     */
    public function getVariable(){
        return new Variable($this->idVariable);
    }

    public function setVariable(Variable $variable){
        $valor=$variable->getIdVariable();
        if(empty($valor))
            throw new AppException('La variable no existe',
                (object)array($this->getNombreJson('idvariable')=>'La variable no existe'));

        return $this->setPropiedad('idVariable', $valor);
    }

    public function getValor(){
        return $this->valor;
    }

    public function setValor($valor){
        $valor=trim($valor);
        if(mb_strlen($valor)>255)
            throw new AppException('El valor de la variable no puede tener mas de 255 caracteres',
                (object)array($this->getNombreJson('valor')=>'El valor de la variable no puede tener mas de 255 caracteres'));

        return $this->setPropiedad('valor', $valor);
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idUsuario))
            throw new AppException('El usuario es un dato obligatorio',
                (object)array($this->getNombreJson('idusuario')=>'El usuario es un dato obligatorio'));

        if(empty($this->idVariable))
            throw new AppException('La variable es un dato obligatorio',
                (object)array($this->getNombreJson('idvariable')=>'La variable es un dato obligatorio'));


        if(empty($this->idVariableUsuario)){
            $sql='insert INTO variableusuario
                (idvariableusuario, idusuario, idvariable, valor)
                values(null, '.$this->idUsuario.','.$this->idVariable.', \''.mysql_real_escape_string($this->valor).'\')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idVariableUsuario', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('idUsuario'));
            $modificacion->addDescripcion($this->getTextoParaAuditoria('idVariable'));
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idVariableUsuario);

            if($this->estaModificada('idUsuario')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idUsuario'));
                $cambios[]='idusuario='.$this->idUsuario;
                $this->marcarNoModificada('idUsuario');
            }

            if($this->estaModificada('idVariable')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idVariable'));
                $cambios[]='idvariable='.$this->idVariable;
                $this->marcarNoModificada('idVariable');
            }

            if($this->estaModificada('valor')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('valor'));
                $cambios[]='valor=\''.mysql_real_escape_string($this->valor).'\'';
                $this->marcarNoModificada('valor');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update variableusuario set $update where idvariableusuario=".$this->idVariableUsuario;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idVariableUsuario;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idVariableUsuario)){
            throw new AppException('La variable de usuario no existe',
                (object)array($this->getNombreJson('idvariable')=>'La variable de usuario no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('La variable de usuario no puede ser borrada, esta ha sido utilizada');

        $sql='delete from variableusuario where idvariableusuario='.$this->idVariableUsuario;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idVariableUsuario);
        $modificacion->guardarObjeto($auditoria);
    }

    public function haSidoUtilizado() {
        return true;
    }
}

?>
