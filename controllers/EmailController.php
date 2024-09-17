<?php

namespace Controllers;

use Exception;
use Model\ActiveRecord;
use MVC\Router;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;

class EmailController
{
    public static function email(Router $router)
    {
        $email = new PHPMailer(true);
        $email->SMTPOptions = [
            "ssl" => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ];

        try {
            // Configuración del correo
            $email->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $email->isSMTP();                                            //Send using SMTP
            $email->Host = $_ENV['MAIL_HOST'];                           //Set the SMTP server to send through
            $email->SMTPAuth = true;                                     //Enable SMTP authentication
            $email->Username = $_ENV['MAIL_USERNAME'];                   //SMTP username
            $email->Password = $_ENV['MAIL_PASSWORD'];                   //SMTP password
            $email->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $email->Port = $_ENV['MAIL_PORT'];
            $email->CharSet = "UTF-8";
            $email->AddReplyTo($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $email->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $email->isHTML();
            
            // Adjuntar imagen
            $imagePath = __DIR__ . '/../public/images/PDF.png';
            $email->AddEmbeddedImage($imagePath, 'hola', 'PDF.png');
            
            // Generar HTML del cuerpo del correo
            $html = $router->load('email/saludo');
            $email->Body = $html;
            $email->Subject = "Prueba de correo";

            // Generar PDF
            $mpdf = new Mpdf([
                "default_font_size" => "12",
                "default_font" => "arial",
                "orientation" => "P",
                "margin_top" => "30",
                "format" => "Letter"
            ]);
            
            $productos = ActiveRecord::fetchArray("SELECT * FROM productos");
            $pdfHtml = $router->load('pdf/reporte', [
                'productos' => $productos
            ]);
            
            // Cargar el CSS y agregar al PDF
            $css = file_get_contents(__DIR__ . '/../views/pdf/styles.css');
            $mpdf->WriteHTML($css, HTMLParserMode::HEADER_CSS);
            $mpdf->WriteHTML($pdfHtml, HTMLParserMode::HTML_BODY);
            $pdfOutput = $mpdf->Output("reporte.pdf", "S");
            $email->addStringAttachment($pdfOutput, 'reporte.pdf');

            // Dirección de destino
            $email->addAddress('capurivas@gmail.com', 'DANIEL RIVAS');
            $email->send();

            echo "Correo enviado con el PDF adjunto";
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$e->getMessage()}";
        }
    }
}
