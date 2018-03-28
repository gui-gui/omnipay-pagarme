<?php

namespace Omnipay\Pagarme\Acquirer;

abstract class Pagarme implements Acquirer
{

  public static function getMessageByCode($code)
  {
    $message = '';

    switch ((string) $code) {
      case '1000': // Transação não autorizada
      case '1002': // Transação não permitida
      case '1003': // Rejeitado emissor
      case '1005': // Transação não autorizada
      case '1004': // Cartão com restrição
      case '1007': // Rejeitado emissor
      case '1008': // Rejeitado emissor
      case '1009': // Transação não autorizada
      case '1013': // Transação não autorizada
      case '1019': // Transação não permitida
      case '1020': // Transação não permitida
      case '1021': // Rejeitado emissor
      case '1022': // Cartão com restrição
      case '1023': // Rejeitado emissor
      case '1024': // Transação não permitida
      case '1025': // Cartão bloqueado
      case '2002': // Transação não permitida
      case '2005': // Transação não autorizada
      case '2000': // Cartão com restrição
      case '2004': // Cartão com restrição
      case '2007': // Cartão com restrição
      case '2008': // Cartão com restrição
      case '2009': // Cartão com restrição
      case '2003': // Rejeitado emissor
      case '9102': // Transação inválida
        $message = 'Operação não autorizada. Por favor, entre em contato com o seu banco.';
        break;
      case '1014': // Tipo de conta inválido
      case '1042': // Tipo de conta inválido
        $message = 'Esse cartão não permite esse tipo de operação.';
        break;
      case '1001': // Cartão vencido
      case '2001': // Cartão vencido
        $message = 'Cartão vencido.';
        break;
      case '1011': // Cartão inválido
        $message = 'Cartão inválido.';
        break;
      case '1006': // Tentativas de senha excedidas
      case '2006': // Tentativas de senha excedidas
        $message = 'Tentativas de senha excedidas';
        break;
      case '1017': // Senha inválida
        $message = 'Senha inválida.';
        break;
      case '1010': // Valor inválido
        $message = 'Valor inválido.';
        break;
      case '1016': // Saldo insuficiente
        $message = 'Saldo insuficiente.';
        break;
      case '1045': // Código de segurança inválido
        $message = 'Código de segurança inválido';
        break;
      case '9108': // Erro no processamento
      case '9109': // Erro no processamento
      case '9111': // Time-out na transação
      case '9112': // Emissor indisponível
      case '9999': // Erro não especificado
        $message = 'Erro no processamento. Por favor, tente novamente.';
        break;
      default:
        return 'Erro no processamento. Verifique os dados e tente novamente.';
    }

    return $message;
  }
}