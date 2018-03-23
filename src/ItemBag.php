<?php
/**
 * Pagarme Item bag
 */

namespace Omnipay\Pagarme;

use Omnipay\Common\ItemBag as BaseItemBag;
use Omnipay\Common\ItemInterface;

/**
 * Class PagarmeItemBag
 *
 * @package Omnipay\Pagarme
 */
class ItemBag extends BaseItemBag
{
    /**
     * Add an item to the bag
     *
     * @see Item
     *
     * @param ItemInterface|array $item An existing item, or associative array of item parameters
     */
    public function add($item)
    {
        if ($item instanceof ItemInterface) {
            $this->items[] = $item;
        } else {
            $this->items[] = new Item($item);
        }
    }
}
