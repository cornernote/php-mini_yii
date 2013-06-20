<?php
/**
 * @var $header_title
 * @var $header_tags
 * @var $content
 */

// set variable defaults
if (empty($header_title)) $header_title = 'My Site';
if (empty($header_tags)) $header_tags = '';
if (empty($content)) $content = '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title><?php echo $header_title; ?></title>
    <link href="css/listen-style.css" rel="stylesheet" type="text/css"/>
    <link href="css/listen-menu-style.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="scripts/jquery-1.5.2.min.js"></script>
    <script src="scripts/jqueryui-1.8.13.js"></script>
    <?php echo $header_tags; ?>
</head>
<body>

<?php //render('elements/menu'); ?>
<?php echo $content; ?>

</body>
</html>					