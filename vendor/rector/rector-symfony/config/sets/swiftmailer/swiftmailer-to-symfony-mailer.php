<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\SwiftMailer\Rector\MethodCall\SwiftCreateMessageToNewEmailRector;
use Rector\Symfony\SwiftMailer\Rector\MethodCall\SwiftSetBodyToHtmlPlainMethodCallRector;
return static function (RectorConfig $rectorConfig) : void {
    // @see https://symfony.com/blog/the-end-of-swiftmailer
    $rectorConfig->rules([SwiftCreateMessageToNewEmailRector::class, SwiftSetBodyToHtmlPlainMethodCallRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'Swift_Mailer' => 'Symfony\\Component\\Mailer\\MailerInterface',
        'Swift_Message' => 'Symfony\\Component\\Mime\\Email',
        // message
        'Swift_Mime_SimpleMessage' => 'Symfony\\Component\\Mime\\RawMessage',
        // transport
        'Swift_SmtpTransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
        'Swift_FailoverTransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
        'Swift_SendmailTransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
    ]);
};
