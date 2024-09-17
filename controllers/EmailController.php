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
            $email->SMTPDebug = SMTP::DEBUG_SERVER;                      
            $email->isSMTP();                                            
            $email->Host = $_ENV['MAIL_HOST'];                          
            $email->SMTPAuth = true;                                    
            $email->Username = $_ENV['MAIL_USERNAME'];                   
            $email->Password = $_ENV['MAIL_PASSWORD'];                   
            $email->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
            $email->Port = $_ENV['MAIL_PORT'];
            $email->CharSet = "UTF-8";
            $email->AddReplyTo($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $email->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $email->isHTML();
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
            $email->addAddress('abnerfuentes05@gmail.com', 'ABNER FUENTES');
            $email->send();

            echo "Correo enviado con el PDF adjunto";
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$e->getMessage()}";
        }
    }
}
