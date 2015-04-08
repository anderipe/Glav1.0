<?php
/**
 * @package co.org.lavado.io
 * @subpackage print
 */

require_once 'ClaseBase.php';
require_once 'Impresora.php';
require_once 'DocumentoImprimible.php';
require_once 'EstadoImpresion.php';
require_once 'TipoImpresion.php';

/**
 * Clase que representa un trabajo de impresoin creado por una impresora.
 * Generalmente define un trabajo de secuencias de escape, un trabajo en excel
 * o un trabajo en pdf
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
class Impresion
    extends ClaseBase{

    protected $idImpresion=0;

    protected $idTipoImpresion=0;

    protected $idUsuario=0;

    protected $idImpresora=0;

    protected $idDocumentoImprimible=0;

    protected $idEstadoImpresion=0;

    protected $comentarios='';

    protected $contenido='';

    protected $fecha='';

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idImpresion', (int)0);
        $this->setPropiedad('idTipoImpresion', (int)0);
        $this->setPropiedad('idUsuario', (int)0);
        $this->setPropiedad('idImpresora', (int)0);
        $this->setPropiedad('idDocumentoImprimible', (int)0);
        $this->setPropiedad('idEstadoImpresion', (int)0);
        $this->setPropiedad('comentarios', '');
        $this->setPropiedad('contenido', '');
        $this->setPropiedad('fecha', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idimpresion='.$id))
                throw new AppException('No existe impresion con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idImpresion))
            throw new AppException('La impresion ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from impresion where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una impresion para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idImpresion', (int)$resultados->get()->idimpresion, true);
        $this->setPropiedad('idTipoImpresion', (int)$resultados->get()->idtipoimpresion, true);
        $this->setPropiedad('idUsuario', (int)$resultados->get()->idusuario, true);
        $this->setPropiedad('idImpresora', (int)$resultados->get()->idimpresora, true);
        $this->setPropiedad('idDocumentoImprimible', (int)$resultados->get()->iddocumentoimprimible, true);
        $this->setPropiedad('idEstadoImpresion', (int)$resultados->get()->idestadoimpresion, true);
        $this->setPropiedad('comentarios', $resultados->get()->comentarios, true);
        $this->setPropiedad('contenido', $resultados->get()->contenido, true);
        $this->setPropiedad('fecha', (string)$resultados->get()->fecha, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function cargarPorDocumento(Usuario $usuario, DocumentoImprimible $documentoImprimible){
        $where='idusuario='.$usuario->getIdUsuario().' and iddocumentoimprimible='.$documentoImprimible->getIdDocumentoImprimible();
        return $this->cargarObjeto($where);
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idImpresion)){
            throw new AppException('La impresion no existe',
                (object)array($this->getNombreJson('idimpresion')=>'La impresion no existe'));
        }

        $sql='delete from impresion where idimpresion='.$this->idImpresion;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idImpresion);
        $modificacion->guardarObjeto($auditoria);
    }

    public function guardarObjeto(Auditoria $auditoria=null){
        $this->conexion->ejecutar('delete from impresion');
        $this->idImpresion=0;
        if(empty($this->idUsuario))
            throw new AppException('El usuario es obligatorio',
                (object)array($this->getNombreJson('idUsuario')=>'El usuario es obligatorio'));

        if(empty($this->idDocumentoImprimible))
            throw new AppException('El tipo de documento es obligatorio',
                (object)array($this->getNombreJson('idDocumentoImprimible')=>'El tipo de documento es obligatorio'));

        if(empty($this->idTipoImpresion))
            throw new AppException('El tipo de impresion es obligatorio',
                (object)array($this->getNombreJson('idTipoImpresion')=>'El tipo de impresion es obligatorio'));

        if(empty($this->idEstadoImpresion))
            $this->idEstadoImpresion=1;

        /*La fecha de impresion siempre se actualiza*/
        $this->setFecha(new DateTime());

        if(empty($this->idImpresion)){
            $sql='insert INTO impresion
                (idimpresion, idtipoimpresion, idusuario, idimpresora, iddocumentoimprimible, idestadoimpresion, comentarios, contenido, fecha)
                values(null, '.$this->idTipoImpresion.', '.$this->idUsuario.', '.(!empty($this->idImpresora)?$this->idImpresora:'null').', '.$this->idDocumentoImprimible.', '.$this->idEstadoImpresion.', \''.mysql_real_escape_string($this->comentarios).'\', \''.mysql_real_escape_string($this->contenido).'\', \''.mysql_real_escape_string($this->fecha).'\')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idImpresion', (int)$id);

            $sql='update '.$this->nombreDeTabla.' set hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\' where '.$this->campoId.'='.$id;
            $this->conexion->ejecutar($sql);

            $modificacion= new Modificacion();
            $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Insercion));
            $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
            $modificacion->addDescripcionId($id);
            $modificacion->addDescripcion($this->getTextoParaAuditoria('comentarios'));
            $modificacion->guardarObjeto($auditoria);

        }else{
            $cambios=array();
            $modificacion= new Modificacion();
            $modificacion->addDescripcionId($this->idImpresion);

            if($this->estaModificada('idTipoImpresion')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idTipoImpresion'));
                $cambios[]='idtipoimpresion='.$this->idTipoImpresion;
                $this->marcarNoModificada('idTipoImpresion');
            }

            if($this->estaModificada('idUsuario')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idUsuario'));
                $cambios[]='idusuario='.$this->idUsuario;
                $this->marcarNoModificada('idUsuario');
            }

            if($this->estaModificada('idDocumentoImprimible')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idDocumentoImprimible'));
                $cambios[]='iddocumentoimprimible='.$this->idDocumentoImprimible;
                $this->marcarNoModificada('idDocumentoImprimible');
            }

            if($this->estaModificada('idImpresora')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idImpresora'));
                $cambios[]='idimpresora='.(!empty($this->idImpresora)?$this->idImpresora:'null');
                $this->marcarNoModificada('idImpresora');
            }

            if($this->estaModificada('idEstadoImpresion')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idEstadoImpresion'));
                $cambios[]='idestadoimpresion='.$this->idEstadoImpresion;
                $this->marcarNoModificada('idEstadoImpresion');
            }

            if($this->estaModificada('comentarios')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('comentarios'));
                $cambios[]='comentarios=\''.mysql_real_escape_string($this->comentarios).'\'';
                $this->marcarNoModificada('comentarios');
            }

            if($this->estaModificada('contenido')){
                $modificacion->addDescripcion('Se modificó la impresión');
                $cambios[]='contenido=\''.mysql_real_escape_string($this->contenido).'\'';
                $this->marcarNoModificada('contenido');
            }

            if($this->estaModificada('fecha')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('fecha'));
                $cambios[]='fecha=\''.mysql_real_escape_string($this->fecha).'\'';
                $this->marcarNoModificada('fecha');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';
                $update=implode(',', $cambios);
                $sql="update impresion set $update where idimpresion=".$this->idImpresion;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idImpresion;
    }

    public function getIdImpresion(){
        return $this->idImpresion;
    }

    public function getIdTipoImpresion(){
        return $this->idTipoImpresion;
    }

    public function getTipoImpresion(){
        return new TipoImpresion($this->idTipoImpresion);
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

    public function getIdUsuario(){
        return $this->idUsuario;
    }

    /**
     *
     * @return \Impresora
     */
    public function getImpresora(){
        return new Impresora($this->idImpresora);
    }

    public function getIdImpresora(){
        return $this->idImpresora;
    }

    /**
     *
     * @return \DocumentoImprimible
     */
    public function getDocumentoImprimible(){
        return new DocumentoImprimible($this->idDocumentoImprimible);
    }

    public function setDocumentoImprimible(DocumentoImprimible $documentoImprimible){
        $valor=$documentoImprimible->getIdDocumentoImprimible();
        if(empty($valor))
            throw new AppException('El tipo de impresion no existe',
                (object)array($this->getNombreJson('idDocumentoImprimible')=>'El tipo de impresion no existe'));

        return $this->setPropiedad('idDocumentoImprimible', $valor);
    }

    public function getIdDocumentoImprimible(){
        return $this->idDocumentoImprimible;
    }

    /**
     *
     * @return \EstadoImpresion
     */
    public function getEstadoImpresion(){
        return new EstadoImpresion($this->idEstadoImpresion);
    }

    public function setEstadoImpresion(EstadoImpresion $estadoImpresion){
        $valor=$estadoImpresion->getIdEstadoImpresion();
        if(empty($valor))
            throw new AppException('El estado de impresion no existe',
                (object)array($this->getNombreJson('idEstadoImpresion')=>'El estado de impresion no existe'));

        return $this->setPropiedad('idEstadoImpresion', $valor);
    }

    public function getIdEstadoImpresion(){
        return $this->idEstadoImpresion;
    }

    public function getFecha(){
        return new DateTime($this->fecha);
    }

    public function setFecha(DateTime $fecha){
        return $this->setPropiedad('fecha', $fecha->format('Y-m-d h:i:s'));
    }

    public function setComentarios($comentarios){
        $valor=(string)$comentarios;
        if(mb_strlen($valor)>256)
            throw new AppException('El comentarios puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('comentarioss')=>'El comentarios puede tener maximo 256 caracteres'));

        return $this->setPropiedad('comentarios', $valor);
    }

    public function getComentarios(){
        return $this->comentarios;
    }

    public function setContenido(ImpresoraBase $impresora){
        $this->setPropiedad('contenido', $impresora->getContenido());

        if(get_class($impresora)=='Impresora'){
            $this->idTipoImpresion=TipoImpresion::FISICA;
            $valor=$impresora->getIdImpresora();
            if(empty($valor))
                throw new AppException('La impresora no existe',
                    (object)array($this->getNombreJson('idImpresora')=>'La impresora no existe'));

            $this->setPropiedad('idImpresora', $valor);
        }elseif(get_class($impresora)=='ImpresoraPDF'){
            $this->idTipoImpresion=TipoImpresion::PDF;
        }elseif(get_class($impresora)=='ImpresoraExcel'){
            $this->idTipoImpresion=TipoImpresion::EXCEL;
        }
    }

    public function getContenido(){
        return $this->contenido;
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>