<?php
    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'Modulo.php';
    class modulos
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);

            $clase=isset($this->args['query'])?$this->args['query']:'';
            $this->retorno->data=array();
            $this->retorno->success=true;
            $this->retorno->msg="";
            $this->retorno->data=Modulo::getModulos($clase, RecordSet::FORMATO_OBJETO);
            $numeroClases=count($this->retorno->data);
            for($i=0; $i<$numeroClases; $i++){
                $ind=mb_stripos($this->retorno->data[$i]->clase, 'siadno.view');
                if($ind!==false){
                    $this->retorno->data[$i]->abreviatura=mb_strcut($this->retorno->data[$i]->clase, 12);
                }else{
                    $this->retorno->data[$i]->abreviatura=$this->retorno->data[$i]->clase;
                }
            }
            $objeto=new stdClass();
            $objeto->idmodulo=0;
            $objeto->abreviatura='--Todos--';
            $this->retorno->data[]=$objeto;
            echo json_encode($this->retorno);
        }
    }
    new modulos(new ArrayObject(array_merge($_POST, $_GET)));
?>