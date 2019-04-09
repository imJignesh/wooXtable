<?php

/**
 * Plugin Name: wooXtable
 * Plugin URI: #
 * Description: Woocommerce bulk pricing.
 * Version: 1.11
 * Author: JigsP
 * Author URI: #
 * Copyright: (c) 2017.
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 4.5
 * Tested up to: 4.8.3
 * Text Domain: woox
 */

global $qtyHeaders;
$qtyHeaders = ['1-11', '12-35', '36-71', '71-143', '144-287', '288-575', '576+', 'Stock'];
add_action('admin_enqueue_scripts','add_css_js_admin');


function add_css_js_admin(){
    wp_enqueue_style( 'woo-x-table', plugins_url( '/assets/woo-x-table.css' , __FILE__ ) );
    wp_enqueue_script( 'woo-x-table', plugins_url( '/js/jquery.jexcel.js' , __FILE__ ), array(), '1.0.0', true );
}

add_action('wp_enqueue_scripts','add_css_js_front');

function add_css_js_front(){
    wp_enqueue_style( 'woo-x-table', plugins_url( '/assets/woo-x-table.css' , __FILE__ ) );
    wp_enqueue_script( 'woo-x-front', plugins_url( '/js/woo-x-table.js' , __FILE__ ), array('jquery'), '1.0.0', true );
}

// First Register the Tab by hooking into the 'woocommerce_product_data_tabs' filter
//add_filter( 'woocommerce_product_data_tabs', 'add_my_custom_product_data_tab' );
function add_my_custom_product_data_tab( $product_data_tabs ) {
    $product_data_tabs['pricetable-tab'] = array(
        'label' => __( 'Price Table', 'woocommerce' ),
        'target' => 'my_custom_product_data',
        'class'     => array( 'show_if_variable' ),
    );
    return $product_data_tabs;
}

/** CSS To Add Custom tab Icon */
function wcpp_custom_style() {?>
    <style>
        #woocommerce-product-data ul.wc-tabs li.my-custom-tab_options a:before {
            font-family: WooCommerce;
            content: '\e006';
        }
    </style>
    <?php 
}
//add_action( 'admin_head', 'wcpp_custom_style' );

// functions you can call to output text boxes, select boxes, etc.
//add_action('woocommerce_product_data_panels', 'woocom_custom_product_data_fields');

function woocom_custom_product_data_fields() {
    global $post;

    // Note the 'id' attribute needs to match the 'target' parameter set above
    ?>
        <div id='my_custom_product_data' class='panel woocommerce_options_panel'>
            <?php
        ?>
                <div class='options_group'>
                    <?php
              // Text Field
  woocommerce_wp_text_input(
    array(
      'id' => '_text_field',
      'label' => __( 'Custom Text Field', 'woocommerce' ),
      'wrapper_class' => 'show_if_variable', //show_if_simple or show_if_variable
      'placeholder' => 'Custom text field',
      'desc_tip' => 'true',
      'description' => __( 'Enter the custom value here.', 'woocommerce' )
    )
  );

  // Number Field
  woocommerce_wp_text_input(
    array(
      'id' => '_number_field',
      'label' => __( 'Custom Number Field', 'woocommerce' ),
      'placeholder' => '',
      'wrapper_class' => 'show_if_variable', //show_if_simple or show_if_variable

      'description' => __( 'Enter the custom value here.', 'woocommerce' ),
      'type' => 'number',
      'custom_attributes' => array(
         'step' => 'any',
         'min' => '15'
      )
    )
  );

 
        ?>
                </div>
        </div>
        <?php
}

/** Hook callback function to save custom fields information */
function woocom_save_proddata_custom_fields($post_id) {
    // Save Text Field
  
    // Save Hidden field
    $hidden = $_POST['_hidden_field'];
    if (!empty($hidden)) {
        update_post_meta($post_id, '_hidden_field', esc_attr($hidden));
    }
}

