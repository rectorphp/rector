<?php

declare (strict_types=1);
namespace RectorPrefix20220418\OndraM\CiDetector\Ci;

use RectorPrefix20220418\OndraM\CiDetector\CiDetector;
use RectorPrefix20220418\OndraM\CiDetector\Env;
use RectorPrefix20220418\OndraM\CiDetector\TrinaryLogic;
class Circle extends \RectorPrefix20220418\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20220418\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('CIRCLECI') !== \false;
    }
    public function getCiName() : string
    {
        return \RectorPrefix20220418\OndraM\CiDetector\CiDetector::CI_CIRCLE;
    }
    public function isPullRequest() : \RectorPrefix20220418\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20220418\OndraM\CiDetector\TrinaryLogic::createFromBoolean($this->env->getString('CI_PULL_REQUEST') !== '');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('CIRCLE_BUILD_NUM');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('CIRCLE_BUILD_URL');
    }
    public function getCommit() : string
    {
        return $this->env->getString('CIRCLE_SHA1');
    }
    public function getBranch() : string
    {
        return $this->env->getString('CIRCLE_BRANCH');
    }
    public function getTargetBranch() : string
    {
        return '';
        // unsupported
    }
    public function getRepositoryName() : string
    {
        return \sprintf('%s/%s', $this->env->getString('CIRCLE_PROJECT_USERNAME'), $this->env->getString('CIRCLE_PROJECT_REPONAME'));
    }
    public function getRepositoryUrl() : string
    {
        return $this->env->getString('CIRCLE_REPOSITORY_URL');
    }
}
