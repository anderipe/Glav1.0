<?php
require_once 'PHPExcel.php';
/**
 * Clase para el manejo de archivos en excel, facilita el uso de los metodos de
 * PHPExcel
 *
 * @author Mabicho
 */
class DocumentoExcel {
    /**
     *
     * @var PHPExcel
     */
    protected $documento=null;
    
    protected $fila=1;
    
    protected $columna=0;

    /**
     * Crea el objeto
     * @param string $PHPExcelPath Ruta relativa a la carpeta que contiene las
     * clase de PHPExcel
     */
    public function __construct() {                
        $this->documento=new PHPExcel();
    }    

    /**
     * Guarda el documento de excel, el archivo sera guardado en formato xlsx
     * de excel 2007, coloque el nombre del archivo completo, por ejemplo:
     * /srv/www/htdocs/documentos/miarchivo.xlsx. Este metodo no hace ningun
     * tipo de completado de extencion sobre el nombre del archivo.
     *
     * ATENCION: Si sale un mensaje que dice Uncaught exception 'Exception' with
     * message 'Could not close zip file' es porque la ruta que esta dando para
     * guardado NO EXISTE.
     *
     * @param string $rutaArchivo Ruta del archivo en el que se quiere guardar
     */
    public function guardar($rutaArchivo){
        $writer=PHPExcel_IOFactory::createWriter($this->documento, 'Excel2007');
        $writer->save($rutaArchivo);        
    }
    
    public function getBase64(){
        $tmpFile = @tempnam(PHPExcel_Shared_File::sys_get_temp_dir(), 'tmp');
        if (file_exists($tmpFile)) 
            unlink($tmpFile);
        
        $writer=PHPExcel_IOFactory::createWriter($this->documento, 'Excel2007');
        $writer->save($tmpFile);        
        $rawData=  file_get_contents($tmpFile);
        unlink($tmpFile);
        $base64Data=base64_encode($rawData);
        return $base64Data;
    }

    /**
     * Modificador de metadatos.
     * Establece el autor del documento
     * @param type $autor
     */
    public function setAutor($autor){
        $this->documento->getProperties()->setCreator($autor);
    }

    /**
     * Modificador de metadatos.
     * Establece la ultima persona que ha modificado el documento
     * @param string $ultimoModificador
     */
    public function setUltimoModificador($ultimoModificador){
        $this->documento->getProperties()->setLastModifiedBy($ultimoModificador);
    }

    /**
     * Modificador de metadatos.
     * Establece el titulo del documento
     * @param string $titulo
     */
    public function setTitulo($titulo){
        $this->documento->getProperties()->setTitle($titulo);
    }

    /**
     * Modificador de metadatos.
     * Establece el tema del documento
     * @param type $tema
     */
    public function setTema($tema){
        $this->documento->getProperties()->setSubject($tema);
    }

    /**
     * Modificador de metadatos.
     * Establece la descripcion del documento
     * @param string $descripcion
     */
    public function setDescripcion($descripcion){
        $this->documento->getProperties()->setDescription($descripcion);
    }

    /**
     * Modificador de metadatos.
     * Establece las palabras clave del documento
     * @param string $palabrasClave
     */
    public function setPalabrasClave($palabrasClave){
        $this->documento->getProperties()->setKeywords($palabrasClave);
    }

    /**
     * Modificador de metadatos.
     * Establece la categoria del documento
     * @param string $categoria
     */
    public function setCategoria($categoria){
        $this->documento->getProperties()->setCategory($categoria);
    }

    /**
     * Selecciona la hoja del documento sobre la cual se llevara acabo las
     * acciones de edicion. Si la hoja no existe aun en el documento esta sera
     * creada automaticamente
     * @param int $indice Indice de la hoja que sera utilizada
     */
    public function seleccionarHojaActiva($indice){
        $indice=(int)$indice;
        $hojas=$this->documento->getAllSheets();
        if($indice>=count($hojas) || !(is_object($hojas[$indice]) && (get_class($hojas[$indice])=='PHPExcel_Worksheet' || is_subclass_of($hojas[$indice], 'PHPExcel_Worksheet')))){
            $this->documento->createSheet($indice);
        }

        $this->documento->setActiveSheetIndex($indice);

    }

