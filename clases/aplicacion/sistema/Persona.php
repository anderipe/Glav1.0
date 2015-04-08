<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'TipoIdentificacion.php';
require_once 'Municipio.php';
require_once 'Validador.php';

/**
 * Clase que representa una persona en el sistema, ya sea natural o juridica.
 * Esta clase es la base para la definicion de usuarios, empleados, clientes,
 * etc
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Persona
    extends ClaseBase{

    protected $idPersona=0;

    protected $idTipoIdentificacion=0;

    protected $identificacion='';

    protected $nombres='';

    protected $direccion='';

    protected $telefonos='';

    protected $email='';

    protected $idPais=0;

    protected $fechaNacimiento=0;

    protected $sexo=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idPersona', 0);
        $this->setPropiedad('idTipoIdentificacion', 0);
        $this->setPropiedad('identificacion', '');
        $this->setPropiedad('nombres', '');
        $this->setPropiedad('direccion', '');
        $this->setPropiedad('telefonos', '');
        $this->setPropiedad('email', '');
        $this->setPropiedad('idPais', 0);
        $this->setPropiedad('fechaNacimiento', 0);
        $this->setPropiedad('sexo', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idpersona='.$id))
                throw new AppException('No existe persona con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idPersona))
            throw new AppException('La persona ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from persona where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una persona para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idPersona', (int)$resultados->get()->idpersona, true);
        $this->setPropiedad('idTipoIdentificacion', (int)$resultados->get()->idtipoidentificacion, true);
        $this->setPropiedad('identificacion', (int)$resultados->get()->identificacion, true);
        $this->setPropiedad('nombres', (string)$resultados->get()->nombres, true);
        $this->setPropiedad('direccion', (string)$resultados->get()->direccion, true);
        $this->setPropiedad('telefonos', (string)$resultados->get()->telefonos, true);
        $this->setPropiedad('email', (string)$resultados->get()->email, true);
        $this->setPropiedad('idPais', (int)$resultados->get()->idpais, true);
        $this->setPropiedad('fechaNacimiento', (string)$resultados->get()->fechanacimiento, true);
        $this->setPropiedad('sexo', (int)$resultados->get()->sexo, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function cargarPorIdentificacion(TipoIdentificacion $tipoIdentificacion, $identificacion){
        $where='idtipoidentificacion='.$tipoIdentificacion->getIdTipoIdentificacion().' and identificacion=\''.mysql_real_escape_string($identificacion).'\'';
        return $this->cargarObjeto($where);
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idPersona)){
            throw new AppException('La persona no existe',
                (object)array($this->getNombreJson('idpersona')=>'La persona no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('La persona no puede ser borrada, esta ha sido utilizada');

        $sql='delete from persona where idpersona='.$this->idPersona;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idPersona);
        $modificacion->guardarObjeto($auditoria);
    }

    public function guardarObjeto(Auditoria $auditoria=null){
        if(empty($this->idTipoIdentificacion))
            throw new AppException('El tipo de identificación es obligatorio',
                (object)array($this->getNombreJson('idtipoidentificacion')=>'El tipo de identificación es obligatorio'));

        if(empty($this->nombres))
            throw new AppException('El nombre es obligatorio',
                (object)array($this->getNombreJson('nombres')=>'El nombre es obligatorio'));

        if(empty($this->identificacion))
            throw new AppException('La identificacion obligatoria',
                (object)array($this->getNombreJson('identificacion')=>'La identificacion obligatoria'));

        if(empty($this->telefonos))
            throw new AppException('El telefono es obligatorio',
                (object)array($this->getNombreJson('telefono')=>'El telefono es obligatorio'));

        if(empty($this->fechaNacimiento))
            throw new AppException('La fecha de nacimiento es obligatoria',
                (object)array($this->getNombreJson('fechanacimiento')=>'La fecha de nacimiento es obligatoria'));

        if(empty($this->idPersona)){
            $persona=new Persona();
            if($persona->cargarPorIdentificacion($this->getTipoIdentificacion(), $this->identificacion))
                throw new AppException('Ya existe una persona registrada con la identificacion proporcionada',
                    (object)array($this->getNombreJson('identificacion')=>'Ya existe una persona registrada con la identificacion proporcionada'));

            $sql='insert INTO persona
                (idpersona, idtipoidentificacion, identificacion, nombres, idpais, direccion, telefonos, email, fechanacimiento, sexo)
                values(null, '.$this->idTipoIdentificacion.', \''.mysql_real_escape_string($this->identificacion).'\', \''.mysql_real_escape_string($this->nombres).'\', '.$this->idPais.', \''.mysql_real_escape_string($this->direccion).'\', \''.mysql_real_escape_string($this->telefonos).'\', \''.mysql_real_escape_string($this->email).'\', \''.mysql_real_escape_string($this->fechaNacimiento).'\', '.$this->sexo.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idPersona', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'   where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('identificacion'));
            $modificacion->addDescripcion($this->getTextoParaAuditoria('nombres'));
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idPersona);

            if($this->estaModificada('idTipoIdentificacion')){
                $persona=new Persona();
                if($persona->cargarPorIdentificacion($this->getTipoIdentificacion(), $this->identificacion))
                    throw new AppException('Ya existe una persona registrada con la identificacion proporcionada',
                        (object)array($this->getNombreJson('identificacion')=>'Ya existe una persona registrada con la identificacion proporcionada'));

                $modificacion->addDescripcion($this->getTextoParaAuditoria('idTipoIdentificacion'));
                $cambios[]='idtipoidentificacion='.$this->idTipoIdentificacion;
                $this->marcarNoModificada('idTipoIdentificacion');
            }

            if($this->estaModificada('identificacion')){
                $persona=new Persona();
                if($persona->cargarPorIdentificacion($this->getTipoIdentificacion(), $this->identificacion))
                    throw new AppException('Ya existe una persona registrada con la identificacion proporcionada',
                        (object)array($this->getNombreJson('identificacion')=>'Ya existe una persona registrada con la identificacion proporcionada'));

                $modificacion->addDescripcion($this->getTextoParaAuditoria('identificacion'));
                $cambios[]='identificacion=\''.mysql_real_escape_string($this->identificacion).'\'';
                $this->marcarNoModificada('identificacion');
            }

            if($this->estaModificada('nombres')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('nombres'));
                $cambios[]='nombres=\''.mysql_real_escape_string($this->nombres).'\'';
                $this->marcarNoModificada('nombres');
            }

            if($this->estaModificada('idPais')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idPais'));
                $cambios[]='idPais='.$this->idPais;
                $this->marcarNoModificada('idPais');
            }

            if($this->estaModificada('sexo')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('sexo'));
                $cambios[]='sexo='.$this->sexo;
                $this->marcarNoModificada('sexo');
            }

            if($this->estaModificada('direccion')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('direccion'));
                $cambios[]='direccion=\''.mysql_real_escape_string($this->direccion).'\'';
                $this->marcarNoModificada('direccion');
            }

            if($this->estaModificada('telefonos')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('telefonos'));
                $cambios[]='telefonos=\''.mysql_real_escape_string($this->telefonos).'\'';
                $this->marcarNoModificada('telefonos');
            }

            if($this->estaModificada('email')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('email'));
                $cambios[]='email=\''.mysql_real_escape_string($this->email).'\'';
                $this->marcarNoModificada('email');
            }

            if($this->estaModificada('fechaNacimiento')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('fechaNacimiento'));
                $cambios[]='fechanacimiento=\''.mysql_real_escape_string($this->fechaNacimiento).'\'';
                $this->marcarNoModificada('fechaNacimiento');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update persona set $update where idpersona=".$this->idPersona;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idPersona;
    }

    public function getIdPersona(){
        return $this->idPersona;
    }

    public function getIdTipoIdentificacion(){
        return $this->idTipoIdentificacion;
    }

    public function getTipoIdentificacion(){
        return new TipoIdentificacion($this->idTipoIdentificacion);
    }

    public function setTipoIdentificacion(TipoIdentificacion $tipoIdentificacion){
        $valor=$tipoIdentificacion->getIdTipoIdentificacion();
        if(empty($valor))
            throw new AppException('El tipo de identificación no existe',
                (object)array($this->getNombreJson('idtipoidentificacion')=>'El tipo de identificación no existe'));

        return $this->setPropiedad('idTipoIdentificacion', $valor);
    }

    public function getIdentificacion(){
        return $this->identificacion;
    }

    public function setIdentificacion($identificacion){
        $valor=(int)$identificacion;

        if(empty($valor) || $valor<=0)
            throw new AppException('La identificacion no es valida',
                (object)array($this->getNombreJson('identificacion')=>'La identificacion no es valida'));


        if(mb_strlen($valor)>64)
            throw new AppException('La identificacion puede tener maximo 64 caracteres',
                (object)array($this->getNombreJson('identificacion')=>'La identificacion puede tener maximo 64 caracteres'));

        return $this->setPropiedad('identificacion', $valor);
    }

    public function getNombres(){
        return $this->nombres;
    }

    public function getNombre(){
        $nombres=explode('|', $this->nombres);
        return $nombres[0];
    }

    public function getApellido(){
        $nombres=explode('|', $this->nombres);
        if(count($nombres)>1)
            return $nombres[1];
        else
            return '';
    }

    public function getNombreCompleto(){
        return Auxiliar::mb_str_replace('|', ' ', $this->nombres);
    }

    public function setNombres($nombres){
        $valor=(string)$nombres;
        if(mb_strlen($valor)>256)
            throw new AppException('Los nombres y apellidos pueden tener maximo 256 caracteres');

        if(!Validador::esAlfabetico(Auxiliar::mb_str_replace('|', ' ', $valor)))
            throw new AppException('Los nombres y apellidos pueden tener solo caracteres alfabeticos');

        return $this->setPropiedad('nombres', $valor);
    }

    public function getDireccion(){
        return $this->direccion;
    }

    public function setDireccion($direccion){
        $valor=(string)$direccion;
        if(mb_strlen($valor)>256)
            throw new AppException('La direccion puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('direccion')=>'La direccion puede tener maximo 256 caracteres'));

        return $this->setPropiedad('direccion', $valor);
    }

    public function getTelefonos(){
        return $this->telefonos;
    }

    public function setTelefonos($telefonos){
        $valor=(string)$telefonos;
        if(mb_strlen($valor)>256)
            throw new AppException('Los telefonos pueden tener maximo 256 caracteres',
                (object)array($this->getNombreJson('telefonos')=>'Los telefonos pueden tener maximo 256 caracteres'));

        return $this->setPropiedad('telefonos', $valor);
    }

    public function getEMail(){
        return $this->email;
    }

    public function setEMail($email){
        $valor=(string)$email;
        if(mb_strlen($valor)>256)
            throw new AppException('El EMail puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('email')=>'El EMail puede tener maximo 256 caracteres'));

        if(!empty($valor)){
            if(!Validador::esEMail($valor))
                throw new AppException('El EMail no es valido',
                    (object)array($this->getNombreJson('email')=>'El EMail no es valido'));
        }

        return $this->setPropiedad('email', $valor);
    }

    public function getIdPais(){
        return $this->idPais;
    }

    public function getNacionalidad(){
        return new Pais($this->idPais);
    }

    public function setNacionalidad(Pais $pais){
        $valor=$pais->getIdPais();
        if(empty($valor))
            throw new AppException('La nacionalidad no existe',
                (object)array($this->getNombreJson('idpais')=>'La nacionalidad no existe'));

        return $this->setPropiedad('idPais', $valor);
    }

    public function getFechaNacimiento(){
        return new DateTime($this->fechaNacimiento);
    }

    public function setFechaNacimiento(DateTime $fechaNacimiento){
        return $this->setPropiedad('fechaNacimiento', $fechaNacimiento->format('Y-m-d'));
    }

    public function getSexo(){
        return $this->sexo;
    }

    public function setSexo($sexo){
        $sexo=(int)$sexo;
        return $this->setPropiedad('sexo', $sexo);
    }

    public function getDigitoVerificacion(){
        return Validador::digitoVerificacion($this->identificacion);
    }

    public static function buscarPorIdentificacion(TipoIdentificacion $tipoIdentificacion, $identificacion, $formato, $personasNaturales=null, $offset=0, $limit=0){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON || $formato==RecordSet::FORMATO_OBJETO){
            $sql='select persona.*, replace(persona.nombres, \'|\', \' \') as nombres, abreviatura
            from
            persona
            join tipoidentificacion using(idtipoidentificacion)
            where identificacion like \''.$identificacion.'%\' ';

            $idTipoIdentificacion=$tipoIdentificacion->getIdTipoIdentificacion();
            if(!empty($idTipoIdentificacion))
                $sql.=' and idtipoidentificacion='.$idTipoIdentificacion;

            if($personasNaturales===true)
                $sql.=' and espersonanatural ';
            elseif($personasNaturales===false)
                $sql.=' and !espersonanatural ';

            if(!empty($limit))
                $sql.=' limit '.$limit;

            if(!empty($offset))
                $sql.=' offset '.$offset;

            $resultados=  FrameWork::getConexion()->consultar($sql);
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados->getRegistros());
            else
                return (array)$resultados->getRegistros();
        }else{
            $objetos=array();
            $sql='select idpersona
            from
            persona
            join tipoidentificacion using(idtipoidentificacion)
            where identificacion like \''.$identificacion.'%\' ';

            $idTipoIdentificacion=$tipoIdentificacion->getIdTipoIdentificacion();
            if(!empty($idTipoIdentificacion))
                $sql.=' and idtipoidentificacion='.$idTipoIdentificacion;

            if($personasNaturales===true)
                $sql.=' and espersonanatural ';
            elseif($personasNaturales===false)
                $sql.=' and !espersonanatural ';

            if(isset($limit))
                $sql.=' limit '.$limit;

            if(isset($offset))
                $sql.=' offset '.$offset;

            $resultados=$this->conexion->consultar($sql);

            while($resultados->irASiguiente())
                $objetos[]=new Persona($resultados->get()->idpersona);

            return $objetos;
        }
    }

    public function haSidoUtilizado() {
        $sql='select idpersona from usuario where idpersona='.$this->idPersona.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        $sql='select idpersona from personal where idpersona='.$this->idPersona.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        $sql='select idpersona from empresa where idpersona='.$this->idPersona.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        $sql='select idpersona from empleado where idpersona='.$this->idPersona.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>