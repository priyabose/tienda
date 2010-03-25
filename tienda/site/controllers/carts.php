<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

JLoader::import( 'com_tienda.helpers.carts', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaControllerCarts extends TiendaController
{

	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();

        $this->set('suffix', 'carts');
	}
	
    /**
     * Sets the model's state
     * 
     * @return array()
     */
    function _setModelState()
    {       
        $state = parent::_setModelState();      
        $app = JFactory::getApplication();
        $model = $this->getModel( $this->get('suffix') );
        $ns = $this->getNamespace();
                        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        return $state;
    }

    /**
     * Adds an item to a User's shopping cart
     * whether in the session or the db 
     * 
     */
    function addToCart()
    {
        $response = array();
        $response['msg'] = '';
        $response['error'] = '';
        
        // get elements from post
        $elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
        
        // convert elements to array that can be binded             
        JLoader::import( 'com_tienda.helpers._base', JPATH_ADMINISTRATOR.DS.'components' );
        $values = TiendaHelperBase::elementsToArray( $elements );
        $product_id = $values['product_id'];
        $product_qty = $values['product_qty'];
        $attributes = array();
        
        foreach ($values as $key=>$value)
        {
        	if (substr($key, 0, 10) == 'attribute_')
        	{
        		$attributes[] = $value;
        	}
        }
        $attributes_csv = implode( ',', $attributes );
        
        $suffix = strtolower(TiendaHelperCarts::getSuffix());
        $model = $this->getModel($suffix);
        
        switch ($suffix) 
        {
	        case 'sessioncarts':
	            $cart = $model->getList();
	            $isPresent = false;
	            
	            // find the product in the visitor's cart, if it exists 
	            foreach ($cart as $cartitem) 
	            {
	            	// TODO Make this support vendor_id
	                if ($cartitem->product_id == $product_id && $cartitem->product_attributes == $attributes_csv) 
	                {
	                    // if the item has been found, update quantities
	                    $isPresent = true;
	                    $cartitem->user_id = JFactory::getUser()->id;
	                    $cartitem->product_id = $product_id;
	                	$cartitem->product_qty = $cartitem->product_qty + $product_qty;
	                    $cartitem->product_attributes = $attributes_csv;
	                    $cartitem->vendor_id = '0'; // vendors only in enterprise version
	                    // store the item so we can send it in the plugin event later
	                    $item = $cartitem;
	                }
	            }
	            
	            // if the item was not found in the cart, add it
	            if (!$isPresent) 
	            {
	                $item = new JObject;
	                $item->user_id = JFactory::getUser()->id;
	                $item->product_id = $product_id;
	                $item->product_qty = $product_qty;
	                $item->product_attributes = $attributes_csv;
	                $item->vendor_id = '0'; // vendors only in enterprise version
	                $cart[] = $item;
	            }
	
	            // Set the session cart with the new values
	            $mainframe =& JFactory::getApplication();
	            $mainframe->setUserState( 'usercart.cart', $cart );
	            break;
	            
	        case 'carts':
	        default:
	            $item = new JObject;
	            $item->user_id = JFactory::getUser()->id;
	            $item->product_id = $product_id;
	            $item->product_qty = $product_qty;
	            $item->product_attributes = $attributes_csv;
	            $item->vendor_id = '0'; // vendors only in enterprise version
	            $cart = array();
	            $cart[] = $item;
	            TiendaHelperCarts::updateDbCart($cart);
	            break;
        }
        
        // fire plugin event
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger( 'onAddToCart', array( $item ) );

        // TODO Do we want to do this?  Or use a back-order system?
        // TiendaHelperCarts::fixQuantities();
        
        // update the cart module, if it is enabled
        $this->displayCart();
    }

    /**
     * Displays the cart, expects to be called via ajax
     * 
     * @return unknown_type
     */
    function displayCart()
    {
        JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.DS.'components' );
      
        $mainframe =& JFactory::getApplication();
        $mainframe->setUserState( 'usercart.isAjax', true );

        jimport( 'joomla.application.module.helper' );
        
        $module = JModuleHelper::getModule('mod_tienda_cart');
        
        if ($module) {
            echo ( json_encode( array('msg'=>JModuleHelper::renderModule($module)) ) );
        }
        return;    
    }

    /**
     * 
     * @return unknown_type
     */
    function update()
    {
        $model 	= $this->getModel( strtolower(TiendaHelperCarts::getSuffix()) );
        $user =& JFactory::getUser();

        $cids = JRequest::getVar('cid', array(0), '', 'ARRAY');
        $product_attributes = JRequest::getVar('product_attributes', array(0), '', 'ARRAY');
        $quantities = JRequest::getVar('quantities', array(0), '', 'ARRAY');
                
        $remove = JRequest::getVar('remove');
        if ($remove) 
        {
            foreach ($cids as $key=>$product_id)
            {
                $row = $model->getTable();
                $ids = array('user_id'=>$user->id, 'product_id'=>$product_id, 'product_attributes'=>$product_attributes[$key] );
                if ($return = $row->delete($ids))
                {
	                $item = new JObject;
	                $item->product_id = $product_id;
	                $item->product_attributes = $product_attributes[$key];
	                $item->vendor_id = '0'; // vendors only in enterprise version
	                	
	                // fire plugin event
	                $dispatcher = JDispatcher::getInstance();
	                $dispatcher->trigger( 'onRemoveFromCart', array( $item ) );
                }
			}
        } 
            else 
        {
            $vals = array();            
            foreach($quantities as $key=>$value) 
            {
            	$keynames = explode('.', $key);
            	$product_id = $keynames[0];
                $vals['user_id'] = $user->id;
                $vals['product_id'] = $product_id;
                $vals['product_qty'] = $value;
                $vals['product_attributes'] = $product_attributes[$key];
                $row = $model->getTable();
                $row->bind($vals);
                $row->save();
            }
        }

        $this->setRedirect( 'index.php?option=com_tienda&controller=carts&view=carts', '', '');
    }
    
    /*
     * 
     */
    function confirmAdd()
    {
        $model  = $this->getModel( $this->get('suffix') );
        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setLayout('confirmadd');
        $view->display();
        $this->footer();
        return;
    }
}