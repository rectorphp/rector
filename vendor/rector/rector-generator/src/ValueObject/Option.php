<?php

declare (strict_types=1);
namespace Rector\RectorGenerator\ValueObject;

/**
 * @api
 */
final class Option
{
    /**
     * @var string
     */
    public const PACKAGE = 'package';
    /**
     * @var string
     */
    public const NAME = 'name';
    /**
     * @var string
     */
    public const NODE_TYPES = 'node_types';
    /**
     * @var string
     */
    public const DESCRIPTION = 'description';
    /**
     * @var string
     */
    public const CODE_BEFORE = 'code_before';
    /**
     * @var string
     */
    public const CODE_AFTER = 'code_after';
    /**
     * @var string
     */
    public const CONFIGURATION = 'configuration';
    /**
     * @var string
     */
    public const RESOURCES = 'resources';
    /**
     * @var string
     */
    public const SET_FILE_PATH = 'set_file_path';
}
