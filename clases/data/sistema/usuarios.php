<?php
    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Usuario.php';
    class usuarios
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);

            $nombres=isset($this->args['query'])?$this->args['query']:'';
            $this->retorno->data=array();
            $this->retorno->success=true;
            $this->retorno->msg="";
            $this->retorno->data=Usuario::getUsuarios($nombres, RecordSet::FORMATO_OBJETO);
            $objeto=new stdClass();
            $objeto->idusuario=0;
            $objeto->nombres='--Todos--';
            $this->retorno->data[]=$objeto;
            echo json_encode($this->retorno);
        }
    }
    new usuarios(new ArrayObject(array_merge($_POST, $_GET)));
?>