<?php
/**
 * @package embed_fb
 * @version 1.0
 */
/*
Plugin Name: Allfacebook.de Embed FB
Plugin URI: http://www.allfacebook.de
Description: Wordpress Plugin to embed facebook posts inside wordpress, just from posting the url in the content. 
Author: Hendrik Luehrsen
Version: 1.0
Author URI: http://www.luehrsen-heinrich.de
*/

define('LHEFB_VERSION', "1");
define('LHEFB_FOLDER_URL', plugin_dir_url(__FILE__));
define('LHEFB_FILTER_PATH', plugin_dir_path(__FILE__));

require_once("lhefb.core.php");

$lh_fb_embed = new fb_embedded_posts();
