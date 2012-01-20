<?php

/**
 * Main view related functions for cart example.
 *
 * @author Daniel Gil Jara (danielgiljara@gmail.com)
 */

/**
 * Process form actions.
 */
if (isset($_POST['submit'])) {
  switch ($_POST['submit']) {
    case 'Añadir':
      cart_add_product($_POST['id'], $_POST['quantity']);
      break;
    case '[X]':
    case 'Quitar':
      cart_del_product($_POST['id']);
      break;
  }
}

/**
 * Add product to cart.
 */
function cart_add_product($product_id, $quantity) {
  if (!isset($_SESSION['cart'])) {
    // Initialize cart.
    $_SESSION['cart'] = array();
  }
  if (isset($_SESSION['cart'][$product_id])) {
    // Update quantity of a existing item.
    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
  }
  else {
    // Insert a new cart item.
    $_SESSION['cart'][$product_id] = products_get(array('product_id' => $product_id));
    $_SESSION['cart'][$product_id]['quantity'] = (int) $quantity;
  }

  // Remove items.
  if ($_SESSION['cart'][$product_id]['quantity'] <= 0 || $quantity < 0) {
    cart_del_product($product_id);
  }

  // Notify item addition.
  else {
    $product_name = $_SESSION['cart'][$product_id]['nombre_esp'];
    $_SESSION['messages']['status'][] = 'Se han añadido ' . $quantity . 'x <em>' . $product_name . '</em> al carrito de la compra.';
  }
}

/**
 * Delete product from cart.
 *
 * @param integer $id
 *   ID of the product.
 */
function cart_del_product($id) {
  if (isset($_SESSION['cart'][(int) $id])) {
    // Message user.
    $product_name = $_SESSION['cart'][$id]['nombre_esp'];
    $_SESSION['messages']['status'][] = 'Se ha quitado ' . '<em>' . $product_name . '</em> del carrito de la compra.';

    // Finally unset the element from the cart.
    unset($_SESSION['cart'][$id]);
  }
}

/**
 * Get a products list or a single product based on the filter.
 *
 * @param array $filter
 *   (optional) An associative array with filter data:
 *   - 'category': Category intenger.
 *   - 'product_id': Product ID integer.
 *
 * @return
 *   Returns single product if $filter['product_id'] is present or an array of
 *   elements if not.
 */
function products_get($filter = array()) {
  $field = array('producto_id', 'sub_familia_id', 'nombre_esp', 'desc_esp', 'precio', 'orden', 'novedad', 'file_foto');
  $sql = 'SELECT ' . implode(', ', $field) . ' FROM producto';

  if (!empty($filter)) {
    if (isset($filter['category'])) {
      $name = 'sub_familia_id';
      $value = $filter['category'];
    }
    elseif (isset($filter['product_id'])) {
      $name = 'producto_id';
      $value = $filter['product_id'];
    }

    if (isset($name)) {
      $sql .= ' WHERE ' . $name . ' = ' . (int) $value;
    }
  }

  $sql .= ' ORDER BY ' . (!isset($name) ? 'novedad, ' : '') . 'orden';

  // Return single or multiple result.
  $result = db_exec($sql);
  return isset($filter['product_id']) ? $result[0] : db_exec($sql);
}

/**
 * Get product categories structure or a single category info.
 *
 * @param integer $category
 *   (optional) Category ID.
 *
 * @return
 *   An array with a single category info if $category is present or an array
 *   containing the category hierarchy if not.
 */
function product_categories($category = NULL) {
  $field = array(
    'f.familia_id familia_id',
    'f.nombre_esp familia_nombre_esp',
    'f.file_foto familia_file_foto',
    'sub_familia_id',
    'sf.nombre_esp subfamilia_nombre_esp',
  );
  $sql = 'SELECT ' . implode(', ', $field) . ' FROM familia f
          LEFT JOIN sub_familia sf ON f.familia_id = sf.familia_id';

  if (isset($category)) {
    $sql .= ' WHERE sub_familia_id = ' . (int) $category;
  }

  $sql .= ' ORDER BY f.orden, f.nombre_esp, sf.orden';

  // Return single or multiple result.
  $result = db_exec($sql);
  return isset($category) ? $result[0] : $result;
}

/**
 * Get VAT of subtotal.
 *
 * @param $subtotal
 *   Number with subtotal.
 * @return
 *   VAT to amount.
 */
function vat($subtotal) {
  return $subtotal * 0.18;
}

/**
 * Theme product categories.
 *
 * @return
 *   HTML data.
 */
function theme_product_categories() {
  $output = '<ul>';

  // Run trought product categories result getting a two level hierarchy logic
  // of families and subfamilies.
  $first = TRUE;
  foreach (product_categories() as $key => $element) {
    if (!isset($familia_id) || $familia_id !== $element['familia_id']) {
      // Create or update $familia_id and $new_family flag.
      $familia_id = $element['familia_id'];
      $new_family = TRUE;
    }

    if ($new_family) {
      $output .= $first ? '' : "\n  </ul>";
      $output .= ' <li>' . $element['familia_nombre_esp'];
      $output .= '   <img class="category-image-tiny" src="imagenes/familia/' . $element['familia_file_foto'] . '" />';
      $output .= " </li>\n";
      $output .= " <ul>\n";
    }

    if (isset($element['sub_familia_id'])) {
      $active_class = $element['sub_familia_id'] == (int) isset_or($_GET['cat']) ? ' class="active"' : '';
      $output .= "      <li$active_class>\n";
      $output .= '        <a href="./?cat=' . $element['sub_familia_id'] .'">' . $element['subfamilia_nombre_esp'] . " </a>\n";
      $output .= "      </li>\n";
    }

    $new_family = FALSE;
    $first = FALSE;
  }

  return $output;
}

