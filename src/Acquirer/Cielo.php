<?php

namespace Omnipay\Pagarme\Acquirer;

abstract class Cielo implements Acquirer
{

  public static function getMessageByCode($code)
  {
    $message = '';

    switch ((string) $code) {
      case '01': // Transação referida pelo banco emissor - Referida pelo banco emissor
      case '21': // Cancelamento não efetuado - Cancelamento não localizado no banco emissor
      case '41': // Cartão com restrição - Existe algum tipo de restrição no cartão
      case '60': // Transação não autorizada - Existe algum tipo de restrição no cartão
      case '62': // Transação não autorizada - Existe algum tipo de restrição no cartão
        $message = 'Operação não autorizada. Por favor, entre em contato com o seu banco.';
      case '04': // Transação não autorizada - Existe algum tipo de restrição no cartão
      case '05': // Transação não autorizada - Existe algum tipo de restrição no cartão
      case '06': // Tente novamente - Falha na autorização
      case '07': // Cartão com restrição - Existe algum tipo de restrição no cartão
      case '57': // Transação não permitida - Existe algum tipo de restrição no cartão
        $message = 'Operação não autorizada. Tente novamente. Caso o erro continue, entre em contato com o seu banco.';
        break;
      case '08': // Código de segurança inválido - Código de segurança incorreto
        $message = 'Código de segurança inválido.';
        break;
      case '13': // Valor inválido - Valor inválido
        $message = 'Valor inválido.';
        break;
      case '14': // Cartão inválido - Digitação incorreta do número do cartão
      case '82': // Erro no cartão - Cartão inválido
        $message = 'Cartão inválido. Verifique os dados do cartão e tente novamente.';
        break;
      case '15': // Banco emissor indisponível - Banco emissor indisponível
      case '91': // Banco fora do ar - Banco emissor indisponível
        $message = 'Banco emissor indisponível. Aguarde alguns instantes e tente novamente.';
        break;
      case '51': // Saldo insuficiente - Saldo insuficiente
        $message = 'Saldo insuficiente. Se necessário, entre em contato com o seu banco.';
        break;
      case '54': // Cartão vencido - Cartão vencido
        $message = 'Cartão vencido. Verifique os dados do cartão.';
        break;
      case '78': // Cartão não foi desbloqueado pelo portador - Cartão não foi desbloqueado pelo portador
        $message = 'Cartão ainda não foi desbloqueado.';
        break;
      case 'AC': // Use função débito - Cartão de débito tentando utilizar produto crédito
        $message = 'Esse cartão não permite esse tipo de operação.';
        break;
      case '96': // Tente novamente - Falha no envio da autorização
      case 'AA': // Tempo excedido - Timeout na comunicação com o banco emissor
      case 'GA': // Transação referida pela Cielo - Referida pela Cielo
        $message = 'Erro no processamento. Aguarde alguns instantes e tente novamente.';
        break;
      default:
        return 'Erro no processamento. Verifique os dados e tente novamente.';
    }

    return $message;
  }
}