    /**
     * Establece el valor de una celda segun su coordenada descriptiva, por
     * ejemplo, para colocar el valor de 5 en la celda 'B4', se debe invocar
     * setValorCelda('B5', 5);
     * @param string $coordenada Coordenada descriptiva
     * @param type $valor
     */
    public function escribirCelda($columna, $fila, $valor){
        $this->documento->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->setValue($valor);
    }
    
    public function escribirFila(){
        $argumentos = func_get_args();
        $numeroArgumentos=count($argumentos);
        
        for($i=0; $i<$numeroArgumentos; $i++){
            $this->documento->getActiveSheet()->getCellByColumnAndRow($this->columna, $this->fila)->setValue($argumentos[$i]);
            $this->columna++;
        }
                
        $this->fila++;
        $this->columna=0;
    }

    /**
     * Agrupa un rango de celdas en una sola. Todas las celdas contenidas en el
     * rectangulo imaginario dado por las coordenadas seran agrupadas.
     * Por ejemplo agruparCeldas('A1', 'E5'), agrupa 25 celdas
     * @param type $coordenadaInicial Coordenada descriptiva inicial
     * @param type $coordenadaFinal Coordenada descriptiva final
     */
    public function agruparCeldas($coordenadaInicial, $coordenadaFinal){
        $this->documento->getActiveSheet()->mergeCells($coordenadaInicial.':'.$coordenadaFinal);
    }

    /**
     * Establece el nombre de la hoja actual
     *
     * @param type $titulo
     */
    public function setNombreDeHoja($titulo){
        $this->documento->getActiveSheet()->setTitle($titulo);
    }

    /**
     * Establece el alineado vertical y horizontal de un rango de celdas.
     * Los valores de alineacion estan dador por las constantes de la clase
     * PHPExcel_Style_Alignment.
     *
     * PHPExcel_Style_Alignment
     * @param string $coordenadaInicial Coordenada descriptiva inicial
     * @param string $coordenadaFinal Coordenada descriptiva final
     * @param string $alineacionHorizontal Alineacion horizontal
     * @param string $alineacionVertical Alineacion vertical
     */
    public function setAlineacion($coordenadaInicial, $coordenadaFinal, $alineacionHorizontal='center', $alineacionVertical='center'){
        $this->documento->getActiveSheet()
                ->getStyle($coordenadaInicial.':'.$coordenadaFinal)
                ->getAlignment()->setVertical($alineacionVertical);

        PHPExcel_Style_Alignment::
        $this->documento->getActiveSheet()
                ->getStyle($coordenadaInicial.':'.$coordenadaFinal)
                ->getAlignment()->setHorizontal($alineacionHorizontal);
    }

    /**
     * Establece la fuente de una rango de celdas. Si los parametros que definen
     * el rango de celdas es nulo, la fuente se establece como la fuente por
     * defecto de la hoja actual
     *
     * @param string $coordenadaInicial Coordenada descriptiva inicial
     * @param string $coordenadaFinal Coordenada descriptiva final
     * @param string $nombreFuente Nombre de la fuente
     */
    public function setFuente($coordenadaInicial=null, $coordenadaFinal=null, $nombreFuente='Arial'){
        if($coordenadaInicial===null && $coordenadaFinal===null)
            $this->documento->getActiveSheet()->getDefaultStyle()
            ->getFont()->setName($nombreFuente);
        else
            $this->documento->getActiveSheet()
                ->getStyle($coordenadaInicial.':'.$coordenadaFinal)
                ->getFont()->setName($nombreFuente);
    }

    /**
     * Establece el formato numerico de la celda
     *
     * @param string $coordenadaInicial Coordenada descriptiva inicial
     * @param string $coordenadaFinal Coordenada descriptiva final
     * @param string $formato Formato de la celda
     */
    public function setFormato($coordenadaInicial=null, $coordenadaFinal=null, $formato='$0,000.00'){
        $this->documento->getActiveSheet()
            ->getStyle($coordenadaInicial.':'.$coordenadaFinal)
            ->getNumberFormat()->setFormatCode($formato);
    }

    /**
     * Establece si la fuente sera negrilla. Si los parametros que definen
     * el rango de celdas es nulo, se establece como la configuracion por
     * defecto de la hoja actual
     *
     * @param string $coordenadaInicial Coordenada descriptiva inicial
     * @param string $coordenadaFinal Coordenada descriptiva final
     * @param boolean $establecer Si es true, se establece a negrilla
     */
    public function setNegrilla($coordenadaInicial=null, $coordenadaFinal=null, $establecer=true){
        $establecer=(boolean)$establecer;
        if($coordenadaInicial===null && $coordenadaFinal===null)
            $this->documento->getActiveSheet()->getDefaultStyle ()->getFont()
            ->setBold($establecer);
        else
            $this->documento->getActiveSheet()
                ->getStyle($coordenadaInicial.':'.$coordenadaFinal)->getFont()
                ->setBold ($establecer);
    }

