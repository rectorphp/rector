<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46714
        'Symfony\\Component\\Mailer\\Bridge\\OhMySmtp\\Transport\\OhMySmtpApiTransport' => 'Symfony\\Component\\Mailer\\Bridge\\MailPace\\Transport\\MailPaceApiTransport',
        'Symfony\\Component\\Mailer\\Bridge\\OhMySmtp\\Transport\\OhMySmtpSmtpTransport' => 'Symfony\\Component\\Mailer\\Bridge\\MailPace\\Transport\\MailPaceSmtpTransport',
        'Symfony\\Component\\Mailer\\Bridge\\OhMySmtp\\Transport\\OhMySmtpTransportFactory' => 'Symfony\\Component\\Mailer\\Bridge\\MailPace\\Transport\\MailPaceTransportFactory',
    ]);
};
