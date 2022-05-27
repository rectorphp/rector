<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\NodeFactory;

use PhpParser\Comment;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class CommandArrayItemFactory
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param array<string, mixed> $commands
     * @return ArrayItem[]
     */
    public function createArrayItems(array $commands) : array
    {
        $arrayItems = [];
        foreach ($commands as $commandName => $command) {
            $commandArray = new \PhpParser\Node\Expr\Array_();
            $value = $this->nodeFactory->createClassConstReference($command);
            $key = new \PhpParser\Node\Scalar\String_('class');
            $commandArray->items[] = new \PhpParser\Node\Expr\ArrayItem($value, $key, \false, [\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS => [new \PhpParser\Comment(\PHP_EOL)]]);
            $arrayItems[] = new \PhpParser\Node\Expr\ArrayItem($commandArray, new \PhpParser\Node\Scalar\String_($commandName), \false, [\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS => [new \PhpParser\Comment(\PHP_EOL)]]);
        }
        return $arrayItems;
    }
}