    /**
     * Establece el tamaño de la fuente. Si los parametros que definen
     * el rango de celdas es nulo, se establece como la configuracion por
     * defecto de la hoja actual
     *
     * @param string $coordenadaInicial Coordenada descriptiva inicial
     * @param string $coordenadaFinal Coordenada descriptiva final
     * @param int $tamanio tamaño de la fuente
     */
    public function setTamanioFuente($coordenadaInicial=null, $coordenadaFinal=null, $tamanio=8){
        $tamanio=(int)$tamanio;
        if($coordenadaInicial===null && $coordenadaFinal===null)
            $this->documento->getActiveSheet()->getDefaultStyle ()->getFont()
            ->setSize($tamanio);
        else
            $this->documento->getActiveSheet()
                ->getStyle($coordenadaInicial.':'.$coordenadaFinal)->getFont()
                ->setSize ($tamanio);
    }

    /**
     * Quita el borde a todas las celdas
     * @param string $coordenadaInicial Coordenada descriptiva inicial
     * @param string $coordenadaFinal Coordenada descriptiva final
     */
    public function setBordeNulo($coordenadaInicial, $coordenadaFinal){
        $styleArray = array(
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_NONE
                        ),
                ),
        );
        $this->documento->getActiveSheet()
                ->getStyle($coordenadaInicial.':'.$coordenadaFinal)
                ->applyFromArray($styleArray);

    }

    /**
     * Establece bordes completos, internos y externos a todas las celdas
     * @param string $coordenadaInicial Coordenada descriptiva inicial
     * @param string $coordenadaFinal Coordenada descriptiva final
     * @param string $color Color en formato hexadecimal
     */
    public function setBordeCompleto($coordenadaInicial, $coordenadaFinal, $color='000000' ){
        $styleArray = array(
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF'.$color),
                        ),
                ),
        );
        $this->documento->getActiveSheet()
                ->getStyle($coordenadaInicial.':'.$coordenadaFinal)
                ->applyFromArray($styleArray);

    }

    /**
     * Establece bordes externos a todas las celdas
     * @param string $coordenadaInicial Coordenada descriptiva inicial
     * @param string $coordenadaFinal Coordenada descriptiva final
     * @param string $color Color en formato hexadecimal
     */
    public function setBordeExterno($coordenadaInicial, $coordenadaFinal, $color='000000' ){
        $styleArray = array(
                'borders' => array(
                        'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF'.$color),
                        ),
                ),
        );
        $this->documento->getActiveSheet()
                ->getStyle($coordenadaInicial.':'.$coordenadaFinal)
                ->applyFromArray($styleArray);

    }

    /**
     * Establece el ancho de una columna dado en unidades de caracter
     * @param string $columna
     * @param int $ancho Ancho de la columna en unidades de caracter
     */
    public function setAnchoColumna($columna, $ancho){
        $ancho=(double)$ancho;
        $this->documento->getActiveSheet()->getColumnDimension($columna)->setWidth($ancho);
    }

    public function setAltoFila($fila, $alto){
        $fila=(int)$fila;
        $alto=(double)$alto;
        $this->documento->getActiveSheet()->getRowDimension($fila)->setRowHeight($alto);
    }

    /**
     * Extablece el nivel de zoom de la pagina activa
     * @param int $zoomLevel Nivel de zoom entre 0 y 100
     */
    public function setZoom($zoomLevel){
        $zoomLevel=(int)$zoomLevel;
        $this->documento->getActiveSheet()->getSheetView()->setZoomScale($zoomLevel);
    }

    /**
     * Establece el encaje de texto en las celdas
     * @param string $coordenadaInicial
     * @param boolean $setWrap
     */
    public function setWrap($coordenadaInicial, $setWrap=true ){
        $setWrap=(boolean)$setWrap;
        $this->documento->getActiveSheet()
                ->getStyle($coordenadaInicial)
                ->getAlignment()->setWrapText($setWrap);

    }


}

?>
