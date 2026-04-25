<?php
declare(strict_types=1);

namespace Vaibhav\SupplierShipping\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class AddSupplierAttribute implements DataPatchInterface, PatchVersionInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;
    private EavSetupFactory $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup   = $moduleDataSetup;
        $this->eavSetupFactory   = $eavSetupFactory;
    }

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create([
            'setup' => $this->moduleDataSetup
        ]);

        // Prevent duplicate creation
        if (!$eavSetup->getAttributeId(Product::ENTITY, 'supplier')) {

            $eavSetup->addAttribute(
                Product::ENTITY,
                'supplier',
                [
                    'type'                       => 'int',
                    'label'                      => 'Supplier',
                    'input'                      => 'select',
                    'source'                     => '',
                    'required'                   => false,
                    'sort_order'                 => 100,
                    'global'                     => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible'                    => true,
                    'user_defined'               => true,
                    'group'                      => 'General',

                    // Admin grid visibility
                    'is_used_in_grid'            => true,
                    'is_visible_in_grid'         => true,
                    'is_filterable_in_grid'      => true,

                    // Dropdown options
                    'option' => [
                        'values' => [
                            'ABC',
                            'XYZ'
                        ]
                    ],
                ]
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getVersion(): string
    {
        return '1.0.1';
    }
}