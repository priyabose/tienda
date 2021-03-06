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
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaModelBase', 'models._base' );

class TiendaModelShippingMethodsWeightbased extends TiendaModelBase
{
	protected function _buildQueryWhere(&$query)
	{
		$filter         = $this->getState('filter');
		$filter_id_from = $this->getState('filter_id_from');
		$filter_id_to   = $this->getState('filter_id_to');
		$filter_name    = $this->getState('filter_name');
		$filter_enabled = $this->getState('filter_enabled');
		$filter_taxclass = $this->getState('filter_taxclass');
		$filter_price = $this->getState('filter_price');

		if ($filter)
		{
			$key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.shipping_method_weightbased_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.shipping_method_weightbased_name) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');
		}

		if (strlen($filter_enabled))
		{
			$query->where('tbl.shipping_method_weightbased_enabled = '.$filter_enabled);
		}

		if (strlen($filter_id_from))
		{
			if (strlen($filter_id_to))
			{
				$query->where('tbl.shipping_method_weightbased_id >= '.(int) $filter_id_from);
			}
			else
			{
				$query->where('tbl.shipping_method_weightbased_id = '.(int) $filter_id_from);
			}
		}

		if (strlen($filter_id_to))
		{
			$query->where('tbl.shipping_method_weightbased_id <= '.(int) $filter_id_to);
		}

		if (strlen($filter_name))
		{
			$key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_name ) ) ).'%');
			$query->where('LOWER(tbl.shipping_method_weightbased_name) LIKE '.$key);
		}

		if (strlen($filter_taxclass))
		{
			$query->where('tbl.tax_class_id = '.(int) $filter_taxclass);
		}

		if ( strlen($filter_price ) )
		{
			$query->where("(
        		tbl.shipping_method_price_start <= '".$filter_price."' 
        		AND (
                    tbl.shipping_method_price_end >= '".$filter_price."'
                    OR
                    tbl.shipping_method_price_end = '0.000'
                    )
			)");			
		}
	}

	protected function _buildQueryJoins(&$query)
	{
		$query->join('LEFT', '#__tienda_taxclasses AS taxclass ON tbl.tax_class_id = taxclass.tax_class_id');
	}

	protected function _buildQueryFields(&$query)
	{
		$field = array();
		$field[] = " taxclass.tax_class_name ";

		$query->select( $this->getState( 'select', 'tbl.*' ) );
		$query->select( $field );
	}

	public function getList()
	{
		$list = parent::getList();

		// If no item in the list, return an array()
		if( empty( $list ) ){
			return array();
		}

		foreach($list as $item)
		{
			$item->link = 'index.php?option=com_tienda&view=shipping&task=view&id='.@$_GET['id'].'&shippingTask=view&sid='.$item->shipping_method_weightbased_id;
		}
		return $list;
	}
}
