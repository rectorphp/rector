<?php

declare (strict_types=1);
namespace Rector\Doctrine\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\ComposerTriggeredSet;
use Rector\Set\ValueObject\Set;
/**
 * @api collected in core
 */
final class DoctrineSetProvider implements SetProviderInterface
{
    /**
     * The composer-based set holds rules and configuration bound to the exact Doctrine package version they are
     * available from. Doctrine has no single package to trigger on, so every package used inside the set triggers it,
     * from the lowest version its rules require.
     *
     * @var array<string, string>
     */
    private const COMPOSER_BASED_TRIGGER_PACKAGES = ['doctrine/data-fixtures' => '>=1.6', 'doctrine/common' => '>=2.0', 'doctrine/collections' => '>=2.2', 'doctrine/doctrine-bundle' => '>=2.3', 'doctrine/orm' => '>=2.5', 'doctrine/dbal' => '>=2.11', 'doctrine/mongodb-odm' => '>=2.3', 'doctrine/mongodb-odm-bundle' => '>=4.4', 'gedmo/doctrine-extensions' => '>=3.5'];
    /**
     * @return SetInterface[]
     */
    public function provide(): array
    {
        return array_merge($this->provideComposerBasedSets(), [
            new Set(SetGroup::DOCTRINE, 'Code Quality', __DIR__ . '/../../../config/sets/doctrine-code-quality.php'),
            new Set(SetGroup::DOCTRINE, 'Typed Collections', __DIR__ . '/../../../config/sets/typed-collections.php'),
            // @see https://github.com/rectorphp/getrector-com/pull/2672
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/common', '2.0', __DIR__ . '/../../../config/sets/doctrine-common-20.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/collection', '2.2', __DIR__ . '/../../../config/sets/doctrine-collection-22.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/doctrine-bundle', '2.3', __DIR__ . '/../../../config/sets/doctrine-bundle-23.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/doctrine-bundle', '2.8', __DIR__ . '/../../../config/sets/doctrine-bundle-28.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/dbal', '3.0', __DIR__ . '/../../../config/sets/doctrine-dbal-30.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/dbal', '3.8', __DIR__ . '/../../../config/sets/doctrine-dbal-38.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/dbal', '4.0', __DIR__ . '/../../../config/sets/doctrine-dbal-40.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/dbal', '4.2', __DIR__ . '/../../../config/sets/doctrine-dbal-42.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/dbal', '2.11', __DIR__ . '/../../../config/sets/doctrine-dbal-211.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/orm', '2.5', __DIR__ . '/../../../config/sets/doctrine-orm-25.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/orm', '2.13', __DIR__ . '/../../../config/sets/doctrine-orm-213.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/orm', '2.14', __DIR__ . '/../../../config/sets/doctrine-orm-214.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/orm', '3.0', __DIR__ . '/../../../config/sets/doctrine-orm-300.php'),
            new Set(SetGroup::ATTRIBUTES, 'Doctrine ORM', __DIR__ . '/../../../config/sets/attributes/doctrine.php'),
            new Set(SetGroup::ATTRIBUTES, 'Gedmo', __DIR__ . '/../../../config/sets/attributes/gedmo.php'),
            new Set(SetGroup::ATTRIBUTES, 'MongoDB', __DIR__ . '/../../../config/sets/attributes/mongodb.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/data-fixtures', '1.6', __DIR__ . '/../../../config/sets/data-fixtures-16.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/data-fixtures', '1.7', __DIR__ . '/../../../config/sets/data-fixtures-17.php'),
            new ComposerTriggeredSet(SetGroup::DOCTRINE, 'doctrine/mongodb-odm', '2.16', __DIR__ . '/../../../config/sets/doctrine-mongodb-odm-216.php'),
        ]);
    }
    /**
     * @return ComposerTriggeredSet[]
     */
    private function provideComposerBasedSets(): array
    {
        $composerTriggeredSets = [];
        foreach (self::COMPOSER_BASED_TRIGGER_PACKAGES as $packageName => $version) {
            $composerTriggeredSets[] = new ComposerTriggeredSet(SetGroup::DOCTRINE, $packageName, $version, __DIR__ . '/../../../config/sets/composer-based.php');
        }
        return $composerTriggeredSets;
    }
}
