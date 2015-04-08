<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppException
 *
 * @author Mabicho
 */
class AppException 
    extends Exception{
    
    /**
     * Objeto de propiedades asociativas con los errores de validacion para
     * campos de formulario     
     * @var stdClass 
     */
    protected $errores;
    
    /**
     *
     * @param type $message
     * @param type $errores 
     */
    public function __construct($message, $errores=NULL) {
        parent::__construct($message);
        
        $this->errores=$errores;
    }
    
    /**
     * Objeto de propiedades asociativas con los errores de validacion para
     * campos de formulario     
     * @return stdClass  
     */
    public function getErrores(){
        return $this->errores;
    }
    
}

?>
