<?php declare(strict_types=1);

namespace Rector\Architecture\Analyzer;

final class SetterToConstructorAnalyzer
{
    public function analyzeType(string $type): void
    {
        // 1. find new and traverse bottom to detect all the required arguments
        // 2. get those class methods and their type + assigns
        // 3. remove them
        // 4. move them too __construct() arguments
        // 5. replace new _ XY with (A, B, C)
    }
}
