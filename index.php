<?php
// global init
require('includes/init.php');

// collect header_tags and content into a variable
$params = array();
$content = render('index', $params, true);

// global layout
render('elements/layout', array(
    'content' => $content,
));