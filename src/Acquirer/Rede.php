<?php

namespace Omnipay\Pagarme\Acquirer;

abstract class Rede implements Acquirer
{
  public static function getMessageByCode($code)
  {
    return 'Erro no processamento. Verifique os dados e tente novamente.';
  }
}