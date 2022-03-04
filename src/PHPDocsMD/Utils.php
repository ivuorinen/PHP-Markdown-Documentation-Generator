<?php

namespace PHPDocsMD;

/**
 * Utilities.
 *
 * @package PHPDocsMD
 */
class Utils
{
    public static array $nativeTypes = [
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

    public static function isNativeType(string $type = ''): bool
    {
        $type = \strtolower(trim($type));
        $type = trim($type, '\\');
        return \in_array($type, self::$nativeTypes, true);
    }

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
        foreach ($parts as $p) {
            if (self::shouldPrefixWithNamespace($p)) {
                $p = self::sanitizeClassName('\\' . trim($currentNameSpace, '\\') . '\\' . $p);
            } elseif (self::isClassReference($p)) {
                $p = self::sanitizeClassName($p);
            }
            $parts[$p] = $p;
        }

        $parts = \array_unique($parts, SORT_NATURAL);

        return implode(' | ', $parts);
    }

    private static function shouldPrefixWithNameSpace(string $typeDeclaration): bool
    {
        return strpos($typeDeclaration, '\\') !== 0 && self::isClassReference($typeDeclaration);
    }

    public static function isClassReference(string $typeDeclaration): bool
    {
        $sanitizedTypeDeclaration = rtrim(trim(strtolower($typeDeclaration)), '[]');

        return !in_array($sanitizedTypeDeclaration, self::$nativeTypes, true) &&
            strpos($typeDeclaration, ' ') === false;
    }

    public static function sanitizeClassName(string $name): string
    {
        return '\\' . trim($name, ' \\');
    }

    public static function isNativeClassReference(string $typeDeclaration): bool
    {
        $sanitizedType = str_replace('[]', '', $typeDeclaration);
        if (class_exists($sanitizedType, false) && self::isClassReference($typeDeclaration)) {
            $reflectionClass = new \ReflectionClass($sanitizedType);

            return !$reflectionClass->getFileName();
        }

        return false;
    }

    private static function getSanitizedTypeDeclaration(string $typeDeclaration): string
    {
        return strtolower(rtrim(trim($typeDeclaration), '[]'));
    }
}
