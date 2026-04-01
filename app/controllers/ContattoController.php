<?php

namespace App\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ContattoController
{

    public function send()
    {

        // Max 3 richieste per IP ogni 10 minuti
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = 'rate_limit_' . md5($ip);
        $cacheFile = sys_get_temp_dir() . '/' . $key;

        $attempts = file_exists($cacheFile) ? (int)file_get_contents($cacheFile) : 0;

        if ($attempts >= 3) {
            jsonResponse(['error' => 'Troppi tentativi. Riprova tra qualche minuto.'], 429);
            return;
        }

        file_put_contents($cacheFile, $attempts + 1);
        // Resetta dopo 10 minuti
        if ($attempts === 0) {
            register_shutdown_function(function () use ($cacheFile) {
                sleep(600);
                @unlink($cacheFile);
            });
        }

        // Leggi il JSON inviato da React
        $data = json_decode(file_get_contents('php://input'), true);

        // Sanitizzazione input
        // Funzione di pulizia — blocca caratteri newline negli header
        function sanitizeHeaderField(string $value): string
        {
            // Rimuove \r \n e altri caratteri di controllo
            return preg_replace('/[\r\n\x00]/', '', trim($value));
        }

        // Applica SOLO ai campi che finiscono negli header email
        $nome    = sanitizeHeaderField(strip_tags($data['nome'] ?? ''));
        $email       = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $oggetto = sanitizeHeaderField(strip_tags($data['oggetto'] ?? ''));
        $cellulare   = htmlspecialchars(trim($data['cellulare']   ?? ''));
        $descrizione = htmlspecialchars(trim($data['descrizione'] ?? ''));

        // Validazione campi obbligatori
        if (empty($nome) || !$email || empty($oggetto) || empty($descrizione)) {
            jsonResponse(['error' => 'Compila tutti i campi obbligatori correttamente.'], 400);
            return;
        }

        if (strlen($nome) > 100 || strlen($oggetto) > 150 || strlen($descrizione) > 2000) {
            jsonResponse(['error' => 'Input troppo lungo.'], 400);
            return;
        }

        // Backend — se il campo honeypot è compilato, è un bot
        $honeypot = trim($data['website'] ?? '');
        if (!empty($honeypot)) {
            // Rispondi 200 per non dare info al bot
            jsonResponse(['success' => 'Messaggio ricevuto'], 200);
            return;
        }

        require_once __DIR__ . '/../../vendor/autoload.php';

        $mail = new PHPMailer(true);

        try {
            // Configurazione SMTP
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION']; // 'tls'
            $mail->Port       = (int) $_ENV['MAIL_PORT'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($_ENV['MAIL_TO_ADDRESS'], $_ENV['MAIL_TO_NAME']);
            $mail->addReplyTo($email, $nome);

            // Corpo email HTML
            $mail->isHTML(true);
            $mail->Subject = "Nuovo contatto: {$oggetto}";
            $mail->Body = "
                <h2 style='color:#333;'>Nuovo messaggio dal form di contatto</h2>
                <table style='border-collapse:collapse;width:100%;'>
                    <tr><td style='padding:8px;font-weight:bold;'>Nome:</td><td style='padding:8px;'>{$nome}</td></tr>
                    <tr style='background:#f9f9f9;'><td style='padding:8px;font-weight:bold;'>Email:</td><td style='padding:8px;'>{$email}</td></tr>
                    <tr><td style='padding:8px;font-weight:bold;'>Cellulare:</td><td style='padding:8px;'>{$cellulare}</td></tr>
                    <tr style='background:#f9f9f9;'><td style='padding:8px;font-weight:bold;'>Oggetto:</td><td style='padding:8px;'>{$oggetto}</td></tr>
                    <tr><td style='padding:8px;font-weight:bold;'>Messaggio:</td><td style='padding:8px;'>{$descrizione}</td></tr>
                </table>
            ";

            $mail->AltBody = "Nome: {$nome}\nEmail: {$email}\nCellulare: {$cellulare}\nOggetto: {$oggetto}\nMessaggio: {$descrizione}";

            $mail->send();
            jsonResponse(['success' => 'Email inviata con successo!'], 200);
        } catch (Exception $e) {
            error_log('[PHPMailer] ' . $mail->ErrorInfo);
            jsonResponse(['error' => 'Errore durante l\'invio. Riprova più tardi.'], 500);
        }
    }
}