/**
 * Theme system messages.
 *
 * @param array $messages
 *   Associative array with system messages:
 *   - ['info'][] = Info messages.
 *   - ['status'][] = Status messages.
 *   - ['error'][] = Error messages.
 *
 * @return
 *   HTML data.
 */
function theme_messages(&$messages) {
  $output = '';
  if (!empty($messages)) {
    $output .= '<div id="messages">';
    foreach ($messages as $type => $message_list) {
      if (!empty($message_list)) {
        $output .= '  <div class="' . $type . '">';
        $output .= '    <ul>';
        foreach($message_list as $key => $message) {
          $output .= '      <li>' . $message . '</li>';
          unset($messages[$type][$key]);
        }
        $output .= '    </ul>';
        $output .= '  </div>';
      }
    }
    $output .= '</div>';
  }
  return $output;
}

/**
 * Theme cart.
 *
 * @param array $cart
 *   Associative array with cart elements.
 *
 * @return
 *   HTML data.
 */
function theme_cart($cart) {
  if (!empty($cart)) {
    $output = '<table class="cart">';
    $total = 0;

    // Cart Items.
    foreach ($cart as $product_id => $item) {
      $item_amount = (float) $item['precio'] * $item['quantity'];
      $output .= '<tr>';
      $output .= '  <td class="quantity">' . $item['quantity'] . 'x</td>';
      $output .= '  <td><a href="./product.php?id=' . $product_id . '">' . $item['nombre_esp'] . '</a></td>';

      $output .= '  <td class="price">' . theme_price($item_amount) . '</td>';
      $output .= '  <td class="panel">
                      <form method="POST">
                        <input type="hidden" name="id" value="' . $product_id . '" />
                        <input type="hidden" name="quantity" value="' . $item['quantity'] . '" />
                        <input type="submit" name="submit" title="Quitar del carrito" value="[X]" />
                      </form>
                    </td>';
      $output .= '</tr>';
      $total += $item_amount;
    }

    // IVA.
    $vat = vat($total);
    $output .= '<tr>';
    $output .= '  <td class="total" colspan="4">IVA:<span class="price">' . theme_price($vat) .'</span></td>';
    $output .= '</tr>';

    // Total.
    $output .= '<tr>';
    $output .= '  <td class="total total-price" colspan="4">Total:<span class="price">' . theme_price($total + $vat) .'</span></td>';
    $output .= '</tr>';

    $output .= '</table>';
    $output .= '<a href="checkout.php">Pasar por caja</a>';
    return $output;
  }
  return 'Carrito vacío.';
}

/**
 * Format price with Euro settings.
 *
 * @param string $price
 *   Price in any format.
 *
 * @return
 *   Formatted price.
 */
function theme_price($price) {
  return number_format((float) $price, 2, ',', '.') . '&euro;';
}

/**
 * Format product teaser description.
 *
 * @param integer $product_id
 *   Product ID.
 * @param string $description
 *   Product description to make teaser.
 * @param iteger $words
 *   (optional) Number of words to parse. Default is 12.
 *
 * @return
 *   Teaser made with the first $words of $description.
 */
function theme_product_description($product_id, $description, $words = 12) {
  preg_match('/([^ ]* ?){0,' . $words .'}/', $description, $match);
  return trim($match[0]) . (!empty($match[1]) ? '<a title="Ver más" href="./product.php?id=' . $product_id .'">...</a>' : '');
}

/**
 * Theme products list.
 *
 * @param integer $category
 *   Category ID.
 *
 * @return
 *   HTML data.
 */
function theme_products($category) {
  $output = '';
  if (isset($category)) {

    // Print category titles and picture.
    $element = product_categories($category);
    $output .= '<h1>' . $element['familia_nombre_esp'] . '</h1>';
    $output .= '<img class="category-image" src="imagenes/familia/' .  $element['familia_file_foto'] . '" />';
    $output .= '<h2>' . $element['subfamilia_nombre_esp'] . '</h2>';
  }
  else {

    // Print generic title.
    $output .= '<h1>Productos</h1>';
  }

  // Products grid.
  if ($products = products_get(array('category' => $category))) {
    $output .= '<ul class="products">';
    foreach ($products as $key => $value) {
      $new = ($value['novedad'] === 'si');

      // Grid item.
      $output .= '  <li' . ($new ? ' class="new"' : '') . '>';
      $output .= '    <form method="POST">
                        <a href="./product.php?id=' . $value['producto_id'] . '">
                          ' . ($new ? 'Novedad: ' : '') . $value['nombre_esp'] . '<br />
                          <img src="imagenes/producto/' . $value['file_foto'] . '" />
                        </a><br />
                        ' . theme_product_description($value['producto_id'], $value['desc_esp']) . '<br />
                        <input type="text" name="quantity" size="2" value="1" />x<br />
                        ' . theme_price($value['precio']) . '<br />
                        <input type="submit" name="submit" value="Añadir" /><input type="hidden" name="id" value="' . $value['producto_id'] . '" /><br />
                      </form>';
       $output .= '  </li>';
    }
    $output .= '</ul>';
    return $output;
  }

  return $output . 'No hay productos en esta subcategoría.';
}
