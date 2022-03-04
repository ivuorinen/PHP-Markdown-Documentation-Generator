<?php

namespace PHPDocsMD\Entities;

use PHPDocsMD\Utils;

/**
 * Object describing a function parameter
 *
 * @package PHPDocsMD
 */
class ParamEntity extends CodeEntity
{
    private string $default = 'false';
    private string $type = 'mixed';

    public function getDefault(): string
    {
        return $this->default;
    }

    public function setDefault(string $default): self
    {
        $this->default = $default;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNativeClassType(): ?string
    {
        foreach (explode('/', $this->type) as $typeDeclaration) {
            if (Utils::isNativeClassReference($typeDeclaration)) {
                return $typeDeclaration;
            }
        }

        return null;
    }
}
