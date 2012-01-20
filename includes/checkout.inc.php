<?php

/**
 * Checkout functions for cart example.
 *
 * @author Daniel Gil Jara (danielgiljara@gmail.com)
 */

/**
 * Process form actions.
 */
if (isset($_POST['submit'])) {
  switch ($_POST['submit']) {
    case 'Actualizar pedido':
      cart_update($_POST['quantity']);
      break;
    case 'Realizar pedido':
      cart_submit($_POST);
  }
}

/**
 * Validate cart submission.
 *
 * @param array $values
 *   An associative array with POST values.
 *
 * @return
 *   TRUE if validates, FALSE if not.
 */
function cart_validate_submit($values) {
  $errors = array();
  if (empty($values['customer-firstname'])) {
    $errors[] = 'El nombre del cliente no puede estar vacío';
  }
  if (empty($values['customer-lastname'])) {
    $errors[] = 'Los apellidos del cliente no pueden estar vacíos';
  }
  if (empty($values['customer-email']) || !valid_email_address($values['customer-email'])) {
    $errors[] = 'El e-mail introducido no es válido.';
  }
  if (empty($values['customer-address'])) {
    $errors[] = 'La dirección de entrega no puede estar vacía.';
  }

  if (empty($errors)) {
    return TRUE;
  }
  $_SESSION['messages']['error'] += $errors;
  return FALSE;
}

/**
 * Submit cart order.
 *
 * @param array $values
 *   An associative array with POST values.
 */
function cart_submit($values) {
  if (cart_validate_submit($values)) {
    $subject = 'Datos del pedido';
    $body = theme_order_mail($_SESSION['cart'], $values);

    // Mail to different recipients.
    $admin_mail = 'tecnico@nivelz.com';
    foreach (array($admin_mail, $values['customer-email']) as $email) {
      cart_mail($subject, $body, $email);
    }

    // Notify user.
    $_SESSION['messages']['info'][] = 'Gracias por su compra. Recibirá un correo en ' . $values['customer-email'] . ' con los detalles del pedido.';

    // Empty cart.
    $_SESSION['cart'] = array();
  }
}

/**
 * Update cart quantities.
 *
 * @param array $quantities
 *   An associative array with quantities. Key are product ID and values are
 *   quantities to update.
 */
function cart_update($quantities) {
  $cart = &$_SESSION['cart'];
  foreach ($quantities as $id => $quantity) {
    // Remove product.
    if ($quantity == 0) {
      cart_del_product($id);
      $action = TRUE;
    }

    // Update product.
    elseif (isset($cart[$id]) && $cart[$id]['quantity'] != $quantity) {
      $cart[$id]['quantity'] = $quantity;
      $_SESSION['messages']['status'][] = 'Se ha actualizado la cantidad de <em>' . $cart[$id]['nombre_esp'] . '</em>';
      $action = TRUE;
    }
  }

  // Notify that no product are updated.
  if (!isset($action)) {
    $_SESSION['messages']['error'][] = 'No se ha actualizado ningún elemento del pedido.';
  }
}

/**
 * Send a HTML e-mail.
 */
function cart_mail($subject, $text, $to, $cc = NULL, $bcc = NULL, $from = 'Tienda 1 Nivel Z', $from_email = 'tienda1@nivelz.com') {
  // To send HTML mail, you can set the Content-type header.
  $headers = "MIME-Version: 1.0\r\n";
  $headers .= "Content-type: text/html; charset=utf-8\r\n"; 	
  $headers .= "From: $from <$from_email>\r\n";
  if ($cc != $to) {
    $headers .= $cc != '' ? 'CC: ' . $cc . "\r\n" : '';
  }
  if ($bcc != $to) {
    $headers .= $bcc != '' ? 'BCC: ' . $bcc . "\r\n" : '';
  }
	
  return mail($to, $subject, $text, $headers);
}

/**
 * Theme checkout page.
 *
 * @return
 *   HTML data.
 */
