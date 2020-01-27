<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

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
    public static function setParamFirst(array $paramFirst)
    {
        self::$paramFirst = $paramFirst;
    }
    /**
     * @inheritDoc
     */
    public static function setParamSecond(array $paramSecond)
    {
        self::$paramSecond = $paramSecond;
    }
    /**
     * {@inheritdoc}
     */
    public static function setParamThird(array $paramThird)
    {
        self::$paramThird = $paramThird;
    }
    /**
     * {@inheritDoc}
     */
    public static function setParamFourth(array $paramFourth)
    {
        self::$paramFourth = $paramFourth;
    }
}
