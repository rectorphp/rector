<?php

declare (strict_types=1);
namespace RectorPrefix202602;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony73\Rector\Class_\AddVoteArgumentToVoteOnAttributeRector;
use Rector\Symfony\Symfony73\Rector\Class_\AuthorizationCheckerToAccessDecisionManagerInVoterRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([AddVoteArgumentToVoteOnAttributeRector::class, AuthorizationCheckerToAccessDecisionManagerInVoterRector::class]);
};
