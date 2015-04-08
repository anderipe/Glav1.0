<?php
    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Cargo.php';
    class cargos
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                default:{
                    $this->retorno->success=true;
                    $this->retorno->msg="";
                    $this->retorno->data=Cargo::getCargos(RecordSet::FORMATO_OBJETO);
                    $objeto=new stdClass();
                    $objeto->idcargo=0;
                    $objeto->nombre='--Seleccione--';
                    $this->retorno->data[]=$objeto;
                    $this->retorno->total=count($this->retorno->data);
                    echo json_encode($this->retorno);
                }
            }
        }
    }
    new cargos(new ArrayObject(array_merge($_POST, $_GET)));
?>