<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Exception;

use Exception;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;

final class ConflictingPriorityException extends Exception
{
    public function __construct(
        PropertyTypeInfererInterface $firstPropertyTypeInfererInterface,
        PropertyTypeInfererInterface $secondPropertyTypeInfererInterface
    ) {
        $message = sprintf(
            'There are 2 property type inferers with %d priority:%s- %s%s- %s.%sChange value in "getPriority()" method in one of them to different value',
            $firstPropertyTypeInfererInterface->getPriority(),
            PHP_EOL,
            get_class($firstPropertyTypeInfererInterface),
            PHP_EOL,
            get_class($secondPropertyTypeInfererInterface),
            PHP_EOL
        );

        parent::__construct($message);
    }
}
