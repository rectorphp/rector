<?php declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit;

use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\Commander\NodeAddingCommander;
use Rector\PhpParser\Node\Commander\NodeRemovingCommander;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Node\Value\ValueResolver;

final class AssertManipulator
{
    /**
     * @see https://github.com/nette/tester/blob/master/src/Framework/Assert.php
     * ↓
     * @see https://github.com/sebastianbergmann/phpunit/blob/master/src/Framework/Assert.php
     * @var string[]
     */
    private $assertMethodsRemap = [
        'same' => 'assertSame',
        'notSame' => 'assertNotSame',
        'equal' => 'assertEqual',
        'notEqual' => 'assertNotEqual',
        'true' => 'assertTrue',
        'false' => 'assertFalse',
        'null' => 'assertNull',
        'notNull' => 'assertNotNull',
        'count' => 'assertCount',
        'match' => 'assertStringMatchesFormat',
        'matchFile' => 'assertStringMatchesFormatFile',
        'nan' => 'assertIsNumeric',
    ];

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var NodeAddingCommander
     */
    private $nodeAddingCommander;

    /**
     * @var NodeRemovingCommander
     */
    private $nodeRemovingCommander;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(
        NameResolver $nameResolver,
        NodeTypeResolver $nodeTypeResolver,
        ValueResolver $valueResolver,
        NodeAddingCommander $nodeAddingCommander,
        NodeRemovingCommander $nodeRemovingCommander,
        DocBlockManipulator $docBlockManipulator
    ) {
        $this->nameResolver = $nameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->valueResolver = $valueResolver;
        $this->nodeAddingCommander = $nodeAddingCommander;
        $this->nodeRemovingCommander = $nodeRemovingCommander;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function processStaticCall(StaticCall $staticCall): void
    {
        $classNode = $staticCall->getAttribute(Attribute::CLASS_NODE);

        // self or class, depending on the context
        $staticCall->class = $classNode ? new Name('self') : new FullyQualified('PHPUnit\Framework\Assert');

        if ($this->nameResolver->isNames($staticCall, ['contains', 'notContains'])) {
            $this->processContainsStaticCall($staticCall);
            return;
        }

        if ($this->nameResolver->isNames($staticCall, ['exception', 'throws'])) {
            $this->processExceptionStaticCall($staticCall);
            return;
        }

        if ($this->nameResolver->isName($staticCall, 'type')) {
            $this->processTypeStaticCall($staticCall);
            return;
        }

        if ($this->nameResolver->isName($staticCall, 'noError')) {
            $this->processNoErrorStaticCall($staticCall);
            return;
        }

        if ($this->nameResolver->isNames($staticCall, ['truthy', 'falsey'])) {
            $this->processTruthyOrFalseyStaticCall($staticCall);
            return;
        }

        foreach ($this->assertMethodsRemap as $oldMethod => $newMethod) {
            if ($this->nameResolver->isName($staticCall, $oldMethod)) {
                $staticCall->name = new Identifier($newMethod);
                continue;
            }
        }
    }

    private function processExceptionStaticCall(StaticCall $staticCall): void
    {
        // expect exception
        $expectException = new StaticCall(new Name('self'), 'expectException');
        $expectException->args[] = $staticCall->args[1];
        $this->nodeAddingCommander->addNodeAfterNode($expectException, $staticCall);

        // expect message
        if (isset($staticCall->args[2])) {
            $expectExceptionMessage = new StaticCall(new Name('self'), 'expectExceptionMessage');
            $expectExceptionMessage->args[] = $staticCall->args[2];
            $this->nodeAddingCommander->addNodeAfterNode($expectExceptionMessage, $staticCall);
        }

        // expect code
        if (isset($staticCall->args[3])) {
            $expectExceptionMessage = new StaticCall(new Name('self'), 'expectExceptionCode');
            $expectExceptionMessage->args[] = $staticCall->args[3];
            $this->nodeAddingCommander->addNodeAfterNode($expectExceptionMessage, $staticCall);
        }

        /** @var Closure $closure */
        $closure = $staticCall->args[0]->value;
        foreach ((array) $closure->stmts as $callableStmt) {
            $this->nodeAddingCommander->addNodeAfterNode($callableStmt, $staticCall);
        }

        $this->nodeRemovingCommander->addNode($staticCall);
    }

    private function processNoErrorStaticCall(StaticCall $staticCall): void
    {
        /** @var Closure $closure */
        $closure = $staticCall->args[0]->value;

        foreach ((array) $closure->stmts as $callableStmt) {
            $this->nodeAddingCommander->addNodeAfterNode($callableStmt, $staticCall);
        }

        $this->nodeRemovingCommander->addNode($staticCall);

        $methodNode = $staticCall->getAttribute(Attribute::METHOD_NODE);
        if ($methodNode === null) {
            return;
        }

        $phpDocTagNode = new PhpDocTextNode('@doesNotPerformAssertions');
        $this->docBlockManipulator->addTag($methodNode, $phpDocTagNode);
    }

    private function processTruthyOrFalseyStaticCall(StaticCall $staticCall): void
    {
        if (! $this->nodeTypeResolver->isBoolType($staticCall->args[0]->value)) {
            $staticCall->args[0]->value = new Bool_($staticCall->args[0]->value);
        }

        if ($this->nameResolver->isName($staticCall, 'truthy')) {
            $staticCall->name = new Identifier('assertTrue');
        } else {
            $staticCall->name = new Identifier('assertFalse');
        }
    }

    private function processTypeStaticCall(StaticCall $staticCall): void
    {
        $value = $this->valueResolver->resolve($staticCall->args[0]->value);

        $typeToMethod = [
            'list' => 'assertIsArray',
            'array' => 'assertIsArray',
            'bool' => 'assertIsBool',
            'callable' => 'assertIsCallable',
            'float' => 'assertIsFloat',
            'int' => 'assertIsInt',
            'integer' => 'assertIsInt',
            'object' => 'assertIsObject',
            'resource' => 'assertIsResource',
            'string' => 'assertIsString',
            'scalar' => 'assertIsScalar',
        ];

        if (isset($typeToMethod[$value])) {
            $staticCall->name = new Identifier($typeToMethod[$value]);
            unset($staticCall->args[0]);
            array_values($staticCall->args);
        } elseif ($value === 'null') {
            $staticCall->name = new Identifier('assertNull');
            unset($staticCall->args[0]);
            array_values($staticCall->args);
        } else {
            $staticCall->name = new Identifier('assertInstanceOf');
        }
    }

    private function processContainsStaticCall(StaticCall $staticCall): void
    {
        if ($this->nodeTypeResolver->isStringyType($staticCall->args[1]->value)) {
            $name = $this->nameResolver->isName(
                $staticCall,
                'contains'
            ) ? 'assertStringContainsString' : 'assertStringNotContainsString';
        } else {
            $name = $this->nameResolver->isName($staticCall, 'contains') ? 'assertContains' : 'assertNotContains';
        }

        $staticCall->name = new Identifier($name);
    }
}
