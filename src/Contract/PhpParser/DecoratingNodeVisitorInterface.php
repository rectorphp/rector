<?php

declare (strict_types=1);
namespace Rector\Contract\PhpParser;

use PhpParser\NodeVisitor;
/**
 * This interface can be used to tag load node visitor, that will run on parsing files to nodes.
 * It can be used e.g. to decorate nodes with extra attributes,
 * that can be used later in rules or services.
 */
interface DecoratingNodeVisitorInterface extends NodeVisitor
{
}
