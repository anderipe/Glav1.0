<?php
/**
 * @package co.org.lavado.io
 * @subpackage print
 */

require_once 'ImpresoraBase.php';
require_once 'PHPExcel.php';

/**
 * Clase que representa una impresora para archivos en excel, esta impresora no
 * imprime realmente, si no que en su lugar crea un archivo xls con los datos
 * a imprimir.
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
class ImpresoraExcel
    extends ImpresoraBase{

    /**
     *
     * @var PHPExcel
     */
    protected $documento=null;

    protected $fila=1;

    protected $columna=0;

    public function __construct(){
        parent::__construct('');
        $this->agregarPieDePagina(null, null);
        $this->documento=new PHPExcel();
    }

    public function escribir($texto, $maximoCaracteres=null, $pad=null, $padAlign=STR_PAD_RIGHT, $nuevaLinea = FALSE) {
        $this->documento->getActiveSheet()->getCellByColumnAndRow($this->columna, $this->fila)->setValue($texto);
        $this->columna++;
        parent::escribir($texto, $maximoCaracteres, $pad, $padAlign, $nuevaLinea);
    }

    protected function secuenciaNuevaLinea(){
        $this->fila++;
        $this->columna=0;
    }

    public function nuevaLinea($numeroDeLineas=0){
        $this->agregarNuevaLinea($this, $numeroDeLineas);
    }

    public function guardar($rutaArchivo){
        $writer=PHPExcel_IOFactory::createWriter($this->documento, 'Excel2007');
        $writer->save($rutaArchivo);
    }

    public function getContenido($terminarImpresion=true){
        if($terminarImpresion)
            $this->terminarImpresion();

        $tmpFile = @tempnam(Auxiliar::sys_get_temp_dir(), 'tmp');
        if (file_exists($tmpFile))
            unlink($tmpFile);

        $this->guardar($tmpFile);
        $rawData=  file_get_contents($tmpFile);
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

    public function nuevaHoja() {
        $this->hojaActual++;
        $this->lineaActual =(int)1;
        $this->secuenciaNuevaLinea();
    }

    public function imprimirPieDePagina(){
        //No debe hacer nada para excel
    }

    public function haSidoUtilizado() {

        return false;
    }
}

?>