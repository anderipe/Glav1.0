<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';
require_once 'Modulo.php';
require_once 'Persona.php';
require_once 'Impresora.php';
require_once 'PerfilUsuario.php';
require_once 'Impresion.php';

/**
 * Clase que representa un usuario del sistema. Los usuarios son los unicos que
 * pueden entrar al sistema y autenticarse emdiante login y password
 * otros
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class Usuario
    extends ClaseBase{

    protected $idUsuario=0;

    protected $idPersona=0;

    protected $login='';

    protected $password='';

    protected $estado=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);
        $this->setPropiedad('idUsuario', (int)0);
        $this->setPropiedad('idPersona', (int)0);
        $this->setPropiedad('login', '');
        $this->setPropiedad('password', '');
        $this->setPropiedad('estado', (int)0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idusuario='.$id))
                throw new AppException('No existe usuario con identificador '.$id);
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idUsuario)){
            throw new AppException('El usuario no existe',
                (object)array($this->getNombreJson('idusuario')=>'El usuario no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El usuario no puede ser borrado, este ha sido utilizado');

        $sql='select idauditoria from auditoria where idusuario='.$this->idUsuario;
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0){
            throw new AppException('El usuario no puede ser borrado porque ya ha usado el sistema y posee registros de auditoria',
                (object)array($this->getNombreJson('idusuario')=>'El usuario no puede ser borrado porque ya ha usado el sistema y posee registros de auditoria'));
        }

        $perfilesUsuario=$this->getPerfilesUsuario(RecordSet::FORMATO_CLASE);
        foreach ($perfilesUsuario as $perfileUsuario) {
            $perfileUsuario->borrarObjeto($auditoria);
        }

        $sql='delete from usuario where idusuario='.$this->idUsuario;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idUsuario);
        $modificacion->guardarObjeto($auditoria);
    }

    protected function cargarObjeto($string) {
//        $sql = 'SELECT distinct TABLE_NAME as tabla
//            FROM information_schema.COLUMNS
//            WHERE
//            TABLE_SCHEMA  like \'siadno\'
//            ';
//        $resultados=$this->conexion->consultar($sql);
//        while($resultados->irASiguiente()){
//            $tabla=$resultados->get()->tabla;
//            $sql='alter table '.$tabla.' add hash varchar(256)';
//            $this->conexion->ejecutar($sql);
//            $sql='alter table '.$tabla.' add firma varchar(1024)';
//            $this->conexion->ejecutar($sql);
//        }
//
//        echo "LISTOS";
//        exit;

        if(!empty($this->idUsuario))
            throw new AppException('La usuario ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from usuario where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una usuario para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idUsuario', (int)$resultados->get()->idusuario, true);
        $this->setPropiedad('idPersona', (int)$resultados->get()->idpersona, true);
        $this->setPropiedad('login', (string)$resultados->get()->login, true);
        $this->setPropiedad('password', (string)$resultados->get()->password, true);
        $this->setPropiedad('estado', (int)$resultados->get()->estado, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function cargarPorPersona(Persona $persona){
        $where='idpersona='.$persona->getIdPersona();
        return $this->cargarObjeto($where);
    }

    public function getEstado(){
        return $this->estado;
    }

    public function setEstado($estado){
        $valor=(int)$estado;
        return $this->setPropiedad('estado', $valor);
    }

    public function getIdUsuario(){
        return $this->idUsuario;
    }

    public function getIdPersona(){
        return $this->idPersona;
    }

    public function setLogin($login){
        $valor=trim($login);
        if(mb_strlen($valor)<8 || mb_strlen($valor)>32)
            throw new AppException('El nombre de usuario debe tener entre 8 y 32 caracteres',
                (object)array($this->getNombreJson('login')=>'El nombre de usuario debe tener entre 8 y 32 caracteres'));

        return $this->setPropiedad('login', $valor);
    }

    public function getLogin(){
        return $this->login;
    }

    public function setPassword($password, $password2){
        $valor=trim($password);
        if(mb_strlen($valor)<8 || mb_strlen($valor)>32)
            throw new AppException('El password de usuario debe tener entre 8 y 32 caracteres',
                (object)array($this->getNombreJson('password')=>'El password de usuario debe tener entre 8 y 32 caracteres'));

        $password2=trim($password2);
        if($password!=$password2)
            throw new AppException('El password de verificacion no coincide'.$password.'<>'.$password2,
                (object)array($this->getNombreJson('password2')=>'El password de verificacion no coincide'));

        $valor= sha1(md5($valor));
        return $this->setPropiedad('password', $valor);
    }

    public function getPassword(){
        return $this->password;
    }

    /**
     *
     * @return \Persona
     */
    public function getPersona(){
        return new Persona($this->idPersona);
    }

    public function setPersona(Persona $persona){
        $valor=$persona->getIdPersona();
        if(empty($valor))
            throw new AppException('La persona no existe',
                (object)array($this->getNombreJson('idpersona')=>'La persona no existe'));

        return $this->setPropiedad('idPersona', $valor);
    }

    public function getMenu(){
        $tableName=  FrameWork::getTmpName();
        $sql='drop table if EXISTS '.$tableName;
        $resultados=$this->conexion->ejecutar($sql);

        $sql='CREATE TEMPORARY TABLE IF NOT EXISTS '.$tableName.'
            (select modulo.* from
            modulo
            join moduloperfil using (idmodulo)
            join perfilusuario using (idperfil)
            where idusuario='.$this->idUsuario.')';
        $resultados=$this->conexion->ejecutar($sql);

        function agregarPadre($conexion, $tableName, $idModulo){
            $idModulo=(int)$idModulo;
            $sql='select idmodulopadre from modulo where idmodulo='.$idModulo;
            $resultados=$conexion->consultar($sql);
            if($resultados->getCantidad()==0)
                return;

            $idModuloPadre=(int)$resultados->get(0)->idmodulopadre;
            if($idModuloPadre==$idModulo){
                return;
            }

            $sql='select idmodulo from '.$tableName.' where idmodulo='.$idModuloPadre;
            $resultados=$conexion->consultar($sql);
            if($resultados->getCantidad()==0){
                $sql='insert into '.$tableName.' (select * from modulo where idmodulo='.$idModuloPadre.')';
                $resultados=$conexion->ejecutar($sql);
                agregarPadre($conexion, $tableName, $idModuloPadre);
            }
        }

        $sql='select idmodulo from '.$tableName.' order by orden';
        $resultados=$this->conexion->consultar($sql);
        while($resultados->irASiguiente()){
            agregarPadre($this->conexion, $tableName, $resultados->get()->idmodulo);
        }

        $modulo=new Modulo(1);
        return $modulo->getToolBar($tableName);
    }

    public function getPerfilesUsuario($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select perfilusuario.* from perfil join perfilusuario using (idperfil) where idusuario='.$this->idUsuario.' order by nombre';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idperfilusuario from perfil join perfilusuario using (idperfil) where idusuario='.$this->idUsuario.' order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new PerfilUsuario($resultados->get()->idperfilusuario);

            return $objetos;
        }
    }

    public function getPerfiles($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select perfil.* from perfil join perfilusuario using (idperfil) where idusuario='.$this->idUsuario.' order by nombre';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select perfil.idperfil from perfil join perfilusuario using (idperfil) where idusuario='.$this->idUsuario.' order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Perfil($resultados->get()->idperfil);

            return $objetos;
        }
    }

    public function getImpresoras($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from impresora where idusuario='.$this->idUsuario.' order by nombre';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idimpresora from impresora where idusuario='.$this->idUsuario.' order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Impresora($resultados->get()->idimpresora);

            return $objetos;
        }
    }

    public function getImpresiones($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select tipoimpresion.nombre as tipoimpresion, impresion.idimpresion, impresion.idtipoimpresion, impresion.idusuario, impresion.idimpresora, impresion.iddocumentoimprimible, impresion.idestadoimpresion, impresion.fecha, impresion.comentarios , lenguajeimpresion.idlenguajeimpresion, lenguajeimpresion.nombre as lenguajeimpresion, documentoimprimible.nombre as documentoimprimible
                from
                impresion
                join impresora using(idimpresora)
                join lenguajeimpresion using(idlenguajeimpresion)
                join documentoimprimible using (iddocumentoimprimible)
                join tipoimpresion using(idtipoimpresion)
                where
                impresion.idusuario='.$this->idUsuario.'
                order by fecha';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idimpresion from impresion where idusuario='.$this->idUsuario.' order by fecha';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Impresion($resultados->get()->idimpresion);

            return $objetos;
        }
    }

    public function getUltimaImpresion($formato){
        $formato=(int)$formato;

        $sql='select max(idimpresion) as idimpresion from impresion where idusuario='.$this->idUsuario;
        $resultados=FrameWork::getConexion()->consultar($sql);
        $idImpresion=$resultados->get(0)->idimpresion;
        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select impresion.* from impresion where idimpresion='.$idImpresion;
            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idimpresion from impresion where idimpresion='.$idImpresion;
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Impresion($resultados->get()->idimpresion);

            return $objetos;
        }
    }

    public function getPerfilesNoAsignados($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select perfil.*
                        from perfil
                        where estado=1 and not exists
                        (select 1 from perfilusuario
                            where
                            perfil.idperfil=perfilusuario.idperfil
                            and
                            idusuario='.$this->idUsuario.') order by nombre';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select perfil.idperfil
                        from perfil
                        where estado=1 and not exists
                        (select 1 from perfilusuario
                            where
                            perfil.idperfil=perfilusuario.idperfil
                            and
                            idusuario='.$this->idUsuario.') order by nombre';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Perfil($resultados->get()->idperfil);

            return $objetos;
        }
    }

    public function asignarPerfil(Perfil $perfil, Auditoria $auditoria){
        $perfilUsuario=new PerfilUsuario();
        $perfilUsuario->setUsuario($this);
        $perfilUsuario->setPerfil($perfil);
        $perfilUsuario->guardarObjeto($auditoria);
    }

    public function quitarPerfil(Perfil $perfil, Auditoria $auditoria){
        if($perfil->getIdPerfil()==1)
            throw new AppException('El perfil de SISTEMA no puede ser quitado a los usuarios',
                (object)array($this->getNombreJson('idperfil')=>'El perfil de SISTEMA no puede ser quitado a los usuarios'));
        $perfilUsuario=new PerfilUsuario();
        $perfilUsuario->cargarPorPerfilUsuario($this, $perfil);
        $perfilUsuario->borrarObjeto($auditoria);
    }

    public function guardarObjeto(Auditoria $auditoria=null){
        if(empty($this->idPersona))
            throw new AppException('La persona es un dato obligatorio',
                (object)array($this->getNombreJson('idpersona')=>'La persona es un dato obligatorio'));

        if(empty($this->login))
            throw new AppException('El login es obligatorio',
                (object)array($this->getNombreJson('login')=>'El login es obligatorio'));

        if(empty($this->password))
            throw new AppException('El password es obligatorio',
                (object)array($this->getNombreJson('password')=>'El password es obligatorio'));

        if(empty($this->idUsuario)){
            $usuario=new Usuario();
            $usuario->cargarPorPersona(new Persona($this->idPersona));
            $idUsuario=$usuario->getIdUsuario();
            if(!empty($idUsuario))
                throw new AppException('La persona seleccionada ya es usuario del sistema',
                    (object)array($this->getNombreJson('identificacion')=>'La persona seleccionada ya es usuario del sistema'));

            $sql='insert INTO usuario
                (idusuario, idpersona, estado, login, password)
                values(null, '.$this->idPersona.', '.$this->estado.', \''.mysql_real_escape_string($this->login).'\', \''.mysql_real_escape_string($this->password).'\')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idUsuario', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->guardarObjeto($auditoria);

            $this->asignarPerfil(new Perfil(1), $auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idUsuario);

            if($this->estaModificada('idPersona')){
                $usuario=new Usuario();
                $usuario->cargarPorPersona(new Persona($this->idPersona));
                $idUsuario=$usuario->getIdUsuario();
                if(!empty($idUsuario))
                    throw new AppException('La persona seleccionada ya es usuario del sistema',
                        (object)array($this->getNombreJson('identificacion')=>'La persona seleccionada ya es usuario del sistema'));

                $modificacion->addDescripcion($this->getTextoParaAuditoria('idPersona'));
                $cambios[]='idpersona='.$this->idPersona;
                $this->marcarNoModificada('idPersona');
            }

            if($this->estaModificada('estado')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('estado'));
                $cambios[]='estado='.$this->estado;
                $this->marcarNoModificada('estado');
            }

            if($this->estaModificada('login')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('login'));
                $cambios[]='login=\''.mysql_real_escape_string($this->login).'\'';
                $this->marcarNoModificada('login');
            }

            if($this->estaModificada('password')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('password'));
                $cambios[]='password=\''.mysql_real_escape_string($this->password).'\'';
                $this->marcarNoModificada('password');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update usuario set $update where idusuario=".$this->idUsuario;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idUsuario;
    }

    public static function getUsuarios($nombres, $formato){
        $formato=(int)$formato;
        $nombres=  mb_strtolower(trim($nombres));

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select usuario.idusuario, persona.nombres from
                usuario
                join persona using (idpersona) ';
            if(!empty($nombres))
                $sql.=' where lower(persona.nombres) like \'%'.mysql_real_escape_string ($nombres).'%\'';
            $sql.=' order by persona.nombres';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select usuario.idusuario from
                usuario
                join persona using (idpersona) ';
            if(!empty($nombres))
                $sql.=' where lower(persona.nombres) like \'%'.mysql_real_escape_string ($nombres).'%\'';
            $sql.=' order by persona.nombres';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Usuario($resultados->get()->idusuario);

            return $objetos;
        }
    }

    /**
     * Obtiene el valor de configuracion para una variable de usuario, si el
     * usuario no tiene definida la variable, se toma el valor por defecto
     * establecido por la configuracion global
     * @param type $idVariable
     * @return String
     */
    public function getVariable($idVariable){
        $variable=new Variable($idVariable);
        $variableUsuario=new VariableUsuario();
        $variableUsuario->cargarPorVariableUsuario($this, $variable);
        if($variableUsuario->getIdVariableUsuario()){
            return $variableUsuario->getValor();
        }else{
            return $variable->getValor();
        }
    }

    public function getConfiguracion($idVariable){
        return $this->getVariable($idVariable);
    }

    public function getSeparadorCSV(){
        return $this->getConfiguracion(8);
    }

    public function haSidoUtilizado() {
        $sql='select idusuario from auditoria where idusuario='.$this->idUsuario.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        $sql='select idperfilusuario from perfilusuario where idusuario='.$this->idUsuario.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>