<?php

namespace Omnipay\Pagarme\Message;

use Omnipay\Pagarme\Acquirer\Pagarme;
use Omnipay\Pagarme\Acquirer\Cielo;
use Omnipay\Pagarme\Acquirer\Rede;
use Omnipay\Pagarme\Acquirer\Stone;
use Omnipay\Common\Message\AbstractResponse;

/**
 * Pagarme Response
 *
 * This is the response class for all Pagarme requests.
 *
 * @see \Omnipay\Pagarme\Gateway
 */
class Response extends AbstractResponse
{
    /**
     * Is the transaction successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        if (isset($this->data['object']) && 'transaction' === $this->data['object']) {
            return !($this->data['status'] == 'refused');
        }
        return false;
    }
    
    /**
     * Get the transaction reference.
     *
     * @return string|null
     */
    public function getTransactionReference()
    {
        if (isset($this->data['object']) && 'transaction' === $this->data['object']) {
            return $this->data['id'];
        }

        return null;
    }
    
    /**
     * Get a card reference, for createCard or createCustomer requests.
     *
     * @return string|null
     */
    public function getCardReference()
    {
        if (isset($this->data['object']) && 'card' === $this->data['object']) {
            if (! empty($this->data['id'])) {
                return $this->data['id'];
            }
        } elseif (isset($this->data['object']) && 'transaction' === $this->data['object']) {
            return $this->data['card']['id'];
        }

        return null;
    }
    
    /**
     * Get a customer reference, for createCustomer requests.
     *
     * @return string|null
     */
    public function getCustomerReference()
    {
        if (isset($this->data['object']) && 'customer' === $this->data['object']) {
            return $this->data['id'];
        }
        if (isset($this->data['object']) && 'transaction' === $this->data['object']) {
            if (! empty($this->data['customer'])) {
                return $this->data['customer']['id'];
            }
        }
        if (isset($this->data['object']) && 'card' === $this->data['object']) {
            if (! empty($this->data['customer'])) {
                return $this->data['customer']['id'];
            }
        }

        return null;
    }
    
    /**
     * Get the error message from the response.
     *
     * Returns null if the request was successful.
     *
     * @return string|null
     */
    public function getMessage()
    {
        if (!$this->isSuccessful()) {
            if (isset($this->data['errors'])) {
                return $this->data['errors'][0]['message'];
            } 
            elseif ($this->data['refuse_reason'] == 'acquirer') {
                $code = $this->data['acquirer_response_code'];
                $acquirer = $this->data['acquirer_name'];
                return $this->getAcquirerMessage($acquirer, $code);
            }
            else {
                return 'Erro no processamento. Verifique os dados e tente novamente.';
            }
            
        }

        return null;
    }
    
    /**
     * Get the boleto_url, boleto_barcode and boleto_expiration_date in the
     * transaction object.
     * 
     * @return array|null the boleto_url, boleto_barcode and boleto_expiration_date
     */
    public function getBoleto()
    {
        if (isset($this->data['object']) && 'transaction' === $this->data['object']) {
            if ( $this->data['boleto_url'] ) {
                $data = array(
                    'boleto_url' => $this->data['boleto_url'], 
                    'boleto_barcode' => $this->data['boleto_barcode'],
                    'boleto_expiration_date' => $this->data['boleto_expiration_date'],
                );
                return $data;
            } else {
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Get the Calculted Installments provided by Pagar.me API.
     * 
     * @return array|null the calculated installments
     */
    public function getCalculatedInstallments()
    {
        if (isset($this->data['installments'])) {
            $data = $this->data['installments'];
            return $data;
        } else {
            return null;
        }
    }


    public function getAcquirerMessage($acquirer, $code)
    {
        if($acquirer == 'cielo')
        {
            return Cielo::getMessageByCode($code);
        }

        if($acquirer == 'pagarme')
        {
            return Pagarme::getMessageByCode($code);
        }

        if($acquirer == 'rede')
        {
            return Rede::getMessageByCode($code);
        }

        if($acquirer == 'stone')
        {
            return Stone::getMessageByCode($code);
        }

        return 'Erro no processamento. Verifique os dados e tente novamente.';

    }
}