<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Type\BooleanType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer;
use Rector\PostRector\Collector\NodesToAddCollector;
use Rector\PostRector\Collector\NodesToRemoveCollector;
final class AssertManipulator
{
    /**
     * @see https://github.com/nette/tester/blob/master/src/Framework/Assert.php
     * ↓
     * @see https://github.com/sebastianbergmann/phpunit/blob/master/src/Framework/Assert.php
     *
     * @var array<string, string>
     */
    private const ASSERT_METHODS_REMAP = ['same' => 'assertSame', 'notSame' => 'assertNotSame', 'equal' => 'assertEqual', 'notEqual' => 'assertNotEqual', 'true' => 'assertTrue', 'false' => 'assertFalse', 'null' => 'assertNull', 'notNull' => 'assertNotNull', 'count' => 'assertCount', 'match' => 'assertStringMatchesFormat', 'matchFile' => 'assertStringMatchesFormatFile', 'nan' => 'assertIsNumeric'];
    /**
     * @var string[]
     */
    private const TYPE_TO_METHOD = ['list' => 'assertIsArray', 'array' => 'assertIsArray', 'bool' => 'assertIsBool', 'callable' => 'assertIsCallable', 'float' => 'assertIsFloat', 'int' => 'assertIsInt', 'integer' => 'assertIsInt', 'object' => 'assertIsObject', 'resource' => 'assertIsResource', 'string' => 'assertIsString', 'scalar' => 'assertIsScalar'];
    /**
     * @var string
     */
    private const CONTAINS = 'contains';
    /**
     * @var string
     */
    private const THIS = 'this';
    /**
     * @var string
     */
    private const SELF = 'self';
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var ValueResolver
     */
    private $valueResolver;
    /**
     * @var StringTypeAnalyzer
     */
    private $stringTypeAnalyzer;
    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;
    /**
     * @var NodesToAddCollector
     */
    private $nodesToAddCollector;
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\PostRector\Collector\NodesToAddCollector $nodesToAddCollector, \Rector\PostRector\Collector\NodesToRemoveCollector $nodesToRemoveCollector, \Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer $stringTypeAnalyzer, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->valueResolver = $valueResolver;
        $this->stringTypeAnalyzer = $stringTypeAnalyzer;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @return StaticCall|MethodCall
     */
    public function processStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : \PhpParser\Node\Expr
    {
        if ($this->nodeNameResolver->isNames($staticCall->name, ['truthy', 'falsey'])) {
            return $this->processTruthyOrFalseyCall($staticCall);
        }
        if ($this->nodeNameResolver->isNames($staticCall->name, [self::CONTAINS, 'notContains'])) {
            $this->processContainsCall($staticCall);
        } elseif ($this->nodeNameResolver->isNames($staticCall->name, ['exception', 'throws'])) {
            $this->processExceptionCall($staticCall);
        } elseif ($this->nodeNameResolver->isName($staticCall->name, 'type')) {
            $this->processTypeCall($staticCall);
        } elseif ($this->nodeNameResolver->isName($staticCall->name, 'noError')) {
            $this->processNoErrorCall($staticCall);
        } else {
            $this->renameAssertMethod($staticCall);
        }
        // self or class, depending on the context
        // prefer $this->assertSame() as more conventional and explicit in class-context
        if (!$this->sholdBeStaticCall($staticCall)) {
            $methodCall = new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable(self::THIS), $staticCall->name);
            $methodCall->args = $staticCall->args;
            $methodCall->setAttributes($staticCall->getAttributes());
            $methodCall->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
            return $methodCall;
        }
        $staticCall->class = new \PhpParser\Node\Name\FullyQualified('PHPUnit\\Framework\\Assert');
        return $staticCall;
    }
    /**
     * @return StaticCall|MethodCall
     */
    private function processTruthyOrFalseyCall(\PhpParser\Node\Expr\StaticCall $staticCall) : \PhpParser\Node\Expr
    {
        $method = $this->nodeNameResolver->isName($staticCall->name, 'truthy') ? 'assertTrue' : 'assertFalse';
        if (!$this->sholdBeStaticCall($staticCall)) {
            $call = new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable(self::THIS), $method);
            $call->args = $staticCall->args;
            $call->setAttributes($staticCall->getAttributes());
            $call->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        } else {
            $call = $staticCall;
            $call->name = new \PhpParser\Node\Identifier($method);
        }
        if (!$this->nodeTypeResolver->isStaticType($staticCall->args[0]->value, \PHPStan\Type\BooleanType::class)) {
            $call->args[0]->value = new \PhpParser\Node\Expr\Cast\Bool_($staticCall->args[0]->value);
        }
        return $call;
    }
    private function processContainsCall(\PhpParser\Node\Expr\StaticCall $staticCall) : void
    {
        if ($this->stringTypeAnalyzer->isStringOrUnionStringOnlyType($staticCall->args[1]->value)) {
            $name = $this->nodeNameResolver->isName($staticCall->name, self::CONTAINS) ? 'assertStringContainsString' : 'assertStringNotContainsString';
        } else {
            $name = $this->nodeNameResolver->isName($staticCall->name, self::CONTAINS) ? 'assertContains' : 'assertNotContains';
        }
        $staticCall->name = new \PhpParser\Node\Identifier($name);
    }
    private function processExceptionCall(\PhpParser\Node\Expr\StaticCall $staticCall) : void
    {
        $method = 'expectException';
        // expect exception
        if ($this->sholdBeStaticCall($staticCall)) {
            $expectException = new \PhpParser\Node\Expr\StaticCall(new \PhpParser\Node\Name(self::SELF), $method);
        } else {
            $expectException = new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable(self::THIS), $method);
        }
        $expectException->args[] = $staticCall->args[1];
        $this->nodesToAddCollector->addNodeAfterNode($expectException, $staticCall);
        // expect message
        if (isset($staticCall->args[2])) {
            $this->refactorExpectException($staticCall);
        }
        // expect code
        if (isset($staticCall->args[3])) {
            $this->refactorExpectExceptionCode($staticCall);
        }
        /** @var Closure $closure */
        $closure = $staticCall->args[0]->value;
        $this->nodesToAddCollector->addNodesAfterNode($closure->stmts, $staticCall);
        $this->nodesToRemoveCollector->addNodeToRemove($staticCall);
    }
    private function processTypeCall(\PhpParser\Node\Expr\StaticCall $staticCall) : void
    {
        $value = $this->valueResolver->getValue($staticCall->args[0]->value);
        if (isset(self::TYPE_TO_METHOD[$value])) {
            $staticCall->name = new \PhpParser\Node\Identifier(self::TYPE_TO_METHOD[$value]);
            unset($staticCall->args[0]);
            $staticCall->args = \array_values($staticCall->args);
        } elseif ($value === 'null') {
            $staticCall->name = new \PhpParser\Node\Identifier('assertNull');
            unset($staticCall->args[0]);
            $staticCall->args = \array_values($staticCall->args);
        } else {
            $staticCall->name = new \PhpParser\Node\Identifier('assertInstanceOf');
        }
    }
    private function processNoErrorCall(\PhpParser\Node\Expr\StaticCall $staticCall) : void
    {
        /** @var Closure $closure */
        $closure = $staticCall->args[0]->value;
        $this->nodesToAddCollector->addNodesAfterNode($closure->stmts, $staticCall);
        $this->nodesToRemoveCollector->addNodeToRemove($staticCall);
        $classMethod = $staticCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->addPhpDocTagNode(new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode('@doesNotPerformAssertions', new \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode('')));
    }
    private function renameAssertMethod(\PhpParser\Node\Expr\StaticCall $staticCall) : void
    {
        foreach (self::ASSERT_METHODS_REMAP as $oldMethod => $newMethod) {
            if (!$this->nodeNameResolver->isName($staticCall->name, $oldMethod)) {
                continue;
            }
            $staticCall->name = new \PhpParser\Node\Identifier($newMethod);
        }
    }
    private function sholdBeStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : bool
    {
        return !(bool) $staticCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
    }
    private function refactorExpectException(\PhpParser\Node\Expr\StaticCall $staticCall) : string
    {
        $method = 'expectExceptionMessage';
        if ($this->sholdBeStaticCall($staticCall)) {
            $expectExceptionMessage = new \PhpParser\Node\Expr\StaticCall(new \PhpParser\Node\Name(self::SELF), $method);
        } else {
            $expectExceptionMessage = new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable(self::THIS), $method);
        }
        $expectExceptionMessage->args[] = $staticCall->args[2];
        $this->nodesToAddCollector->addNodeAfterNode($expectExceptionMessage, $staticCall);
        return $method;
    }
    private function refactorExpectExceptionCode(\PhpParser\Node\Expr\StaticCall $staticCall) : void
    {
        if ($this->sholdBeStaticCall($staticCall)) {
            $expectExceptionCode = new \PhpParser\Node\Expr\StaticCall(new \PhpParser\Node\Name(self::SELF), 'expectExceptionCode');
        } else {
            $expectExceptionCode = new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable(self::THIS), 'expectExceptionCode');
        }
        $expectExceptionCode->args[] = $staticCall->args[3];
        $this->nodesToAddCollector->addNodeAfterNode($expectExceptionCode, $staticCall);
    }
}
