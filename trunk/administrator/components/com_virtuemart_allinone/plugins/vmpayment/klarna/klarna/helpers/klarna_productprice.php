<?php

defined ('_JEXEC') or die('Restricted access');

/**
 * @version $Id$
 *
 * @author Valérie Isaksen
 * @package VirtueMart

 * @copyright Copyright (C) iStraxx - All rights reserved.
 * @license istraxx_license.txt Proprietary License. This code belongs to istraxx UG (haftungsbeschränkt)
 * You are not allowed to distribute or sell this code.
 * You are not allowed to modify this code
 */
class klarna_productPrice {

	private $klarna_virtuemart;
	private $cData;
	private $path;

	/**
	 * @param $cData
	 */
	public function __construct ($cData) {

		$this->path = JPATH_VMKLARNAPLUGIN . '/klarna/';
		if (!class_exists ('ShopFunctions')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
		}

		$this->cData = $cData;
		//$this->currencyId = ShopFunctions::getCurrencyIDByName($this->cData['currency_code']);
		vmdebug ('klarna_productPrice', $this->cData);
		try {
			$this->klarna_virtuemart = new Klarna_virtuemart();
			$this->klarna_virtuemart->config ($this->cData['eid'], $this->cData['secret'], $this->cData['country'], $this->cData['language'], $this->cData['currency'], $this->cData['mode'], VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type (), FALSE);
		}
		catch (Exception $e) {
			vmDebug ('klarna_productPrice', $e->getMessage (), $this->cData);
			unset($this->klarna);
		}
	}

	/**
	 * @param $product
	 * @return bool
	 */
	private function showPP ($product) {

		if (!isset($this->klarna_virtuemart) || !($this->klarna_virtuemart instanceof Klarna_virtuemart)) {
			return FALSE;
		}
		if (!VMKLARNA_SHOW_PRODUCTPRICE) {
			return FALSE;
		}
		// the price is in the vendor currency
		// convert price in NLD currency= euro

		$price = KlarnaHandler::convertPrice ($product->prices['salesPrice'], $product->product_currency, 'EUR');

		if (strtolower ($this->cData['country_code']) == 'nl' && $price > 250) {
			vmDebug ('showPP', 'dont show price for NL', $this->cData['country_code'], $price);
			return FALSE;
		}

		if ($price <= $this->cData['min_amount'] AND !empty($this->cData['min_amount'])) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @param $product
	 * @return array|null
	 */
	public function showProductPrice ($product) {
		if (!$this->showPP ($product)) {
			return NULL;
		}

		$viewData = $this->getViewData ($product);
		return $viewData;
	}

	/**
	 * @param $product
	 * @return array|null
	 */
	private function getViewData ($product) {

		if (!class_exists ('KlarnaAPI')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaapi.php');
		}
		if (!class_exists ('VirtueMartModelCurrency')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');
		}

		$price = $product->prices['salesPrice'];
		$country = $this->cData['country'];
		$lang = $this->cData['language_code'];

		$types = array(KlarnaPClass::CAMPAIGN, KlarnaPClass::ACCOUNT, KlarnaPClass::FIXED);

		$kCheckout = new KlarnaAPI($country, $lang, 'part', $price, KlarnaFlags::PRODUCT_PAGE, $this->klarna_virtuemart, $types, $this->path);

		$kCheckout->setCurrency ($this->cData['currency']);

		// TODO : Not top to get setup  values here!
		$this->settings = $kCheckout->getSetupValues ();
		if ($price > 0 && count ($kCheckout->aPClasses) > 0) {
			$currencydisplay = CurrencyDisplay::getInstance ();
			$sMonthDefault = NULL;
			$sTableHtml = "";
			$monthTable = array();
			// either in vendor's currency, or shipTo Currency
			$countryCurrencyId = ShopFunctions::getCurrencyIDByName ($this->cData['currency_code']);
			$currency = CurrencyDisplay::getInstance ($countryCurrencyId);
			$fromCurrency = $currency->getCurrencyForDisplay ();

			//$paymentCurrency = CurrencyDisplay::getInstance($this->cart->paymentCurrency);
			//$totalInPaymentCurrency = $paymentCurrency->priceDisplay( $this->cart->pricesUnformatted['billTotal'],$this->cart->paymentCurrency) ;
			//$currencyDisplay = CurrencyDisplay::getInstance($this->cart->pricesCurrency);
			$i = 0;
			foreach ($kCheckout->aPClasses as $pclass) {
				if ($sMonthDefault === NULL || $pclass['monthlyCost'] < $sMonthDefault) {
					$sMonthDefault = $currency->priceDisplay ($pclass['monthlyCost'], $countryCurrencyId);
				}

				if ($pclass['pclass']->getType () == KlarnaPClass::ACCOUNT) {
					$pp_title = JText::_ ('VMPAYMENT_KLARNA_PPBOX_ACCOUNT');
				}
				else {
					$pp_title = $pclass['pclass']->getMonths () . " " . JText::_ ('VMPAYMENT_KLARNA_PPBOX_TH_MONTH');
				}

				$pp_price = $currency->priceDisplay ($pclass['monthlyCost'], $countryCurrencyId);
				$monthTable[$i] = array(
					'pp_title' => html_entity_decode ($pp_title),
					'pp_price' => $pp_price,
					'country'  => $country);
				$i++;
			}
			$cd = CurrencyDisplay::getInstance ($fromCurrency);
			$aInputValues = array();
			$aInputValues['defaultMonth'] = $sMonthDefault;
			$aInputValues['monthTable'] = $monthTable;
			$aInputValues['eid'] = $this->cData['eid'];
			$aInputValues['country'] = KlarnaCountry::getCode ($country);

			if ($country == KlarnaCountry::DE) {
				$aInputValues['asterisk'] = '*';
			}
			else {
				$aInputValues['asterisk'] = '';
			}

			return $aInputValues;
		}
		return NULL;
	}

}
