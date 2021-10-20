<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\DependencyInjection\Loader\Configurator;

use RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ServiceConfigurator extends \RectorPrefix20211020\Symfony\Component\DependencyInjection\Loader\Configurator\AbstractServiceConfigurator
{
    use Traits\AbstractTrait;
    use Traits\ArgumentTrait;
    use Traits\AutoconfigureTrait;
    use Traits\AutowireTrait;
    use Traits\BindTrait;
    use Traits\CallTrait;
    use Traits\ClassTrait;
    use Traits\ConfiguratorTrait;
    use Traits\DecorateTrait;
    use Traits\DeprecateTrait;
    use Traits\FactoryTrait;
    use Traits\FileTrait;
    use Traits\LazyTrait;
    use Traits\ParentTrait;
    use Traits\PropertyTrait;
    use Traits\PublicTrait;
    use Traits\ShareTrait;
    use Traits\SyntheticTrait;
    use Traits\TagTrait;
    public const FACTORY = 'services';
    private $container;
    private $instanceof;
    private $allowParent;
    private $path;
    private $destructed = \false;
    public function __construct(\RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder $container, array $instanceof, bool $allowParent, \RectorPrefix20211020\Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator $parent, \RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition $definition, ?string $id, array $defaultTags, string $path = null)
    {
        $this->container = $container;
        $this->instanceof = $instanceof;
        $this->allowParent = $allowParent;
        $this->path = $path;
        parent::__construct($parent, $definition, $id, $defaultTags);
    }
    public function __destruct()
    {
        if ($this->destructed) {
            return;
        }
        $this->destructed = \true;
        parent::__destruct();
        $this->container->removeBindings($this->id);
        $this->container->setDefinition($this->id, $this->definition->setInstanceofConditionals($this->instanceof));
    }
}
