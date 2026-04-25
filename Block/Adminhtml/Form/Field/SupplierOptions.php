<?php
namespace Vaibhav\SupplierShipping\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Eav\Model\Config as EavConfig;

class SupplierOptions extends Select
{
    protected $eavConfig;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        EavConfig $eavConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->eavConfig = $eavConfig;
    }

    protected function _toHtml()
    {
        if (!$this->getOptions()) {

            $attribute = $this->eavConfig->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'supplier'
            );

            if ($attribute && $attribute->usesSource()) {
                foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                    $this->addOption($option['value'], $option['label']);
                }
            }
        }

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
