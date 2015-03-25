<?php
    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Clase.php';
    class clases
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);

            $nombre=isset($this->args['query'])?$this->args['query']:'';
            $this->retorno->data=array();
            $this->retorno->success=true;
            $this->retorno->msg="";
            $this->retorno->data=Clase::getClases($nombre, RecordSet::FORMATO_OBJETO);
            $objeto=new stdClass();
            $objeto->idclase=0;
            $objeto->nombre='--Todas--';
            $this->retorno->data[]=$objeto;
            echo json_encode($this->retorno);
        }
    }
    new clases(new ArrayObject(array_merge($_POST, $_GET)));
?>