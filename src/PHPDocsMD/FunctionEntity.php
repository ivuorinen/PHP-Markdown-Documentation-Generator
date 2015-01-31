<?php
namespace PHPDocsMD;


/**
 * Object describing a function
 * @package PHPDocsMD
 */
class FunctionEntity extends CodeEntity {

    /**
     * @var \PHPDocsMD\ParamEntity[]
     */
    private $params = array();

    /**
     * @var string
     */
    private $returnType = 'void';

    /**
     * @var string
     */
    private $visibility = 'public';

    /**
     * @param \PHPDocsMD\ParamEntity[] $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @return \PHPDocsMD\ParamEntity[]
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $returnType
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
    }

    /**
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

}

