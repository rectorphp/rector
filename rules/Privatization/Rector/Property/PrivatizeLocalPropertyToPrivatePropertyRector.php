<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VendorLocker\NodeVendorLocker\PropertyVisibilityVendorLockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\PrivatizeLocalPropertyToPrivatePropertyRectorTest
 */
final class PrivatizeLocalPropertyToPrivatePropertyRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const TAGS_REQUIRING_PUBLIC_PROPERTY = [
        'api',
        // Symfony DI
        'required',
        // Nette + other DI
        'inject',
    ];

    /**
     * @var ObjectType[]
     */
    private array $excludedObjectTypes = [];

    public function __construct(
        private PropertyVisibilityVendorLockResolver $propertyVisibilityVendorLockResolver,
        private ClassAnalyzer $classAnalyzer
    ) {
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
        return $phpDocInfo->hasByNames(self::TAGS_REQUIRING_PUBLIC_PROPERTY);
    }

    private function shouldSkipClass(ClassLike $classLike): bool
    {
        if (! $classLike instanceof Class_) {
            return true;
        }

        if ($this->classAnalyzer->isAnonymousClass($classLike)) {
            return true;
        }

        if ($this->nodeTypeResolver->isObjectTypes($classLike, $this->excludedObjectTypes)) {
            return true;
        }

        return $classLike->isAbstract();
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
