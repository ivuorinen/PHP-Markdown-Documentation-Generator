<?php

namespace PHPDocsMD;

use ReflectionClass;

/**
 * Class that can extract all use statements in a file
 *
 * @package PHPDocsMD
 */
class UseInspector
{
    public function getUseStatements(ReflectionClass $reflectionClass): array
    {
        $classUseStatements = [];
        $classFile = $reflectionClass->getFileName();
        if ($classFile) {
            $classUseStatements = $this->getUseStatementsInFile($classFile);
        }

        return $classUseStatements;
    }

    public function getUseStatementsInFile(string $filePath): array
    {
        return $this->getUseStatementsInString(file_get_contents($filePath));
    }

    /**
     * @param string $content
     *
     * @return string[]
     */
    public function getUseStatementsInString(string $content): array
    {
        $usages = [];
        $chunks = array_slice(preg_split('/use[\s+]/', $content), 1);
        foreach ($chunks as $chunk) {
            $usage = trim(current(explode(';', $chunk)));
            $usages[] = Utils::sanitizeClassName($usage);
        }

        return $usages;
    }
}
