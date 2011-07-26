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
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaViewBase', 'views._base' );

class TiendaViewGroups extends TiendaViewBase 
{
	
	/**
     * 
     * @param $tpl
     * @return unknown_type
     */
    function getLayoutVars($tpl=null) 
    {
    	JFactory::getApplication()->enqueueMessage( 'id - '.$this->getModel()->getId() );
        $layout = $this->getLayout();
        switch(strtolower($layout))
        {
        	case "selectusers":
                $this->_default($tpl);
              break;
            case "form":
                JRequest::setVar('hidemainmenu', '1');
                $this->_form($tpl);
              break;
            case "default":
            default:
                $this->set( 'leftMenu', 'leftmenu_users' );
                $this->_default($tpl);
              break;
        }
    }
}
