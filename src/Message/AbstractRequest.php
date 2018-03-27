<?php

namespace Omnipay\Pagarme\Message;

// use Omnipay\Pagarme\CreditCard;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Common\Exception\InvalidResponseException;

use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;

/**
 * Abstract Request
 *
 */
abstract class AbstractRequest extends BaseAbstractRequest
{
    /**
     * Live or Test Endpoint URL
     *
     * @var string URL
     */
    protected $endpoint = 'https://api.pagar.me/1/';

    
    public function getAmountInCents($amount)
    {
        return (int) round($amount * 100);
    }

    /**
     * Get API key
     *
     * @return string API key
     */
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    /**
     * Set API key
     *
     * @param string $value
     * @return AbstractRequest provides a fluent interface.
     */
    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * Get Customer Data
     *
     * @return array customer data
     */
    public function getCustomer()
    {
        return $this->getParameter('customer');
    }

    /**
     * Set Customer data
     *
     * @param array $value
     * @return AbstractRequest provides a fluent interface.
     */
    public function setCustomer($value)
    {
        return $this->setParameter('customer', $value);
    }

    /**
     * Get the customer reference
     *
     * @return string customer reference
     */
    public function getCustomerReference()
    {
        return $this->getParameter('customerReference');
    }

    /**
     * Set the customer reference
     *
     * Used when calling CreateCardRequest on an existing customer. If this
     * parameter is not set then a new customer is created.
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setCustomerReference($value)
    {
        return $this->setParameter('customerReference', $value);
    }

    /**
     * Get the card hash / token
     *
     * @return string card hash
     */
    public function getCardHash()
    {
        return $this->getToken();
    }

    /**
     * Set the card hash
     *
     * Must be a card hash like the ones returned by Pagarme.js.
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setCardHash($value)
    {
        return $this->setToken($value);
    }


    /**
     * Get the card hash
     *
     * @return string card hash
     */
    public function getHolderDocumentNumber()
    {
        return $this->getParameter('holderDocumentNumber');
    }

    /**
     * Set the card hash
     *
     * Must be a card hash like the ones returned by Pagarme.js.
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setHolderDocumentNumber($value)
    {
        return $this->setParameter('holderDocumentNumber', preg_replace('/\D/', '', $value));
    }

    /**
     * Get Metadata
     *
     * @return array metadata
     */
    public function getMetadata()
    {
        return $this->getParameter('metadata');
    }

    /**
     *
     * @param array $value
     * @return AbstractRequest provides a fluent interface.
     */
    public function setMetadata($value)
    {
        return $this->setParameter('metadata', $value);
    }


    /**
     * Get PostbackUrl
     *
     * @return string Url
     */
    public function getPostbackUrl()
    {
        return $this->getParameter('postbackUrl');
    }

    /**
     *
     * @param array $value
     * @return AbstractRequest provides a fluent interface.
     */
    public function setPostbackUrl($value)
    {
        return $this->setParameter('postbackUrl', $value);
    }

    /**
     * Get Items
     *
     * @return array items
     */
    public function getItems()
    {
        return $this->getParameter('items');
    }

    /**
     *
     * @param array $value
     * @return AbstractRequest provides a fluent interface.
     */
    public function setItems($value)
    {
        return $this->setParameter('items', $value);
    }


    /**
     * Get ShippingFee
     *
     * @return array shipping_fee
     */
    public function getShippingFee()
    {
        return $this->getParameter('shippingFee');
    }


    /**
     *
     * @param array $value
     * @return AbstractRequest provides a fluent interface.
     */
    public function setShippingFee($value)
    {
        return $this->setParameter('shippingFee', $value);
    }

    /**
     * Insert the API key into de array.
     *
     * @param array $data
     * @return array The data with the API key to be used in all Requests
     */
    protected function insertApiKeyToData($data)
    {
        $data['api_key'] = $this->getApiKey();

        return $data;
    }

    /**
     * Get HTTP Method.
     *
     * This is nearly always POST but can be over-ridden in sub classes.
     *
     * @return string the HTTP method
     */
    public function getHttpMethod()
    {
        return 'POST';
    }

