<?php

/**
 * Product related functions for cart example.
 *
 * @author Daniel Gil Jara (danielgiljara@gmail.com)
 */

/**
 * Theme product page.
 *
 * @param integer $id
 *   Product ID.
 *
 * @return
 *   HTML data containing product view.
 */
function theme_product($id) {
  if ($product = products_get(array('product_id' => $id))) {
    $output = '<h1>' . $product['nombre_esp'] . '</h1>';
    $output .= '<a href="./imagenes/producto/' . $product['file_foto'] . '"><img class="thumbnail" align="right" src="./imagenes/producto/' . $product['file_foto'] . '" /></a>';
    $output .= isset_or($product['desc_esp'], 'Sin descripción por el momento.');
    $output .= '<form method="POST">';
    $output .= '  <span class="price">' . theme_price($product['precio']) . '</span><br />
                  <input type="text" name="quantity" size="2" value="1" />x 
                  <input type="submit" name="submit" value="Añadir" /><input type="hidden" name="id" value="' . $product['producto_id'] . '" />';
    $output .= '</form>';

    return $output;
  }
  return '<p>No se ha encontrado el producto solicitado.</p>';
}
