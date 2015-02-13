<?php
namespace PHPDocsMD;


/**
 * Class that can create a markdown-formatted table describing class functions
 * referred to via FunctionEntity objects
 *
 * @package PHPDocsMD
 */
class MDTableGenerator {

    /**
     * @var string
     */
    private $markdown = '';

    /**
     *
     */
    function openTable()
    {
        $this->markdown = ''; // Clear table
        $this->add('| Visibility | Function |');
        $this->add('|:-----------|:---------|');
    }

    /**
     * Generates a markdown formatted table row with information about given function. Then adds the
     * row to the table and returns the markdown formatted string
     * @param FunctionEntity $func
     * @return string
     */
    function addFunc(FunctionEntity $func)
    {
        $str = '<strong>';

        if( $func->isAbstract() )
            $str .= 'abstract ';

        $str .=  $func->getName().'(';

        if( $func->hasParams() ) {
            $params = array();
            foreach($func->getParams() as $param) {
                $paramStr = '<em>'.$param->getType().'</em> <strong>'.$param->getName();
                if( $param->getDefault() ) {
                    $paramStr .= '='.$param->getDefault();
                }
                $paramStr .= '</strong>';
                $params[] = $paramStr;
            }
            $str .= '</strong>'.implode(', ', $params) .')';
        } else {
            $str .= ')';
        }

        $str .= '</strong> : <em>'.$func->getReturnType().'</em>';

        if( $func->isDeprecated() ) {
            $str = '<strike>'.$str.'</strike>';
            $str .= '<br /><em>DEPRECATED - '.$func->getDeprecationMessage().'</em>';
        } elseif( $func->getDescription() ) {
            $str .= '<br /><em>'.$func->getDescription().'</em>';
        }

        $str = str_replace(array('</strong><strong>', '</strong></strong> '), array('','</strong>'), trim($str));

        $firstCol =  $func->getVisibility() . ($func->isStatic() ? ' static':'');
        $markDown = '| '.$firstCol.' | '.$str.' |';

        $this->add($markDown);
        return $markDown;
    }

    /**
     * @return string
     */
    function getTable()
    {
        return trim($this->markdown);
    }

    /**
     * @param $str
     */
    private function add($str)
    {
        $this->markdown .= $str .PHP_EOL;
    }
}