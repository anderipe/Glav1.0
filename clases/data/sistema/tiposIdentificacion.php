<?php
    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'TipoIdentificacion.php';

    class tiposIdentificacion
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                default:{
                    $esPersonaNatural=isset($this->args['esPersonaNatural'])?(boolean)strtoupper($this->args['esPersonaNatural']):null;
                    $objetoJson=new stdClass();
                    $objetoJson->success=true;
                    $objetoJson->msg="";
                    $objetoJson->data=TipoIdentificacion::getTiposIdentificacion(RecordSet::FORMATO_OBJETO, $esPersonaNatural);
                    $objeto=new stdClass();
                    $objeto->idtipoidentificacion=0;
                    $objeto->abreviatura='-N.D-';
                    $objetoJson->data[]=$objeto;
                    $objetoJson->total=count($objetoJson->data);
                    echo json_encode($objetoJson);
                }
            }
        }
    }
    new tiposIdentificacion(new ArrayObject(array_merge($_POST, $_GET)));
?>