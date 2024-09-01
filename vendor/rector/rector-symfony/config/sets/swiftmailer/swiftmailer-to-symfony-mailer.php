<?php

declare (strict_types=1);
namespace RectorPrefix202409;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Symfony\SwiftMailer\Rector\ClassMethod\SwiftMessageToEmailRector;
use Rector\Symfony\SwiftMailer\Rector\MethodCall\SwiftCreateMessageToNewEmailRector;
use Rector\Symfony\SwiftMailer\Rector\MethodCall\SwiftSetBodyToHtmlPlainMethodCallRector;
return static function (RectorConfig $rectorConfig) : void {
    // @see https://symfony.com/blog/the-end-of-swiftmailer
    $rectorConfig->rules([SwiftCreateMessageToNewEmailRector::class, SwiftSetBodyToHtmlPlainMethodCallRector::class, SwiftMessageToEmailRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'Swift_Mailer' => 'Symfony\\Component\\Mailer\\MailerInterface',
        // message
        'Swift_Mime_SimpleMessage' => 'Symfony\\Component\\Mime\\RawMessage',
        // transport
        'Swift_SmtpTransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
        'Swift_FailoverTransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
        'Swift_SendmailTransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Swift_Mime_SimpleMessage', 'PRIORITY_HIGHEST', 'Symfony\\Component\\Mime\\Email', 'PRIORITY_HIGHEST'), new RenameClassAndConstFetch('Swift_Mime_SimpleMessage', 'PRIORITY_HIGH', 'Symfony\\Component\\Mime\\Email', 'PRIORITY_HIGH'), new RenameClassAndConstFetch('Swift_Mime_SimpleMessage', 'PRIORITY_NORMAL', 'Symfony\\Component\\Mime\\Email', 'PRIORITY_NORMAL'), new RenameClassAndConstFetch('Swift_Mime_SimpleMessage', 'PRIORITY_LOW', 'Symfony\\Component\\Mime\\Email', 'PRIORITY_LOW'), new RenameClassAndConstFetch('Swift_Mime_SimpleMessage', 'PRIORITY_LOWEST', 'Symfony\\Component\\Mime\\Email', 'PRIORITY_LOWEST')]);
};
