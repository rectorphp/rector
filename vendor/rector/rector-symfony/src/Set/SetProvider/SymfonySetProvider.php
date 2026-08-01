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
    private const COMPOSER_BASED_TRIGGER_PACKAGES = ['symfony/bridge-swift-mailer' => '>=3.0', 'symfony/class-loader' => '>=3.0', 'symfony/console' => '>=3.0', 'symfony/form' => '>=3.0', 'symfony/http-kernel' => '>=3.0', 'symfony/process' => '>=3.0', 'symfony/property-access' => '>=3.0', 'symfony/security' => '>=3.0', 'symfony/translation' => '>=3.0', 'symfony/twig-bundle' => '>=3.0', 'symfony/validator' => '>=3.0', 'symfony/yaml' => '>=3.1', 'symfony/dependency-injection' => '>=3.2', 'symfony/http-foundation' => '>=3.2', 'symfony/debug' => '>=3.3', 'symfony/framework-bundle' => '>=3.3', 'symfony/workflow' => '>=4.1', 'symfony/cache' => '>=4.2', 'symfony/config' => '>=4.2', 'symfony/dom-crawler' => '>=4.2', 'symfony/finder' => '>=4.2', 'symfony/monolog-bridge' => '>=4.2', 'symfony/serializer' => '>=4.2', 'symfony/browser-kit' => '>=4.3', 'symfony/event-dispatcher' => '>=4.3', 'symfony/security-core' => '>=4.3', 'symfony/security-http' => '>=4.3', 'symfony/templating' => '>=4.4', 'symfony/web-link' => '>=4.4', 'symfony/inflector' => '>=5.1', 'symfony/notifier' => '>=5.1', 'symfony/mime' => '>=5.2', 'symfony/property-info' => '>=5.2', 'symfony/security-mailer' => '>=5.3', 'symfony/security-bundle' => '>=5.4', 'symfony/contracts' => '>=6.0', 'symfony/doctrine-bridge' => '>=6.0', 'symfony/expression-language' => '>=6.0', 'symfony/options-resolver' => '>=6.0', 'symfony/routing' => '>=6.0', 'symfony/mail-pace-mailer' => '>=6.2', 'symfony/symfony' => '>=6.2', 'symfony/twig-bridge' => '>=6.2', 'symfony/http-client' => '>=6.3', 'symfony/messenger' => '>=6.3', 'symfony/error-handler' => '>=6.4', 'symfony/mailer' => '>=7.2', 'symfony/json-streamer' => '>=7.4'];
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
