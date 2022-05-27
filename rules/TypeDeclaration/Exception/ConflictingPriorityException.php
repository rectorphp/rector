<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Exception;

use Exception;
use Rector\TypeDeclaration\Contract\PriorityAwareInterface;
final class ConflictingPriorityException extends \Exception
{
    public function __construct(\Rector\TypeDeclaration\Contract\PriorityAwareInterface $firstPriorityAwareTypeInferer, \Rector\TypeDeclaration\Contract\PriorityAwareInterface $secondPriorityAwareTypeInferer)
    {
        $message = \sprintf('There are 2 type inferers with %d priority:%s- %s%s- %s.%sChange value in "getPriority()" method in one of them to different value', $firstPriorityAwareTypeInferer->getPriority(), \PHP_EOL, \get_class($firstPriorityAwareTypeInferer), \PHP_EOL, \get_class($secondPriorityAwareTypeInferer), \PHP_EOL);
        parent::__construct($message);
    }
}
