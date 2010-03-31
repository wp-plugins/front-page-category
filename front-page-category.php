<?php
/*
Plugin Name: Front Page Category
Version: 1.0
Plugin URI: http://www.forexp.net/front-page-category/
Description: Choose categories that you want to display in front page. Visit <a href="options-general.php?page=front-page-excluded-categories/front_page_excluded_cats.php" title="Front Page Categories Settings Panel">Front Page Categories Settings panel</a>
Author: JWall
Author URI: http://www.forexp.net
*/ 

class jwall_fp {

		static $hide_categories = array();
		static $show_all = true;

    function version() { return 1.0; }
    
		function load(){
			if ($coptions = get_option('hide_categories')){
				self::$hide_categories = explode(',',$coptions);
				self::$show_all = false;						
			}
		}    
		
    function add_menu() {
			add_submenu_page('options-general.php','Front Page Categories Settings','Front Page Categories',8,__FILE__,array('jwall_fp','settings'));
    }    
    
    function settings() {

      $all_categories = get_categories(array('hide_empty' => false));
      
			echo '<div class="wrap"><h2>Front Page Categories Settings</h2></div>';
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    // Save settings
	    switch ($_POST['action']) {
				case "save":
						$show_categories=$_POST['allows'];
						
						self::$hide_categories = array();
						foreach ($all_categories as $cat){
							if (!in_array($cat->cat_ID,$show_categories)) self::$hide_categories[]=$cat->cat_ID;
						}
						$saveopt = implode(',',self::$hide_categories);
				    if (!get_option('hide_categories'))	{ 
				    	if (!empty(self::$hide_categories))
								add_option('hide_categories',$saveopt); //Fix Add empty option bugs
							} else {
								if (empty(self::$hide_categories)) delete_option('hide_categories');
								else update_option('hide_categories',$saveopt);
							}
		    //echo '<div id="message" class="updated fade">These categories ID are hidden: '.$saveopt.'</div>';							
		    echo '<div id="message" class="updated fade">Settings saved!</div>';
		    break;
	    }
			self::load();
	   	}
?>

	<p> Show these categories: </>
  <form action="<?=$_SERVER['REQUEST_URI']?>" method="POST">
		<input type="hidden" name="action" value="save"/>
		<?php 
			foreach ($all_categories as $cat){
				$check = (self::$show_all or (!in_array($cat->cat_ID,self::$hide_categories))) ? 'checked = "checked"' : '';
				echo '<input type="checkbox" name="allows[]" value="'.$cat->cat_ID.'" '.$check.'> '.$cat->name.'<br/>';
			}			
		?>
		<br/>
		<input type="submit" value="Save Settings"/>
   </form>

<?	
    }
    
    function set_cats_in(&$params){
			if (self::$show_all) return true;    
    	global $wp_query;
			if (! $wp_query->is_home || empty(self::$hide_categories)) return true;
    	$params->query_vars["category__not_in"] = self::$hide_categories;
    	
    }
}

add_action('admin_menu',array('jwall_fp','add_menu'));
add_action('plugins_loaded',array('jwall_fp','load'));
add_action('pre_get_posts',array('jwall_fp','set_cats_in'),1,1);


?>
