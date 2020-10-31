<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeVisitor\FunctionMethodAndClassNodeVisitor;
use Rector\Testing\PHPUnit\AbstractNodeVisitorTestCase;

final class FunctionMethodAndClassNodeVisitorTest extends AbstractNodeVisitorTestCase
{
    /**
     * @var FunctionMethodAndClassNodeVisitor
     */
    private $functionMethodAndClassNodeVisitor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootKernel(RectorKernel::class);
        $this->functionMethodAndClassNodeVisitor = self::$container->get(FunctionMethodAndClassNodeVisitor::class);
    }

    public function testMethodName(): void
    {
        $parsedAttributes = $this->parseFileToAttribute(__DIR__ . '/Fixture/simple.php.inc', AttributeKey::METHOD_NAME);

        $classAttributes = $parsedAttributes[3];
        $this->assertSame(null, $classAttributes[AttributeKey::METHOD_NAME]);

        $classMethodAttributes = $parsedAttributes[4];
        $this->assertSame('bar', $classMethodAttributes[AttributeKey::METHOD_NAME]);

        $methodNameAttributes = $parsedAttributes[5];
        $this->assertSame('bar', $methodNameAttributes[AttributeKey::METHOD_NAME]);
    }

    public function testClassName(): void
    {
        $parsedAttributes = $this->parseFileToAttribute(__DIR__ . '/Fixture/simple.php.inc', AttributeKey::CLASS_NAME);

        $classMethodAttributes = $parsedAttributes[3];
        $this->assertSame(
            'Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\Fixture\Foo',
            $classMethodAttributes[AttributeKey::CLASS_NAME]
        );

        $classAttributes = $parsedAttributes[4];
        $this->assertSame(
            'Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\Fixture\Foo',
            $classAttributes[AttributeKey::CLASS_NAME]
        );
    }

    public function testClassNode(): void
    {
        $parsedAttributes = $this->parseFileToAttribute(__DIR__ . '/Fixture/simple.php.inc', AttributeKey::CLASS_NODE);

        $classMethodAttributes = $parsedAttributes[3];
        $this->assertInstanceOf(Class_::class, $classMethodAttributes[AttributeKey::CLASS_NODE]);
    }

    public function testAnonymousClassName(): void
    {
        $parsedAttributes = $this->parseFileToAttribute(
            __DIR__ . '/Fixture/anonymous_class.php.inc',
            AttributeKey::CLASS_NAME
        );

        $funcCallAttributes = $parsedAttributes[10];
        $this->assertSame(
            'Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitorTest\Fixture\AnonymousClass',
            $funcCallAttributes[AttributeKey::CLASS_NAME]
        );

        // method in the anonymous class has no class name
        $anoymousClassMethodAttributes = $parsedAttributes[8];
        $this->assertNull($anoymousClassMethodAttributes[AttributeKey::CLASS_NAME]);
    }

    /**
     * @param Node[] $nodes
     */
    public function traverseNodes(array $nodes): void
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $nodeTraverser->addVisitor($this->functionMethodAndClassNodeVisitor);
        $nodeTraverser->traverse($nodes);
    }
}
