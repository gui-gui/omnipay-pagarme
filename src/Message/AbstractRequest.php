<?php

namespace Omnipay\Pagarme\Message;

use Omnipay\Pagarme\CreditCard;
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

    /**
     * Get the card.
     *
     * @return CreditCard
     */
    public function getCard()
    {
        return $this->getParameter('card');
    }

    /**
     * Sets the card.
     *
     * @param CreditCard $value
     * @return AbstractRequest Provides a fluent interface
     */
    public function setCard($value)
    {
        if ($value && !$value instanceof CreditCard) {
            $value = new CreditCard($value);
        }

        return $this->setParameter('card', $value);
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
     * Get the card hash
     *
     * @return string card hash
     */
    public function getCardHash()
    {
        return $this->getParameter('card_hash');
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
        return $this->setParameter('card_hash', $value);
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

        $httpRequest = $this->httpClient->createRequest(
            $this->getHttpMethod(),
            $this->getEndpoint(),
            null,
            $this->insertApiKeyToData($data),
            $this->getOptions()
        );
        $httpResponse = $httpRequest->send();

        return $this->response = new Response($this, $httpResponse->json());
    }

    /**
     * Get Query Options.
     *
     * Must be over-ridden in sub classes that make GET requests
     * with query parameters.
     *
     * @return array The query Options
     */
    protected function getOptions()
    {
        return array();
    }

    protected function getEndpoint()
    {
        return $this->endpoint;
    }

    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
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

        $card->validate();
        $data['object'] = 'card';
        $data['card_holder_name'] = $card->getName();
        $data['card_number'] = $card->getNumber();
        $data['card_expiration_date'] = sprintf('%02d',$card->getExpiryMonth()).(string)$card->getExpiryYear();
        if ( $card->getCvv() ) {
            $data['card_cvv'] = $card->getCvv();
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
        $data = new stdClass();
        
        $data['customer']['name'] = $card->getName();
        $data['customer']['email'] = $card->getEmail();
        $data['customer']['external_id'] = $this->getUserId() ? $this->getUserId() : $card->getEmail();
        $data['customer']['type'] = $this->extractCustomerType($card->getHolderDocumentNumber());
        $data['customer']['country'] = $card->getCountry();
        $data['customer']['phone_numbers'] = $this->extractPhones(array($card->getPhone(), $card->getCountry()));
        $data['customer']['documents'] = $this->extractDocuments(array($card->getHolderDocumentNumber()));
        // TODO: Birthday ['customer']['birthday'] 
        // TODO: allow referencing saved customer.id

        return $data;
    }

    // TODO: METHOD DOCS
    protected function getBillingData()
    {
        $card = $this->getCard();
        $data = new stdClass();
        $address = $this->extractAddress($card->getBillingAddress1());  

        $data['name'] = "${$card->getBillingFirstName()} ${$card->getBillingLastName()}";
        $data['address']['country'] = $card->getBillingCountry();
        $data['address']['state'] = $card->getBillingState();
        $data['address']['city'] = $card->getBillingCity();
        $data['address']['street'] = $address[0];
        $data['address']['street_number'] = $address[1];
        $data['address']['complementary'] = $card->getBillingAddress2();
        $data['address']['zipcode'] = $card->getBillingPostcode();
        // TODO: neighbourhood ['address']['neighbourhood'];
    }

    // TODO: METHOD DOCS
    protected function getShippingData()
    {
        $card = $this->getCard();
        $data = new stdClass();
        $address = $this->extractAddress($card->getShippingAddress1());
        
        $data['name'] = "${$card->getShippingFirstName()} ${$card->getShippingLastName()}";
        $data['address']['country'] = $card->getShippingCountry();
        $data['address']['state'] = $card->getShippingState();
        $data['address']['city'] = $card->getShippingCity();
        $data['address']['street'] = $address[0];
        $data['address']['street_number'] = $address[1];
        $data['address']['complementary'] = $card->getShippingAddress2();
        $data['address']['zipcode'] = $card->getShippingPostcode();
        // TODO: Allow for:
        //   "fee" ['fee'] = 1000 (in cents)
        //   "delivery_date" ['delivery_date'] = date(YYYY-MM-DD)
        //   "expedited" ['expedited'] = bool
        //   "neighbourhood" ['address']['neighbourhood'];
    }

    // TODO: METHOD DOCS
    protected function getItemsData()
    {
        return $this->getItems();
        // returns an array of [{ id, title, unit_price, quantity e tangible }]
        // {
        //     "id": "r123",
        //     "title": "Red pill",
        //     "unit_price": 10000,
        //     "quantity": 1,
        //     "tangible": true
        //   },
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
     * @param array of $document_numbers
     * @return array of documents
     */
    protected function extractDocuments($document_numbers)
    {
        $result = array();

        foreach ($document_numbers as $number) 
        {
            $document = new stdClass();
            $document->number = $number;
            $document->type = $this->extractDocumentType($number);
            array_push($result, $document);
        }

        return $result;
    }

    /**
     * Return wether is Individual or Corporation
     *
     * @param string $document_number
     * @return string 'individual' | 'corporation'
     */
    protected function extractCustomerType($document_number) 
    {
        return strlen($document_number) == 11 ? 'individual' : 'corporation';
    }

    /**
     * Return wether is CPF or CNPJ
     *
     * @param string $document_number
     * @return string 'cpf' | 'cnpj'
     */
    protected function extractDocumentType($document_number) 
    {
        return strlen($document_number) == 11 ? 'cpf' : 'cnpj';
    }

    /**
     * Generate an array with the phone numbers formatted in E.164 format
     *
     * @param array $phone_numbers
     * @return array containing E.164 format numbers
     */
    protected function extractPhones($phone_numbers, $country)
    {
        $result = array();
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

        foreach($phone_numbers as $number)
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
}