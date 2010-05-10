<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
JHTML::_('script', 'joomla.javascript.js', 'includes/js/');
Tienda::load( 'TiendaGrid', 'library.grid' );
$state = @$this->state;
$order = @$this->order;
$items = @$this->orderitems;
?>

<div class="cartitems">

           <table class="adminlist" style="clear: both;">
            <thead>
                <tr>
                    <th style="text-align: left;"><?php echo JText::_( "Product" ); ?></th>
                    <th style="width: 50px;"><?php echo JText::_( "Quantity" ); ?></th>
                    <th style="width: 50px;"><?php echo JText::_( "Total" ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php $i=0; $k=0; ?> 
            <?php foreach ($items as $item) : ?>
                <tr class="row<?php echo $k; ?>">
                    <td>
                        <a href="<?php echo JRoute::_("index.php?option=com_tienda&controller=products&view=products&task=view&id=".$item->product_id); ?>">
                            <?php echo $item->orderitem_name; ?>
                        </a>
                        <br/>
                        
                        <?php if (!empty($item->orderitem_attribute_names)) : ?>
                            <?php echo $item->orderitem_attribute_names; ?>
                            <br/>
                        <?php endif; ?>
                        
                        <?php echo JText::_( "Price" ); ?>:
                        <?php $price = $item->orderitem_price + floatval( $item->orderitem_attributes_price ); ?> 
                        <?php echo TiendaHelperBase::currency($price); ?>
                    </td>
                    <td style="width: 50px; text-align: center;">
                        <?php echo $item->orderitem_quantity;?>  
                    </td>
                    <td style="text-align: right;">
                        <?php $itemsubtotal = $price * $item->orderitem_quantity; ?>
                        <?php echo TiendaHelperBase::currency($itemsubtotal); ?>                       
                    </td>
                </tr>
              
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="font-weight: bold; white-space: nowrap;">
                        <?php echo JText::_( "Subtotal" ); ?>
                    </td>
                    <td colspan="3" style="text-align: right;">
                        <?php echo TiendaHelperBase::currency($order->order_subtotal); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <table class="adminlist" style="clear: both;">
                <tr>
                    <td colspan="2" style="white-space: nowrap;">
                        <b><?php echo JText::_( "Tax and Shipping Totals" ); ?></b>
                        <br/>
                    </td>
                    <td colspan="2" style="text-align: right;">
                    <?php 
                    	$display_shipping_tax = TiendaConfig::getInstance()->get('display_shipping_tax', '1');
                    	$display_taxclass_lineitems = TiendaConfig::getInstance()->get('display_taxclass_lineitems', '0');
                    	if ($display_taxclass_lineitems)
                    	{
                            foreach ($order->getTaxClasses() as $taxclass)
                            {
                                $tax_desc = $taxclass->tax_rate_description ? $taxclass->tax_rate_description : 'Tax';
                                if ($order->getTaxClassAmount( $taxclass->tax_class_id ))
                                    echo JText::_( $tax_desc ).":<br/>";
                            }
                    	}
                    	    else
                    	{
                    	    echo JText::_("Product Tax").":<br>";    
                    	}
   						
                    	echo JText::_("Shipping and Handling").":";
                    	if ($display_shipping_tax)
                    		echo "<br>".JText::_("Shipping Tax").":";
                    ?>
                    </td>
                    <td colspan="2" style="text-align: right;">
                     <?php 
                        if ($display_taxclass_lineitems)
                        {
                            foreach ($order->getTaxClasses() as $taxclass)
                            {
                                if ($order->getTaxClassAmount( $taxclass->tax_class_id ))
                                    echo TiendaHelperBase::currency($order->getTaxClassAmount( $taxclass->tax_class_id ), $order->currency)."<br/>";
                            }
                        }
                            else
                        {
                            echo TiendaHelperBase::currency($order->order_tax) . "<br>";    
                        }
                        
                    	echo TiendaHelperBase::currency($order->order_shipping);
                    	if ($display_shipping_tax)
                    		echo "<br>" . TiendaHelperBase::currency($order->order_shipping_tax);	
                    ?>                  
                    </td>
                </tr>
                <tr>
                	<td colspan="3" style="font-weight: bold; white-space: nowrap;">
                        <?php echo JText::_( "Total" ); ?>
                    </td>
                    <td colspan="3" style="text-align: right;">
                        <?php echo TiendaHelperBase::currency($order->order_total); ?>
                    </td>
                </tr>                
        </table>        
</div>
