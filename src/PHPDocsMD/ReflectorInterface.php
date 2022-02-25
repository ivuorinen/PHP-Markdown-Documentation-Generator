<?php

namespace PHPDocsMD;

use PHPDocsMD\Entities\ClassEntity;

/**
 * Interface for classes that can compute ClassEntity objects
 *
 * @package PHPDocsMD
 */
interface ReflectorInterface
{
    public function getClassEntity(): ClassEntity;
}
