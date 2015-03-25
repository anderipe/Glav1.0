<?php
    $certs=array();
    for($i=0; $i<1000; $i++){
        if(openssl_pkcs12_read (file_get_contents('edgar.cortes.pfx'), $certs, 'y2k13721551')!=1)
            die('Falla al leer el p12');

        $Cert = $certs["cert"];
        $Clave = $certs["pkey"];

        $pkeyid = openssl_get_privatekey($Clave);
        if($pkeyid==false)
            die('Falla al preparar la llave');

        $signature=null;
        if(!openssl_sign("mis berracos datos a firmarsdfdsfdf", $signature, $pkeyid, OPENSSL_ALGO_SHA1))
            die('Falla al encriptar');

    // liberar la clave de la memoria
        openssl_free_key($pkeyid);

        $pubkeyid = openssl_get_publickey($Cert);

    // establecer si la firma es correcta o no
        $ok = openssl_verify("mis berracos datos a firmarsdfdsfdf", $signature, $pubkeyid);
//        if ($ok == 1) {
//            echo "buena";
//        } elseif ($ok == 0) {
//            echo "mala";
//        } else {
//            echo "alarmante, error verificando la firma";
//        }
        openssl_free_key($pubkeyid);
    }


    echo "<hr>";
    echo 'caracteres: '.mb_strlen($signature);
    echo "<hr>";
    var_dump($signature);
    echo "<hr>";
    var_dump($pkeyid);
    echo "<hr>";
    var_dump($Cert);
    echo "<hr>";
    var_dump($Clave);
?>
