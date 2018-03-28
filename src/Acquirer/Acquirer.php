<?php

namespace Omnipay\Pagarme\Acquirer;

interface Acquirer
{
  /**
   * @return string
   */
  public static function getMessageByCode($code);
}
