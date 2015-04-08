<?php
    require_once '/media/www/lavado/clases/interfaces/InterfazBase.php';
    require_once 'ModuloPerfil.php';
    class menuUsuario
        extends InterfazBase{

        public function __construct(ArrayObject $args = NULL) {
            parent::__construct($args);
            $accion=isset($this->args['accion'])?(int)$this->args['accion']:0;
            switch($accion){

                default:{
                    $idModulo=isset($this->args['idmodulo'])?(int)$this->args['idmodulo']:0;
                    $idUsuario=isset($this->args['idusuario'])?(int)$this->args['idusuario']:0;

                    $tableName=  FrameWork::getTmpName().'modulousuario';
                    $sql='drop table if EXISTS '.$tableName;
                    $resultados=$this->conexion->ejecutar($sql);

                    $sql='CREATE TEMPORARY TABLE IF NOT EXISTS '.$tableName.'
                        (select distinct modulo.* from
                        modulo
                        join moduloperfil using (idmodulo)
                        join perfilusuario using (idperfil)
                        where idusuario='.$idUsuario.')';
                    $resultados=$this->conexion->ejecutar($sql);

                    function agregarPadre($conexion, $tableName, $idModulo){
                        $idModulo=(int)$idModulo;
                        $sql='select idmodulopadre from modulo where idmodulo='.$idModulo;
                        $resultados=$conexion->consultar($sql);
                        if($resultados->getCantidad()==0)
                            return;

                        $idModuloPadre=(int)$resultados->get(0)->idmodulopadre;
                        if($idModuloPadre==$idModulo){
                            return;
                        }

                        $sql='select idmodulo from '.$tableName.' where idmodulo='.$idModuloPadre;
                        $resultados=$conexion->consultar($sql);
                        if($resultados->getCantidad()==0){
                            $sql='insert into '.$tableName.' (select * from modulo where idmodulo='.$idModuloPadre.')';
                            $resultados=$conexion->ejecutar($sql);
                            agregarPadre($conexion, $tableName, $idModuloPadre);
                        }
                    }

                    $sql='select idmodulo from '.$tableName.' order by orden';
                    $resultados=$this->conexion->consultar($sql);
                    while($resultados->irASiguiente()){
                        agregarPadre($this->conexion, $tableName, $resultados->get()->idmodulo);
                    }

                    $objetoJson=new stdClass();
                    $objetoJson->success=true;
                    $objetoJson->msg="todo bien";
                    $objetoJson->data=array();
                    if($idModulo==-1){
                        $moduloHijo=new stdClass();
                        $moduloHijo->idmodulo=1;
                        $moduloHijo->nombre='Menu: ';
                        $moduloHijo->clase='';
                        $moduloHijo->iconcss='';
                        $moduloHijo->orden=1;
                        $objetoJson->data[]=$moduloHijo;
                    }else{
                        $moduloPadre=new Modulo($idModulo);
                        $modulosHijos=$moduloPadre->getModulosHijo($tableName);
                        foreach ($modulosHijos as $modulo){
                            $moduloHijo=$modulo->getJson(true);
                            $clase=$modulo->getClase();
                            if(!empty($clase))
                                $moduloHijo->leaf=true;
                            $objetoJson->data[]=$moduloHijo;
                        }
                    }
                    $objetoJson->total=count($objetoJson->data);
                    echo json_encode($objetoJson);
                }
            }
        }
    }
    new menuUsuario(new ArrayObject(array_merge($_POST, $_GET)));
?>