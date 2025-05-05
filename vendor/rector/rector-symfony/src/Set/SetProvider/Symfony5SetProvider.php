<?php

declare (strict_types=1);
namespace Rector\Symfony\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\ComposerTriggeredSet;
final class Symfony5SetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide() : array
    {
        return [
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/symfony', '5.0', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony50.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/console', '5.0', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony50/symfony50-console.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/debug', '5.0', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony50/symfony50-debug.php'),
            // @todo handle types per package?
            // __DIR__ . '/../../../config/sets/symfony/symfony5/symfony50/symfony50-types.php'
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/symfony', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony51.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/config', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony51/symfony51-config.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/console', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony51/symfony51-console.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/dependency-injection', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony51/symfony51-dependency-injection.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/event-dispatcher', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony51/symfony51-event-dispatcher.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/form', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony51/symfony51-form.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/framework-bundle', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony51/symfony51-framework-bundle.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/http-foundation', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony51/symfony51-http-foundation.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/inflector', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony51/symfony51-inflector.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/notifier', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony51/symfony51-notifier.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/security-http', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony51/symfony51-security-http.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/security-core', '5.1', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony51/symfony51-security-core.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/symfony', '5.2', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony52.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/dependency-injection', '5.2', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony52/symfony52-dependency-injection.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/form', '5.2', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony52/symfony52-form.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/http-foundation', '5.2', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony52/symfony52-http-foundation.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/mime', '5.2', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony52/symfony52-mime.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/notifier', '5.2', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony52/symfony52-notifier.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/property-access', '5.2', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony52/symfony52-property-access.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/property-info', '5.2', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony52/symfony52-property-info.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/security-core', '5.2', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony52/symfony52-security-core.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/security-http', '5.2', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony52/symfony52-security-http.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/validator', '5.2', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony52/symfony52-validator.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/symfony', '5.3', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony53.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/http-foundation', '5.3', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony53/symfony53-http-foundation.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/console', '5.3', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony53/symfony53-console.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/http-kernel', '5.3', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony53/symfony53-http-kernel.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/security-core', '5.3', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony53/symfony53-security-core.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/security-mailer', '5.3', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony53/symfony53-mailer.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/form', '5.3', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony53/symfony53-form.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/framework-bundle', '5.3', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony53/symfony53-framework-bundle.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/symfony', '5.4', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony54.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/validator', '5.4', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony54/symfony54-validator.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/security-bundle', '5.4', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony54/symfony54-security-bundle.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/security-core', '5.4', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony54/symfony54-security-core.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/security-http', '5.4', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony54/symfony54-security-http.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/cache', '5.4', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony54/symfony54-cache.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/http-kernel', '5.4', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony54/symfony54-http-kernel.php'),
            new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/notifier', '5.4', __DIR__ . '/../../../config/sets/symfony/symfony5/symfony54/symfony54-notifier.php'),
        ];
    }
}
