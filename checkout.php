<?php

/**
 * Checkout controller for cart example.
 *
 * @author Daniel Gil Jara (danielgiljara@gmail.com)
 */

include('includes/core.inc.php');
include('includes/main.inc.php');
include('includes/checkout.inc.php');

$variables = array(
  'title' => 'Ejemplo carrito compra - Caja',
  'content' => theme_checkout(isset_or($_GET['checkout'])),
);
echo template('page', $variables);
