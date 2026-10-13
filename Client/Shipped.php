<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client;

use Intelipost\Shipping\Model\Shipment;

class Shipped extends AbstractShipment
{
    /**
     * @param $shipment
     * @throws \Exception
     */
    public function shippedRequestBody($shipment)
    {
        $requestBody = $this->prepareRequestBody($shipment);
        $this->sendShippedRequest($this->helper->serializeData($requestBody), $shipment);
        return $this;
    }

    /**
     * @param $requestBody
     * @param $shipment
     * @throws \Exception
     */
    public function sendShippedRequest($requestBody, $shipment)
    {
        $response = $this->helperApi->apiRequest('POST', 'shipment_order/multi/shipped/with_date', $requestBody);
        $result = $this->helper->unserializeData($response);
        $responseStatus = $result['status'];

        if ($responseStatus == \Intelipost\Shipping\Client\Intelipost::RESPONSE_STATUS_ERROR) {
            $this->setError($shipment, $result);
        }

        if ($responseStatus == \Intelipost\Shipping\Client\Intelipost::RESPONSE_STATUS_OK) {
            $incrementId = $shipment->getData('order_increment_id');
            $this->updateShipment($shipment, $responseStatus, Shipment::STATUS_SHIPPED);
            $status = $this->helper->getConfig('shipped_status', 'order_status', 'intelipost_push');
            $comment = __('Order shipped to Intelipost');
            $this->updateOrder($incrementId, $comment, $status);
            $this->helper->createOrderShipment($incrementId, $shipment->getData('tracking_url'));
        }
    }
}
