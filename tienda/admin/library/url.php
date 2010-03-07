<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

class TiendaUrl {
	
	/**
	 * Wrapper that adds the current Itemid to the URL
	 *
	 * @param	string $string The string to translate
	 *
	 */
	function &_( $url, $text, $params='', $xhtml=true, $ssl=null, $addItemid='1' ) {
		if ($addItemid == '1') { $url = TiendaUrl::addItemid($url); }
		$return = "<a href='".JRoute::_($url, $xhtml, $ssl)."' ".addslashes($params)." >".$text."</a>";
		return $return;			
	}

	/**
	 * Wrapper that adds the current Itemid to the URL
	 *
	 * @param	string $string The string to translate
	 *
	 */
	function &addItemid( $url ) {
		global $Itemid;
		$return = $url;
		$return.= "&Itemid=".$Itemid;
		return $return;			
	}

	/**
	 * Displays a url in a lightbox
	 * 
	 * @param $url
	 * @param $text
	 * @param array options( 
	 * 				'width',
	 *				'height',
	 * 				'top',
	 * 				'left',
	 * 				'class',
	 * 				'update',
	 * 				'img'
	 * 				)
	 * @return popup html
	 */
	function popup( $url, $text, $options = array() ) 
	{		
		$html = "";
		($options['update'])
            ? JHTML::_('behavior.modal', 'a.modal', array('onClose'=>'\function(){tiendaUpdate();}'))
            : JHTML::_('behavior.modal');

		$url;
		
		$handler = ($options['img'])
		  ? "{handler:'image'}"
		  : "{handler:'iframe',size:{x:window.getSize().scrollSize.x-80, y: window.getSize().size.y-80}, onShow:$('sbox-window').setStyles({'padding': 0})}";

		if (!empty($options['width']))
		{
			if (empty($options['height']))
			{
				$options['height'] = 480;
			}
			$handler = "{handler: 'iframe', size: {x: ".$options['width'].", y: ".$options['height']. "}}";
		}

		$html	= "<a class=\"modal\" href=\"$url\" rel=\"$handler\" >\n";
		$html 	.= "<span class=\"".$options['class']."\" >\n";
        $html   .= "$text\n";
		$html 	.= "</span>\n";
		$html	.= "</a>\n";
		
		return $html;
	}

}