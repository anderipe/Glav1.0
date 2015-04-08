<?php
/**
 * @package co.org.lavado.io
 * @subpackage print
 */

require_once 'ImpresoraBase.php';
require_once 'LenguajeImpresion.php';
require_once 'TipoImpresora.php';
require_once 'Usuario.php';
require_once 'SecuenciaImpresion.php';

/**
 * Clase que representa una impresora fisica capaz de imprimir en matriz de
 * punto o en tinta. Funciona creando un archivo de secuencias de escape.
 * Actualmente se soportan las secuencias de escape compatibles con IBM/POS
 * o PCL en sus versiones 1 y 2
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
class Impresora
    extends ImpresoraBase{

    protected $idImpresora=0;

    protected $idUsuario=0;

    protected $idLenguajeImpresion=0;

    protected $idTipoImpresora=0;

    protected $nombre='';

    protected $descripcion='';

    protected $offSetX=0.0;

    protected $offSetY=0.0;

    protected $rawData='';

    protected $secuencias=array();

    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idImpresora', (int)0);
        $this->setPropiedad('idUsuario', (int)0);
        $this->setPropiedad('idLenguajeImpresion', (int)0);
        $this->setPropiedad('idTipoImpresora', (int)0);
        $this->setPropiedad('nombre', '');
        $this->setPropiedad('descripcion', '');
        $this->setPropiedad('offSetX', (double)0.0);
        $this->setPropiedad('offSetY', (double)0.0);

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idimpresora='.$id))
                throw new AppException('No existe impresora con identificador '.$id);
    }

    protected function cargarObjeto($string) {
        if(!empty($this->idImpresora))
            throw new AppException('La impresora ya se encuentra cargada');

        $resultados=$this->conexion->consultar('select * from impresora where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de una impresora para la carga del objeto', null);

        $resultados->irASiguiente();
        $this->setPropiedad('idImpresora', (int)$resultados->get()->idimpresora, true);
        $this->setPropiedad('idUsuario', (int)$resultados->get()->idusuario, true);
        $this->setPropiedad('idLenguajeImpresion', (int)$resultados->get()->idlenguajeimpresion, true);
        $this->setPropiedad('idTipoImpresora', (int)$resultados->get()->idtipoimpresora, true);
        $this->setPropiedad('nombre', $resultados->get()->nombre, true);
        $this->setPropiedad('descripcion', $resultados->get()->descripcion, true);
        $this->setPropiedad('offSetX', (double)$resultados->get()->offsetx, true);
        $this->setPropiedad('offSetY', (double)$resultados->get()->offsety, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    public function borrarObjeto(Auditoria $auditoria=null) {
        if(empty($this->idImpresora)){
            throw new AppException('La impresora no existe',
                (object)array($this->getNombreJson('idimpresora')=>'La impresora no existe'));
        }

        $impresiones=$this->getImpresiones(RecordSet::FORMATO_CLASE);
        foreach ($impresiones as $impresion) {
            $impresion->borrarObjeto($auditoria);
        }

        $sql='delete from impresora where idimpresora='.$this->idImpresora;
        $this->conexion->ejecutar($sql);

        $modificacion= new Modificacion();
        $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Eliminacion));
        $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
        $modificacion->addDescripcionId($this->idImpresora);
        $modificacion->guardarObjeto($auditoria);
    }

    public function cargarPorUsuario(Usuario $usuario, TipoImpresora $tipoImpresora){
        $where='idusuario='.$usuario->getIdUsuario().' and idtipoimpresora='.$tipoImpresora->getIdTipoImpresora();
        return $this->cargarObjeto($where);
    }

    public function cargarPorTipoImpresora(Usuario $usuario, TipoImpresora $tipoImpresora, $existenciaObligatoria=false){
        $where='idusuario='.$usuario->getIdUsuario().' and idtipoimpresora='.$tipoImpresora->getIdTipoImpresora();
        $this->cargarObjeto($where);
        if($this->getIdImpresora()==0 && $existenciaObligatoria==true)
            throw new Exception('El usuario no posee una impresora: '.$tipoImpresora->getNombre());

        return true;
    }

    public function cargarPorNombre(Usuario $usuario, $nombre){
        $where='idusuario='.$usuario->getIdUsuario().' and nombre=\''.mysql_real_escape_string($nombre).'\'';
        return $this->cargarObjeto($where);
    }

    public function guardarObjeto(Auditoria $auditoria=null){

        if(empty($this->idUsuario))
            throw new AppException('El usuario es obligatorio',
                (object)array($this->getNombreJson('idUsuario')=>'El usuario es obligatorio'));

        if(empty($this->idLenguajeImpresion))
            throw new AppException('El lenguaje de impresion es obligatorio',
                (object)array($this->getNombreJson('idLenguajeImpresion')=>'El lenguaje de impresion es obligatorio'));

        if(empty($this->idTipoImpresora))
            throw new AppException('El tipo de impresion es obligatorio',
                (object)array($this->getNombreJson('idTipoImpresora')=>'El tipo de impresion es obligatorio'));

        if(empty($this->nombre))
            throw new AppException('El nombre de la impresora es obligatorio',
                (object)array($this->getNombreJson('nombre')=>'El nombre de la impresora es obligatorio'));

        if(empty($this->idImpresora)){
            $sql='insert INTO impresora
                (idimpresora, idusuario, idlenguajeimpresion, idtipoimpresora, nombre, descripcion, offsetx, offsety)
                values(null, '.$this->idUsuario.', '.$this->idLenguajeImpresion.', '.$this->idTipoImpresora.', \''.mysql_real_escape_string($this->nombre).'\', \''.mysql_real_escape_string($this->descripcion).'\', '.$this->offSetX.', '.$this->offSetY.')';
            $id=$this->conexion->ejecutar($sql);
            $this->setPropiedad('idImpresora', (int)$id);

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
            $modificacion->addDescripcionId($this->idImpresora);

            if($this->estaModificada('idUsuario')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idUsuario'));
                $cambios[]='idusuario='.$this->idUsuario;
                $this->marcarNoModificada('idUsuario');
            }

            if($this->estaModificada('idTipoImpresora')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idTipoImpresora'));
                $cambios[]='idtipoimpresora='.$this->idTipoImpresora;
                $this->marcarNoModificada('idTipoImpresora');
            }

            if($this->estaModificada('idLenguajeImpresion')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('idLenguajeImpresion'));
                $cambios[]='idlenguajeimpresion='.$this->idLenguajeImpresion;
                $this->marcarNoModificada('idLenguajeImpresion');
            }

            if($this->estaModificada('nombre')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('nombre'));
                $cambios[]='nombre=\''.mysql_real_escape_string($this->nombre).'\'';
                $this->marcarNoModificada('nombre');
            }

            if($this->estaModificada('descripcion')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('descripcion'));
                $cambios[]='descripcion=\''.mysql_real_escape_string($this->descripcion).'\'';
                $this->marcarNoModificada('descripcion');
            }

            if($this->estaModificada('offSetX')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('offSetX'));
                $cambios[]='offsetx='.$this->offSetX;
                $this->marcarNoModificada('offSetX');
            }

            if($this->estaModificada('offSetY')){
                $modificacion->addDescripcion($this->getTextoParaAuditoria('offSetY'));
                $cambios[]='offsety='.$this->offSetY;
                $this->marcarNoModificada('offSetY');
            }

            if(count($cambios)>0){
                $cambios[]='hash=\''.$this->calcularHash().'\', firma=\''.$this->calcularFirma().'\'';

                $update=implode(',', $cambios);
                $sql="update impresora set $update where idimpresora=".$this->idImpresora;
                $this->conexion->ejecutar($sql);

                $modificacion->setAccionAuditable(new AccionAuditable(AccionAuditable::Modificacion));
                $modificacion->setClase(Clase::crearPorNombre($this->nombreDeClase));
                $modificacion->guardarObjeto($auditoria);
            }
        }

        $this->modificado=false;
        return $this->idImpresora;
    }

    public function getIdImpresora(){
        return $this->idImpresora;
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
     * @return \LenguajeImpresion
     */
    public function getLenguajeImpresion(){
        return new LenguajeImpresion($this->idLenguajeImpresion);
    }

    public function setLenguajeImpresion(LenguajeImpresion $lenguajeImpresion){
        $valor=$lenguajeImpresion->getIdLenguajeImpresion();
        if(empty($valor))
            throw new AppException('El lenguaje de impresion no existe',
                (object)array($this->getNombreJson('idLenguajeImpresion')=>'El lenguaje de impresion no existe'));

        return $this->setPropiedad('idLenguajeImpresion', $valor);
    }

    public function getIdLenguajeImpresion(){
        return $this->idLenguajeImpresion;
    }

    /**
     *
     * @return \TipoImpresora
     */
    public function getTipoImpresora(){
        return new TipoImpresora($this->idTipoImpresora);
    }

    public function setTipoImpresora(TipoImpresora $tipoImpresora){
        $valor=$tipoImpresora->getIdTipoImpresora();
        if(empty($valor))
            throw new AppException('El tipo de impresora no existe',
                (object)array($this->getNombreJson('idTipoImpresora')=>'El tipo de impresora no existe'));

        return $this->setPropiedad('idTipoImpresora', $valor);
    }

    public function getIdTipoImpresora(){
        return $this->idTipoImpresora;
    }

    public function setOffSetX($offSet){
        $valor=(double)$offSet;
        if($valor<-2.0 || $valor>2.0)
            throw new AppException('El offset debe estar entre -2.0 y 2.0',
                (object)array($this->getNombreJson('offsetx')=>'El offset debe estar entre -2.0 y 2.0'));

        return $this->setPropiedad('offSetX', $valor);
    }

    public function setOffSetY($offSet){
        $valor=(double)$offSet;
        if($valor<-2.0 || $valor>2.0)
            throw new AppException('El offset debe estar entre -2.0 y 2.0',
                (object)array($this->getNombreJson('offsety')=>'El offset debe estar entre -2.0 y 2.0'));

        return $this->setPropiedad('offSetY', $valor);
    }

    public function setNombre($nombre){
        $valor=(string)$nombre;
        if(mb_strlen($valor)>256)
            throw new AppException('El nombre puede tener maximo 256 caracteres',
                (object)array($this->getNombreJson('nombres')=>'El nombre puede tener maximo 256 caracteres'));

        return $this->setPropiedad('nombre', $valor);
    }

    public function getNombre(){
        return $this->nombre;
    }

    /**
     *
     * @param type $formato
     * @return \Impresion
     */
    public function getImpresiones($formato){
        $formato=(int)$formato;

        if($formato==RecordSet::FORMATO_JSON ||
                $formato==RecordSet::FORMATO_OBJETO){
            $sql='select * from impresion where idimpresora='.$this->idImpresora.' order by fecha';

            $resultados=FrameWork::getConexion()->consultar($sql)->getRegistros();
            if($formato==RecordSet::FORMATO_JSON)
                return (string)json_encode($resultados);
            else
                return (array)$resultados;
        }else{
            $objetos=array();
            $sql='select idimpresion from impresion where idimpresora='.$this->idImpresora.' order by fecha';
            $resultados=FrameWork::getConexion()->consultar($sql);
            while($resultados->irASiguiente())
                $objetos[]=new Impresion($resultados->get()->idimpresion);

            return $objetos;
        }
    }

    public function get($abreviatura){
        $abreviatura=strtoupper($abreviatura);
        if(empty($abreviatura))
            throw new Exception('La abreviatura de la secuencia no es valida');
        if(!isset($this->secuencias[$abreviatura])){
            $sql='select secuencia from
                secuenciaimpresion
                join comandoimpresion using(idcomandoimpresion)
                where
                idlenguajeimpresion='.$this->idLenguajeImpresion.'
                and
                abreviatura=\''.mysql_real_escape_string($abreviatura).'\'';
            $resultados=$this->conexion->consultar($sql);
            if($resultados->getCantidad()==0)
                throw new Exception('No existe la secuencia de impresion '.$abreviatura.' para el lenguaje especificado');

            $this->secuencias[$abreviatura]=new SecuenciaImpresion($resultados->get(0)->secuencia);
        }
        return $this->secuencias[$abreviatura]->get();
    }

    public function set($abreviatura){
        $this->rawData.=$this->get($abreviatura);
    }

    public function iniciarImpresion($tamanioHoja=Impresora::CARTA, $interlineado=Impresora::NORMAL, $caracteresPorLinea=Impresora::C80){
        $this->set('reset');
        $this->set('cr_lf_ff_mode');
        $this->set('papel_agotado_off');

        parent::iniciarImpresion($tamanioHoja, $interlineado, $caracteresPorLinea);
    }

    public function negrita($encender=true) {
        parent::negrita($encender);
        if($encender)
            $this->set('negrita_on');
        else
            $this->set('negrita_off');
    }

    public function cursiva($encender=true){
        parent::cursiva($encender);
        if($encender)
            $this->set('cursiva_on');
        else
            $this->set('cursiva_off');
    }

    public function subrayada($encender=true) {
        parent::subrayada($encender);
        if($encender)
            $this->set('subrayado_on');
        else
            $this->set('subrayado_off');
    }

    public function usarHojaCarta() {
        parent::usarHojaCarta();
        $this->set('carta');
    }

    public function usarHojaOficio() {
        parent::usarHojaOficio();
        $this->set('oficio');
    }

    public function usarInterEspaciadoNormal() {
        parent::usarInterEspaciadoNormal();
        $this->set('interlineado_normal');
    }

    public function usarInterEspaciadoCorto() {
        parent::usarInterEspaciadoCorto();
        $this->set('interlineado_corto');
    }

    public function caracteresPorLinea40() {
        parent::caracteresPorLinea40();
        $this->set('cpi_5');
    }

    public function caracteresPorLinea80() {
        parent::caracteresPorLinea80();
        $this->set('cpi_10');
    }

    public function caracteresPorLinea96() {
        parent::caracteresPorLinea96();
        $this->set('cpi_12');
    }

    public function caracteresPorLinea137() {
        parent::caracteresPorLinea137();
        $this->set('cpi_17');
    }

    public function caracteresPorLinea160() {
        parent::caracteresPorLinea160();
        $this->set('cpi_20');
    }

    public function getCaracteresPorLinea(){
        return $this->caracteresPorLinea;
    }

    public function terminarImpresion(){
        parent::terminarImpresion();
        $this->set('FF');
        $this->rawData=Auxiliar::mb_str_replace(Impresora::TOTAL_PAGINAS, $this->hojaActual, $this->rawData);
        $this->rawData=Auxiliar::mb_str_replace(Impresora::COMENTARIOS_DOCUMENTO, $this->impresion->getComentarios(), $this->rawData);
        $this->rawData=Auxiliar::mb_str_replace(Impresora::NOMBRE_DOCUMENTO, $this->impresion->getDocumentoImprimible()->getNombre(), $this->rawData);
    }

    public function getContenido($terminarImpresion=true){
        if($terminarImpresion)
            $this->terminarImpresion();

        return $this->rawData;

    }

    public function nuevaHoja() {
        $this->set("ff");
        parent::nuevaHoja();
    }

    protected function secuenciaNuevaLinea(){
        $this->set('lf');
    }

    public function nuevaLinea($numeroDeLineas=0){
        $this->agregarNuevaLinea($this, $numeroDeLineas);
    }

    public function escribir($texto, $maximoCaracteres=null, $pad=null, $padAlign=STR_PAD_RIGHT, $nuevaLinea = FALSE){
        $maximoCaracteres=(int)$maximoCaracteres;
        $pad=(int)$pad;

        if($maximoCaracteres>0)
            $texto=mb_substr($texto, 0, $maximoCaracteres);
        if($pad>0)
            $texto=Auxiliar::mb_str_pad($texto, $pad, ' ', $padAlign);

        $this->rawData.=utf8_encode($texto);
        parent::escribir($texto, $maximoCaracteres, $pad, $padAlign, $nuevaLinea);
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>