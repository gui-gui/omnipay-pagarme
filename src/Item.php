<?php
/**
 * Pagarme Item
 */

namespace Omnipay\Pagarme;

use Omnipay\Common\Item as BaseItem;

/**
 * Class PagarmeItem
 *
 * @package Omnipay\Pagarme
 */
class Item extends BaseItem
{
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->getParameter('id');
    }

    /**
     * Set the item code
     */
    public function setId($value)
    {
        return $this->setParameter('id', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getTangible()
    {
        return $this->getParameter('tangible');
    }

    /**
     * Set the item code
     */
    public function setTangible($value)
    {
        return $this->setParameter('tangible', $value);
    }
}
