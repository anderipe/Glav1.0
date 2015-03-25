<?php
/**
 * @package co.org.lavado.app
 * @subpackage sistema
 */

require_once 'Auditoria.php';
require_once 'Variable.php';

/**
 * Clase base para todas las clases de instancia de la aplicacion
 *
 * @author Universidad Cooperativa de Colombia - 2012
 * @since 2012/09/01
 * @version 1.0
 * @package co.org.lavado.app
 * @subpackage sistema
 */
abstract class ClaseBase {
    //put your code here

    /**
     *
     * @var boolean
     */
    protected $modificado;

    /**
     *
     * @var mixed
     */
    protected $objetoJson;

    /**
     *
     * @var mixed
     */
    protected $prefijoPropiedadJson;

    /**
     *
     * @var ConexionMySQL
     */
    protected $conexion;

    /**
     *
     * @var string
     */
    protected $hash='';

    /**
     *
     * @var string
     */
    protected $firma='';

    /**
     *
     * @var string
     */
    protected $nombreDeClase='';

    /**
     *
     * @var string
     */
    protected $nombreDeTabla='';

    /**
     *
     * @var string
     */
    protected $campoId='';

    /**
     *
     * @var string
     */
    protected $rawData='';

    /**
     *
     */
    public function __construct($prefijoPropiedadJson=null) {
        $this->conexion=  FrameWork::getConexion();
        $this->modificado=false;
        $this->prefijoPropiedadJson=$prefijoPropiedadJson;
        $this->objetoJson=new stdClass();

        $this->nombreDeClase=get_class($this);
        $this->nombreDeTabla=mb_strtolower($this->nombreDeClase);
        $this->campoId='id'.$this->nombreDeTabla;
    }

    /**
     *
     * @param string $propiedad
     * @param boolean $lanzarExcepcion
     * @return boolean
     * @throws AppException
     */
    public function existePropiedad($propiedad, $lanzarExcepcion=true){
        if(!isset($this->modificado))
            throw new AppException('La propiedad directa no existe en '.$this->nombreDeClase);
        //echo "$propiedad: ".$this->$propiedad." - ";
        if(!isset($this->$propiedad)){
            if($lanzarExcepcion)
                throw new AppException('La propiedad '.$propiedad.' no existe en '.$this->nombreDeClase);
            else
                return false;
        }

        return true;
    }

    /**
     *
     * @param string $propiedad
     * @return mixed
     */
    public function getValorOriginal($propiedad){
        $this->existePropiedad($propiedad);
        $nuevaPropiedad="original_".$propiedad;
        if(isset($this->$nuevaPropiedad))
            return $this->$nuevaPropiedad;

        return null;
    }

    /**
     *
     * @param type $propiedad
     * @return boolean
     */
    public function estaModificada($propiedad=NULL){
        if(empty($propiedad))
            return $this->modificado;

        $this->existePropiedad($propiedad);
        $propiedadOriginal="original_".$propiedad;

        if(!$this->existePropiedad($propiedadOriginal, false))
            return false;

        if($this->$propiedadOriginal===$this->$propiedad)
            return false;

        return true;
    }

    /*
    public function getTextoParaAuditoria($propiedad){
        $this->existePropiedad($propiedad);
        $propiedadOriginal="original_".$propiedad;

        if(!$this->existePropiedad($propiedadOriginal, false))
            return $propiedad.':'.$this->$propiedad;

        return $propiedad.':'.$this->$propiedadOriginal.'->'.$this->$propiedad;
    }
     * */

    /**
     *
     * @param type $propiedad
     * @return boolean
     */
    public function marcarNoModificada($propiedad){
        $this->existePropiedad($propiedad);
        $nuevaPropiedad="original_".$propiedad;
        $this->$nuevaPropiedad=$this->$propiedad;

        $propiedadJson=$this->prefijoPropiedadJson.strtolower($propiedad);
        $this->objetoJson->$propiedadJson=$this->$propiedad;
        return $this->$propiedad;
    }