add_action( 'woocommerce_process_product_meta_simple', 'woocom_save_proddata_custom_fields'  );

// You can uncomment the following line if you wish to use those fields for "Variable Product Type"
//add_action( 'woocommerce_process_product_meta_variable', 'woocom_save_proddata_custom_fields'  );


function woocom_custom_product_data_tab( $original_prodata_tabs) {
    $new_custom_tab['pricetable-tab'] = array(
        'label' => __( 'Price Table', 'woocommerce' ),
        'target' => 'my_custom_product_data_tab',
        'class'     => array( 'show_if_simple', 'show_if_variable'  ),
    );
    $insert_at_position = 2; // Change this for desire position
    $tabs = array_slice( $original_prodata_tabs, 0, $insert_at_position, true ); // First part of original tabs
    $tabs = array_merge( $tabs, $new_custom_tab ); // Add new
    $tabs = array_merge( $tabs, array_slice( $original_prodata_tabs, $insert_at_position, null, true ) ); // Glue the second part of original
    return $tabs;
}




/**/


// Add Variation Custom fields

//Display Fields in admin on product edit screen
add_action( 'woocommerce_product_after_variable_attributes', 'woo_variable_fields', 10, 3 );

//Save variation fields values
add_action( 'woocommerce_save_product_variation', 'save_variation_fields', 10, 2 );

// Create new fields for variations
function woo_variable_fields( $loop, $variation_data, $variation ) {
global $product;
    global $qtyHeaders;
  echo '<div class="variation-custom-fields">';

   
      // Textarea
      woocommerce_wp_checkbox( 
        array( 
          'id'          => '_woox_enable_'. $loop, 
          'label'       => __( ' &nbsp; Enable Price Table ', 'woocommerce' ), 
          //'desc_tip'    => true,
          // 'wrapper_class' => 'form-row',
          //'description' => __( 'Enter the custom value here.', 'woocommerce' ),
          'value'       => get_post_meta($variation->ID, '_woox_enable', true)
        )
      );

      $pt = get_post_meta($variation->ID,'_pricetable',true);
      // Hidden field
      woocommerce_wp_hidden_input(
      array( 
        'id'    => '_pricetable_'. $loop , 
        'value' => $pt
        )
      );
// $variation->post_parent

 // print_r($pv);
  
    ?>
            <div id="pricetable_<?php echo $loop; ?>"></div>
            <?php 
    $wooproduct = new WC_Product($variation->post_parent);
    $_pa = explode(",",$wooproduct->get_attribute( 'pa_size' )); 

?>
                <script>
                    function makerowHeaders() {
                        var headers = <?php echo json_encode($_pa); ?>;
                        jQuery('.jexcel').each(function (i) {
                            jQuery(this).find('tbody tr td.jexcel_label').each(function (j) {
                                jQuery(this).text(headers[j]);
                            })
                        });
                    }

                    function getData_<?php echo $loop; ?> () {
                        var getdatatxt = jQuery('#pricetable_<?php echo $loop ?>').jexcel('getData');
                        jQuery('#_pricetable_<?php echo $loop ?>').val(JSON.stringify(getdatatxt));
                        //  console.log(jQuery('#_pricetable_<?php echo $loop ?>').val());
                    }
                    data = <?php echo $pt?htmlspecialchars_decode($pt):'""'; ?>;
                    jQuery('#pricetable_<?php echo $loop; ?>').jexcel({
                        data: data
                        , colWidths: [80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80]
                        , colHeaders: <?php echo json_encode($qtyHeaders); ?>
                        , onchange: function (obj, cell, val) {
                            makerowHeaders();
                            getData_<?php echo $loop; ?> ();
                        }
                        , onload: function () {
                            makerowHeaders();
                            getData_<?php echo $loop; ?> ();
                        }
                        , oninsertrow: function () {
                            makerowHeaders();
                            getData_<?php echo $loop; ?> ();
                        }
                        , oninsertcolumn: function () {
                            makerowHeaders();
                            getData_<?php echo $loop; ?> ();
                        }
                    });
                </script>
                <?php
  echo '<div>';
  foreach($pt as $row){
    //print_r($pt)
  }
  echo '</div>';
   
  echo "</div>"; 

}

