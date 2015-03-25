<?php
    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'TipoImpresora.php';
    class tiposDeImpresora
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                default:{
                    $objetoJson=new stdClass();
                    $objetoJson->success=true;
                    $objetoJson->msg="";
                    $objetoJson->data=TipoImpresora::getTipoImpresoras(RecordSet::FORMATO_OBJETO);
                    $objeto=new stdClass();
                    $objeto->idtipoimpresora=0;
                    $objeto->nombre='-No Defininido-';
                    $objetoJson->data[]=$objeto;
                    $objetoJson->total=count($objetoJson->data);
                    echo json_encode($objetoJson);
                }
            }
        }
    }
    new tiposDeImpresora(new ArrayObject(array_merge($_POST, $_GET)));
?>