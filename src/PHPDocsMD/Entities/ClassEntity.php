<?php

namespace PHPDocsMD\Entities;

use PHPDocsMD\Utils;

/**
 * Object describing a class or an interface
 *
 * @package PHPDocsMD
 */
class ClassEntity extends CodeEntity
{
    /**
     * @var \PHPDocsMD\Entities\FunctionEntity[]
     */
    private array $functions = [];
    private bool $isInterface = false;
    private bool $abstract = false;
    private bool $hasIgnoreTag = false;
    private bool $hasInternalTag = false;
    private string $extends = '';
    private array $interfaces = [];
    private array $see = [];
    private bool $isNative;

    public function hasIgnoreTag(bool $toggle = null): bool
    {
        return $toggle === null
            ? $this->hasIgnoreTag
            : ($this->hasIgnoreTag = (bool) $toggle);
    }

    public function hasInternalTag(bool $toggle = null): bool
    {
        return $toggle === null
            ? $this->hasInternalTag
            : ($this->hasInternalTag = (bool) $toggle);
    }

    public function isNative(bool $toggle = null): bool
    {
        return $toggle === null
            ? $this->isNative
            : ($this->isNative = (bool) $toggle);
    }

    public function getExtends(): string
    {
        return $this->extends;
    }

    public function setExtends(string $extends): self
    {
        $this->extends = Utils::sanitizeClassName($extends);

        return $this;
    }

    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    public function setInterfaces(array $implements): self
    {
        $this->interfaces = [];
        foreach ($implements as $interface) {
            $this->interfaces[] = Utils::sanitizeClassName($interface);
        }

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

    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @param \PHPDocsMD\Entities\FunctionEntity[] $functions
     */
    public function setFunctions(array $functions): self
    {
        $this->functions = $functions;

        return $this;
    }

    public function setName(string $name): self
    {
        parent::setName(Utils::sanitizeClassName($name));

        return $this;
    }

    /**
     * Check whether this object is referring to given class name or object instance
     *
     * @param string|object $class
     *
     * @return bool
     */
    public function isSame($class): bool
    {
        $className = is_object($class) ? get_class($class) : $class;

        return Utils::sanitizeClassName($className) === $this->getName();
    }

    /**
     * Generates an anchor link out of the generated title (see generateTitle)
     *
     * @return string
     */
    public function generateAnchor(): string
    {
        $title = $this->generateTitle();

        return strtolower(
            str_replace(
                [ ':', ' ', '\\', '(', ')' ],
                [ '', '-', '', '', '' ],
                $title
            )
        );
    }

    /**
     * Generate a title describing the class this object is referring to
     *
     * @param string $format
     *
     * @return string
     */
    public function generateTitle(string $format = '%label%: %name% %extra%'): string
    {
        $translate = [
            '%label%' => $this->isInterface() ? 'Interface' : 'Class',
            '%name%'  => substr_count($this->getName(), '\\') === 1
                ? substr($this->getName(), 1)
                : $this->getName(),
            '%extra%' => '',
        ];

        if (strpos($format, '%label%') === false) {
            if ($this->isInterface()) {
                $translate['%extra%'] = '(interface)';
            } elseif ($this->isAbstract()) {
                $translate['%extra%'] = '(abstract)';
            }
        } else {
            $translate['%extra%'] = $this->isAbstract() && ! $this->isInterface() ? '(abstract)' : '';
        }

        return trim(strtr($format, $translate));
    }

    public function isInterface(bool $toggle = null): bool
    {
        return $toggle === null
            ? $this->isInterface
            : ($this->isInterface = (bool) $toggle);
    }

    public function isAbstract(bool $toggle = null): bool
    {
        return $toggle === null
            ? $this->abstract
            : ($this->abstract = (bool) $toggle);
    }
}
