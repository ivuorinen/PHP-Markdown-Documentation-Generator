<?php

namespace PHPDocsMD\Entities;

/**
 * Object describing a function
 *
 * @package PHPDocsMD
 */
class FunctionEntity extends CodeEntity
{
    /**
     * @var \PHPDocsMD\Entities\ParamEntity[]
     */
    private array $params = [];
    private string $returnType = 'void';
    private string $visibility = 'public';
    private bool $abstract = false;
    private bool $isStatic = false;
    private string $class = '';
    private array $see = [];
    private bool $isReturningNativeClass = false;

    public function isStatic(bool $toggle = null): bool
    {
        return $toggle === null
            ? $this->isStatic
            : ($this->isStatic = (bool) $toggle);
    }

    public function isAbstract(bool $toggle = null): bool
    {
        return $toggle === null
            ? $this->abstract
            : ($this->abstract = (bool) $toggle);
    }

    public function isReturningNativeClass(bool $toggle = null): bool
    {
        return $toggle === null
            ? $this->isReturningNativeClass
            : ($this->isReturningNativeClass = (bool) $toggle);
    }

    public function hasParams(): bool
    {
        return ! empty($this->params);
    }

    /**
     * @return \PHPDocsMD\Entities\ParamEntity[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param \PHPDocsMD\Entities\ParamEntity[] $params
     */
    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function getReturnType(): string
    {
        return $this->returnType;
    }

    public function setReturnType(string $returnType): self
    {
        $this->returnType = $returnType;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getSee(): array
    {
        return $this->see;
    }

    public function setSee(array $see): self
    {
        $this->see = $see;

        return $this;
    }
}
