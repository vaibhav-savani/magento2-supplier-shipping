<?php
namespace Vaibhav\SupplierShipping\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class Suppliers extends AbstractFieldArray
{
    protected $supplierRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('supplier', [
            'label' => __('Supplier'),
            'renderer' => $this->getSupplierRenderer()
        ]);

        $this->addColumn('charge', [
            'label' => __('Extra Charge'),
            'class' => 'required-entry'
        ]);

        $this->addColumn('threshold', [
            'label' => __('Free Shipping Threshold'),
            'class' => 'required-entry'
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Supplier');
    }

    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];

        $supplier = $row->getData('supplier');
        if ($supplier !== null) {
            $options['option_' . $this->getSupplierRenderer()->calcOptionHash($supplier)]
                = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    protected function getSupplierRenderer()
    {
        if (!$this->supplierRenderer) {
            $this->supplierRenderer = $this->getLayout()->createBlock(
                \Vaibhav\SupplierShipping\Block\Adminhtml\Form\Field\SupplierOptions::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->supplierRenderer;
    }
}