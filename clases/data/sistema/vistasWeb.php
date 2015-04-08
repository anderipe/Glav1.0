<?php
    require_once '../../interfaces/InterfazBase.php';
    class vistasWeb
        extends InterfazBase{

        private function obtenerModulos($directorio, $base){
            $modulos=array();
            if(!is_dir($directorio))
                throw new Exception('El directorio no existe o no esvalido');

            @$recurso = opendir($directorio);
            if($recurso==FALSE)
                throw new Exception('No fue posible abrir el directorio');

            do{
                @$entrada=readdir($recurso);
                if($entrada!=FALSE && $entrada!="." && $entrada!=".."){
                    $file=$directorio.'/'.$entrada;

                    if(is_file($file)){
                        $informacionArchivo=pathinfo($entrada);
                        $objeto=new stdClass();
                        $objeto->clase=$base.'.'.$informacionArchivo['filename'];
                        $objeto->nombre=$base.'.'.$informacionArchivo['filename'];
                        $modulos[]=$objeto;
                    }

                    if(is_dir($file)){
                        $modulos=array_merge($modulos, $this->obtenerModulos($file, $base.'.'.$entrada));
                    }
                }
            }while($entrada!=FALSE);

            @closedir($recurso);
            return $modulos;
        }

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $objetoJson=new stdClass();
            $objetoJson->success=true;
            $objetoJson->msg="todo bien";

            $directorio=FrameWork::getRootPath().'/app/view/';
            $objetoJson->data=array();
            $objeto=new stdClass();
            $objeto->clase='';
            $objeto->nombre='--Sin ningun modulo a ejecutar--';
            $objetoJson->data[]=$objeto;
            $objetoJson->data=array_merge($objetoJson->data, $this->obtenerModulos($directorio, 'siadno.view'));
            $objetoJson->total=count($objetoJson->data);
            echo json_encode($objetoJson);
        }
    }
    new vistasWeb(new ArrayObject(array_merge($_POST, $_GET)));
?>