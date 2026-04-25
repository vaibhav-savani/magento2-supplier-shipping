<?php
namespace Vaibhav\SupplierShipping\Model\Carrier;

use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;

class SupplierShipping extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'supplier_shipping';
    protected $_isFixed = true;

    protected $rateResultFactory;
    protected $rateMethodFactory;
    protected $productResource;


    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        ProductResource $productResource,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->productResource = $productResource;
    }

    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {

        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $config = $this->getConfigData('suppliers');
            
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $supplierTotals = [];
        $totalShipping = 0;

        foreach ($request->getAllItems() as $item) {

            if ($item->getProduct()->isVirtual()) {
                continue;
            }

            $productId = $item->getProductId();

            $supplierId = (int)$this->productResource->getAttributeRawValue($productId, 'supplier', 0);


            // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/suppliershipping.log');
            // $logger = new \Zend_Log();
            // $logger->addWriter($writer);
            // $logger->info("logger for verify details");
            // $logger->info(json_encode($item->getProduct()->getData()));
            // $logger->info($supplierId);
            // die;
            
            $rowTotal   = (float)$item->getRowTotal();


            if (!$supplierId) {
                continue;
            }

            $supplierTotals[$supplierId] =
                ($supplierTotals[$supplierId] ?? 0) + $rowTotal;
        }


        if (is_array($config)) {

            foreach ($supplierTotals as $supplierId => $subtotal) {

                foreach ($config as $row) {

                    if (!isset($row['supplier'], $row['charge'], $row['threshold'])) {
                        continue;
                    }


                    if ((int)$row['supplier'] === (int)$supplierId) {

                        $charge = (float)$row['charge'];
                        $threshold = (float)$row['threshold'];


                        if ($subtotal < $threshold) {
                            $totalShipping += $charge;
                        } else {
                        }

                        break;
                    }
                }
            }
        }


        $result = $this->rateResultFactory->create();
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice($totalShipping);
        $method->setCost($totalShipping);

        $result->append($method);

        return $result;
    }

    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}