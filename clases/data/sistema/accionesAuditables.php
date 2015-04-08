<?php
    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'AccionAuditable.php';
    class accionesAuditables
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);

            $this->retorno->data=array();
            $this->retorno->success=true;
            $this->retorno->msg="";
            $this->retorno->data= AccionAuditable::getAccionesAuditables(RecordSet::FORMATO_OBJETO);
            $objeto=new stdClass();
            $objeto->idusuario=0;
            $objeto->nombres='--Todas--';
            $this->retorno->data[]=$objeto;
            echo json_encode($this->retorno);
        }
    }
    new accionesAuditables(new ArrayObject(array_merge($_POST, $_GET)));
?>