<?php
/**
 * @package embed_fb
 * @version 1.0
 */
 
 
class fb_embedded_posts {
	
	private $facebook_post_regex = array("post" => "((http|https)://(www.|)facebook.com/([\w\.-_]*)/posts/(\d*))", "photo" => "((http|https)://(www.|)facebook.com/photo.php\?fbid=(\d*))");
	private $add_sdk_to_footer = false;
	
	/**
	 * __construct the class.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct(){
		
		// Register the embed Handler
		foreach($this->facebook_post_regex as $k => $f){
			wp_embed_register_handler(
				'lh_facebook_post_'.$k,				// An internal ID/name for the handler. Needs to be unique.
				$this->get_regex($f), 			// The regex that will be used to see if this handler should be used for a URL.
				array($this, "generate_html"), 	// The callback function that will be called if the regex is matched.
				10								// Used to specify the order in which the registered handlers will be tested (default: 10)
			);
		}
		
		
		// Init the settings api on admin_init
		add_action('admin_init', array($this, 'register_settings'));
		$this->add_sdk_to_footer = (bool) get_option('fb_embed_sdk');

		if($this->add_sdk_to_footer){
			add_action('wp_footer', array($this, 'print_sdk_in_footer'));
		}
		
		// Actions
		add_action('plugins_loaded', array($this, 'load_i18n') );
	}
	
	/**
	 * load_i18n function.
	 * 
	 * @access public
	 * @return void
	 */
	public function load_i18n(){
		 $plugin_dir = dirname( plugin_basename( __FILE__ ) ) . '/lang/';
		 load_plugin_textdomain( 'lh', false, $plugin_dir );
	}
	
	/**
	 * Returns the regular expression needed for the embedding.
	 * 
	 * @access private
	 * @return void
	 */
	private function get_regex($regex){
		return "@".$regex."@i";
	}
	
	
	/**
	 * Generates the HTML for the wp_embed_register_handler.
	 * 
	 * @access public
	 * @param mixed $matches
	 * @param mixed $attr
	 * @param mixed $url
	 * @param mixed $rawattr
	 * @return string $embed The Embed HTML, filtered by filter "embed_lh_facebook_post"
	 */
	public function generate_html($matches, $attr, $url, $rawattr){
		$embed = '<fb:post href="'.$url.'"></fb:post>';
			
		return apply_filters( 'embed_lh_facebook_post', $embed, $matches, $attr, $url, $rawattr );
	}
	
	
	public function print_sdk_in_footer(){
		?>
		
		<div id="fb-root"></div>
		<script>
		  window.fbAsyncInit = function() {
		    // init the FB JS SDK
		    FB.init({
		    <?php if(get_option('fb_embed_app_id') == false or get_option('fb_embed_app_id') == ""): ?>
		      appId      : '<?=get_option('fb_embed_app_id')?>',	// App ID from the app dashboard
		    <?php endif; ?>
		      channelUrl : '<?=LHEFB_FOLDER_URL?>/channel.php', 	// Channel file for x-domain comms
		      status     : true,                                 	// Check Facebook Login status
		      xfbml      : true                                  	// Look for social plugins on the page
		    });
		
		    // Additional initialization code such as adding Event Listeners goes here
		  };
		
		  // Load the SDK asynchronously
		  (function(d, s, id){
		     var js, fjs = d.getElementsByTagName(s)[0];
		     if (d.getElementById(id)) {return;}
		     js = d.createElement(s); js.id = id;
		     js.src = "//connect.facebook.net/<?php _e("en_US", "lh") ?>/all.js";
		     fjs.parentNode.insertBefore(js, fjs);
		   }(document, 'script', 'facebook-jssdk'));
		</script>
		
		<?php
	}
	
	
	///////
	// SETTINGS
	//////
	
	/**
	 * Registers the needed settings.
	 * 
	 * @access public
	 * @return void
	 */
	public function register_settings(){
		
		// Register the settings section
		add_settings_section(
				'fb_embed_setting_section',
				__("Facebook Post Embed", "lh"),
				array($this, 'fb_embed_section_callback'),
				'general'
		);
		
		 	
		 add_settings_field(
		 		'fb_embed_sdk',
				__("JS SDK", "lh"),
				array($this, 'fb_embed_jssdk_setting'),
				'general',
				'fb_embed_setting_section');
		
		 	
		 add_settings_field(
		 		'fb_embed_app_id',
				__("APP ID", "lh"),
				array($this, 'fb_embed_app_id_setting'),
				'general',
				'fb_embed_setting_section');
				
		register_setting('general','fb_embed_sdk');
		register_setting('general','fb_embed_app_id');

		
	}
	
	
	/**
	 * Echoes the text for the section.
	 * 
	 * @access public
	 * @return void
	 */
	public function fb_embed_section_callback(){
		$content = __("Brought to you by Allfacebook.de & Luehrsen // Heinrich. Have fun!", "lh");
		
		echo "<p>".$content."</p>";
	}
	
	
	/**
	 * Echoes the text for the setting "fb_embed_sdk.
	 * 
	 * @access public
	 * @return void
	 */
	public function fb_embed_jssdk_setting(){
		?>
			<fieldset><label for="fb_embed_sdk_checkbox"><input type="checkbox" name="fb_embed_sdk" id="fb_embed_sdk_checkbox" value="1" <?=checked(get_option('fb_embed_sdk'))?>> <? _e("Use the plugin JS SDK", "lh"); ?></label>
				<p class="description">
					<?php _e("Check this, if you don't have a Facebook JS SDK on the page. (Maybe implemented in the theme, from another plugin, or similar) We don't want to load the SDK twice!", "lh"); ?>
				</p>
			</fieldset>
		<?php
	}
	
	
	/**
	 * fb_embed_app_id_setting function.
	 * 
	 * @access public
	 * @return void
	 */
	public function fb_embed_app_id_setting(){
		?>
			<fieldset>
				<input type="text" name="fb_embed_app_id" value="<?=get_option('fb_embed_app_id')?>" class="regular-text">
				<p class="description"> <?php _e('Add your <a href="https://developers.facebook.com/apps" target="_blank">Facebook App ID here</a>. This is recommended for the JS SDK (setting above) to work, but it will likely work without!', "lh"); ?>
			</fieldset>
		<?php
	}
}
