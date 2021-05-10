<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NetteToSymfony\ValueObject\EventInfo;
use Rector\NetteToSymfony\ValueObjectFactory\EventInfosFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/doc/current/components/http_kernel.html#creating-an-event-listener
 *
 * @see \Rector\NetteToSymfony\Tests\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector\RenameEventNamesInEventSubscriberRectorTest
 */
final class RenameEventNamesInEventSubscriberRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var EventInfo[]
     */
    private $symfonyClassConstWithAliases = [];
    public function __construct(\Rector\NetteToSymfony\ValueObjectFactory\EventInfosFactory $eventInfosFactory)
    {
        $this->symfonyClassConstWithAliases = $eventInfosFactory->create();
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes event names from Nette ones to Symfony ones', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SomeClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['nette.application' => 'someMethod'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SomeClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [\SymfonyEvents::KERNEL => 'someMethod'];
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classLike = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        if (!$this->isObjectType($classLike, new \PHPStan\Type\ObjectType('Symfony\\Component\\EventDispatcher\\EventSubscriberInterface'))) {
            return null;
        }
        if (!$this->isName($node, 'getSubscribedEvents')) {
            return null;
        }
        /** @var Return_[] $returnNodes */
        $returnNodes = $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Stmt\Return_::class);
        foreach ($returnNodes as $returnNode) {
            if (!$returnNode->expr instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            $this->renameArrayKeys($returnNode);
        }
        return $node;
    }
    private function renameArrayKeys(\PhpParser\Node\Stmt\Return_ $return) : void
    {
        if (!$return->expr instanceof \PhpParser\Node\Expr\Array_) {
            return;
        }
        foreach ($return->expr->items as $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }
            $eventInfo = $this->matchStringKeys($arrayItem);
            if (!$eventInfo instanceof \Rector\NetteToSymfony\ValueObject\EventInfo) {
                $eventInfo = $this->matchClassConstKeys($arrayItem);
            }
            if (!$eventInfo instanceof \Rector\NetteToSymfony\ValueObject\EventInfo) {
                continue;
            }
            $arrayItem->key = new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name\FullyQualified($eventInfo->getClass()), $eventInfo->getConstant());
            // method name
            $className = (string) $return->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
            $methodName = (string) $this->valueResolver->getValue($arrayItem->value);
            $this->processMethodArgument($className, $methodName, $eventInfo);
        }
    }
    private function matchStringKeys(\PhpParser\Node\Expr\ArrayItem $arrayItem) : ?\Rector\NetteToSymfony\ValueObject\EventInfo
    {
        if (!$arrayItem->key instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        foreach ($this->symfonyClassConstWithAliases as $symfonyClassConstWithAlias) {
            foreach ($symfonyClassConstWithAlias->getOldStringAliases() as $netteStringName) {
                if ($this->valueResolver->isValue($arrayItem->key, $netteStringName)) {
                    return $symfonyClassConstWithAlias;
                }
            }
        }
        return null;
    }
    private function matchClassConstKeys(\PhpParser\Node\Expr\ArrayItem $arrayItem) : ?\Rector\NetteToSymfony\ValueObject\EventInfo
    {
        if (!$arrayItem->key instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return null;
        }
        foreach ($this->symfonyClassConstWithAliases as $symfonyClassConstWithAlias) {
            $isMatch = $this->resolveClassConstAliasMatch($arrayItem, $symfonyClassConstWithAlias);
            if ($isMatch) {
                return $symfonyClassConstWithAlias;
            }
        }
        return null;
    }
    private function processMethodArgument(string $class, string $method, \Rector\NetteToSymfony\ValueObject\EventInfo $eventInfo) : void
    {
        $classMethodNode = $this->nodeRepository->findClassMethod($class, $method);
        if (!$classMethodNode instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        if (\count($classMethodNode->params) !== 1) {
            return;
        }
        $classMethodNode->params[0]->type = new \PhpParser\Node\Name\FullyQualified($eventInfo->getEventClass());
    }
    private function resolveClassConstAliasMatch(\PhpParser\Node\Expr\ArrayItem $arrayItem, \Rector\NetteToSymfony\ValueObject\EventInfo $eventInfo) : bool
    {
        $classConstFetchNode = $arrayItem->key;
        if (!$classConstFetchNode instanceof \PhpParser\Node\Expr) {
            return \false;
        }
        foreach ($eventInfo->getOldClassConstAliases() as $netteClassConst) {
            if ($this->isName($classConstFetchNode, $netteClassConst)) {
                return \true;
            }
        }
        return \false;
    }
}
