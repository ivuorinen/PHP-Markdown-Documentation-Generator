<?php

namespace PHPDocsMD;

/**
 * Utilities.
 *
 * @package PHPDocsMD
 */
class Utils
{
    public static function getClassBaseName(string $fullClassName): string
    {
        $parts = explode('\\', trim($fullClassName));

        return end($parts);
    }

    public static function sanitizeDeclaration(
        string $typeDeclaration,
        string $currentNameSpace,
        string $delimiter = '|'
    ): string {
        $parts = explode($delimiter, $typeDeclaration);
        foreach ($parts as $i => $p) {
            if (self::shouldPrefixWithNamespace($p)) {
                $p = self::sanitizeClassName('\\' . trim($currentNameSpace, '\\') . '\\' . $p);
            } elseif (self::isClassReference($p)) {
                $p = self::sanitizeClassName($p);
            }
            $parts[ $i ] = $p;
        }

        return implode('/', $parts);
    }

    private static function shouldPrefixWithNameSpace(string $typeDeclaration): bool
    {
        return strpos($typeDeclaration, '\\') !== 0 && self::isClassReference($typeDeclaration);
    }

    public static function isClassReference(string $typeDeclaration): bool
    {
        $natives                  = [
            'mixed',
            'string',
            'int',
            'float',
            'integer',
            'number',
            'bool',
            'boolean',
            'object',
            'false',
            'true',
            'null',
            'array',
            'void',
            'callable',
        ];
        $sanitizedTypeDeclaration = rtrim(trim(strtolower($typeDeclaration)), '[]');

        return ! in_array($sanitizedTypeDeclaration, $natives) &&
               strpos($typeDeclaration, ' ') === false;
    }

    public static function sanitizeClassName(string $name): string
    {
        return '\\' . trim($name, ' \\');
    }

    public static function isNativeClassReference($typeDeclaration): bool
    {
        $sanitizedType = str_replace('[]', '', $typeDeclaration);
        if (Utils::isClassReference($typeDeclaration) && class_exists($sanitizedType, false)) {
            $reflectionClass = new \ReflectionClass($sanitizedType);

            return ! $reflectionClass->getFileName();
        }

        return false;
    }
}
