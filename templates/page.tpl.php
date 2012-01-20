<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es" dir="ltr">
  <head>
    <title><?php echo $title; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link type="text/css" rel="stylesheet" media="all" href="css/style.css" />
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
    <script type="text/javascript" src="./js/script.js"></script>
  </head>
  <body>
    <div id="container">
      <div id="header">
        <h1 class="site-name"><a href="./">Ejemplo carrito de la compra</a></h1>
      </div>

      <div id="wrapper">
        <div id="content">
          <?php echo theme_messages($_SESSION['messages']); ?>
          <?php echo $content; ?>
        </div>
      </div>

      <div id="sidebar-first">
        <h2>Categorias</h2>
        <?php echo theme_product_categories(); ?>
      </div>

      <div id="sidebar-second">
        <h2>Carrito</h2>
        <?php echo theme_cart(isset_or($_SESSION['cart'])); ?>
      </div>

      <div id="footer">by Daniel Gil Jara (danielgiljara@gmail.com)</div>
  </div>
  </body>
</html>
