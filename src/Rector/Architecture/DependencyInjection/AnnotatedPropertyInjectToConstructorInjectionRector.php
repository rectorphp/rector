<?php

declare(strict_types=1);

namespace Rector\Core\Rector\Architecture\DependencyInjection;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * Can cover these cases:
 * - https://doc.nette.org/en/2.4/di-usage#toc-inject-annotations
 * - https://github.com/Kdyby/Autowired/blob/master/docs/en/index.md#autowired-properties
 * - http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * - https://github.com/rectorphp/rector/issues/700#issue-370301169
 *
 * @see \Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\AnnotatedPropertyInjectToConstructorInjectionRectorTest
 */
final class AnnotatedPropertyInjectToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var string
     */
    private const INJECT_ANNOTATION = 'inject';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns non-private properties with `@annotation` to private properties and constructor injection',
            [
                new CodeSample(
                    <<<'PHP'
/**
 * @var SomeService
 * @inject
 */
public $someService;
PHP
                    ,
                    <<<'PHP'
/**
 * @var SomeService
 */
private $someService;

public function __construct(SomeService $someService)
{
    $this->someService = $someService;
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipProperty($node)) {
            return null;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->removeByName(self::INJECT_ANNOTATION);

        // set to private
        $this->makePrivate($node);
        $this->addPropertyToCollector($node);

        return $node;
    }

    private function shouldSkipProperty(Property $property): bool
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);

        if (! $phpDocInfo->hasByName(self::INJECT_ANNOTATION)) {
            return true;
        }

        // it needs @var tag as well, to get the type
        if ($phpDocInfo->getVarTagValue() !== null) {
            return false;
        }

        return $property->type === null;
    }

    private function addPropertyToCollector(Property $property): void
    {
        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return;
        }

        $propertyType = $this->getObjectType($property);

        // use first type
        if ($propertyType instanceof UnionType) {
            $propertyType = $propertyType->getTypes()[0];
        }

        $propertyName = $this->getName($property);

        $this->addPropertyToClass($classNode, $propertyType, $propertyName);
    }
}