    /**
     *
     * @param string $propiedad
     * @param mixed $valor
     */
    public function setPropiedad($propiedad, $valor, $forzarOriginal=false){
        $this->existePropiedad($propiedad);
        $nuevaPropiedad="original_".$propiedad;
        if($forzarOriginal){
            $this->$nuevaPropiedad=$valor;
        }else{
            if($this->existePropiedad($nuevaPropiedad, false)){
                if($this->$propiedad===$valor)
                    return $valor;
                else{
                    $this->$nuevaPropiedad=$this->$propiedad;
                }
            }else{
                $this->$nuevaPropiedad=$valor;
            }
        }

        $this->$propiedad=$valor;
        //echo "El prefino de joson es para $propiedad es ".$this->prefijoPropiedadJson."-";
        $propiedadJson=$this->prefijoPropiedadJson.strtolower($propiedad);
        $this->objetoJson->$propiedadJson=$valor;
        $this->modificado=true;
        return $this->$nuevaPropiedad;
    }

    /**
     *
     * @param string $propiedad
     * @return string
     */
    public function getNombreJson($propiedad){
        return $this->prefijoPropiedadJson.strtolower($propiedad);
    }

    /**
     *
     * @param type $propiedad
     * @return string
     */
    public function getTextoParaAuditoria($propiedad){
        $this->existePropiedad($propiedad);
        $textoAuditoria=$propiedad.": ";
        $propiedadOriginal="original_".$propiedad;
        if($this->$propiedadOriginal!==$this->$propiedad){
            if(is_string($this->$propiedadOriginal))
                $textoAuditoria.="'".$this->$propiedadOriginal."'>>";
            else
                $textoAuditoria.=$this->$propiedadOriginal.">>";
        }

        if(is_string($this->$propiedad))
            $textoAuditoria.="'".$this->$propiedad."'";
        else
            $textoAuditoria.=$this->$propiedad;

        return $textoAuditoria;
    }

    /**
     *
     * @param type $comoObjeto
     * @return stdClass
     */
    public function getJson($comoObjeto=false, $prefijoJson=null){
        $objetoJson=null;
        if(empty($prefijoJson)){
            $objetoJson=$this->objetoJson;
        }else{
            $objetoJson=new stdClass();
            foreach($this->objetoJson as $key => $value) {
                $nuevaPropiedad=$prefijoJson.$key;
                $objetoJson->$nuevaPropiedad=$value;
            }
        }

        if($comoObjeto)
            return $objetoJson;
        else
            return json_encode($objetoJson);
    }

    public function getHash(){
        return $this->hash;
    }

    public function getFirma(){
        return $this->firma;
    }

    public function calcularHash(){
        $this->calcularRawData();

        return hash ("sha256", $this->rawData);
    }

    public function verificarHash(){
        if(empty($this->hash))
            throw new Exception('Imposible verificar hash de '.$this->nombreDeClase.', no existe hash guardado');

        if($this->hash!=$this->calcularHash())
            return false;

        return true;
    }

    public function calcularFirma(){
        $this->firma=FrameWork::firmarDatos($this->rawData);
        if(!empty($this->firma))
            $this->firma=base64_encode($this->firma);

        return $this->firma;
    }

    public function verificarFirma(){
        return FrameWork::verificarFirma($this->rawData, empty($this->firma)?'':base64_decode($this->firma));
    }

    protected function calcularRawData(){
        if(empty($this->rawData)){
            $this->rawData='';

            $sql = 'SELECT lower(COLUMN_NAME) as columna
                FROM information_schema.COLUMNS
                WHERE
                TABLE_SCHEMA  like \'lavado\'
                AND
                TABLE_NAME = lower(\''.$this->nombreDeClase.'\')
                AND
                COLUMN_NAME<>\'hash\'
                AND
                COLUMN_NAME<>\'firma\'
                ORDER BY
                COLUMN_NAME';

            $resultados=$this->conexion->consultar($sql);
            $numeroCampos=$resultados->getCantidad();
            if($numeroCampos==0)
                throw new Exception('Imposible obtener RawData de '.$this->nombreDeClase.', cero columnas devueltas');

            while($resultados->irASiguiente()){
                foreach($this as $columna => $valor) {
                    if(strtolower($columna)===$resultados->get()->columna){
                        $numeroCampos--;
                        $this->rawData.=(string)$valor."-";
                        break;
                    }
                }
            }

            if($numeroCampos>0)
                throw new Exception('Imposible obtener RawData de '.$this->nombreDeClase.', no todos los campos fueron incluidos');
        }
    }

    public function getRawData(){
        $this->calcularRawData();

        return $this->rawData;
    }

    abstract protected function cargarObjeto($string);

    abstract public function guardarObjeto(Auditoria $auditoria=null);

    abstract public function haSidoUtilizado();











}
?>