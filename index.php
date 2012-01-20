<?php

/**
 * Main controller for cart example.
 *
 * @author Daniel Gil Jara (danielgiljara@gmail.com)
 */

include('includes/core.inc.php');
include('includes/main.inc.php');

$variables = array(
  'title' => 'Ejemplo carrito compra',
  'content' => theme_products(isset_or($_GET['cat'])),
);
echo template('page', $variables);
