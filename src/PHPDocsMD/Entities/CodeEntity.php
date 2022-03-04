<?php

namespace PHPDocsMD\Entities;

/**
 * Object describing a piece of code
 *
 * @package PHPDocsMD
 */
class CodeEntity
{
    private string $name = '';
    private string $description = '';
    private bool $isDeprecated = false;
    private bool $isInternal = false;
    private string $deprecationMessage = '';
    private string $example = '';
    private array $see = [];

    public function isDeprecated(bool $toggle = null): bool
    {
        return $toggle === null
            ? $this->isDeprecated
            : ($this->isDeprecated = $toggle);
    }

    public function isInternal(bool $toggle = null): ?bool
    {
        return $toggle === null
            ? $this->isInternal
            : ($this->isInternal = $toggle);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDeprecationMessage(): string
    {
        return $this->deprecationMessage;
    }

    public function setDeprecationMessage(string $deprecationMessage): self
    {
        $this->deprecationMessage = $deprecationMessage;

        return $this;
    }

    public function getExample(): string
    {
        return $this->example;
    }

    public function setExample(string $example): self
    {
        $this->example = $example;

        return $this;
    }

    public function getSee(): array
    {
        return $this->see;
    }

    public function setSee(array $see): self
    {
        $this->see = [];
        foreach ($see as $i) {
            $this->see[] = $i;
        }

        return $this;
    }
}