/** Save new fields for variations */
function save_variation_fields( $variation_id, $i) {
    
    $enabled = $_POST['_woox_enable_'.$i];
    if( ! empty( $enabled ) ) {
      update_post_meta( $variation_id, '_woox_enable', esc_attr( $enabled ) );
    }
     
    // Hidden field
    $hidden = $_POST['_pricetable_'.$i];
    if( ! empty( $hidden ) ) {
      update_post_meta( $variation_id, '_pricetable', esc_attr( $hidden ) );
    }
}



/****
frontend
******/

add_action('wp_head','pluginname_ajaxurl');
	function pluginname_ajaxurl() {
	?>
                    <script type="text/javascript">
                        var ajaxurl = '<?php echo admin_url("admin-ajax.php"); ?>';
                    </script>
                    <?php
	
	
}

function get_price_from_column($row,$qty){
    global $qtyHeaders;
    $i=0;
    //print_r($row);echo "-";
    for($i=0;$i<count($row);$i++){
         $minmax = explode('-',$qtyHeaders[$i]);
         if($qty > $minmax[0] && $qty < $minmax[1]){
             
           // print_r($row[$i]);echo "\n";
            return $row[$i]; 
        }
    }
  
}

function get_row_name($i,$sizes){
    $sizelist = explode(",",$sizes);
    return sanitize_title($sizelist[$i]);
   
}



function woox_get_price($product_id,$row_name,$qty,$sizes){
   // $row_name = s m l etc
    global $qtyHeaders;
   
    $pricetable = json_decode(html_entity_decode(get_post_meta( $product_id, '_pricetable',true )));

        $i=0;
    	foreach($pricetable as $row){
            
            //price in selected cell
            if( $row_name == get_row_name($i,$sizes)){
                
            for($j=0; $j < count($pricetable[0]); $j++){
                
                //$current_cell_price =  $pricetable[$i][$j];
              //  echo $current_cell_price . " ".$qtyHeaders[$j] ."\n"; //exit;
                $price_from_table =  get_price_from_column( $pricetable[$i],$qty);
                if($price_from_table){
                    
                  //  echo $price_from_table ."\n";
                    return $price_from_table;
                    }
                }
            }

            $i++;
        }//exit;

}

add_action( 'wp_ajax_add_product', 'woox_add_product' );
add_action( 'wp_ajax_nopriv_add_product', 'woox_add_product' );

