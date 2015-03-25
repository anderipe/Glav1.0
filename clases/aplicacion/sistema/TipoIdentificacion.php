<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'ClaseBase.php';

/**
 * Clase que representa un tipo de identificación, como C.C, N.I.T o T.I entre
 * otros
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
class TipoIdentificacion
    extends ClaseBase{

    protected $idTipoIdentificacion=0;

    protected $nombre='';

    protected $abreviatura='';

    protected $esPersonaNatural=0;

    protected $estado=0;

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idTipoIdentificacion', 0);
        $this->setPropiedad('nombre', '');
        $this->setPropiedad('abreviatura', '');
        $this->setPropiedad('esPersonaNatural', 0);
        $this->setPropiedad('estado', 0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idtipoidentificacion='.$id))
                throw new AppException('No existe tipoidentificacion con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idTipoIdentificacion))
            throw new AppException('El tipoidentificacion ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from tipoidentificacion where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un tipoidentificacion para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idTipoIdentificacion', (int)$resultados->get()->idtipoidentificacion, true);
        $this->setPropiedad('nombre', (string)$resultados->get()->nombre, true);
        $this->setPropiedad('abreviatura', (string)$resultados->get()->abreviatura, true);
        $this->setPropiedad('esPersonaNatural', (int)$resultados->get()->espersonanatural, true);
        $this->setPropiedad('estado', (int)$resultados->get()->estado, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idTipoIdentificacion)){
            throw new AppException('El tipo de identificacióna no existe',
                (object)array($this->getNombreJson('idTipoIdentificacion')=>'El tipo de identificacióna no existe'));
        }

        if($this->haSidoUtilizado())
            throw new AppException('El tipo de identificación no puede ser borrado, este ha sido utilizado');

        $sql='delete from tipoidentificacion where idtipoidentificacion='.$this->idTipoIdentificacion;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idTipoIdentificacion);
        $modificacion->guardarObjeto($auditoria);
    }

    public function guardarObjeto(Auditoria $auditoria=null) {
        if(empty($this->nombre))
            throw new AppException('El nombre es obligatorio',
                (object)array($this->getNombreJson('nombre')=>'El nombre es obligatorio'));

        if(empty($this->abreviatura))
            throw new AppException('La abreviatura es obligatoria',
                (object)array($this->getNombreJson('abreviatura')=>'La abreviatura es obligatoria'));

        if(empty($this->idTipoIdentificacion)){
            $sql='insert INTO tipoidentificacion
                (idtipoidentificacion, nombre, abreviatura, estado, espersonanatural)
                values(null, \''.mysql_real_escape_string($this->nombre).'\', \''.mysql_real_escape_string($this->abreviatura).'\','.$this->estado.','.$this->esPersonaNatural.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idTipoIdentificacion', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
            $modificacion->guardarObjeto($auditoria);
        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idTipoIdentificacion);

            if($this->estaModificada('nombre')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
                $cambios[]='nombre=\''.mysql_real_escape_string($this->nombre).'\'';
                $this->marcarNoModificada('nombre');
            }

            if($this->estaModificada('abreviatura')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('abreviatura'));
                $cambios[]='abreviatura=\''.mysql_real_escape_string($this->abreviatura).'\'';
                $this->marcarNoModificada('abreviatura');
            }

            if($this->estaModificada('esPersonaNatural')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('esPersonaNatural'));
                $cambios[]='espersonanatural='.$this->esPersonaNatural;
                $this->marcarNoModificada('esPersonaNatural');
            }

            if($this->estaModificada('estado')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('estado'));
                $cambios[]='estado='.$this->estado;
                $this->marcarNoModificada('estado');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update tipoidentificacion set $update where idtipoidentificacion=".$this->idTipoIdentificacion;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idTipoIdentificacion;
    }

    public function getIdTipoIdentificacion(){
        return $this->idTipoIdentificacion;
    }

    public function setNombre($nombre){
        $valor=(string)$nombre;
        if(mb_strlen($valor)>256)
            throw new AppException('El nombre puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('nombre')=>'El nombre puede tener maximo 256 caracteres'));

        return $this->setPropiedad('nombre', $valor);
    }

    public function getNombre(){
        return $this->nombre;
    }

    public function setEstado($estado){
        $valor=(int)$estado;
        if($valor<0 ||$valor>1)
            throw new AppException('Valor no valido para el estado',
                (object)array($this->getNombreJson('estado')=>'Valor no valido para el estado'));

        return $this->setPropiedad('estado', $valor);
    }

    public function getEstado(){
        return $this->estado;
    }

    public function setAbreviatura($abreviatura){
        $valor=(string)$abreviatura;
        if(mb_strlen($valor)>10)
            throw new AppException('La abreviatura puede tener maximo 10 caracteres',
                (object)array($this->getNombreJson('abreviatura')=>'La abreviatura puede tener maximo 10 caracteres'));

        return $this->setPropiedad('abreviatura', $valor);
    }

    public function getAbreviatura(){
        return $this->abreviatura;
    }

    public function setEsPersonaNatural($esPersonaNatural){
        $valor=(int)$esPersonaNatural;
        if($valor<0 ||$valor>1)
            throw new AppException('Valor no valido para la definiciones de la persona natural',
                (object)array($this->getNombreJson('esPersonaNatural')=>'Valor no valido para la definiciones de la persona natural'));

        return $this->setPropiedad('esPersonaNatural', $valor);
    }

    public function getEsPersonaNatural(){
        return $this->esPersonaNatural;
    }

    public static function getTiposIdentificacion($formato, $personasNaturales=null){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from tipoidentificacion ';

            if($personasNaturales===true)
                $sql.=' where espersonanatural ';
            elseif($personasNaturales===false)
                $sql.=' where !espersonanatural ';

            $sql.='order by abreviatura';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idtipoidentificacion from tipoidentificacion ';

            if($personasNaturales===true)
                $sql.=' where espersonanatural ';
            elseif($personasNaturales===false)
                $sql.=' where !espersonanatural ';

            $sql.='order by abreviatura';


            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new TipoIdentificacion($resultados->get()->idtipoidentificacion);

            return $objetos;
        }
    }

    public function haSidoUtilizado() {
        $sql='select idpersona from persona where idtipoidentificacion='.$this->idTipoIdentificacion.' limit 1';
        $resultados=$this->conexion->consultar($sql);
        if($resultados->getCantidad()>0)
            return true;

        return false;
    }
}

?>
