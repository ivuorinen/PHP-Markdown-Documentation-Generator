<?php

namespace Acme;

/**
 * Interface ExampleInterface
 *
 * @package Acme
 * @ignore
 */
interface ExampleInterface
{

    /**
     * @param string $arg
     *
     * @return \stdClass
     */
    public function func($arg = 'a');
}

interface InterfaceReferringToImportedClass
{

    /**
     * @return CLI
     */
    public function theFunc();

    /**
     * @return CLI[]
     */
    public function funcReturningArr();
}

/**
 * This is a description
 * of this class
 *
 * @package Acme
 */
abstract class ExampleClass implements \Reflector
{

    /**
     * Description of a*a
     *
     * @param       $arg
     * @param array $arr
     * @param int   $bool
     */
    public function funcA($arg, array $arr, $bool = 10)
    {
    }

    /**
     * Description of b
     *
     * @param int   $arg
     * @param array $arr
     * @param int   $bool
     *
     * @example
     *      <code>
     *      <?php
     *      $lorem = 'te';
     *      $ipsum = 'dolor';
     *      </code>
     *
     */
    public function funcB($arg, array $arr, $bool = 10)
    {
    }

    public function funcD($arg, $arr = [], ExampleInterface $depr = null, \stdClass $class = null)
    {
    }

    public function getFunc()
    {
    }

    public function hasFunc()
    {
    }

    abstract public function isFunc();

    /**
     * @ignore
     */
    public function someFunc()
    {
    }

    /**
     * Description of c
     *
     * @param       $arg
     * @param array $arr
     * @param int   $bool
     *
     * @return \Acme\ExampleClass
     * @deprecated This one is deprecated
     */
    protected function funcC($arg, array $arr, $bool = 10)
    {
    }

    private function privFunc()
    {
    }
}

/**
 * @deprecated This one is deprecated
 *
 * Lorem te ipsum
 *
 * @package    Acme
 */
class ExampleClassDepr
{
}

class SomeClass
{

    /**
     * @return int
     */
    public function aMethod()
    {
    }
}

class ClassImplementingInterface extends SomeClass implements ExampleInterface
{
    /**
     * @inheritdoc
     */
    public function func($arg = 'a')
    {
    }

    /**
     * @inheritDoc
     */
    public function aMethod()
    {
    }

    /**
     * @return \FilesystemIterator
     */
    public function methodReturnNativeClass()
    {
    }

    /**
     * @return \FilesystemIterator[]
     */
    public function methodReturningArrayNativeClass()
    {
    }
}

use PHPDocsMD\Console\CLI;

class ClassWithStaticFunc
{

    /**
     * @return float
     */
    public static function someStaticFunc()
    {
    }
}
