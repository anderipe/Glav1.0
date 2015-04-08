<?php
/**
 * @package co.org.lavado.io
 * @subpackage print
 */

require_once 'ClaseBase.php';
require_once 'Impresion.php';
require_once 'Auxiliar.php';
require_once 'Informe.php';

/**
 * Clase que representa una impresora generica de tipo no especificado.
 * Define los metodos y propiedades comunes a todas las impresoras
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
abstract class ImpresoraBase
    extends ClaseBase{

    const CARTA=1;
    const OFICIO=2;

    const CORTO=1;
    const NORMAL=2;

    const C40=40;
    const C80=80;
    const C96=96;
    const C137=137;
    const C160=160;

    const NEGRITA=1;
    const CURSIVA=2;
    const SUBRAYADA=4;

    const TODOS_ENCABEZADOS=-1;
    const PRIMER_ENCABEZADO=0;
    const SEGUNDO_ENCABEZADO=1;
    const TERCER_ENCABEZADO=2;
    const CUARTO_ENCABEZADO=3;

    const NOMBRE_DOCUMENTO='[*-NOMBRE_DOCUMENTO-*]';
    const COMENTARIOS_DOCUMENTO='[*-COMENTARIOS_DOCUMENTO-*]';
    const PAGINA_ACTUAL='[*-PAGINA_ACTUAL-*]';
    const TOTAL_PAGINAS='[*-TOTAL_PAGINAS-*]';
    const PIE_DE_PAGINA='[*-PIE_DE_PAGINA-*]';

    protected $tamanioHoja=0;

    protected $lineasPorHoja=0;

    protected $caracteresPorLinea=0;

    protected $decoracion=0;

    protected $interlineado=0;

    protected $hojaActual=0;

    protected $lineaActual=0;

    protected $encabezados=array();

    protected $parametrosEncabezados=array();

    protected $pieDePagina=null;

    protected $parametrosPieDePagina=array();

    protected $estilosSalvados=null;

    /**
     *
     * @var Impresion
     */
    protected $impresion=null;

    public function __construct($prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

//        $imprimirPieDePagina=function(ImpresoraBase $impresora){
//            Informe::imprimirPieDePaginaBasico($impresora);
//        };
        $this->agregarPieDePagina($imprimirPieDePagina, $this);

//        $imprimirEncabezadoPrincipal=function(ImpresoraBase $impresora){
//            Informe::imprimirEncabezadoBasico($impresora);
//        };
        $this->agregarEncabezado($imprimirEncabezadoPrincipal, $this, Impresora::PRIMER_ENCABEZADO);
    }

    public function setImpresion(Impresion $impresion){
        $this->impresion=$impresion;
    }

    public function getImpresion(){
        return $this->impresion;
    }

    public function usarHojaCarta() {
        $this->tamanioHoja = Impresora::CARTA;
    }

    public function usarHojaOficio() {
        $this->tamanioHoja = Impresora::OFICIO;
    }

    public function getTamanioHoja(){
        return $this->tamanioHoja;
    }

    public function negrita($encender=true) {
        if($encender)
            $this->decoracion|=Impresora::NEGRITA;
        else
            $this->decoracion&=(~Impresora::NEGRITA);
    }

    public function estaNegrita(){
        if($this->decoracion & ImpresoraPDF::NEGRITA){
            return true;
        }
        return false;
    }

    public function cursiva($encender=true) {
        if($encender)
            $this->decoracion|=Impresora::CURSIVA;
        else
            $this->decoracion&=(~Impresora::CURSIVA);
    }

    public function estaCursiva(){
        if($this->decoracion & ImpresoraPDF::CURSIVA){
            return true;
        }
        return false;
    }

    public function subrayada($encender=true) {
        if($encender)
            $this->decoracion|=Impresora::SUBRAYADA;
        else
            $this->decoracion&=(~Impresora::SUBRAYADA);
    }

    public function estaSubrayada(){
        if($this->decoracion & Impresora::SUBRAYADA){
            return true;
        }
        return false;
    }

    public function getHojaActual(){
        return $this->hojaActual;
    }

    public function getLineaActual(){
        return $this->lineaActual;
    }

    public function salvarEstilo(){
        $this->estilosSalvados=array();
        $this->estilosSalvados['decoracion']=$this->decoracion;
        $this->estilosSalvados['caracteresPorLinea']=$this->caracteresPorLinea;
        $this->estilosSalvados['tamanioHoja']=$this->tamanioHoja;
        $this->estilosSalvados['interlineado']=$this->interlineado;
    }

    public function restaurarEstilo(){
        if(!is_array($this->estilosSalvados))
            throw new Exception('Los estilos de la impresion no se han slavado y no pueden ser restablecidos');

        $decoracionAnterior=$this->estilosSalvados['decoracion'];
        if(($decoracionAnterior & Impresora::NEGRITA) && !($this->decoracion & Impresora::NEGRITA)){
            $this->negrita(true);
        }elseif(!($decoracionAnterior & Impresora::NEGRITA) && ($this->decoracion & Impresora::NEGRITA)){
            $this->negrita(false);
        }

        if(($decoracionAnterior & Impresora::CURSIVA) && !($this->decoracion & Impresora::CURSIVA)){
            $this->cursiva(true);
        }elseif(!($decoracionAnterior & Impresora::CURSIVA) && ($this->decoracion & Impresora::CURSIVA)){
            $this->cursiva(false);
        }

        if(($decoracionAnterior & Impresora::SUBRAYADA) && !($this->decoracion & Impresora::SUBRAYADA)){
            $this->subrayada(true);
        }elseif(!($decoracionAnterior & Impresora::SUBRAYADA) && ($this->decoracion & Impresora::SUBRAYADA)){
            $this->subrayada(false);
        }

        $tamanioHojaAnterior=$this->estilosSalvados['tamanioHoja'];
        if($tamanioHojaAnterior==Impresora::CARTA && $this->tamanioHoja!=Impresora::CARTA){
            $this->usarHojaCarta();
        }elseif($tamanioHojaAnterior==Impresora::OFICIO && $this->tamanioHoja!=Impresora::OFICIO){
            $this->usarHojaOficio();
        }

        $interlineadoAnterior=$this->estilosSalvados['interlineado'];
        if($interlineadoAnterior==Impresora::NORMAL && $this->interlineado!=Impresora::NORMAL){
            $this->usarInterEspaciadoNormal();
        }elseif($interlineadoAnterior==Impresora::CORTO && $this->interlineado!=Impresora::CORTO){
            $this->usarInterEspaciadoCorto();
        }

        $caracteresPorLineaAnterior=$this->estilosSalvados['caracteresPorLinea'];
        if($caracteresPorLineaAnterior==Impresora::C40 && $this->caracteresPorLinea!=Impresora::C40){
            $this->caracteresPorLinea40();
        }elseif($caracteresPorLineaAnterior==Impresora::C80 && $this->caracteresPorLinea!=Impresora::C80){
            $this->caracteresPorLinea80();
        }elseif($caracteresPorLineaAnterior==Impresora::C96 && $this->caracteresPorLinea!=Impresora::C96){
            $this->caracteresPorLinea96();
        }elseif($caracteresPorLineaAnterior==Impresora::C137 && $this->caracteresPorLinea!=Impresora::C137){
            $this->caracteresPorLinea137();
        }elseif($caracteresPorLineaAnterior==Impresora::C160 && $this->caracteresPorLinea!=Impresora::C160){
            $this->caracteresPorLinea160();
        }

        $this->estilosSalvados=null;
    }

    public function caracteresPorLinea40() {
        $this->caracteresPorLinea=Impresora::C40;
    }

    public function caracteresPorLinea80() {
        $this->caracteresPorLinea=Impresora::C80;
    }

    public function caracteresPorLinea96() {
        $this->caracteresPorLinea=Impresora::C96;
    }

    public function caracteresPorLinea137() {
        $this->caracteresPorLinea=Impresora::C137;
    }

    public function caracteresPorLinea160() {
        $this->caracteresPorLinea=Impresora::C160;
    }

    public function getCaracteresPorLinea(){
        return $this->caracteresPorLinea;
    }

    public function usarInterEspaciadoNormal() {
        if ($this->tamanioHoja == Impresora::CARTA)
            $this->lineasPorHoja = (int) 60;
        else
            $this->lineasPorHoja = (int) 72;
        $this->interlineado=Impresora::NORMAL;
    }

    public function usarInterEspaciadoCorto() {
        if ($this->tamanioHoja == Impresora::CARTA)
            $this->lineasPorHoja = (int) 80;
        else
            $this->lineasPorHoja = (int) 96;
        $this->interlineado=Impresora::CORTO;
    }

    public function getInterlineado(){
        return $this->interlineado;
    }

    public function getLineasPorHoja(){
        return $this->lineasPorHoja;
    }

    public function agregarEncabezado($funcionDeImpresion, $parametros, $indice=0){
        if(!empty($funcionDeImpresion) && !is_callable($funcionDeImpresion))
            throw new Exception('La impresion de encabezado no parece ser una funcion ejecutable');

        if(!empty($parametros) && !is_object($parametros))
            throw new Exception('Los parametros de la funcion de impresion de encabezado deben ser proporcionados en un objeto estandar');

        $this->encabezados[$indice]=$funcionDeImpresion;
        $this->parametrosEncabezados[$indice]=$parametros;
    }

    public function imprimirEmcabezados($numeroEncabezado=Impresora::TODOS_ENCABEZADOS){
        $this->salvarEstilo();
        $numeroEncabezados=count($this->encabezados);
        for($i=0; $i<$numeroEncabezados; $i++){
            if($numeroEncabezado!=Impresora::TODOS_ENCABEZADOS && $numeroEncabezado!=$i)
                continue;
            $encabezado=$this->encabezados[$i];
            $parametros=isset($this->parametrosEncabezados[$i])?$this->parametrosEncabezados[$i]:null;
            if(is_callable($encabezado)) {
                $encabezado($parametros);
            }
        }

        $this->restaurarEstilo();
    }

    public function agregarPieDePagina($funcionDeImpresion, $parametros){
        if(!empty($funcionDeImpresion) && !is_callable($funcionDeImpresion))
            throw new Exception('La impresion de pie de pagina no parece ser una funcion ejecutable');

        if(!empty($parametros) && !is_object($parametros))
            throw new Exception('Los parametros de la funcion de impresion de pie de pagina deben ser proporcionados en un objeto estandar');

        $this->pieDePagina=$funcionDeImpresion;
        $this->parametrosPieDePagina=$parametros;
    }

    public function imprimirPieDePagina(){
        $this->salvarEstilo();

        $pieDePagina=$this->pieDePagina;
        $parametros=isset($this->parametrosPieDePagina)?$this->parametrosPieDePagina:null;

        if(is_callable($pieDePagina)) {
            $pieDePagina($parametros);
        }

        $this->restaurarEstilo();
    }

    public function nuevaHoja() {
        $this->hojaActual++;
        $this->lineaActual = (int) 1;
        $this->imprimirEmcabezados(Impresora::TODOS_ENCABEZADOS);
    }

    public function iniciarImpresion($tamanioHoja=ImpresoraPDF::CARTA, $interlineado=ImpresoraPDF::NORMAL, $caracteresPorLinea=ImpresoraPDF::C80){
        if($tamanioHoja==ImpresoraPDF::CARTA)
            $this->usarHojaCarta();
        elseif($tamanioHoja==ImpresoraPDF::OFICIO)
            $this->usarHojaOficio();

        if($interlineado==ImpresoraPDF::NORMAL)
            $this->usarInterEspaciadoNormal();
        elseif($interlineado==ImpresoraPDF::CORTO)
            $this->usarInterEspaciadoCorto();

        if($caracteresPorLinea==Impresora::C80)
            $this->caracteresPorLinea80();
        elseif($caracteresPorLinea==Impresora::C40)
            $this->caracteresPorLinea40();
        elseif($caracteresPorLinea==Impresora::C96)
            $this->caracteresPorLinea96();
        elseif($caracteresPorLinea==Impresora::C137)
            $this->caracteresPorLinea137();
        elseif($caracteresPorLinea==Impresora::C160)
            $this->caracteresPorLinea160();

        $this->negrita(false);
        $this->cursiva(false);
        $this->subrayada(false);
        $this->hojaActual = 1;
        $this->lineaActual = 1;
    }

    public function escribir($texto, $maximoCaracteres=null, $pad=null, $padAlign=STR_PAD_RIGHT, $nuevaLinea = FALSE){
        if($nuevaLinea == TRUE)
            $this->nuevaLinea();
    }

     public function terminarImpresion(){
        $lineasFaltantes=$this->lineasPorHoja-$this->lineaActual;
        if($lineasFaltantes>0){
            $this->lineaActual=-100;
            $this->nuevaLinea($lineasFaltantes);
            $this->imprimirPieDePagina();
        }
    }

    abstract protected function secuenciaNuevaLinea();

    public function agregarNuevaLinea(ImpresoraBase $impresoraBase, $numeroDeLineas=0){
        $numeroDeLineas = (int)$numeroDeLineas;

        if ($numeroDeLineas <= 0) {
            $this->lineaActual++;

            if($this->lineaActual <= $this->lineasPorHoja)
                $impresoraBase->secuenciaNuevaLinea();
            else
                $this->nuevaHoja();

            if ($this->lineaActual == $this->lineasPorHoja-1 && is_callable($this->pieDePagina)) {
                $this->nuevaLinea();
                $this->imprimirPieDePagina();
                $this->nuevaLinea();
                return true;
            }
        } else {
            for ($i = 0; $i < $numeroDeLineas; $i++) {
                $this->nuevaLinea();
            }
        }
    }

    abstract public function getContenido($terminarImpresion=true);



}

?>