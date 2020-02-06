<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Rector\Include_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://previo.cz/ for sponsoring this rule
 *
 * @see \Rector\ZendToSymfony\Tests\Rector\Include_\RemoveAutoloadingIncludeRector\RemoveAutoloadingIncludeRectorTest
 */
final class RemoveAutoloadingIncludeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Remove include/require statements, that supply autoloading (PSR-4 composer autolaod is going to be used instead)',
            [
                new CodeSample(
                    <<<'PHP'
include 'SomeFile.php';
require_once 'AnotherFile.php';

$values = require_once 'values.txt';
PHP
                    ,
                    <<<'PHP'
$values = require_once 'values.txt';
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
        return [Include_::class];
    }

    /**
     * @param Include_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Return_) {
            return null;
        }

        if (! $this->isPhpFileIncluded($node)) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    private function isPhpFileIncluded(Include_ $include): bool
    {
        $requiredValue = $this->getValue($include->expr);

        return Strings::endsWith($requiredValue, '.php');
    }
}
