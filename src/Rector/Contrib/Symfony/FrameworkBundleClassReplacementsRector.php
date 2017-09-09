<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony;

use Rector\Rector\AbstractClassReplacerRector;

/**
 * Ref.: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#frameworkbundle
 *
 * FrameworkBundle classes replaced by new ones
 */
final class FrameworkBundleClassReplacementsRector extends AbstractClassReplacerRector
{
    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return [
            'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddConsoleCommandPass' => 'Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass',
            'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\SerializerPass' => 'Symfony\Component\Serializer\DependencyInjection\SerializerPass',
            'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\FormPass' => 'Symfony\Component\Form\DependencyInjection\FormPass',
            'Symfony\Bundle\FrameworkBundle\EventListener\SessionListener' => 'Symfony\Component\HttpKernel\EventListener\SessionListener',
            'Symfony\Bundle\FrameworkBundle\EventListener\TestSessionListener' => 'Symfony\Component\HttpKernel\EventListener\TestSessionListener',
            'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\ConfigCachePass' => 'Symfony\Component\Config\DependencyInjection\ConfigCachePass',
            'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\PropertyInfoPass' => 'Symfony\Component\PropertyInfo\DependencyInjection\PropertyInfoPass',
        ];
    }
}
