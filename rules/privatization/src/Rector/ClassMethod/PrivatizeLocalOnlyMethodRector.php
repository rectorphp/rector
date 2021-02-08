<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\SymfonyRequiredTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\ApiPhpDocTagNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette\NetteInjectTagNode;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeAnalyzer\ClassMethodExternalCallNodeAnalyzer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodVisibilityVendorLockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\PrivatizeLocalOnlyMethodRectorTest
 */
final class PrivatizeLocalOnlyMethodRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/f97wwM/1
     */
    private const COMMON_PUBLIC_METHOD_CONTROLLER_REGEX = '#^(render|action|handle|inject)#';

    /**
     * @var string
     * @see https://regex101.com/r/FXhI9M/1
     */
    private const CONTROLLER_PRESENTER_SUFFIX_REGEX = '#(Controller|Presenter)$#';

    /**
     * @var ClassMethodVisibilityVendorLockResolver
     */
    private $classMethodVisibilityVendorLockResolver;

    /**
     * @var ClassMethodExternalCallNodeAnalyzer
     */
    private $classMethodExternalCallNodeAnalyzer;

    /**
     * @var DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;

    public function __construct(
        ClassMethodExternalCallNodeAnalyzer $classMethodExternalCallNodeAnalyzer,
        ClassMethodVisibilityVendorLockResolver $classMethodVisibilityVendorLockResolver,
        DoctrineDocBlockResolver $doctrineDocBlockResolver
    ) {
        $this->classMethodVisibilityVendorLockResolver = $classMethodVisibilityVendorLockResolver;
        $this->classMethodExternalCallNodeAnalyzer = $classMethodExternalCallNodeAnalyzer;
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Privatize local-only use methods', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @api
     */
    public function run()
    {
        return $this->useMe();
    }

    public function useMe()
    {
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @api
     */
    public function run()
    {
        return $this->useMe();
    }

    private function useMe()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        if ($this->classMethodExternalCallNodeAnalyzer->hasExternalCall($node)) {
            return null;
        }

        $this->visibilityManipulator->makePrivate($node);

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return true;
        }

        if ($this->shouldSkipClassLike($classLike)) {
            return true;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        if ($phpDocInfo->hasByType(ApiPhpDocTagNode::class)) {
            return true;
        }

        if ($this->isControllerAction($classLike, $classMethod)) {
            return true;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        if ($this->shouldSkipClassMethod($classMethod, $phpDocInfo)) {
            return true;
        }

        // is interface required method? skip it
        if ($this->classMethodVisibilityVendorLockResolver->isParentLockedMethod($classMethod)) {
            return true;
        }

        if ($this->classMethodVisibilityVendorLockResolver->isChildLockedMethod($classMethod)) {
            return true;
        }

        return $phpDocInfo->hasByTypes([SymfonyRequiredTagNode::class, ApiPhpDocTagNode::class]);
    }

    private function shouldSkipClassLike(Class_ $class): bool
    {
        if ($this->classNodeAnalyzer->isAnonymousClass($class)) {
            return true;
        }

        if ($this->doctrineDocBlockResolver->isDoctrineEntityClass($class)) {
            return true;
        }

        if ($this->isObjectType($class, 'PHPUnit\Framework\TestCase')) {
            return true;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        return $phpDocInfo->hasByType(ApiPhpDocTagNode::class);
    }

    private function isControllerAction(Class_ $class, ClassMethod $classMethod): bool
    {
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        if (! Strings::match($className, self::CONTROLLER_PRESENTER_SUFFIX_REGEX)) {
            return false;
        }

        $classMethodName = $this->getName($classMethod);

        if ((bool) Strings::match($classMethodName, self::COMMON_PUBLIC_METHOD_CONTROLLER_REGEX)) {
            return true;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasByType(NetteInjectTagNode::class);
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod, PhpDocInfo $phpDocInfo): bool
    {
        if ($phpDocInfo->hasByType(ApiPhpDocTagNode::class)) {
            return true;
        }

        if ($classMethod->isPrivate()) {
            return true;
        }

        if ($classMethod->isAbstract()) {
            return true;
        }

        // skip for now
        if ($classMethod->isStatic()) {
            return true;
        }

        if ($this->isName($classMethod, '__*')) {
            return true;
        }

        // possibly container service factories
        return $this->isNames($classMethod, ['create', 'create*']);
    }
}
