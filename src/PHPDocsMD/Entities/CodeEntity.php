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

    /**
     * @param bool|null $toggle
     *
     * @return void|bool
     */
    public function isDeprecated(bool $toggle = null)
    {
        if ($toggle === null) {
            return $this->isDeprecated;
        } else {
            return $this->isDeprecated = (bool) $toggle;
        }
    }

    /**
     * @param bool|null $toggle
     *
     * @return bool|null
     */
    public function isInternal(bool $toggle = null)
    {
        if ($toggle === null) {
            return $this->isInternal;
        } else {
            return $this->isInternal = (bool) $toggle;
        }
    }

    /**
     * @return string
     */
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
