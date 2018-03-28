<?php

namespace Omnipay\Pagarme\Acquirer;

abstract class Stone implements Acquirer
{
  public static function getMessageByCode($code)
  {
    return 'Erro no processamento. Verifique os dados e tente novamente.';
  }
}