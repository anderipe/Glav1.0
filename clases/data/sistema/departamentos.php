<?php
    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Pais.php';
    class departamentos
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            $idPais=isset($this->args['idpais'])?(int)$this->args['idpais']:0;
            switch($accion){
                default:{
                    $pais=new Pais($idPais);
                    $this->retorno->success=true;
                    $this->retorno->msg="";
                    $this->retorno->data=$pais->getDepartamentos(RecordSet::FORMATO_OBJETO);
                    $objeto=new stdClass();
                    $objeto->iddepartamento=0;
                    $objeto->nombre='--Seleccione--';
                    $this->retorno->data[]=$objeto;
                    $this->retorno->total=count($this->retorno->data);
                    echo json_encode($this->retorno);
                }
            }
        }
    }
    new departamentos(new ArrayObject(array_merge($_POST, $_GET)));
?>