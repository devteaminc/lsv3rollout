<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter;

/**
 * Class AdditionalFieldMapper
 * @package Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter
 */
class AdditionalFieldMapper
{
    const ES_DATA_TYPE_STRING = 'string';
    const ES_DATA_TYPE_FLOAT = 'float';
    const ES_DATA_TYPE_INT = 'integer';
    const ES_DATA_TYPE_DATE = 'date';

    /** @deprecated */
    const ES_DATA_TYPE_ARRAY = 'array';

    /**
     * @var array
     */
    private $fields = [];

    /**
     * AdditionalFieldMapper constructor.
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllAttributesTypes($subject, array $result)
    {
        foreach ($this->fields as $fieldName => $fieldType) {
            if (empty($fieldName)) {
                continue;
            }
            if ($this->isValidFieldType($fieldType)) {
                $result[$fieldName] = ['type' => $fieldType];
            }
        }

        return $result;
    }

    /**
     * @param $fieldType
     * @return bool
     */
    private function isValidFieldType($fieldType)
    {
        switch ($fieldType) {
            case self::ES_DATA_TYPE_STRING:
            case self::ES_DATA_TYPE_DATE:
            case self::ES_DATA_TYPE_INT:
            case self::ES_DATA_TYPE_FLOAT:
                break;
            default: $fieldType = false; break;
        }
        return $fieldType;
    }
}
