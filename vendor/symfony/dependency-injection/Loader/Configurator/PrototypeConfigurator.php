<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220501\Symfony\Component\DependencyInjection\Loader\Configurator;

use RectorPrefix20220501\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class PrototypeConfigurator extends \RectorPrefix20220501\Symfony\Component\DependencyInjection\Loader\Configurator\AbstractServiceConfigurator
{
    use Traits\AbstractTrait;
    use Traits\ArgumentTrait;
    use Traits\AutoconfigureTrait;
    use Traits\AutowireTrait;
    use Traits\BindTrait;
    use Traits\CallTrait;
    use Traits\ConfiguratorTrait;
    use Traits\DeprecateTrait;
    use Traits\FactoryTrait;
    use Traits\LazyTrait;
    use Traits\ParentTrait;
    use Traits\PropertyTrait;
    use Traits\PublicTrait;
    use Traits\ShareTrait;
    use Traits\TagTrait;
    public const FACTORY = 'load';
    private $loader;
    /**
     * @var string
     */
    private $resource;
    /**
     * @var mixed[]|null
     */
    private $excludes;
    /**
     * @var bool
     */
    private $allowParent;
    public function __construct(\RectorPrefix20220501\Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator $parent, \RectorPrefix20220501\Symfony\Component\DependencyInjection\Loader\PhpFileLoader $loader, \RectorPrefix20220501\Symfony\Component\DependencyInjection\Definition $defaults, string $namespace, string $resource, bool $allowParent)
    {
        $definition = new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Definition();
        if (!$defaults->isPublic() || !$defaults->isPrivate()) {
            $definition->setPublic($defaults->isPublic());
        }
        $definition->setAutowired($defaults->isAutowired());
        $definition->setAutoconfigured($defaults->isAutoconfigured());
        // deep clone, to avoid multiple process of the same instance in the passes
        $definition->setBindings(\unserialize(\serialize($defaults->getBindings())));
        $definition->setChanges([]);
        $this->loader = $loader;
        $this->resource = $resource;
        $this->allowParent = $allowParent;
        parent::__construct($parent, $definition, $namespace, $defaults->getTags());
    }
    public function __destruct()
    {
        parent::__destruct();
        if (isset($this->loader)) {
            $this->loader->registerClasses($this->definition, $this->id, $this->resource, $this->excludes);
        }
        unset($this->loader);
    }
    /**
     * Excludes files from registration using glob patterns.
     *
     * @param string[]|string $excludes
     *
     * @return $this
     */
    public final function exclude($excludes)
    {
        $this->excludes = (array) $excludes;
        return $this;
    }
}
