<?php
    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Persona.php';
    class personas
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){
                default:{
                    $idTipoIdentificacion=isset($this->args['idtipoidentificacion'])?$this->args['idtipoidentificacion']:0;
                    $identificacion=isset($this->args['identificacion'])?$this->args['identificacion']:'';
                    $offset=isset($this->args['offset'])?$this->args['offset']:0;
                    $limit=isset($this->args['limit'])?$this->args['limit']:0;
                    $esPersonaNatural=isset($this->args['esPersonaNatural'])?(boolean)strtoupper($this->args['esPersonaNatural']):null;

                    $data=array();
                    $data=Persona::buscarPorIdentificacion(new TipoIdentificacion($idTipoIdentificacion), $identificacion, RecordSet::FORMATO_OBJETO, $esPersonaNatural, $offset, $limit);

                    $this->retorno->success=true;
                    $this->retorno->msg="";
                    $this->retorno->data=$data;
                    $this->retorno->total=count($this->retorno->data);
                    echo json_encode($this->retorno);
                }
            }
        }
    }
    new personas(new ArrayObject(array_merge($_POST, $_GET)));
?>