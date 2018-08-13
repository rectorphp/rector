<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PHPStan\Broker\Broker;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\PHPStan\Type\TypeToStringResolver;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class NodeTypeResolverFactory
{
    /**
     * @var ContainerInterface|Container
     */
    private $container;

    /**
     * @param ContainerInterface|Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(): NodeTypeResolver
    {
        /** @var TypeToStringResolver $typeToStringResolver */
        $typeToStringResolver = $this->container->get(TypeToStringResolver::class);

        /** @var Broker $broker */
        $broker = $this->container->get(Broker::class);

        /** @var ClassReflectionTypesResolver $classReflectionTypesResolver */
        $classReflectionTypesResolver = $this->container->get(ClassReflectionTypesResolver::class);

        $nodeTypeResolver = new NodeTypeResolver($typeToStringResolver, $broker, $classReflectionTypesResolver);

        foreach ($this->container->getServiceIds() as $serviceId) {
            if (! is_a($serviceId, PerNodeTypeResolverInterface::class, true)) {
                continue;
            }

            /** @var PerNodeTypeResolverInterface $perNodeTypeResolver */
            $perNodeTypeResolver = $this->container->get($serviceId);
            $nodeTypeResolver->addPerNodeTypeResolver($perNodeTypeResolver);
        }

        return $nodeTypeResolver;
    }
}
