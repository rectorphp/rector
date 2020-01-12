<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanAttributeTypeSyncer\ClassNaming;

use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Utils\PHPStanAttributeTypeSyncer\ValueObject\Paths;

final class AttributeClassNaming
{
    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(ClassNaming $classNaming)
    {
        $this->classNaming = $classNaming;
    }

    public function createAttributeAwareShortClassName(string $nodeClass): string
    {
        $shortMissingNodeClass = $this->classNaming->getShortName($nodeClass);

        return 'AttributeAware' . $shortMissingNodeClass;
    }

    public function createAttributeAwareFactoryShortClassName(string $nodeClass): string
    {
        $shortMissingNodeClass = $this->classNaming->getShortName($nodeClass);

        return 'AttributeAware' . $shortMissingNodeClass . 'Factory';
    }

    public function createAttributeAwareClassName(string $nodeClass): string
    {
        return Paths::NAMESPACE_PHPDOC_NODE . '\\' . $this->createAttributeAwareShortClassName($nodeClass);
    }

    public function createAttributeAwareFactoryClassName(string $nodeClass): string
    {
        return Paths::NAMESPACE_NODE_FACTORY . '\\' . $this->createAttributeAwareFactoryShortClassName($nodeClass);
    }
}
