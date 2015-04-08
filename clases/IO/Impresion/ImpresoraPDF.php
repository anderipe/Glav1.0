<?php
/**
 * @package co.org.lavado.io
 * @subpackage print
 */

require_once 'ImpresoraBase.php';

/**
 * Clase que representa una impresora para archivos pdf, esta impresora no
 * imprime realmente, si no que en su lugar crea un archivo pdf con los datos
 * a imprimir.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
class ImpresoraPDF
    extends ImpresoraBase{

    /**
     * Constante de conversion de milimetros a pixeles
     * @var int
     * @access public
     */
    const CONSTANTE_MM_2_PT=2.8346456692913385826771653543307;

    /**
     * Constante de conversion de pixeles a milimetros
     * @var int
     * @access public
     */
    const CONSTANTE_PT_2_MM=0.35277777777777777777777777777778;

    /**
     * Ancho de la hoja, contante usada para acceder al vector de propiedades
     */
    const ANCHO=2;

    /**
     * Alto de la hoja, contante usada para acceder al vector de propiedades
     */
    const ALTO=3;

    /**
     * Representa el documento PDF
     *
     * @var HaruDoc
     * @access private
     */
    private $documentoPdf=null;

    /**
     * Representa la pagina actual del documento PDF
     *
     * @var HaruPage
     * @access private
     */
    private $paginaPdf="";

    /**
     * Define al tamaño de la hoja
     * @var array
     * @access private
     */
    private $medidasHoja=array(null, null, null, null, null);

    /**
     * Posicion horizontal del cursor en la pagina PDF
     *
     * @var int
     * @access private
     */
    private $posX=0.0;

    /**
     * Posicion vertical del cursor en la pagina PDF
     *
     * @var int
     * @access private
     */
    private $posY=0.0;

    private $fuente=null;

    private $fontSize=null;

    private $charSpace=null;

    protected $espacioEntreLineas=0.0;

    /**
     * Margen superior del documento
     *
     * @var int
     * @access private
     */
    private $margenSuperior=0.0;

    /**
     * Margen izquierdo del documento
     *
     * @var int
     * @access private
     */
    private $margenIzquierdo=0.0;



    public function __construct(){
        parent::__construct('');
        $this->documentoPdf = new HaruDoc();
        $this->documentoPdf->setCompressionMode(HaruDoc::COMP_ALL);
        $this->fuente='Courier';
    }

    public static function mm2pt($milimetros){
        return (double)(ImpresoraPDF::CONSTANTE_MM_2_PT*$milimetros);
    }

    public static function pt2mm($puntos){
        return (double)(ImpresoraPDF::CONSTANTE_PT_2_MM*$puntos);
    }

    public function mm2x($milimetros){
        return ImpresoraPDF::mm2pt($milimetros);
    }

    public function mm2y($milimetros){
        return $this->tamanioHoja[ImpresoraPDF::ALTO]-ImpresoraPDF::mm2pt($milimetros);
    }

    public function setMargen($margenSuperior, $margenIzquiero){
        $this->margenSuperior=$this->mm2pt($margenSuperior);
        $this->margenIzquierdo=$this->mm2pt($margenIzquiero);
    }

    public function usarHojaCarta() {
        parent::usarHojaCarta();
        $this->medidasHoja[ImpresoraPDF::ANCHO]=$this->mm2pt(215.9);
        $this->medidasHoja[ImpresoraPDF::ALTO]=$this->mm2pt(279.4);

    }

    public function usarHojaOficio() {
        parent::usarHojaOficio();
        $this->medidasHoja[ImpresoraPDF::ANCHO]=$this->mm2pt(215.9);
        $this->medidasHoja[ImpresoraPDF::ALTO]=$this->mm2pt(330.2);
        $this->tamanioHoja=ImpresoraPDF::OFICIO;
    }

    protected function aplicarFuente(){
        if($this->hojaActual>0 && $this->fontSize!==null && $this->charSpace!=null){
            $this->paginaPdf->setFontAndSize($this->documentoPdf->getFont($this->fuente,"CP1252"), $this->fontSize);
            $this->paginaPdf->setCharSpace($this->charSpace);
        }
    }

    public function negrita($encender=true) {
        parent::negrita($encender);
        if($encender){
            $this->fuente='Courier-Bold';
            if($this->estaCursiva())
                $this->fuente.='Oblique';
        }else{
            $this->fuente='Courier';
            if($this->estaCursiva())
                $this->fuente.='-Oblique';
        }

        $this->aplicarFuente();
    }

    public function cursiva($encender=true) {
        parent::cursiva($encender);
        if($encender){
            $this->fuente='Courier-';
            if($this->estaNegrita())
                $this->fuente.='Bold';
            $this->fuente.='Oblique';
        }else{
            $this->fuente='Courier';
            if($this->estaNegrita())
                $this->fuente.='-Bold';
        }

        $this->aplicarFuente();
    }

    public function caracteresPorLinea40() {
        parent::caracteresPorLinea40();
        $this->fontSize=(double)20;
        $this->charSpace=(double)0.0001;
        $this->aplicarFuente();
    }

    public function caracteresPorLinea80() {
        parent::caracteresPorLinea80();
        $this->fontSize=(double)8.7;
        $this->charSpace=(double)0.8;
        $this->aplicarFuente();
    }

    public function caracteresPorLinea96() {
        parent::caracteresPorLinea96();
        $this->fontSize=(double)7.5;
        $this->charSpace=(double)0.5;
        $this->aplicarFuente();
    }

    public function caracteresPorLinea137() {
        parent::caracteresPorLinea137();
        $this->fontSize=(double)6.67;
        $this->charSpace=(double)-0.5;
        $this->aplicarFuente();
    }

    public function caracteresPorLinea160() {
        parent::caracteresPorLinea160();
        $this->fontSize=(double)6.34;
        $this->charSpace=(double)-0.8;
        $this->aplicarFuente();
    }

    public function usarInterEspaciadoNormal() {
        parent::usarInterEspaciadoNormal();
        $this->espacioEntreLineas=(double)11.3;
    }

    public function usarInterEspaciadoCorto() {
        $this->set('interlineado_corto');
        $this->espacioEntreLineas=(double)8.4;
    }

    public function nuevaHoja() {
        $this->paginaPdf=$this->documentoPdf->addPage();
        $this->paginaPdf->setWidth($this->medidasHoja[ImpresoraPdf::ANCHO]);
        $this->paginaPdf->setHeight($this->medidasHoja[ImpresoraPdf::ALTO]);

        $this->posX=$this->mm2x(0.0);
        $this->posY=$this->posY=$this->medidasHoja[ImpresoraPDF::ALTO];
        $this->aplicarFuente();

        parent::nuevaHoja();
    }

    public function escribir($texto, $maximoCaracteres=null, $pad=null, $padAlign=STR_PAD_RIGHT, $nuevaLinea = FALSE) {
        $maximoCaracteres=(int)$maximoCaracteres;
        $pad=(int)$pad;

        if($maximoCaracteres>0)
            $texto=mb_substr($texto, 0, $maximoCaracteres);
        if($pad>0)
            $texto=Auxiliar::mb_str_pad($texto, $pad, ' ', $padAlign);

        $this->paginaPdf->beginText();
        $this->paginaPdf->textOut(round($this->margenIzquierdo+$this->posX), round($this->posY-$this->margenSuperior), utf8_decode($texto));
        $pos=$this->paginaPdf->getCurrentTextPos();
        $this->paginaPdf->endText();
        $this->posX=((double)$pos['x'])-$this->margenIzquierdo;
        parent::escribir($texto, $maximoCaracteres, $pad, $padAlign, $nuevaLinea);
    }

    protected function secuenciaNuevaLinea(){
        $this->posX=0;
        $this->posY=$this->posY-$this->espacioEntreLineas;
    }

    public function nuevaLinea($numeroDeLineas=0){
        $this->agregarNuevaLinea($this, $numeroDeLineas);
    }

    public function guardar($rutaArchivo){
        $this->terminarImpresion();
        $this->documentoPdf->save($rutaArchivo);
    }

    public function iniciarImpresion($tamanioHoja=ImpresoraPDF::CARTA, $interlineado=ImpresoraPDF::NORMAL, $caracteresPorLinea=ImpresoraPDF::C80){
        $this->paginaPdf=$this->documentoPdf->addPage();
        parent::iniciarImpresion($tamanioHoja, $interlineado, $caracteresPorLinea);
        $this->paginaPdf->setWidth($this->medidasHoja[ImpresoraPDF::ANCHO]);
        $this->paginaPdf->setHeight($this->medidasHoja[ImpresoraPDF::ALTO]);
        $this->posX=$this->mm2x(0.0);
        $this->posY=$this->medidasHoja[ImpresoraPDF::ALTO];
        $this->setMargen(25, 23);
    }

    public function getContenido($terminarImpresion=true){
        if($terminarImpresion)
            $this->terminarImpresion();

        $tmpFile = @tempnam(Auxiliar::sys_get_temp_dir(), 'tmp');
        if (file_exists($tmpFile))
            unlink($tmpFile);

        $this->guardar($tmpFile);
        $rawData=file_get_contents($tmpFile);
        unlink($tmpFile);
        $base64Data=base64_encode($rawData);
        return $base64Data;
    }

    protected function cargarObjeto($string) {
        throw new Exception('Metodo cargarObjeto no implementado');
    }

    public function guardarObjeto(Auditoria $auditoria = null) {
        throw new Exception('Metodo guardarObjeto no implementado');
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>