    protected function getEndpoint()
    {
        return $this->endpoint;
    }

    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
    }

    public function sendData($data)
    {
        // don't throw exceptions for 4xx errors
        $this->httpClient->getEventDispatcher()->addListener(
            'request.error',
            function ($event) {
                if ($event['response']->isClientError()) {
                    $event->stopPropagation();
                }
            }
        );

        $headers = array(
            'Accept' => 'application/json',
            'Content-type' => 'application/json',
            'timeout'         => 60,
        );

        // Guzzle HTTP Client createRequest does funny things when a GET request
        // has attached data, so don't send the data if the method is GET.
        if ($this->getHttpMethod() == 'GET') {
            $httpRequest = $this->httpClient->createRequest(
                $this->getHttpMethod(),
                $this->getEndpoint() . '?' . http_build_query($this->insertApiKeyToData($data)),
                $headers
            );
        } else {
            $httpRequest = $this->httpClient->createRequest(
                $this->getHttpMethod(),
                $this->getEndpoint(),
                $headers,
                $this->toJSON($this->insertApiKeyToData($data))
            );
        }

        try {
            // CURL_SSLVERSION_TLSv1_2 for libcurl < 7.35
            $httpRequest->getCurlOptions()->set(CURLOPT_SSLVERSION, 6);
            $httpResponse = $httpRequest->send();
            // Empty response body should be parsed also as and empty array
            $body = $httpResponse->getBody(true);
            $jsonToArrayResponse = !empty($body) ? $httpResponse->json() : array();
            return $this->response = $this->createResponse($jsonToArrayResponse, $httpResponse->getStatusCode());
        } catch (\Exception $e) {
            throw new InvalidResponseException(
                'Error communicating with payment gateway: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * Get the card data.
     *
     * Because the pagarme gateway uses a common format for passing
     * card data to the API, this function can be called to get the
     * data from the associated card object in the format that the
     * API requires.
     *
     * @return array card data
     */
    protected function getCardData()
    {
        $card = $this->getCard();
        $data = array();

        $data['card_holder_name'] = $card->getName();
        if ( $this->getCardHash() ) {
            $data['card_expiration_date'] = '1020';
            $data['card_hash'] = $this->getCardHash();
        } elseif( $this->getCardReference() ) {
            $data['card_id'] = $this->getCardReference();
        } else  {
            $data['card_expiration_date'] = sprintf('%02d',$card->getExpiryMonth()).(string)$card->getExpiryYear();
            $data['card_number'] = $card->getNumber();
            if ( $card->getCvv() ) {
                $data['card_cvv'] = $card->getCvv();
            }
        } 

        return $data;
    }

    /**
     * Get the Customer data.
     *
     * Because the pagarme gateway uses a common format for passing
     * customer data to the API, this function can be called to get the
     * data from the card object in the format that the API requires.
     *
     * @return array customer data
     */
    protected function getCustomerData()
    {
        $card = $this->getCard();
        $data = array();
        
        $data['name'] = $card->getName();
        $data['email'] = $card->getEmail();
        $data['external_id'] = $card->getEmail();
        $data['type'] = $this->extractCustomerType($this->getHolderDocumentNumber());
        $data['country'] = strtolower($card->getCountry());
        $data['phone_numbers'] = $this->extractPhones(array($card->getPhone()), $card->getCountry());
        $data['documents'] = $this->extractDocuments(array($this->getHolderDocumentNumber()));

        return $data;
    }

    // TODO: Comments for method (Docs)
    protected function getBillingData()
    {
        $card = $this->getCard();
        $data = array();
        $address = $this->extractAddress($card->getBillingAddress1());  

        $data['name'] = "{$card->getBillingFirstName()} {$card->getBillingLastName()}";
        $data['address']['country'] = strtolower($card->getBillingCountry());
        $data['address']['state'] = strtolower($card->getBillingState());
        $data['address']['city'] = $card->getBillingCity();
        $data['address']['street'] = $address['street'];
        $data['address']['street_number'] = $address['street_number'] ?: '00';
        $data['address']['complementary'] = $card->getBillingAddress2() ?: 'Sem Complemento';
        $data['address']['zipcode'] = preg_replace('/\D/', '', $card->getBillingPostcode());

        return $data;
    }

    // TODO: Comments for method (Docs)
    protected function getShippingData()
    {
        $card = $this->getCard();
        $data = array();
        $address = $this->extractAddress($card->getShippingAddress1());
        
        $data['name'] = "{$card->getShippingFirstName()} {$card->getShippingLastName()}";
        $data['address']['country'] = strtolower($card->getShippingCountry());
        $data['address']['state'] = strtolower($card->getShippingState());
        $data['address']['city'] = $card->getShippingCity();
        $data['address']['street'] = $address['street'];
        $data['address']['street_number'] = $address['street_number'] ?: '00';
        $data['address']['complementary'] = $card->getShippingAddress2() ?: 'Sem Complemento';
        $data['address']['zipcode'] = preg_replace('/\D/', '', $card->getShippingPostcode());
        $data['fee'] = $this->getAmountInCents($this->getShippingFee());
        // TODO: Allow for these values:
        // ['delivery_date'] = date(YYYY-MM-DD)
        // ['expedited'] = bool
        return $data;
    }

    // TODO: DOCS and Improve Id and Tangible Logic
    protected function getItemsData()
    {
        $result = array();

        foreach ($this->getItems() as $lineItem) 
        {
            $item = array();
            $item['id'] = $lineItem->getId() ?: "UNKNOWN_ID" ;
            $item['title'] = $lineItem->getName();
            $item['unit_price'] = $this->getAmountInCents($lineItem->getPrice());
            $item['quantity'] = intval($lineItem->getQuantity());
            $item['tangible'] = $lineItem->getTangible() ?: true;
            array_push($result, $item);
        }

        return $result;
    }

    /**
     * Separate data from the credit card Address in an
     * array containing the keys:
     * * street
     * * street_number
     * * complementary
     *
     * It's important to provide the parameter $address
     * with the information in the given order and separated
     * by commas.
     *
     * @param string $address
     * @return array containing the street, street_number and complementary
     */
    protected function extractAddress($address)
    {
        $explode = array_map('trim', explode(',', $address));

        $result['street'] = $explode[0];
        $result['street_number'] = isset($explode[1]) ? $explode[1] : '';

        return $result;
    }

    /**
     * Generate an array with the document object with keys
     * * type
     * * number
     *
     *
     * @param array of $documentNumbers
     * @return array of documents
     */
    protected function extractDocuments($documentNumbers)
    {
        $result = array();

        foreach ($documentNumbers as $number) 
        {
            $document = array();
            $document['number'] = $number;
            $document['type'] = $this->extractDocumentType($number);
            array_push($result, $document);
        }

        return $result;
    }

    /**
     * Return wether is Individual or Corporation
     *
     * @param string $documentNumber
     * @return string 'individual' | 'corporation'
     */
    protected function extractCustomerType($documentNumber) 
    {
        return strlen(preg_replace('/\D/', '', $documentNumber)) == 11 ? 'individual' : 'corporation';
    }

    /**
     * Return wether is CPF or CNPJ
     *
     * @param string $documentNumber
     * @return string 'cpf' | 'cnpj'
     */
    protected function extractDocumentType($documentNumber) 
    {
        return strlen(preg_replace('/\D/', '', $documentNumber)) == 11 ? 'cpf' : 'cnpj';
    }

    /**
     * Generate an array with the phone numbers formatted in E.164 format
     *
     * @param array $phoneNumbers
     * @return array containing E.164 format numbers
     */
    protected function extractPhones($phoneNumbers, $country)
    {
        $result = array();
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

        foreach($phoneNumbers as $number)
        {   
            try {
                $number_proto = $phoneUtil->parse($number, $country);
                array_push($result, $phoneUtil->format($number_proto, \libphonenumber\PhoneNumberFormat::E164));
            }
            catch (\libphonenumber\NumberParseException $e) {
                // Let the phone number go as it is. Pagarme response will handle this error for now.
                array_push($result, $number);
            }
        }

        return $result;
    }


    /**
     * Returns object JSON representation required by Pagarme.
     *
     * @param int $options http://php.net/manual/en/json.constants.php
     * @return string
     */
    public function toJSON($data, $options = 0)
    {
        return json_encode($data, $options | 64);
    }
}