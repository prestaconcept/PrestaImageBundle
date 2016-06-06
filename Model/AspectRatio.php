<?php

namespace Presta\ImageBundle\Model;

/**
 * @author Benoit Jouhaud <bjouhaud@prestaconcept.net>
 */
class AspectRatio
{
    /**
     * @var float
     */
    private $value;

    /**
     * @var string
     */
    private $label;

    /**
     * @var bool
     */
    private $checked = false;

    /**
     * @param float $value
     * @param string $label
     * @param bool $checked
     */
    public function __construct($value, $label, $checked = false)
    {
        $this->value = $value;
        $this->label = $label;
        $this->checked = $checked;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function isChecked()
    {
        return $this->checked;
    }
}
