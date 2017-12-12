<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Api\Data;

interface OrderAttributeDataInterface
{
    /**
     * @param string $code
     *
     * @return $this
     */
    public function setAttributeCode($code);

    /**
     * @return string
     */
    public function getAttributeCode();

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label);

    /**
     * @return string|null
     */
    public function getLabel();

    /**
     * @param string|int|array|null $value
     *
     * @return $this
     */
    public function setValue($value);

    /**
     * @return string|int|array|null
     */
    public function getValue();

    /**
     * @param string|int|null $value
     *
     * @return $this
     */
    public function setValueOutput($value);

    /**
     * @return string|int|null
     */
    public function getValueOutput();

    /**
     * @since 2.2.0
     * @param string|int|null $value
     *
     * @return $this
     */
    public function setValueOutputAdmin($value);

    /**
     * @since 2.2.0
     * @return string|int|null
     */
    public function getValueOutputAdmin();
}
