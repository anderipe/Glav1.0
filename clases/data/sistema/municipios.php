<?php
    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Departamento.php';
    class municipios
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            $idDepartamento=isset($this->args['iddepartamento'])?(int)$this->args['iddepartamento']:0;
            switch($accion){
                default:{
                    $departamento=new Departamento($idDepartamento);
                    $this->retorno->success=true;
                    $this->retorno->msg="";
                    $this->retorno->data=$departamento->getMunicipios(RecordSet::FORMATO_OBJETO);
                    $objeto=new stdClass();
                    $objeto->idmunicipio=0;
                    $objeto->nombre='--Seleccione--';
                    $this->retorno->data[]=$objeto;
                    $this->retorno->total=count($this->retorno->data);
                    echo json_encode($this->retorno);
                }
            }
        }
    }
    new municipios(new ArrayObject(array_merge($_POST, $_GET)));
?>