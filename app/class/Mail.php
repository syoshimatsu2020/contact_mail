<?php
    class Mail{

        // メール送信
        private static function send(array $headers, string $to, string $subject, string $mail_body){
            mb_language( 'Japanese' );
            mb_internal_encoding( 'UTF-8' );

            if(mb_send_mail($to, $subject, $mail_body, $headers)){
                return true;
            }

            return false;
        }
    }
?>