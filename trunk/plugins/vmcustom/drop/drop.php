<?php
defined('_JEXEC') or 	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
//@Banquet Tables Pro custom dropbox plugin.
//Lets go 2.0

if (!class_exists('vmCustomPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmcustomplugin.php');

class plgVmCustomDrop extends vmCustomPlugin {

	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);

		$varsToPush = array(
		'custom_drop'=> array('', 'string'),
		'custom_drop_name'=> array('', 'string'),
		);

		$this->setConfigParameterable('custom_params',$varsToPush);
	}

	// get product param for this plugin on edit
	function plgVmOnProductEdit($field, $product, &$row,&$retValue) {
		if ($field->custom_element != $this->_name) return '';
		$this->parseCustomParams($field);
		$html ='
			<fieldset>
				<legend>'. JText::_('VMCUSTOM_DROP_BOX') .'</legend>
				<table class="admintable">
					'.VmHTML::row('input','VMCUSTOM_DROP_STRING_NAME','custom_param['.$row.'][custom_drop_name]',$field->custom_drop_name).'
					'.VmHTML::row('input','VMCUSTOM_DROP_STRING','custom_param['.$row.'][custom_drop]',$field->custom_drop).'
				</table>
			</fieldset>';
		$retValue .= $html;

		return true ;
	}


	function plgVmOnDisplayProductVariantFE($field,&$idx,&$group) {
		// default return if it's not this plugin
		 if ($field->custom_element != $this->_name) return '';
		$this->parseCustomParams($field);
		$options = explode(',', $field->custom_drop);
		$class='';
		$selects= array();
		if(!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');
		$currency = CurrencyDisplay::getInstance();
		foreach ($options as $valuesWithPrice) {
			$valueWithPrice = explode('|', $valuesWithPrice);

			if ( isset ($valueWithPrice[1]) ) {
				$op = $valueWithPrice[1][0];
				$price = substr($valueWithPrice[1], 1) ;
				$text = $valueWithPrice[0].' ('.$op.$currency->priceDisplay((float)$price).')';
			} else {
				$text = $valueWithPrice[0] ;
			}
			$selects[] = array('value' =>$valueWithPrice[0], 'text' => $text );
		}
// 		vmdebug('plgVmOnDisplayProductVariantFE',$field,$idx,$group);
		$html = JHTML::_('select.genericlist', $selects,'customPlugin['.$field->virtuemart_customfield_id.']['.$this->_name.'][custom_drop]','','value','text',$selects[0],false,true);
		$group->display .= $html;
		return true;
    }

	public function plgVmCalculateCustomVariant($product, &$productCustomsPrice,$selected){
		if ($productCustomsPrice->custom_element !==$this->_name) return ;
		$customVariant = $this->getCustomVariant($product, $productCustomsPrice,$selected);
		$this->parseCustomParams($productCustomsPrice);
		$productCustomsPrice->custom_price = 0 ;
		if ( isset ($customVariant['custom_drop'])) {
			$options = explode(',', $productCustomsPrice->custom_drop);
			foreach ($options as $valuesWithPrice) {
				$valueWithPrice = explode('|', $valuesWithPrice);

				if ( $customVariant['custom_drop'] == $valueWithPrice[0])  {
					if ( isset ($valueWithPrice[1]) ) {

						$op = $valueWithPrice[1][0];
						if ($op == '+') $productCustomsPrice->custom_price = (float) substr($valueWithPrice[1], 1);
						else if ($op == '-') $productCustomsPrice->custom_price = -(float) substr($valueWithPrice[1], 1);
						else $productCustomsPrice->custom_price = (float) $valueWithPrice[1] ;

					}
						return ;
				}

			}
		}
// 			return $field->custom_price;

	}

	function plgVmOnViewCart($product,$row,&$html) {
		if (empty($product->productCustom->custom_element) or $product->productCustom->custom_element != $this->_name) return '';
		if (!$plgParam = $this->GetPluginInCart($product)) return false ;

		$separator= '';
		$myparams = json_decode($product->productCustom->custom_param);
		$html  .= '<span>'.$myparams->custom_drop_name.' ';
		foreach ($plgParam as $k => $item) {
			if($product->productCustom->virtuemart_customfield_id==$k){

				if(!empty($item['custom_drop']) ){
					$html .=$separator.$item['custom_drop'].'</span>';
					$separator= ',';
				}
			}
		}

		$html .='';

		return true;
    }



    /**
    * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCartModule()
    * @author Patrick Kohl
    */
    function plgVmOnViewCartModule( $product,$row,&$html) {
    	return $this->plgVmOnViewCart($product,$row,$html) ;
    }

    /**
     *
     * vendor order display BE
     */
    function plgVmDisplayInOrderBE($item, $row, &$html) {
    	if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
    	$this->plgVmOnViewCart($item,$row,$html); //same render as cart
    }

    /**
     *
     * shopper order display FE
     */
    function plgVmDisplayInOrderFE($item, $row, &$html) {
    	if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
    	$this->plgVmOnViewCart($item,$row,$html); //same render as cart
    }

	/**
	 * We must reimplement this triggers for joomla 1.7
	 * vmplugin triggers note by Max Milbers
	 */
	public function plgVmOnStoreInstallPluginTable($psType) {
	}


	function plgVmDeclarePluginParamsCustom($psType,$name,$id, &$data){
		return $this->declarePluginParams($psType, $name, $id, $data);
	}

	function plgVmSetOnTablePluginParamsCustom($name, $id, &$table){
		return $this->setOnTablePluginParams($name, $id, $table);
	}

	/**
	 * Custom triggers note by Max Milbers
	 */
	function plgVmOnDisplayEdit($virtuemart_custom_id,&$customPlugin){
		return $this->onDisplayEditBECustom($virtuemart_custom_id,$customPlugin);
	}


	public function plgVmDisplayInOrderCustom(&$html,$item, $param,$productCustom, $row ,$view='FE'){
		$this->plgVmDisplayInOrderCustom($html,$item, $param,$productCustom, $row ,$view);
	}

	public function plgVmCreateOrderLinesCustom(&$html,$item,$productCustom, $row ){
	}


}

// No closing tag