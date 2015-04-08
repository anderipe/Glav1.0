<?php
/**
 * @package co.org.lavado.io
 * @subpackage print
 */

require_once 'Empresa.php';
require_once 'Personal.php';

/**
 * Clase que representa las bases para todos los informes impresos, contiene
 * algunas funcinalidades comunes como la impresion de encabezados y pies de
 * paginas
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
class Informe {
    public static $empresa=null;

    public static $personaJuridica=null;

    public static $propietario=null;

    public static $tituloInforme=null;

    public static function imprimirEncabezadoBasico(ImpresoraBase $impresora){
        if(empty(Informe::$empresa)){
            Informe::$empresa=Empresa::obtenerMiEmpresa();
            Informe::$personaJuridica=Informe::$empresa->getPersona();
            $personal=new Personal();
            $personal->cargarPorCargo(new Cargo(1));
            Informe::$propietario=$personal->getPersona();
        }

        $nombreEmpresa=Informe::$personaJuridica->getNombres();
        if(mb_strlen($nombreEmpresa)>40)
            $nombreEmpresa=Informe::$empresa->getNombreAbreviado();

        $impresora->negrita();
        $impresora->caracteresPorLinea40();
        $impresora->escribir($nombreEmpresa, 40, 40, STR_PAD_BOTH, true);

        $linea='';
        $linea.=Informe::$propietario->getNombreCompleto();

        $impresora->caracteresPorLinea80();
        $impresora->escribir($linea, 80, 80, STR_PAD_BOTH, true);
        $impresora->negrita(false);

        $linea=Informe::$personaJuridica->getTipoIdentificacion()->getAbreviatura();
        $linea.=' '.Informe::$personaJuridica->getIdentificacion();
        $linea.='-'.Informe::$personaJuridica->getDigitoVerificacion();
        $impresora->caracteresPorLinea96();
        $impresora->escribir($linea, 96, 96, STR_PAD_BOTH, true);

        $linea=Informe::$personaJuridica->getDireccion();
        $linea.=' - telefono '.Informe::$personaJuridica->getTelefonos();

        $impresora->caracteresPorLinea137();
        $impresora->escribir($linea, 137, 137, STR_PAD_BOTH, true);

        $impresora->caracteresPorLinea80();
        if(get_class($impresora)=='Impresora'){
            $nombreDocumento=Impresora::NOMBRE_DOCUMENTO;
            if(Impresora::COMENTARIOS_DOCUMENTO!='' && Impresora::COMENTARIOS_DOCUMENTO!=null)
                $nombreDocumento.=', '.Impresora::COMENTARIOS_DOCUMENTO;
        }else{
            $nombreDocumento=$impresora->getImpresion()->getDocumentoImprimible()->getNombre();
            if($impresora->getImpresion()->getComentarios()!='' && $impresora->getImpresion()->getComentarios()!=null)
                $nombreDocumento.=', '.$impresora->getImpresion()->getComentarios();
        }
        $impresora->negrita(true);
        $impresora->escribir($nombreDocumento, 80, 80, STR_PAD_BOTH, true);
        $impresora->negrita(false);

        $impresora->nuevaLinea();
    }

    public static function imprimirPieDePaginaBasico(ImpresoraBase $impresora){
        $impresora->caracteresPorLinea160();
        if(get_class($impresora)=='Impresora')
            $impresora->escribir(Impresora::NOMBRE_DOCUMENTO.', '.Impresora::COMENTARIOS_DOCUMENTO.' - pagina '.$impresora->getHojaActual().' de '.Impresora::TOTAL_PAGINAS);
        else/*if(get_class($impresora)=='ImpresoraPDF')*/
            $impresora->escribir($impresora->getImpresion()->getDocumentoImprimible()->getNombre().', '.$impresora->getImpresion()->getComentarios().' - pagina '.$impresora->getHojaActual());
    }
}

?>
