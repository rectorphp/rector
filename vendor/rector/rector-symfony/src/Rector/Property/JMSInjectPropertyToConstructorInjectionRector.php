<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DependencyInjection\NodeManipulator\PropertyConstructorInjectionManipulator;
use Rector\Symfony\TypeAnalyzer\JMSDITypeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Can cover these cases:
 * - https://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * - https://github.com/rectorphp/rector/issues/700#issue-370301169
 *
 * @see \Rector\Symfony\Tests\Rector\Property\JMSInjectPropertyToConstructorInjectionRector\JMSInjectPropertyToConstructorInjectionRectorTest
 */
final class JMSInjectPropertyToConstructorInjectionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const INJECT_ANNOTATION_CLASS = 'JMS\\DiExtraBundle\\Annotation\\Inject';
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\JMSDITypeResolver
     */
    private $jmsDITypeResolver;
    /**
     * @readonly
     * @var \Rector\DependencyInjection\NodeManipulator\PropertyConstructorInjectionManipulator
     */
    private $propertyConstructorInjectionManipulator;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\Symfony\TypeAnalyzer\JMSDITypeResolver $jmsDITypeResolver, \Rector\DependencyInjection\NodeManipulator\PropertyConstructorInjectionManipulator $propertyConstructorInjectionManipulator, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->jmsDITypeResolver = $jmsDITypeResolver;
        $this->propertyConstructorInjectionManipulator = $propertyConstructorInjectionManipulator;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns properties with `@inject` to private properties and constructor injection', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
/**
 * @var SomeService
 * @inject
 */
public $someService;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @var SomeService
 */
private $someService;

public function __construct(SomeService $someService)
{
    $this->someService = $someService;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return null;
        }
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass(self::INJECT_ANNOTATION_CLASS);
        if (!$doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return null;
        }
        $serviceType = $this->resolveServiceType($doctrineAnnotationTagValueNode, $phpDocInfo, $node);
        if ($serviceType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        $this->propertyConstructorInjectionManipulator->refactor($node, $serviceType, $doctrineAnnotationTagValueNode);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::PROPERTY_PROMOTION)) {
            $this->removeNode($node);
            return null;
        }
        return $node;
    }
    private function resolveServiceType(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Stmt\Property $property) : \PHPStan\Type\Type
    {
        $serviceType = $phpDocInfo->getVarType();
        if (!$serviceType instanceof \PHPStan\Type\MixedType) {
            return $serviceType;
        }
        return $this->jmsDITypeResolver->resolve($property, $doctrineAnnotationTagValueNode);
    }
}
