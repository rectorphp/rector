<?php

declare(strict_types=1);

namespace Rector\Tests\NodeDumper;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PHPUnit\Framework\TestCase;
use Rector\NodeDumper\NodeDumper;
use Tracy\Dumper;

final class NodeDumperTest extends TestCase
{
    protected function setUp(): void
    {
        // disable terminal colors
        Dumper::$terminalColors = [];
    }

    public function test(): void
    {
        $fullyQualified = new FullyQualified('SomeClass');
        $fullyQualified->setAttributes(['some' => 'attribute']);
        $new = new New_($fullyQualified);

        $content = $this->getDumpedNodeContent($new);

        $this->assertStringMatchesFormat(
            <<<'CODE_SAMPLE'
PhpParser\Node\Expr\New_ #%s
   class => PhpParser\Node\Name\FullyQualified #%s
   |  parts => array (1)
   |  |  0 => "SomeClass" (9)
   |  attributes protected => array ()
   args => array ()
   attributes protected => array ()
CODE_SAMPLE
            , trim($content));
    }

    private function getDumpedNodeContent(Node $node): string
    {
        ob_start();
        NodeDumper::dumpNode($node);

        return ob_get_clean();
    }
}
