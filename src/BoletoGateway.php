<?php

namespace Omnipay\Pagarme;

/**
 * 
 * Pagarme Boleto Gateway
 *
 */
class BoletoGateway extends Gateway
{
    public function getName()
    {
        return 'Pagarme Boleto';
    }
    
    // TODO: Move this to my custom craft commerce plugin instead of messing with this here
    // Override authorize to be purchase
    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Pagarme\Message\PurchaseRequest', $parameters);
    }

    // TODO: Move this to my custom craft commerce plugin instead of messing with this here
    // Override authorize to be purchase
    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Pagarme\Message\FetchTransactionRequest', $parameters);
    }
    
    // TODO: Consider implementing bank account details to handle boleto refund
    public function supportsRefund() {
        return false;
    }
}
