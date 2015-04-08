<?php
    require_once '../../interfaces/InterfazBase.php';
    class iconcss
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $objetoJson=new stdClass();
            $objetoJson->success=true;
            $objetoJson->msg="todo bien";
            $objetoJson->data=array();

            $directorio=FrameWork::getRootPath().'/iconos/';

            if(!is_dir($directorio))
                throw new Exception('El directorio no existe o no esvalido');

            @$recurso = opendir($directorio);
            if($recurso==FALSE)
                throw new Exception('No fue posible abrir el directorio');

            $archivo=new stdClass();
            $archivo->iconcss='';
            $archivo->nombre='-Sin icono-';
            $objetoJson->data[]=$archivo;

            do{
                @$entrada=readdir($recurso);
                if(@$entrada!=FALSE && @$entrada!="." && @$entrada!=".." && is_file($directorio.'/'.@$entrada)){
                    $informacionArchivo=pathinfo(@$entrada);
                    $archivo=new stdClass();
                    $archivo->iconcss='icon-'.$informacionArchivo['filename'];
                    $archivo->nombre=$informacionArchivo['filename'];
                    $objetoJson->data[]=$archivo;
                }
            }while($entrada!=FALSE);

            @closedir($recurso);
            $objetoJson->total=count($objetoJson->data);
            echo json_encode($objetoJson);
        }
    }
    new iconcss(new ArrayObject(array_merge($_POST, $_GET)));
?>