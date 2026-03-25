<?php

namespace App\Services;

// Incluir autoloader de PHPMailer v5.2 (la versión instalada)
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

/**
 * EmailService - Servicio centralizado para envío de correos
 * 
 * Uso básico:
 * ```php
 * use App\Services\EmailService;
 * 
 * $emailService = new EmailService();
 * $result = $emailService->send([
 *     'account' => 'seleccion', // sistemas, seleccion, costos, logistica, gestionhumana, controlinterno
 *     'to' => 'destinatario@example.com', // string o array de correos
 *     'subject' => 'Asunto del correo',
 *     'body' => '<h1>Contenido HTML</h1>',
 *     'attachments' => [] // opcional: array de rutas de archivos o info de $_FILES
 * ]);
 * ```
 */
class EmailService extends BaseService
{
    private $accounts;
    private $smtpConfig;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Cargar configuración de cuentas
        $configPath = __DIR__ . '/../../Config/email_accounts.php';
        if (!file_exists($configPath)) {
            throw new \Exception("Archivo de configuración de cuentas de email no encontrado: {$configPath}");
        }
        $this->accounts = require $configPath;

        // Configuración SMTP de Gmail (igual para todas las cuentas)
        $this->smtpConfig = [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'auth' => true
        ];
    }

    /**
     * Enviar correo electrónico
     * 
     * @param array $params Parámetros del correo:
     *   - account: string (required) Cuenta a usar: 'sistemas', 'seleccion', 'costos', 'logistica', 'gestionhumana', 'controlinterno'
     *   - to: string|array (required) Destinatario(s). Puede ser un string con emails separados por ; o , o un array
     *   - subject: string (required) Asunto del correo
     *   - body: string (required) Cuerpo del mensaje en HTML
     *   - attachments: array (optional) Archivos adjuntos. Puede ser:
     *       - Array de rutas de archivos: ['/path/to/file1.pdf', '/path/to/file2.xlsx']
     *       - Array de info de $_FILES: [['tmp_name' => '...', 'name' => '...']]
     *   - replyTo: string|array (optional) Email(s) para responder
     *   - cc: string|array (optional) Destinatarios con copia
     *   - bcc: string|array (optional) Destinatarios con copia oculta
     *   - fromName: string (optional) Sobrescribir nombre del remitente
     * 
     * @return array ['success' => bool, 'message' => string, 'error' => string|null]
     */
    public function send(array $params)
    {
        try {
            // Validar parámetros requeridos
            $this->validateParams($params, ['account', 'to', 'subject', 'body']);

            // Validar que la cuenta exista
            if (!isset($this->accounts[$params['account']])) {
                throw new \Exception("Cuenta de correo '{$params['account']}' no configurada. Cuentas disponibles: " . implode(', ', array_keys($this->accounts)));
            }

            $accountConfig = $this->accounts[$params['account']];

            // Crear instancia de PHPMailer (v5.2)
            $mail = new \PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            // Registra salida de depuración en el log para diagnosticar problemas SMTP
            $mail->SMTPDebug = 0; // 0 = off, 2 = cliente+server (solo activar para debugging)
            $mail->Debugoutput = function ($str, $level) {
                error_log(sprintf('[PHPMailer][debug %d] %s', $level, $str));
            };

            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host = $this->smtpConfig['host'];
            $mail->SMTPAuth = $this->smtpConfig['auth'];
            $mail->Username = $accountConfig['username'];
            $mail->Password = $accountConfig['password'];
            $mail->SMTPSecure = $this->smtpConfig['encryption'];
            $mail->Port = $this->smtpConfig['port'];

            // Remitente
            $fromName = $params['fromName'] ?? $accountConfig['fromName'];
            $mail->setFrom($accountConfig['username'], $fromName);

            // Destinatarios
            $recipients = $this->normalizeEmails($params['to']);
            if (empty($recipients)) {
                throw new \Exception('No se proporcionaron destinatarios válidos');
            }
            foreach ($recipients as $email) {
                $mail->addAddress($email);
            }

            // Reply-To (opcional)
            if (!empty($params['replyTo'])) {
                $replyToEmails = $this->normalizeEmails($params['replyTo']);
                foreach ($replyToEmails as $email) {
                    $mail->addReplyTo($email);
                }
            }

            // CC (opcional)
            if (!empty($params['cc'])) {
                $ccEmails = $this->normalizeEmails($params['cc']);
                foreach ($ccEmails as $email) {
                    $mail->addCC($email);
                }
            }

            // BCC (opcional)
            if (!empty($params['bcc'])) {
                $bccEmails = $this->normalizeEmails($params['bcc']);
                foreach ($bccEmails as $email) {
                    $mail->addBCC($email);
                }
            }

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $params['subject'];
            $mail->Body = $params['body'];
            $mail->AltBody = strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $params['body']));

            // Adjuntos (opcional)
            if (!empty($params['attachments'])) {
                $this->addAttachments($mail, $params['attachments']);
            }

            // Enviar
            $mail->send();

            return [
                'success' => true,
                'message' => 'Correo enviado exitosamente',
                'error' => null
            ];
        } catch (\Exception $e) {
            error_log('[EmailService] PHPMailer Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al enviar el correo (PHPMailer)',
                'error' => $e->getMessage()
            ];
        } catch (\Throwable $e) {
            error_log('[EmailService] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error inesperado al enviar el correo',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Normalizar emails: convierte string separado por ; o , en array de emails válidos
     * 
     * @param string|array $emails
     * @return array Array de emails válidos
     */
    private function normalizeEmails($emails)
    {
        if (!is_array($emails)) {
            // Separar por ; o ,
            $emails = preg_split('/[;,]+/', (string)$emails, -1, PREG_SPLIT_NO_EMPTY);
        }

        // Limpiar espacios y duplicados
        $emails = array_values(array_unique(array_map('trim', $emails)));

        // Filtrar solo emails válidos
        return array_values(array_filter($emails, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }));
    }

    /**
     * Agregar adjuntos al correo
     * 
     * @param PHPMailer $mail
     * @param array $attachments Puede ser:
     *   - String: ruta del archivo
     *   - Array con 'path' y 'name': ['path' => '/ruta/archivo.pdf', 'name' => 'MiArchivo.pdf']
     *   - Array $_FILES: ['tmp_name' => '...', 'name' => '...']
     */
    private function addAttachments( $mail, array $attachments)
    {
        foreach ($attachments as $attachment) {
            if (is_string($attachment)) {
                // Es una ruta de archivo simple
                if (file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            } elseif (is_array($attachment)) {
                // Formato: ['path' => '...', 'name' => '...']
                if (isset($attachment['path']) && isset($attachment['name'])) {
                    if (file_exists($attachment['path'])) {
                        $mail->addAttachment($attachment['path'], $attachment['name']);
                    }
                }
                // Formato $_FILES: ['tmp_name' => '...', 'name' => '...']
                elseif (isset($attachment['tmp_name']) && is_uploaded_file($attachment['tmp_name'])) {
                    $fileName = $attachment['name'] ?? basename($attachment['tmp_name']);
                    $mail->addAttachment($attachment['tmp_name'], $fileName);
                }
            }
        }
    }

    /**
     * Validar parámetros requeridos
     * 
     * @param array $params
     * @param array $required
     * @throws \Exception
     */
    private function validateParams(array $params, array $required)
    {
        $missing = [];
        foreach ($required as $param) {
            if (!isset($params[$param]) || (is_string($params[$param]) && trim($params[$param]) === '')) {
                $missing[] = $param;
            }
        }

        if (!empty($missing)) {
            throw new \Exception('Parámetros requeridos faltantes: ' . implode(', ', $missing));
        }
    }

    /**
     * Obtener lista de cuentas disponibles
     * 
     * @return array
     */
    public function getAvailableAccounts()
    {
        return array_keys($this->accounts);
    }

    /**
     * Verificar si una cuenta existe
     * 
     * @param string $account
     * @return bool
     */
    public function accountExists($account)
    {
        return isset($this->accounts[$account]);
    }
}