function woox_add_product() {
    global $woocommerce,$cart_item_data;
	$prices = array();
	
	//print_r($cart_item_data);
	if(isset($_POST['data'])){
         parse_str($_POST['data'], $post);
         $product_id = $post['pid'];
        $cartqtys = array();
        $cartvarqty=array();
		//$post = ($_POST['data']);
       
       // print_r($product_id);
        $variation = wc_get_product($product_id);
        $product = wc_get_product( $variation->get_parent_id() );
        $_pa = ($product->get_attribute( 'pa_size' ));

        foreach($post as $key=>$currentdata){
            if(strpos($key,"woox_price_")!== false){
               // print_r($currentdata);
                 $trimkey = str_replace("woox_price_","",$key);
                 $kv =  explode("-",$trimkey);
                 $cartvarqty[$kv[1]] = $currentdata;
                 $cartqtys[] = $currentdata;
            }
            
        }
       // print_r($cartqtys);
        
        $wholesellquanty="";
        foreach($cartvarqty as $k=>$v){   
            $prices[$k] = (woox_get_price($product_id,$k,$v,$_pa));
            $wholesellquanty .= $k . " x " . $cartvarqty[$k] . "<br/>";
            
        }
        $i=0;
        $total=0;
        
            foreach($prices as $k=>$v){
                
                $q = $cartqtys[$i];
                $total = $total + ($v*$q);
                $i++;
            }
       // print_r($total);
		
        foreach($woocommerce->cart->get_cart() as $cart_item_key => $values ) {
		  $_product = $values['data'];
		  $cartids[] = $_product->id;
			}
        
        $custom_price = $total;
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
					
        $_SESSION['woox_pid'] = $product_id;
        $_SESSION['woox_custom_data'] = $wholesellquanty;
		$_SESSION['woox_custom_price'] = $custom_price;
		$woocommerce->cart->add_to_cart($product_id,1);
        // WC()->session->set( $cart_item_key.'_gift_wrap_fee', 'YES' );
									
        $items = [];
        $items = $woocommerce->cart->cart_contents;
        //print_r($woocommerce->cart->cart_contents);
        foreach($items as $item => $values) { 
            // $values['data']->price = $custom_price;
            if($values['data']->id==$product_id){
                //  $items[$item]['data']->price = $custom_price;
				$woocommerce->cart->cart_contents[$item]['data']->price = $custom_price;
                //  print_r($woocommerce->cart->cart_contents[$item]['data']->price.'<br/>');
				}
				$woocommerce->cart->cart_contents =  $items;
        } 
            //wc_clear_notices();			
	}
	wp_send_json( array('location'=>WC()->cart->get_checkout_url()) );
	
	die();
}
 

function save_price( $cart_item_key, $product_id = null, $quantity= null, $variation_id= null, $variation= null ) {
   //echo $_SESSION['woox_custom_price'];
     WC()->session->set( $cart_item_key.'woox_custom_data', $_SESSION['woox_custom_data']);
	 WC()->session->set( $cart_item_key.'woox_custom_price', $_SESSION['woox_custom_price']);
}
add_action( 'woocommerce_add_to_cart', 'save_price', 1, 5 );


function woox_calculate_gift_wrap_fee( $cart_object ) {
    /* Gift wrap price */
   //$additionalPrice = $_SESSION['wdm_user_custom_price'];
    foreach ( $cart_object->cart_contents as $key => $value ) {       
        if( WC()->session->__isset( $key.'woox_custom_data' ) ) {
            $quantity = intval( $value['quantity'] );
            $orgPrice = 0;//intval( $value['data']->price );
			$additionalPrice =  WC()->session->get( $key.'woox_custom_price' );
            $value['data']->price = ( ( $orgPrice + $additionalPrice ) * $quantity );
        }           
    }
}
//add_action( 'woocommerce_before_calculate_totals', 'woox_calculate_gift_wrap_fee', 1, 1 );

function calculate_embossing_fee( $cart_object ) {
    if( !WC()->session->__isset( "reload_checkout" )) {
        /* Gift wrap price */
        $additionalPrice = 0;
        foreach ( $cart_object->cart_contents as $key => $value ) {
            if( isset( $value["quantity"] ) ) {
                // Turn $value['data']->price in to $value['data']->get_price()
                $additionalPrice = WC()->session->get( $key.'woox_custom_price' );
                $orgPrice = floatval( $value['data']->get_price() );
                $discPrice = $orgPrice + $additionalPrice;
                $value['data']->set_price($discPrice);
            }
        }
    }
}
add_action( 'woocommerce_before_calculate_totals', 'calculate_embossing_fee', 99 );


function woox_render_meta_on_cart_item( $title = null, $cart_item = null, $cart_item_key = null ) {
    if( $cart_item_key && is_cart() ) {
        if( WC()->session->__isset( $cart_item_key.'woox_custom_data' ) ) {
            echo $title. '<dl class="variation">
                 <dt>Quantities: </dt>
                 <dd><p>'.  nl2br(WC()->session->get( $cart_item_key.'woox_custom_data' )) .'</p></dd>            
              </dl>';
        } else {
            echo $title;
        }
    }else {
        echo $title;
    }
}
add_filter( 'woocommerce_cart_item_name', 'woox_render_meta_on_cart_item', 1, 3 );


