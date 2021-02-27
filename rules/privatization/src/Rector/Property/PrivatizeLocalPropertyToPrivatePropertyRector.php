<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\SymfonyRequiredTagNode;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\ApiPhpDocTagNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette\NetteInjectTagNode;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VendorLocker\NodeVendorLocker\PropertyVisibilityVendorLockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Privatization\Tests\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\PrivatizeLocalPropertyToPrivatePropertyRectorTest
 */
final class PrivatizeLocalPropertyToPrivatePropertyRector extends AbstractRector
{
    /**
     * @var array<class-string<AttributeAwareNodeInterface>>
     */
    private const TAG_NODES_REQUIRING_PUBLIC = [
        ApiPhpDocTagNode::class,
        // Symfony DI
        SymfonyRequiredTagNode::class,
        // other DI
        NetteInjectTagNode::class,
    ];

    /**
     * @var PropertyVisibilityVendorLockResolver
     */
    private $propertyVisibilityVendorLockResolver;

    /**
     * @var ObjectType[]
     */
    private $excludedObjectTypes = [];

    public function __construct(PropertyVisibilityVendorLockResolver $propertyVisibilityVendorLockResolver)
    {
        $this->propertyVisibilityVendorLockResolver = $propertyVisibilityVendorLockResolver;

        $this->excludedObjectTypes = [
            new ObjectType('PHPUnit\Framework\TestCase'),
            new ObjectType('PHP_CodeSniffer\Sniffs\Sniff'),
        ];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Privatize local-only property to private property', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public $value;

    public function run()
    {
        return $this->value;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    private $value;

    public function run()
    {
        return $this->value;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $propertyFetches = $this->nodeRepository->findPropertyFetchesByProperty($node);

        $usedPropertyFetchClassNames = [];
        foreach ($propertyFetches as $propertyFetch) {
            $usedPropertyFetchClassNames[] = $propertyFetch->getAttribute(AttributeKey::CLASS_NAME);
        }

        $usedPropertyFetchClassNames = array_unique($usedPropertyFetchClassNames);

        $propertyClassName = $node->getAttribute(AttributeKey::CLASS_NAME);

        // has external usage
        if ($usedPropertyFetchClassNames !== [] && [$propertyClassName] !== $usedPropertyFetchClassNames) {
            return null;
        }

        $this->visibilityManipulator->makePrivate($node);

        return $node;
    }

    private function shouldSkip(Property $property): bool
    {
        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            return true;
        }

        if ($this->shouldSkipClass($classLike)) {
            return true;
        }

        if ($this->shouldSkipProperty($property)) {
            return true;
        }

        // is parent required property? skip it
        if ($this->propertyVisibilityVendorLockResolver->isParentLockedProperty($property)) {
            return true;
        }

        if ($this->propertyVisibilityVendorLockResolver->isChildLockedProperty($property)) {
            return true;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        return $phpDocInfo->hasByTypes(self::TAG_NODES_REQUIRING_PUBLIC);
    }

    private function shouldSkipClass(ClassLike $classLike): bool
    {
        if (! $classLike instanceof Class_) {
            return true;
        }

        if ($this->classNodeAnalyzer->isAnonymousClass($classLike)) {
            return true;
        }

        if ($this->nodeTypeResolver->isObjectTypes($classLike, $this->excludedObjectTypes)) {
            return true;
        }

        if (! $classLike->isAbstract()) {
            return false;
        }

        return $this->isOpenSourceProjectType();
    }

    private function shouldSkipProperty(Property $property): bool
    {
        // already private
        if ($property->isPrivate()) {
            return true;
        }

        // skip for now
        return $property->isStatic();
    }
}
