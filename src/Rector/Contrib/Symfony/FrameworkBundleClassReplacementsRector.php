<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony;

use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractClassReplacerRector;

/**
 * Ref.: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#frameworkbundle
 *
 * FrameworkBundle classes replaced by new ones
 */
final class FrameworkBundleClassReplacementsRector extends AbstractClassReplacerRector
{
    public function getSetName(): string
    {
        return SetNames::SYMFONY;
    }

    public function sinceVersion(): float
    {
        return 4.0;
    }

    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return [
            'Symfony\Bundle\FrameworkBundle\DependencyInjectino\Compiler\SerializerPass' => 'Symfony\Component\Serializer\DependencyInjection\SerializerPass',
        ];
    }
}
