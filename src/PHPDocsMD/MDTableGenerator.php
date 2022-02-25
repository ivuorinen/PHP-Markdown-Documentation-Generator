<?php

namespace PHPDocsMD;

use PHPDocsMD\Entities\FunctionEntity;

/**
 * Class that can create a markdown-formatted table describing class functions
 * referred to via FunctionEntity objects
 *
 * @example
 * <code>
 *  <?php
 *      $generator = new PHPDocsMD\MDTableGenerator();
 *      $generator->openTable();
 *      foreach($classEntity->getFunctions() as $func) {
 *              $generator->addFunc( $func );
 *      }
 *      echo $generator->getTable();
 * </code>
 *
 * @package PHPDocsMD
 */
class MDTableGenerator implements TableGenerator
{
    private string $fullClassName = '';
    private string $markdown = '';
    private array $examples = [];
    private bool $appendExamples = true;
    private bool $declareAbstraction = true;

    /**
     * All example comments found while generating the table will be
     * appended to the end of the table. Set $toggle to false to
     * prevent this behaviour
     *
     * @param bool $toggle
     */
    public function appendExamplesToEndOfTable(bool $toggle): void
    {
        $this->appendExamples = (bool) $toggle;
    }

    /**
     * Begin generating a new markdown-formatted table
     */
    public function openTable(): void
    {
        $this->examples           = [];
        $this->markdown           = ''; // Clear table
        $this->declareAbstraction = true;
        $this->add('| Visibility | Function |');
        $this->add('|:-----------|:---------|');
    }

    private function add(string $str): void
    {
        $this->markdown .= $str . PHP_EOL;
    }

    /**
     * Toggle whether or not methods being abstract (or part of an interface)
     * should be declared as abstract in the table
     *
     * @param bool $toggle
     */
    public function doDeclareAbstraction(bool $toggle)
    {
        $this->declareAbstraction = (bool) $toggle;
    }

    /**
     * Generates a markdown formatted table row with information about given function. Then adds the
     * row to the table and returns the markdown formatted string.
     *
     * @param FunctionEntity $func
     * @param bool           $see
     *
     * @return string
     */
    public function addFunc(FunctionEntity $func, $includeSee = false): string
    {
        $this->fullClassName = $func->getClass();

        $str = '<strong>';

        if ($this->declareAbstraction && $func->isAbstract()) {
            $str .= 'abstract ';
        }

        $str .= $func->getName() . '(';

        if ($func->hasParams()) {
            $params = [];
            foreach ($func->getParams() as $param) {
                $paramStr = '<em>' . $param->getType() . '</em> <strong>' . $param->getName();
                if ($param->getDefault()) {
                    $paramStr .= '=' . $param->getDefault();
                }
                $paramStr .= '</strong>';
                $params[] = $paramStr;
            }
            $str .= '</strong>' . implode(', ', $params) . ')';
        } else {
            $str .= ')';
        }

        $str .= '</strong> : <em>' . $func->getReturnType() . '</em>';

        if ($func->isDeprecated()) {
            $str = '<strike>' . $str . '</strike>';
            $str .= '<br /><em>DEPRECATED - ' . $func->getDeprecationMessage() . '</em>';
        } elseif ($func->getDescription()) {
            $str .= '<br /><em>' . $func->getDescription() . '</em>';
        }
        if ($func->getSee() && $includeSee) {
            $str .= '<br /><em>&nbsp;&nbsp;&nbsp;&nbsp;See: ' .
                    implode(', ', $func->getSee()) . '</em>';
        }

        $str = str_replace(
            [ '</strong><strong>', '</strong></strong> ' ],
            [ '', '</strong>' ],
            trim($str)
        );

        if ($func->getExample()) {
            $this->examples[ $func->getName() ] = $func->getExample();
        }

        $firstCol = $func->getVisibility() . ($func->isStatic() ? ' static' : '');
        $markDown = '| ' . $firstCol . ' | ' . $str . ' |';

        $this->add($markDown);

        return $markDown;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        $tbl = trim($this->markdown);
        if ($this->appendExamples && ! empty($this->examples)) {
            $className = Utils::getClassBaseName($this->fullClassName);
            foreach ($this->examples as $funcName => $example) {
                $tbl .= sprintf(
                    "\n###### Examples of %s::%s()\n%s",
                    $className,
                    $funcName,
                    self::formatExampleComment($example)
                );
            }
        }

        return $tbl;
    }

    /**
     * Create a markdown-formatted code view out of an example comment
     *
     * @param string $example
     *
     * @return string
     */
    public static function formatExampleComment(string $example): string
    {
        // Remove possible code tag
        $example = self::stripCodeTags($example);

        if (preg_match('/(\n       )/', $example)) {
            $example = preg_replace('/(\n       )/', "\n", $example);
        } elseif (preg_match('/(\n    )/', $example)) {
            $example = preg_replace('/(\n    )/', "\n", $example);
        } else {
            $example = preg_replace('/(\n   )/', "\n", $example);
        }
        $type = '';

        // A very naive analysis of the programming language used in the comment
        if (strpos($example, '<?php') !== false) {
            $type = 'php';
        } elseif (strpos($example, 'var ') !== false && strpos($example, '</') === false) {
            $type = 'js';
        }

        return sprintf("```%s\n%s\n```", $type, trim($example));
    }

    /**
     * @param $example
     *
     * @return mixed
     */
    private static function stripCodeTags($example)
    {
        if (strpos($example, '<code') !== false) {
            $parts   = array_slice(explode('</code>', $example), - 2);
            $example = current($parts);
            $parts   = array_slice(explode('<code>', $example), 1);
            $example = current($parts);
        }

        return $example;
    }
}
