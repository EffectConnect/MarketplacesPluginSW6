<?php
namespace EffectConnect\Marketplaces\Core\ExportQueue\Data;

use EffectConnect\Marketplaces\Object\OrderLineDeliveryData;

class OrderExportQueueData extends ExportQueueData
{
    /**
     * @var OrderLineDeliveryData[]
     */
    protected $lineDeliveries;

    /**
     * @return OrderLineDeliveryData[]
     */
    public function getLineDeliveries(): array
    {
        return $this->lineDeliveries;
    }

    /**
     * @param OrderLineDeliveryData[] $lineDeliveries
     * @return OrderExportQueueData
     */
    public function setLineDeliveries(array $lineDeliveries): OrderExportQueueData
    {
        $this->lineDeliveries = $lineDeliveries;
        return $this;
    }

    public static function fromArray(array $data): OrderExportQueueData
    {
        $lineDeliveries = [];
        foreach($data['lineDeliveries'] as $deliveryData) {
            $lineDeliveries[] = new OrderLineDeliveryData(
                $deliveryData['effectConnectLineId'],
                $deliveryData['effectConnectId'],
                $deliveryData['trackingNumber'],
                $deliveryData['carrier']
            );
        }
        return (new OrderExportQueueData())->setLineDeliveries($lineDeliveries);
    }

    private function deliveryToData(OrderLineDeliveryData $data): array
    {
        return [
            'effectConnectLineId' => $data->getEffectConnectLineId(),
            'effectConnectId'     => $data->getEffectConnectId(),
            'trackingNumber'      => $data->getTrackingNumber(),
            'carrier'             => $data->getCarrier(),
        ];
    }

    public function toArray(): array
    {
        return [
            'lineDeliveries' => array_map(function ($d) {return $this->deliveryToData($d);}, $this->lineDeliveries)
        ];
    }

}