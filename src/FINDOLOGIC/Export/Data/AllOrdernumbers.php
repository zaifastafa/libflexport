<?php

namespace FINDOLOGIC\Export\Data;

use FINDOLOGIC\Export\Helpers\UsergroupAwareMultiValue;

class AllOrdernumbers extends UsergroupAwareMultiValue
{
    public function __construct()
    {
        parent::__construct('allOrdernumbers', 'ordernumbers', '|');
    }

    /**
     * @inheritdoc
     */
    public function getCsvFragment(array $availableProperties = []): string
    {
        if (array_key_exists('', $this->values)) {
            return implode('|', array_map(function (Ordernumber $ordernumber): string {
                return $ordernumber->getCsvFragment();
            }, $this->values['']));
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function getValueName(): string
    {
        return 'allOrdernumbers';
    }
}
