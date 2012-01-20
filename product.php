<?php

/**
 * Product controller for cart example.
 *
 * @author Daniel Gil Jara (danielgiljara@gmail.com)
 */

include('includes/core.inc.php');
include('includes/main.inc.php');
include('includes/product.inc.php');

$variables = array(
  'title' => 'Ejemplo carrito compra - Vista de producto',
  'content' => theme_product(isset_or($_GET['id'])),
);
echo template('page', $variables);
