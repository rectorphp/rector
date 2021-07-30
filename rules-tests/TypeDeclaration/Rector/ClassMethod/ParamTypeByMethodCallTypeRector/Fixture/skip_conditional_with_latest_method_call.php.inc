<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Fixture;

use Nette\Utils\Strings;

final class SkipConditionalWithLatestMethodCall
{
    public function run($value)
    {
        if (\is_array($value)) {
            return $this->removeQuotesFromArray($value);
        }

        if (! is_string($value)) {
            return $value;
        }

        $matches = Strings::match($value, 'some_');
        if ($matches === null) {
            return $value;
        }

        return $matches['content'];
    }

    private function removeQuotesFromArray(array $value)
    {
        return $value;
    }
}
