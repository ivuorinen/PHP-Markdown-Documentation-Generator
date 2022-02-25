<?php

namespace PHPDocsMD;

/**
 * Class containing information about a function/class that's being made
 * available via a comment block
 *
 * @package PHPDocsMD
 */
class DocInfo
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = array_merge([
            'return'      => '',
            'params'      => [],
            'description' => '',
            'example'     => false,
            'deprecated'  => false,
            'see'         => [],
        ], $data);
    }

    public function getReturnType(): string
    {
        return $this->data['return'];
    }

    public function getParameters(): array
    {
        return $this->data['params'];
    }

    public function getParameterInfo(string $name): array
    {
        return $this->data['params'][ $name ] ?? [];
    }

    public function getExample(): string
    {
        return $this->data['example'];
    }

    public function getDescription(): string
    {
        return $this->data['description'];
    }

    public function getDeprecationMessage(): string
    {
        return $this->data['deprecated'];
    }

    public function getSee(): array
    {
        return $this->data['see'];
    }

    public function shouldInheritDoc(): bool
    {
        return isset($this->data['inheritDoc']) || isset($this->data['inheritdoc']);
    }

    public function shouldBeIgnored(): bool
    {
        return isset($this->data['ignore']);
    }

    public function isInternal(): bool
    {
        return isset($this->data['internal']);
    }
}
