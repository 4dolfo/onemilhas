<?php

namespace Model\mms_gestao\__CG__;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Billetreceive extends \Billetreceive implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = [];



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return ['__isInitialized__', '' . "\0" . 'Billetreceive' . "\0" . 'id', '' . "\0" . 'Billetreceive' . "\0" . 'status', '' . "\0" . 'Billetreceive' . "\0" . 'description', '' . "\0" . 'Billetreceive' . "\0" . 'originalValue', '' . "\0" . 'Billetreceive' . "\0" . 'actualValue', '' . "\0" . 'Billetreceive' . "\0" . 'tax', '' . "\0" . 'Billetreceive' . "\0" . 'discount', '' . "\0" . 'Billetreceive' . "\0" . 'dueDate', '' . "\0" . 'Billetreceive' . "\0" . 'docNumber', '' . "\0" . 'Billetreceive' . "\0" . 'ourNumber', '' . "\0" . 'Billetreceive' . "\0" . 'issueDate', '' . "\0" . 'Billetreceive' . "\0" . 'paymentDate', '' . "\0" . 'Billetreceive' . "\0" . 'alreadyPaid', '' . "\0" . 'Billetreceive' . "\0" . 'bank', '' . "\0" . 'Billetreceive' . "\0" . 'checkinState', '' . "\0" . 'Billetreceive' . "\0" . 'hasBillet', '' . "\0" . 'Billetreceive' . "\0" . 'usedCommission', '' . "\0" . 'Billetreceive' . "\0" . 'client', '' . "\0" . 'Billetreceive' . "\0" . 'billingPartner', '' . "\0" . 'Billetreceive' . "\0" . 'sentContaAzul'];
        }

        return ['__isInitialized__', '' . "\0" . 'Billetreceive' . "\0" . 'id', '' . "\0" . 'Billetreceive' . "\0" . 'status', '' . "\0" . 'Billetreceive' . "\0" . 'description', '' . "\0" . 'Billetreceive' . "\0" . 'originalValue', '' . "\0" . 'Billetreceive' . "\0" . 'actualValue', '' . "\0" . 'Billetreceive' . "\0" . 'tax', '' . "\0" . 'Billetreceive' . "\0" . 'discount', '' . "\0" . 'Billetreceive' . "\0" . 'dueDate', '' . "\0" . 'Billetreceive' . "\0" . 'docNumber', '' . "\0" . 'Billetreceive' . "\0" . 'ourNumber', '' . "\0" . 'Billetreceive' . "\0" . 'issueDate', '' . "\0" . 'Billetreceive' . "\0" . 'paymentDate', '' . "\0" . 'Billetreceive' . "\0" . 'alreadyPaid', '' . "\0" . 'Billetreceive' . "\0" . 'bank', '' . "\0" . 'Billetreceive' . "\0" . 'checkinState', '' . "\0" . 'Billetreceive' . "\0" . 'hasBillet', '' . "\0" . 'Billetreceive' . "\0" . 'usedCommission', '' . "\0" . 'Billetreceive' . "\0" . 'client', '' . "\0" . 'Billetreceive' . "\0" . 'billingPartner', '' . "\0" . 'Billetreceive' . "\0" . 'sentContaAzul'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Billetreceive $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', []);
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', []);

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus($status)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setStatus', [$status]);

        return parent::setStatus($status);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStatus', []);

        return parent::getStatus();
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($description)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDescription', [$description]);

        return parent::setDescription($description);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDescription', []);

        return parent::getDescription();
    }

    /**
     * {@inheritDoc}
     */
    public function setOriginalValue($originalValue)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setOriginalValue', [$originalValue]);

        return parent::setOriginalValue($originalValue);
    }

    /**
     * {@inheritDoc}
     */
    public function getOriginalValue()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getOriginalValue', []);

        return parent::getOriginalValue();
    }

    /**
     * {@inheritDoc}
     */
    public function setActualValue($actualValue)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setActualValue', [$actualValue]);

        return parent::setActualValue($actualValue);
    }

    /**
     * {@inheritDoc}
     */
    public function getActualValue()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getActualValue', []);

        return parent::getActualValue();
    }

    /**
     * {@inheritDoc}
     */
    public function setTax($tax)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setTax', [$tax]);

        return parent::setTax($tax);
    }

    /**
     * {@inheritDoc}
     */
    public function getTax()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTax', []);

        return parent::getTax();
    }

    /**
     * {@inheritDoc}
     */
    public function setDiscount($discount)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDiscount', [$discount]);

        return parent::setDiscount($discount);
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscount()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDiscount', []);

        return parent::getDiscount();
    }

    /**
     * {@inheritDoc}
     */
    public function setDueDate($dueDate)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDueDate', [$dueDate]);

        return parent::setDueDate($dueDate);
    }

    /**
     * {@inheritDoc}
     */
    public function getDueDate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDueDate', []);

        return parent::getDueDate();
    }

    /**
     * {@inheritDoc}
     */
    public function setDocNumber($docNumber)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDocNumber', [$docNumber]);

        return parent::setDocNumber($docNumber);
    }

    /**
     * {@inheritDoc}
     */
    public function getDocNumber()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDocNumber', []);

        return parent::getDocNumber();
    }

    /**
     * {@inheritDoc}
     */
    public function setOurNumber($ourNumber)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setOurNumber', [$ourNumber]);

        return parent::setOurNumber($ourNumber);
    }

    /**
     * {@inheritDoc}
     */
    public function getOurNumber()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getOurNumber', []);

        return parent::getOurNumber();
    }

    /**
     * {@inheritDoc}
     */
    public function setIssueDate($issueDate)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIssueDate', [$issueDate]);

        return parent::setIssueDate($issueDate);
    }

    /**
     * {@inheritDoc}
     */
    public function getIssueDate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIssueDate', []);

        return parent::getIssueDate();
    }

    /**
     * {@inheritDoc}
     */
    public function setPaymentDate($paymentDate)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPaymentDate', [$paymentDate]);

        return parent::setPaymentDate($paymentDate);
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentDate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPaymentDate', []);

        return parent::getPaymentDate();
    }

    /**
     * {@inheritDoc}
     */
    public function setAlreadyPaid($alreadyPaid)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAlreadyPaid', [$alreadyPaid]);

        return parent::setAlreadyPaid($alreadyPaid);
    }

    /**
     * {@inheritDoc}
     */
    public function getAlreadyPaid()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAlreadyPaid', []);

        return parent::getAlreadyPaid();
    }

    /**
     * {@inheritDoc}
     */
    public function setBank($bank)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setBank', [$bank]);

        return parent::setBank($bank);
    }

    /**
     * {@inheritDoc}
     */
    public function getBank()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBank', []);

        return parent::getBank();
    }

    /**
     * {@inheritDoc}
     */
    public function setCheckinState($checkinState)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCheckinState', [$checkinState]);

        return parent::setCheckinState($checkinState);
    }

    /**
     * {@inheritDoc}
     */
    public function getCheckinState()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCheckinState', []);

        return parent::getCheckinState();
    }

    /**
     * {@inheritDoc}
     */
    public function setHasBillet($hasBillet)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setHasBillet', [$hasBillet]);

        return parent::setHasBillet($hasBillet);
    }

    /**
     * {@inheritDoc}
     */
    public function getHasBillet()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getHasBillet', []);

        return parent::getHasBillet();
    }

    /**
     * {@inheritDoc}
     */
    public function setUsedCommission($usedCommission)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUsedCommission', [$usedCommission]);

        return parent::setUsedCommission($usedCommission);
    }

    /**
     * {@inheritDoc}
     */
    public function getUsedCommission()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUsedCommission', []);

        return parent::getUsedCommission();
    }

    /**
     * {@inheritDoc}
     */
    public function setClient(\Businesspartner $client = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setClient', [$client]);

        return parent::setClient($client);
    }

    /**
     * {@inheritDoc}
     */
    public function getClient()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getClient', []);

        return parent::getClient();
    }

    /**
     * {@inheritDoc}
     */
    public function setBillingPartner(\Businesspartner $billingPartner = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setBillingPartner', [$billingPartner]);

        return parent::setBillingPartner($billingPartner);
    }

    /**
     * {@inheritDoc}
     */
    public function getBillingPartner()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBillingPartner', []);

        return parent::getBillingPartner();
    }

    /**
     * {@inheritDoc}
     */
    public function setSentContaAzul($sentContaAzul)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSentContaAzul', [$sentContaAzul]);

        return parent::setSentContaAzul($sentContaAzul);
    }

    /**
     * {@inheritDoc}
     */
    public function getSentContaAzul()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSentContaAzul', []);

        return parent::getSentContaAzul();
    }

}
