<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use Rector\Node\Attribute;
use Rector\Php\TypeAnalyzer;
use Symplify\BetterPhpDocParser\PhpDocParser\PhpDocInfo;

final class PhpDocInfoFqnTypeDecorator // official interface, collected by compiler pass in BetterPhpDocParser
{
    public function decorate(PhpDocInfo $phpDocInfo)
    {
        // iterate all the nodes and make types FQN

        return $phpDocInfo;
    }
}
