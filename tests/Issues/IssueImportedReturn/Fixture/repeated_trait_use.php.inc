<?php

namespace Rector\Core\Tests\Issues\IssueImportedReturn\Fixture;

use Rector\Core\Tests\Issues\IssueImportedReturn\Source\SomeTrait;

final class RepeatedTraitUse
{
    use SomeTrait;
}
