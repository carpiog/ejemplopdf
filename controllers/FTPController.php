<?php

namespace Controllers;
use Exception;
use phpseclib3\Net\SFTP;

class FtpController
{
    public static function subir()
    {

        try{
            
           $sftp = new SFTP('ftp', 22);  // Reemplaza 'ftp' por la dirección IP o dominio de tu servidor

           // Intentar autenticarse
           if ($sftp->login('ftpuser', 'ftppassword')) { 
               echo "Conectado";
           } else {
               echo "No conectado";
           }

        } catch (Exception $e){
            echo $e->getMessage();
        }
      
    }
    public static function saludo()
    {
        echo "Hola desde saludo";
    }
}