<?php

class Microtron_Customprice_IndexController extends Mage_Core_Controller_Front_Action {

    /**
     * Session entity
     *
     * @var Mage_Core_Model_Session_Abstract
     */
    protected $_session;

    /**
     * Cookie currency key
     */

    const COOKIE_PRICETYPE = 'pricetype';

    public function indexAction() {
        
    }

    public function switchAction() {
        if ($pricetype = (string) $this->getRequest()->getParam('pricetype')) {

            $this->setCurrentPriceTypeCode($pricetype);
        }

        $url = $_SERVER['HTTP_REFERER'];
        $this->_redirectUrl($url);
    }
    /**
     * Retrieve store session object
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    protected function _getSession()
    {
        if (!$this->_session) {
            $this->_session = Mage::getModel('core/session')
                ->init('store_1');
        }
        return $this->_session;
    }
    /**
     * Set current store currency code
     *
     * @param   string $code
     * @return  string
     */
    public function setCurrentPriceTypeCode($code) {
        $availablePriceTypeCodes = Mage::helper('customprice/groups')
                ->getAvailablePriceTypeCodes();

        if (in_array($code, $availablePriceTypeCodes)) {
            if (substr($code, -4) == "_usd") {
                $curency_code = 'USD';                
            } else {
                $curency_code = 'UAH';
            }
            $this->_getSession()->setCurrencyCode($curency_code);;
            Mage::getModel('core/session')->setPriceType($code);
            
            if ($code == 'price') {
                Mage::app()->getCookie()->delete(self::COOKIE_PRICETYPE, $code);
            } else {
                Mage::app()->getCookie()->set(self::COOKIE_PRICETYPE, $code);
            }
        }
        return $this;
    }

}