function theme_checkout($checkout) {
  $output = '<h1>Caja</h1>';
  if (!empty($_SESSION['cart'])) {
    $output .= '<form method="POST">';
    $cart = $_SESSION['cart'];
    $output .= '  <h2>Pedido</h2>';
    $output .= '  <table class="order">';
    $total = 0;
    foreach ($cart as $product_id => $item) {
      $output .= '    <tr>';
      $output .= '      <td class="preview"><a href="product.php?id=' . $product_id . '"><img src="./imagenes/producto/' . $item['file_foto'] . '" /></a></td>';
      $output .= '      <td class="quantity"><input type="text" name="quantity[' . $product_id . ']" value="' . $item['quantity'] . '" size="3" />x</td>';
      $output .= '      <td><a href="product.php?id=' . $product_id . '">' . $item['nombre_esp'] . '</a></td>';
      $output .= '      <td>' . theme_price((float) $item['precio'] * $item['quantity']) . '</td>';
      $output .= '      <td><input type="hidden" name="id" value="' . $product_id . '"><input type="submit" name="submit" value="Quitar"></td>';
      $output .= '    </tr>';
      $total += (int) $item['precio'] * $item['quantity'];
    }

    // Base.
    $output .= '    <tr>';
    $output .= '      <td colspan="3">Subtotal:</td>';
    $output .= '      <td>' . theme_price($total) .'</td>';
    $output .= '    </tr>';

    // Item VAT.
    $vat = vat($total);

    $output .= '    <tr>';
    $output .= '      <td colspan="3">IVA 18%:</td>';
    $output .= '      <td>' . theme_price($vat) .'</td>';
    $output .= '    </tr>';

    // Item Total.
    $output .= '    <tr>';
    $output .= '      <td colspan="3">Total:</td>';
    $output .= '      <td>' . theme_price($total + $vat) .'</td>';
    $output .= '    </tr>';

    $output .= '  </table>';
    $output .= '  <input type="submit" name="submit" id="update-order" value="Actualizar pedido" />';
    $output .= '</form><br />';
    $output .= '<form method="POST">';
    $output .= '  <h2>Formulario de envío</h2>';
    $output .= '  <p>Rellene el siguiente formulario para que le enviemos el pedido.</p>';

    // VAT, Subtotal, and Total
    foreach (array_keys(theme_checkout_form_deliver_item_names()) as $key) {
      $output .= theme_checkout_form_deliver_item($key);
    }

    $output .= '  <input type="submit" name="submit" value="Realizar pedido" />';
    $output .= '</form>';

    return $output;
  }
  return $output . '<p>No hay productos en la cesta.</p><p>Navegue a través de las categorías y añada algún producto.</p>';
}

/**
 * Get delivery form fields info.
 *
 * @return
 *   An associative array with data. Keys are field names and values are
 *   field titles.
 */
function theme_checkout_form_deliver_item_names() {
  return array(
    'customer-firstname' => 'Nombre',
    'customer-lastname' => 'Apellidos',
    'customer-email' => 'E-mail',
    'customer-address' => 'Dirección de Envío',
  );
}

/**
 * Creates an form item element for delivery form.
 *
 * @return
 *   HTML data.
 */
function theme_checkout_form_deliver_item($name) {
  $names = theme_checkout_form_deliver_item_names();
  $label = $names[$name];
  $value = isset_or($_POST[$name], '');
  $output = '<label for="$name">' . $label . ':</label> ';
  if ($name == 'customer-address') {
    $output .= '<br /><textarea size="4" id="' . $name . '" name="' . $name . '" />' . $value .'</textarea>';
  }
  else {
    $output .= '<input type="text" id="' . $name . '" name="' . $name . '" value="' . $value .'" />';
  }
  return $output . '<br />';
}

/**
 * Get mail body for cart order submission.
 *
 * @param array $cart
 *   An associative array containing cart elements.
 * @param array $customer_values
 *   An associative array with POST values of delivery form submission.
 *
 * @return
 *   HTML data formatted for mail browsers.
 */
function theme_order_mail($cart, $customer_values) {
  $output = '';
  $output .= '<p>Se ha realizado el siguiente pedido:</p>';
  $output .= '<table border="0" cellpadding="10" width="100%">';
  $total = 0;
  $td_value_style = ' style="border: 1px solid;"';
  foreach ($cart as $product_id => $item) {
    $item_total = (float) $item['precio'] * $item['quantity'];
    $output .= '  <tr>';
    $output .= '    <td' . $td_value_style .'>' . $item['quantity'] . 'x</td>';
    $output .= '    <td' . $td_value_style .'>' . $item['nombre_esp'] . '</td>';
    $output .= '    <td' . $td_value_style .'>' . theme_price($item_total) . '</td>';
    $output .= '  </tr>';
    $total += $item_total;
  }

  $vat = vat($total);
  $calculations = array(
    'Subtotal' => theme_price($total),
    'IVA 18%' => theme_price($vat),
    'Total' => theme_price($total + $vat),
  );
  foreach ($calculations as $title => $value) {
    $output .= '  <tr>';
    $output .= '    <td colspan="2" align="right">' . $title . ':</td>';
    $output .= '    <td' . $td_value_style . '>' . $value .'</td>';
    $output .= '  </tr>';
  }

  $output .= '</table>';

  $output .= '<p>Con los datos siguientes de entrega:</p>';
  $output .= '<ul>';
  unset($customer_values['submit']);
  $names = theme_checkout_form_deliver_item_names();
  foreach ($customer_values as $name => $value) {
    $output .= '<li><label>' . $names[$name] . ':</label> ' . $value . '</li>';
  }
  $output .= '</ul>';

  return $output;
}
