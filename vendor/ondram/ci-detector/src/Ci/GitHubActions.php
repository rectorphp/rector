<?php

declare (strict_types=1);
namespace RectorPrefix20220501\OndraM\CiDetector\Ci;

use RectorPrefix20220501\OndraM\CiDetector\CiDetector;
use RectorPrefix20220501\OndraM\CiDetector\Env;
use RectorPrefix20220501\OndraM\CiDetector\TrinaryLogic;
class GitHubActions extends \RectorPrefix20220501\OndraM\CiDetector\Ci\AbstractCi
{
    public const GITHUB_BASE_URL = 'https://github.com';
    public static function isDetected(\RectorPrefix20220501\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('GITHUB_ACTIONS') !== \false;
    }
    public function getCiName() : string
    {
        return \RectorPrefix20220501\OndraM\CiDetector\CiDetector::CI_GITHUB_ACTIONS;
    }
    public function isPullRequest() : \RectorPrefix20220501\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20220501\OndraM\CiDetector\TrinaryLogic::createFromBoolean($this->env->getString('GITHUB_EVENT_NAME') === 'pull_request');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('GITHUB_RUN_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return \sprintf('%s/%s/commit/%s/checks', self::GITHUB_BASE_URL, $this->env->get('GITHUB_REPOSITORY'), $this->env->get('GITHUB_SHA'));
    }
    public function getCommit() : string
    {
        return $this->env->getString('GITHUB_SHA');
    }
    public function getBranch() : string
    {
        $prBranch = $this->env->getString('GITHUB_HEAD_REF');
        if ($this->isPullRequest()->no() || empty($prBranch)) {
            $gitReference = $this->env->getString('GITHUB_REF');
            return \preg_replace('~^refs/heads/~', '', $gitReference) ?? '';
        }
        return $prBranch;
    }
    public function getTargetBranch() : string
    {
        return $this->env->getString('GITHUB_BASE_REF');
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('GITHUB_REPOSITORY');
    }
    public function getRepositoryUrl() : string
    {
        return \sprintf('%s/%s', self::GITHUB_BASE_URL, $this->env->get('GITHUB_REPOSITORY'));
    }
}