function render_meta_on_checkout_order_review_item( $quantity = null, $cart_item = null, $cart_item_key = null ) {
    if( $cart_item_key ) {
        if( WC()->session->__isset( $cart_item_key.'woox_custom_data' ) ) {
            echo $quantity. '<dl class="variation">
                <dt>Quantities: </dt>
                 <dd><p>'.  nl2br(WC()->session->get( $cart_item_key.'woox_custom_data' )) .'</p></dd>                   
              </dl>';
        } else {
            echo $quantity;
        }
    }
}
add_filter( 'woocommerce_checkout_cart_item_quantity', 'render_meta_on_checkout_order_review_item', 1, 3 );

function gift_wrap_order_meta_handler( $item_id, $values, $cart_item_key ) {
    if( WC()->session->__isset( $cart_item_key.'woox_custom_data' ) ) {
        wc_add_order_item_meta( $item_id, "quantities", WC()->session->get( $cart_item_key.'woox_custom_data' ) ); 
    }
}
add_action( 'woocommerce_add_order_item_meta', 'gift_wrap_order_meta_handler', 1, 3 );


function action_woocommerce_before_add_to_cart_button(  ) { 

    global $product;
    global $qtyHeaders;
    $headings = (array)($qtyHeaders);
    echo "<div class='woox_tab'>";
    $available_variations = $product->get_available_variations();
    $parent_id = $product->get_id();   
    $i=0;

    foreach($available_variations as $current_variation){
        $i++;
              
        if(get_post_meta( $current_variation['variation_id'], '_woox_enable', true )=="yes"){
          

       //  = get_post_meta( $parent_id, '_product_attributes', true );
        $_pa = explode(",",$product->get_attribute( 'pa_size' ));
            //print_r($_pa);
        
            
        $attlist = ($current_variation['attributes']);
        if(array_key_exists("attribute_pa_color",$attlist)){
            echo "<div class='woo_var' id='woox_".$attlist['attribute_pa_color']."'>";
            $tableArr = (htmlspecialchars_decode(get_post_meta( $current_variation['variation_id'],"_pricetable",true )));
            $colors = $terms = get_terms('pa_colors', $options);

            $tablejson = (json_decode($tableArr));
            $colLength = count($tablejson[0]);
            
            $vname = "Size";
            echo "<form method='get' id='woox_frm_".$attlist['attribute_pa_color']."'><input name='pid' value='".$current_variation['variation_id']."' type='hidden'/><table border=1>";
             echo "<th>";

               
                for($i=0; $i< $colLength; $i++){
                        echo "<td>".$headings[$i]."</td>";
                }
                echo "<td>Quantity</td>";
                echo "</th>";
            $i=0;
            foreach($tablejson as $row){
               $vname = $_pa[$i];  
               // echo "<th>HEADING</th>";
                echo "<tr>";
                echo "<td>".$vname."</td>";
                foreach($row as $col){
                    echo "<td>".$col."</td>";
                }
                 echo "<td><input id='woox_price_". sanitize_title($vname)."' name='woox_price_" .$attlist['attribute_pa_color'] ."-".sanitize_title($vname)."' value='0' type='number' style='width:50px' /></td>";
                echo "</tr>";
                $i++;
            }
            echo "</table>";
            echo "<input name='button' value='Inquire us' type='button' class='woox-add-to-cart'/>";
             echo "</form></div>";
        }
        
        } 
    }
    echo "</div>" ;
}; 
         
// add the action 
add_action( 'woocommerce_after_add_to_cart_form', 'action_woocommerce_before_add_to_cart_button', 1, 10 ); 
//add_action( 'woocommerce_before_add_to_cart_button', 'action_woocommerce_before_add_to_cart_button', 1, 10 );


remove_action( 'woocommerce_single_product_summary','woocommerce_template_single_price',10 );
remove_action( 'woocommerce_single_product_summary','woocommerce_template_single_add_to_cart',30 );
