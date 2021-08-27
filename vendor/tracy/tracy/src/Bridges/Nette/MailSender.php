<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210827\Tracy\Bridges\Nette;

use RectorPrefix20210827\Nette;
use RectorPrefix20210827\Tracy;
/**
 * Tracy logger bridge for Nette Mail.
 */
class MailSender
{
    use Nette\SmartObject;
    /** @var Nette\Mail\IMailer */
    private $mailer;
    /** @var string|null sender of email notifications */
    private $fromEmail;
    public function __construct(\RectorPrefix20210827\Nette\Mail\IMailer $mailer, string $fromEmail = null)
    {
        $this->mailer = $mailer;
        $this->fromEmail = $fromEmail;
    }
    /**
     * @param  mixed  $message
     * @param string $email
     */
    public function send($message, $email) : void
    {
        $host = \preg_replace('#[^\\w.-]+#', '', $_SERVER['SERVER_NAME'] ?? \php_uname('n'));
        $mail = new \RectorPrefix20210827\Nette\Mail\Message();
        $mail->setHeader('X-Mailer', 'Tracy');
        if ($this->fromEmail || \RectorPrefix20210827\Nette\Utils\Validators::isEmail("noreply@{$host}")) {
            $mail->setFrom($this->fromEmail ?: "noreply@{$host}");
        }
        foreach (\explode(',', $email) as $item) {
            $mail->addTo(\trim($item));
        }
        $mail->setSubject('PHP: An error occurred on the server ' . $host);
        $mail->setBody(\RectorPrefix20210827\Tracy\Logger::formatMessage($message) . "\n\nsource: " . \RectorPrefix20210827\Tracy\Helpers::getSource());
        $this->mailer->send($mail);
    }
}
