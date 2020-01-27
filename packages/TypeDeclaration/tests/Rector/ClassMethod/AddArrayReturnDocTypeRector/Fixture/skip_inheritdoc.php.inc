<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class SkipInheritdocPhpdoc
{
    /**
     * @var int[]
     */
    private static $paramFirst;
    /**
     * @var string[]
     */
    private static $paramSecond;
    /**
     * @var int[]
     */
    private static $paramThird;
    /**
     * @var string[]
     */
    private static $paramFourth;

    /**
     * @inheritdoc
     */
    public static function getParamFirst(): array
    {
        return self::$paramFirst;
    }
    /**
     * @inheritDoc
     */
    public static function getParamSecond(): array
    {
        return self::$paramSecond;
    }
    /**
     * {@inheritdoc}
     */
    public static function getParamThird(): array
    {
        return self::$paramThird;
    }
    /**
     * {@inheritDoc}
     */
    public static function getParamFourth(): array
    {
        return self::$paramFourth;
    }
}
