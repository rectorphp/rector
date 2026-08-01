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
     * The composer-based set holds rules bound to the exact Symfony package version they are available from.
     * Symfony has no single package to trigger on, so every package used inside the set triggers it,
     * from the lowest version its rules require.
     *
     * @var array<string, string>
     */
    private const COMPOSER_BASED_TRIGGER_PACKAGES = ['symfony/config' => '>=4.2', 'symfony/process' => '>=4.2', 'symfony/event-dispatcher' => '>=4.3', 'symfony/console' => '>=4.4', 'symfony/security-http' => '>=5.1', 'symfony/dependency-injection' => '>=5.2', 'symfony/http-foundation' => '>=5.2', 'symfony/property-access' => '>=5.2', 'symfony/property-info' => '>=5.2', 'symfony/validator' => '>=5.2', 'symfony/http-kernel' => '>=6.2'];
    /**
     * @return SetInterface[]
     */
    public function provide(): array
    {
        return array_merge($this->provideComposerBasedSets(), [
            new Set(SetGroup::SYMFONY, 'Configs', __DIR__ . '/../../../config/sets/symfony/configs.php'),
            new Set(SetGroup::SYMFONY, 'Code Quality', __DIR__ . '/../../../config/sets/symfony/symfony-code-quality.php'),
            new Set(SetGroup::SYMFONY, 'Constructor Injection', __DIR__ . '/../../../config/sets/symfony/symfony-constructor-injection.php'),
            new Set(SetGroup::SYMFONY, 'SwiftMailer to Symfony Mailer', __DIR__ . '/../../../config/sets/swiftmailer/swiftmailer-to-symfony-mailer.php'),
            // attributes
            new Set(SetGroup::ATTRIBUTES, 'FOS Rest', __DIR__ . '/../../../config/sets/fosrest/annotations-to-attributes.php'),
            new Set(SetGroup::ATTRIBUTES, 'JMS', __DIR__ . '/../../../config/sets/jms/annotations-to-attributes.php'),
            new Set(SetGroup::ATTRIBUTES, 'Sensiolabs', __DIR__ . '/../../../config/sets/sensiolabs/annotations-to-attributes.php'),
            new Set(SetGroup::ATTRIBUTES, 'Symfony', __DIR__ . '/../../../config/sets/symfony/annotations-to-attributes.php'),
            new Set(SetGroup::ATTRIBUTES, 'Symfony Validator', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony52-validator-attributes.php'),
        ]);
    }
    /**
     * @return ComposerTriggeredSet[]
     */
    private function provideComposerBasedSets(): array
    {
        $composerTriggeredSets = [];
        foreach (self::COMPOSER_BASED_TRIGGER_PACKAGES as $packageName => $version) {
            $composerTriggeredSets[] = new ComposerTriggeredSet(SetGroup::SYMFONY, $packageName, $version, __DIR__ . '/../../../config/sets/symfony/composer-based.php');
        }
        return $composerTriggeredSets;
    }
}
