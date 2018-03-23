<?php

namespace Omnipay\Pagarme\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Pagarme Response
 *
 * This is the response class for all Pagarme requests.
 *
 * @see \Omnipay\Pagarme\Gateway
 */
class FetchTransactionResponse extends Response
{

    const PROCESSING      = 'processing';
    const AUTHORIZED      = 'authorized';
    const PAID            = 'paid';
    const REFUNDED        = 'refunded';
    const WAITING_PAYMENT = 'waiting_payment';
    const PENDING_REFUND  = 'pending_refund';
    const REFUSED         = 'refused';

    /**
     * Is the transaction successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        if (isset($this->data['object']) && 'transaction' === $this->data['object']) {
            return ($this->data['status'] == self::PAID || 
                    $this->data['status'] == self::AUTHORIZED ||
                    $this->data['status'] == self::REFUNDED);
        }
        return !isset($this->data['errors']);
    }
    

    public function isPending()
    {
      if (isset($this->data['object']) && 'transaction' === $this->data['object']) {
        return ($this->data['status'] == self::PROCESSING || 
                $this->data['status'] == self::WAITING_PAYMENT ||
                $this->data['status'] == self::PENDING_REFUND);
      }
      return !isset($this->data['errors']);
    }


    public function isCanceled()
    {
      if (isset($this->data['object']) && 'transaction' === $this->data['object']) {
        return ($this->data['status'] == self::REFUSED);
      }
      return !isset($this->data['errors']);
    }
}