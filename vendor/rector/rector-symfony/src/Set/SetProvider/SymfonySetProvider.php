<?php

declare (strict_types=1);
namespace Rector\Symfony\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\ComposerTriggeredSet;
use Rector\Set\ValueObject\Set;
final class SymfonySetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide() : array
    {
        return [
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '2.5', __DIR__ . '/../../../config/sets/symfony/symfony25.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '2.6', __DIR__ . '/../../../config/sets/symfony/symfony26.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '2.7', __DIR__ . '/../../../config/sets/symfony/symfony27.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '3.0', __DIR__ . '/../../../config/sets/symfony/symfony30.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '3.1', __DIR__ . '/../../../config/sets/symfony/symfony31.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '3.2', __DIR__ . '/../../../config/sets/symfony/symfony32.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '3.3', __DIR__ . '/../../../config/sets/symfony/symfony33.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '3.4', __DIR__ . '/../../../config/sets/symfony/symfony34.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '4.0', __DIR__ . '/../../../config/sets/symfony/symfony40.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '4.1', __DIR__ . '/../../../config/sets/symfony/symfony41.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '4.2', __DIR__ . '/../../../config/sets/symfony/symfony42.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '4.3', __DIR__ . '/../../../config/sets/symfony/symfony43.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '4.4', __DIR__ . '/../../../config/sets/symfony/symfony44.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '5.0', __DIR__ . '/../../../config/sets/symfony/symfony50.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony51.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '5.2', __DIR__ . '/../../../config/sets/symfony/symfony52.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '5.3', __DIR__ . '/../../../config/sets/symfony/symfony53.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '5.4', __DIR__ . '/../../../config/sets/symfony/symfony54.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '6.0', __DIR__ . '/../../../config/sets/symfony/symfony60.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '6.1', __DIR__ . '/../../../config/sets/symfony/symfony61.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '6.2', __DIR__ . '/../../../config/sets/symfony/symfony62.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '6.3', __DIR__ . '/../../../config/sets/symfony/symfony63.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '6.4', __DIR__ . '/../../../config/sets/symfony/symfony64.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '7.0', __DIR__ . '/../../../config/sets/symfony/symfony70.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '7.1', __DIR__ . '/../../../config/sets/symfony/symfony71.php'),
            new Set(SetGroup::SYMFONY, 'Configs', __DIR__ . '/../../../config/sets/symfony/configs.php'),
            new Set(SetGroup::SYMFONY, 'Code Quality', __DIR__ . '/../../../config/sets/symfony/symfony-code-quality.php'),
            new Set(SetGroup::SYMFONY, 'Constructor Injection', __DIR__ . '/../../../config/sets/symfony/symfony-constructor-injection.php'),
            new Set(SetGroup::SYMFONY, 'SwiftMailer to Symfony Mailer', __DIR__ . '/../../../config/sets/swiftmailer/swiftmailer-to-symfony-mailer.php'),
            // attributes
            new Set(SetGroup::ATTRIBUTES, 'FOS Rest', __DIR__ . '/../../../config/sets/fosrest/annotations-to-attributes.php'),
            new Set(SetGroup::ATTRIBUTES, 'JMS', __DIR__ . '/../../../config/sets/jms/annotations-to-attributes.php'),
            new Set(SetGroup::ATTRIBUTES, 'Sensiolabs', __DIR__ . '/../../../config/sets/sensiolabs/annotations-to-attributes.php'),
            new Set(SetGroup::ATTRIBUTES, 'Symfony', __DIR__ . '/../../../config/sets/symfony/annotations-to-attributes.php'),
            new Set(SetGroup::ATTRIBUTES, 'Symfony Validator', __DIR__ . '/../../../config/sets/symfony/symfony52-validator-attributes.php'),
        ];
    }
}
