<?php
/**
 * @package co.org.lavado.io
 * @subpackage print
 */

/**
 * Representa un tipo de comando de impresion enviado a impresoras de matriz
 * de punto o pdf. Un comando de impresion puede ser el comando de nueva linea,
 * o el comando de cambiar el tamaño de la letra
 *
 * @access public
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.io
 * @subpackage print
 */
class ComandoImpresion
    extends ClaseBase {

    /**
     * Identificador unico del comando de impresion
     * @var int
     */
    protected $idComandoImpresion=0;

    /**
     * Abreviatura del comando de impresion
     * @var string
     */
    protected $abreviatura='';

    /**
     * Descripcion completa del comando de impresion
     * @var string
     */
    protected $descripcion='';

    /**
     * Contructor de la clase
     * @param int $id Identificador unico del comando de impresion
     * @param string $prefijoPropiedadJson Prefijo de las propiedades al ser devueltas como json
     * @throws AppException
     */
    public function __construct($id=null, $prefijoPropiedadJson=null) {
        parent::__construct($prefijoPropiedadJson);

        $this->setPropiedad('idComandoImpresion', 0);
        $this->setPropiedad('abreviatura', '');
        $this->setPropiedad('descripcion', '');

        $id=(int)$id;
        if($id!=null)
            if(!$this->cargarObjeto('idcomandoimpresion='.$id))
                throw new AppException('No existe comando de impresion con identificador '.$id);
    }


    /**
     * Carga un comando dentro de las propiedades de la clase
     * @param string $string selector del comando
     * @return boolean false si ningun objeto coincide con el selector
     * @throws AppException
     */
    protected function cargarObjeto($string) {
        if(!empty($this->idComandoImpresion))
            throw new AppException('El comando de impresion ya se encuentra cargado');

        $resultados=$this->conexion->consultar('select * from comandoimpresion where '.$string);

        if($resultados->getCantidad()==0)
            return false;

        if($resultados->getCantidad()>1)
            throw new AppException('Se ha devuelto mas de un comando de impresion para la carga del objeto', null);

        $resultados->irASiguiente();

        $this->setPropiedad('idComandoImpresion', (int)$resultados->get()->idcomandoimpresion, true);
        $this->setPropiedad('abreviatura', (string)$resultados->get()->abreviatura, true);
        $this->setPropiedad('descripcion', (int)$resultados->get()->descripcion, true);
        $this->hash=(string)$resultados->get()->hash;
        $this->firma=(string)$resultados->get()->firma;

        return true;
    }

    /**
     * Guarda el objeto en la base de datos
     * No aplica para esta clase
     * @param Auditoria $auditoria
     */
    public function guardarObjeto(Auditoria $auditoria = null) {

    }

    /**
     * Define si el objeto ha sido utilizado por otras relaciones en la base
     * de datos
     * @return boolean
     */
    public function haSidoUtilizado() {
        return false;
    }
}

?>
