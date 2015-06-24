<?php
if (!class_exists('SLMFacebookOptionsUI')) {
	class SLMFacebookOptionsUI {
		
		public function __construct() {
			$this->processPost();
			$this->initializeSettings();			
		}
		
		public function getOptionsTab() {
			
			$html = '';
			
			$html .= '<div class="metabox-holder">'; //left div
			
			$html .= '<form action="" method="post">';
			$html .= '<table style="width:100%;">';
			$textsize = ' style="width:300px;"';
			
			$html .= '<tr><td style="width:200px;">Instructions:</td><td><a href="https://s3-us-west-2.amazonaws.com/wpslm/docs/PDFs/Facebook.pdf" target="_blank"><strong>Help</strong></a></td></tr>';
			$html .= '<tr><td style="width:200px;"><strong>AUTO SHARING</strong></td><td><hr/></td></tr>';
			
			$html .= '<tr><td>Enable Sharing </td>';
			$html .= 					'<td>';
			if (isset($this->settings['share_auto_share_enabled']) && $this->settings['share_auto_share_enabled']=='false') {
				$html .=					'<div id="social-acc-auto-share-enable" class="btn-group" data-toggle="buttons-radio">';
				$html .= 						'<button id="social-acc-auto-share-enable-on" type="button" class="btn" value="true" >on</button>';
				$html .= 						'<button id="social-acc-auto-share-enable-off" type="button" class="btn btn-danger active" value="false" >off</button>';
				$html .= 					'</div>';
				$html .= 					'<input id="social-acc-auto-share-enable-hidden" name="social-acc-auto-share-enable-hidden" type="hidden" value="false" />';
			} else {
				$html .=					'<div id="social-acc-auto-share-enable" class="btn-group" data-toggle="buttons-radio">';
				$html .= 						'<button id="social-acc-auto-share-enable-on" type="button" class="btn btn-success active" value="true" >on</button>';
				$html .= 						'<button id="social-acc-auto-share-enable-off" type="button" class="btn" value="false" >off</button>';
				$html .= 					'</div>';
				$html .= 					'<input id="social-acc-auto-share-enable-hidden" name="social-acc-auto-share-enable-hidden" type="hidden" value="true" />';
			}
			$html .= '<script>onOffSocialRadio("social-acc-auto-share-enable");</script>';
			$html .= ' <span class="st-form-label">(Enables/Disables auto sharing for the whole blog)</span>';
			$html .= 					'</td></tr>';

			$html .= '<tr><td></td><td><hr/></td></tr>';
			$html .= '<tr><td>Facebook Page ID:</td><td><input type="text"'.$textsize.' name="slm_facebook_url" value="'.((isset($this->settings['slm_facebook_url']))?$this->settings['slm_facebook_url']:'').'" /></td></tr>';
			$html .= '<tr><td>App ID:</td><td><input type="text"'.$textsize.' name="slm_facebook_consumer_key" value="'.((isset($this->settings['slm_facebook_consumer_key']))?$this->settings['slm_facebook_consumer_key']:'').'" /></td></tr>';
			$html .= '<tr><td>App Secret:</td><td><input type="text"'.$textsize.' name="slm_facebook_consumer_secret" value="'.((isset($this->settings['slm_facebook_consumer_secret']))?$this->settings['slm_facebook_consumer_secret']:'').'" /></td></tr>';
			$html .= '<tr><td><!-- Token: --></td><td><input type="hidden"'.$textsize.' name="slm_facebook_token" value="'.((isset($this->settings['slm_facebook_token']))?$this->settings['slm_facebook_token']:'').'" /></td></tr>';
			$html .= '<tr><td><!-- Token Secret: --></td><td><input type="hidden"'.$textsize.' name="slm_facebook_token_secret" value="'.((isset($this->settings['slm_facebook_token_secret']))?$this->settings['slm_facebook_token_secret']:'').'" /></td></tr>';
			$html .= '<tr><td><!-- Token Secret: --></td><td><input type="hidden"'.$textsize.' name="slm_facebook_code" value="'.((isset($this->settings['slm_facebook_code']))?$this->settings['slm_facebook_code']:'').'" /></td></tr>';
			
			$callback = $this->getCallback();
			$html .= '<tr><td>Authorize Application:<br/><br/></td><td><b>'.$callback.'</b><br/><br/></td></tr>';
			
			
			$html .= '<tr>';
			$html .= 	'<td>Content format</td>';
			$html .= 	'<td><textarea style="width:500px;" name="slm_facebook_content_format">'.((isset($this->settings['slm_facebook_content_format']))?$this->settings['slm_facebook_content_format']:"%excerpt%\n\nRead more at %url%").'</textarea></td>';
			$html .= '</tr>';
			
			$html .= '<tr><td style="width:200px;"></td><td><hr/></td></tr>';
			$html .= '<tr>';
			$html .= 	'<td>What to share</td>';
			$html .= 	'<td>';
			$post_types = get_post_types(array("public" => true));
			//var_dump($this->settings['wp_maxv_st_share_post_types']);
			if (!isset($this->settings['wp_maxv_st_share_post_types'])) {
				$this->settings['wp_maxv_st_share_post_types'] = array();
				if (isset($this->settings['share_posts_or_pages']) && $this->settings['share_posts_or_pages'] == 'post') $this->settings['wp_maxv_st_share_post_types']['post']='on';
				if (isset($this->settings['share_posts_or_pages']) && $this->settings['share_posts_or_pages'] == 'page') $this->settings['wp_maxv_st_share_post_types']['page']='on';
				if (!isset($this->settings['share_posts_or_pages']) || $this->settings['share_posts_or_pages'] == '' || $this->settings['share_posts_or_pages'] == 'all') {
					$this->settings['wp_maxv_st_share_post_types']['post']='on';
					$this->settings['wp_maxv_st_share_post_types']['page']='on';
				}
			}
			foreach ($post_types as $post_type) {
				if ($post_type!='attachment') {
					$post_type_name = ucwords($post_type).'s';
					//if ($post_type_name=='Post' || $post_type_name=='Page') $post_type_name .= 's';
					$html .= 	'<input type="checkbox" name="wp_maxv_st_share_post_types_'.$post_type.'" '.(((isset($this->settings['wp_maxv_st_share_post_types'][$post_type]) && $this->settings['wp_maxv_st_share_post_types'][$post_type]))?'checked="1"':"").'/> '.$post_type_name.'<br/>';
				}
			}
			$html .= 	'</td>';
			$html .= '</tr>';
			$html .= '<tr><td style="width:200px;"></td><td><hr/></td></tr>';
				
			$html .= $this->getCategoriesList();
			
			$html .= '<tr><td style="width:150px;"><strong>SCHEDULING</strong></td><td><hr/></td></tr>';
			
			$html .= '<tr><td style="width:150px;">Next Schedule</td><td>'.((isset($this->settings['slm_facebook_next_schedule']) && $this->settings['slm_facebook_next_schedule'])?date('Y-m-d H:i:s',$this->settings['slm_facebook_next_schedule']):'').'</td></tr>';
			
			
			$html .= '<tr>';
			$html .= 	'<td>Sharing order</td>';
			$html .= 	'<td><select name="wp_maxv_st_share_posts_order[]">'.
					'<option value="newest" '.((!isset($this->settings['share_posts_order']) || $this->settings['share_posts_order'] == '' || $this->settings['share_posts_order'] == 'newest')?'selected="1"':'').'>Newest Posts First</option>'.
					'<option value="oldest" '.((isset($this->settings['share_posts_order']) && $this->settings['share_posts_order'] == 'oldest')?'selected="1"':'').'>Oldest Posts First</option>'.
					'<option value="random" '.((isset($this->settings['share_posts_order']) && $this->settings['share_posts_order'] == 'random')?'selected="1"':'').'>Random Posts</option>'.
					'</select>';
			$html .= ' Max <input id="slm_facebook_max_posts" name="slm_facebook_max_posts" type="text" style="width:30px;" value="'.((isset($this->settings['slm_facebook_max_posts']))?$this->settings['slm_facebook_max_posts']:'1').'"/> '.
					'posts per <input id="slm_facebook_max_posts_hours" name="slm_facebook_max_posts_hours" type="text" style="width:30px;" value="'.((isset($this->settings['slm_facebook_max_posts_hours']))?$this->settings['slm_facebook_max_posts_hours']:'24').'"/> hours';
				
			$html .= 	'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= 	'<td>Do not share posts published before</td>';
			$html .= 	'<td><input type="text" class="datepicker" id="wp_maxv_st_share_old_posts_date" name="wp_maxv_st_share_old_posts_date" value="'.((isset($this->settings['share_old_posts_date']))?$this->settings['share_old_posts_date']:'').'" style="width:100px;"/>'.
					' <span class="st-form-label">(leave blank if you want to share all old posts. IMPORTANT: the date format MUST be YYYY-MM-DD)</span></td>';
			$html .= '</tr>';

			$html .= '<tr><td style="width:150px;"><strong>BACKLINKS INDEXER</strong></td><td><hr/></td></tr>';
			$html .= '<tr>';
			$html .= 	'<td>Use <a href="http://www.maxvim.com/private/tools/bi.php?who=twitterslm" target="_blank">BacklinksIndexer.com</a></td>';
			$html .= 	'<td><input type="checkbox" name="wp_maxv_st_share_use_backlinksindexer" '.((isset($this->settings['share_use_backlinksindexer']) && $this->settings['share_use_backlinksindexer'])?'checked="1"':"").'/>'.
					' <span class="st-form-label"></span></td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= 	'<td><a href="http://www.maxvim.com/private/tools/bi.php?who=twitterslm" target="_blank">BacklinksIndexer.com</a> API Key</td>';
			$html .= 	'<td><input type="text" name="wp_maxv_st_share_use_backlinksindexer_key" value="'.((isset($this->settings['share_use_backlinksindexer_key']))?$this->settings['share_use_backlinksindexer_key']:'').'" style="width:100px;"/>'.
					' <span class="st-form-label"></span> <a href="http://www.maxvim.com/private/tools/bi.php?who=twitterslm" target="_blank">Get a Backlinks Indexer Account HERE</a></td>';
			$html .= '</tr>';
				
			$html .= '<tr><td><br/><br/><input name="wp_maxv_st_share_save_options" id="wp_maxv_st_share_save_options" class="btn" value="Save Changes" type="submit"/></td><td></td></tr>';
			
			$html .= '</table>';
			$html .= '</form>';
				
			$html .= '</div>'; //left div
			
			return $html;
		}
		
		private function getCategoriesList() {
			$args = array(
					'orderby' => 'name',
					'hide_empty'=> 0 //,
			);
				
			$cats = get_categories($args);
			/*echo '<pre>';
			 print_r($cats);
			echo '</pre>';*/
				
				
			$html = '';
			//$html .= '<tr><td><strong>CATEGORIES</strong></td><td><hr/></td></tr>';
			$html .= '<tr><td valign="top">Categories to share</td>';
			$html .= '<td>';
			$i = 0;
			foreach ($cats as $nextCat) {
				if (isset($this->settings['slm_postcats'])) {
					if (isset($this->settings['slm_postcats'][$nextCat->term_taxonomy_id]) && $this->settings['slm_postcats'][$nextCat->term_taxonomy_id])
						$html .= '<input type="checkbox" name="slm_postcat_'.$i.'" value="'.$nextCat->term_taxonomy_id.'" checked/> '.$nextCat->name.'<br>';
					else
						$html .= '<input type="checkbox" name="slm_postcat_'.$i.'" value="'.$nextCat->term_taxonomy_id.'"/> '.$nextCat->name.'<br>';
				} else
					$html .= '<input type="checkbox" name="slm_postcat_'.$i.'" value="'.$nextCat->term_taxonomy_id.'" checked/> '.$nextCat->name.'<br>';
				$i++;
			}
				
			$html .= 					'</td></tr>';
		
			return $html;
		
		}
		
		public function initializeSettings() {
			$this->settings = get_option('wp_maxv_st_facebook_settings');
			if (!is_array($this->settings)) {
				$this->settings = array();
				//$this->settings['auto_share_posts'] = 1;
				$this->settings['share_old_posts'] = 1;
				$this->settings['share_old_posts_date'] = ''; //date('Y-m-d');
				$this->settings['share_posts_order'] = 'newest';
				$this->settings['share_excerpt_length'] = 300;
				$this->settings['share_min_content_length'] = 50;
		
				$this->settings['videos_rand_factor'] = '';
				$this->settings['images_rand_factor'] = '';		
			} else {
				if (!isset($this->settings['share_excerpt_length']) || $this->settings['share_excerpt_length']<=0) $this->settings['share_excerpt_length'] = 300;
				if (!isset($this->settings['share_min_content_length']) || $this->settings['share_min_content_length']<0) $this->settings['share_min_content_length'] = 50;
			}
			if (!isset($this->settings['slm_facebook_code']) || (isset($this->settings['slm_facebook_code']) && isset($_GET['code']) && $this->settings['slm_facebook_code']!=$_GET['code'])) {
				if (isset($_GET['page']) && trim($_GET['page'])!='') $page_id = $_GET['page'];
				else $page_id = 'slm-facebook-autoposter';
				$callback = admin_url('').'admin.php?page='.$page_id;
			
				$this->settings['slm_facebook_code'] = urldecode($_GET['code']);
				require_once dirname(__FILE__).'/../utils/facebook/CustomFacebook.php';
				$config = array(  'appId' => $this->settings['slm_facebook_consumer_key'],
						'secret' => $this->settings['slm_facebook_consumer_secret'],
						'nosession' => true, // my setting
						'cookie' => true );
				$facebook = new CustomFacebook($config);
				$this->settings['slm_facebook_token'] = $facebook->getAccessTokenByAuthorizationCode($this->settings['slm_facebook_code'], $callback);
				//echo 'token: '.$obj->settings->token." - {$obj->settings->page_id}<br/><br/>";
				//var_dump($obj->settings->token);
				$facebook->setAccessToken($this->settings['slm_facebook_token']);
				$this->settings['slm_facebook_user'] = $facebook->getUser();
					
				try {
					$postinfo = $facebook->api("/{$this->settings['slm_facebook_url']}?fields=access_token");
					//echo "<br/>--------------<br/>";
					//var_dump($postinfo);
					if (is_array($postinfo) && isset($postinfo['access_token']) && $postinfo['access_token']) {
						$this->settings['slm_facebook_token'] = $postinfo['access_token'];
					}
				} catch (MV_FacebookApiException $e) {
					if (!isset($this->settings['slm_facebook_token']) || !$this->settings['slm_facebook_token'])
						echo "There was an Exception: ".$e->getMessage().' Token: '.$this->settings['slm_facebook_token'];
				}
				update_option('wp_maxv_st_facebook_settings',$this->settings);
			}			
			
				
		}
		
		public function processPost() {
			$settings = get_option('wp_maxv_st_facebook_settings');
			if (!is_array($settings)) $settings = array();
			if (isset($_POST['wp_maxv_st_share_save_options']) && $_POST['wp_maxv_st_share_save_options']) {
				$settings['share_old_posts'] = (isset($_POST['wp_maxv_st_share_old_posts']))?$_POST['wp_maxv_st_share_old_posts']:'';
				$settings['share_old_posts_date'] = (isset($_POST['wp_maxv_st_share_old_posts_date']))?$_POST['wp_maxv_st_share_old_posts_date']:'';
				$settings['share_posts_order'] = (isset($_POST['wp_maxv_st_share_posts_order'][0]))?$_POST['wp_maxv_st_share_posts_order'][0]:'';
				$settings['share_posts_or_pages'] = (isset($_POST['wp_maxv_st_share_posts_or_pages'][0]))?$_POST['wp_maxv_st_share_posts_or_pages'][0]:'';
				$settings['share_blog_category'] = (isset($_POST['wp_maxv_st_share_blog_category'][0]))?$_POST['wp_maxv_st_share_blog_category'][0]:'';
				$settings['share_blog_alt_category'] = (isset($_POST['wp_maxv_st_share_blog_alt_category'][0]))?$_POST['wp_maxv_st_share_blog_alt_category'][0]:'';
		
				$settings['share_auto_share_enabled'] = (isset($_POST['social-acc-auto-share-enable-hidden']))?$_POST['social-acc-auto-share-enable-hidden']:'';
				$settings['slm-twitter-attach-image-enable'] = (isset($_POST['slm-twitter-attach-image-enable-hidden']))?$_POST['slm-twitter-attach-image-enable-hidden']:'';
				
				$settings['share_use_backlinksindexer'] = (isset($_POST['wp_maxv_st_share_use_backlinksindexer']))?$_POST['wp_maxv_st_share_use_backlinksindexer']:'';
				$settings['share_use_backlinksindexer_key'] = (isset($_POST['wp_maxv_st_share_use_backlinksindexer_key']))?$_POST['wp_maxv_st_share_use_backlinksindexer_key']:'';

				$settings['slm_facebook_max_posts'] = (isset($_POST['slm_facebook_max_posts']))?$_POST['slm_facebook_max_posts']:'';
				$settings['slm_facebook_max_posts_hours'] = (isset($_POST['slm_facebook_max_posts_hours']))?$_POST['slm_facebook_max_posts_hours']:'';
				
				$settings['slm_facebook_url'] = (isset($_POST['slm_facebook_url']))?$_POST['slm_facebook_url']:'';
				$settings['slm_facebook_consumer_key'] = (isset($_POST['slm_facebook_consumer_key']))?$_POST['slm_facebook_consumer_key']:'';
				$settings['slm_facebook_consumer_secret'] = (isset($_POST['slm_facebook_consumer_secret']))?$_POST['slm_facebook_consumer_secret']:'';
				$settings['slm_facebook_token'] = (isset($_POST['slm_facebook_token']))?$_POST['slm_facebook_token']:'';
				$settings['slm_facebook_code'] = (isset($_POST['slm_facebook_code']))?$_POST['slm_facebook_code']:'';
				
				$settings['slm_facebook_token_secret'] = (isset($_POST['slm_facebook_token_secret']))?$_POST['slm_facebook_token_secret']:'';
				$settings['slm_facebook_content_format'] = (isset($_POST['slm_facebook_content_format']))?$_POST['slm_facebook_content_format']:"%excerpt%\n\nRead more at %url%";
				if (!$settings['slm_facebook_content_format']) $settings['slm_facebook_content_format'] = "%excerpt%\n\nRead more at %url%";
				if (!isset($settings['slm_facebook_acc_id'])) $settings['slm_facebook_acc_id'] = uniqid();
				
				$post_types_to_share = array();
				$post_categories_to_share = array();
				foreach($_POST as $k=>$v) {
					if (strpos('_'.$k, 'wp_maxv_st_share_post_types_')>0) {
						$k = str_replace('wp_maxv_st_share_post_types_', '', $k);
						$post_types_to_share[$k] = $v;
					}
					if (strpos('_'.$k, 'slm_postcat_')>0) {
						$post_categories_to_share[$v] = 1;
					}
				}
				$settings['wp_maxv_st_share_post_types'] = $post_types_to_share;
				$settings['slm_postcats'] = $post_categories_to_share;
				//$settings['slm_facebook_next_schedule'] = 0;
				update_option('wp_maxv_st_facebook_settings',$settings);
			}
		
		}
		
		private function getCallback() {
			$html = '';
			if (isset($this->settings['slm_facebook_consumer_key']) && $this->settings['slm_facebook_consumer_key'] &&
				isset($this->settings['slm_facebook_consumer_secret']) && $this->settings['slm_facebook_consumer_secret']) {
				if (isset($_GET['page']) && trim($_GET['page'])!='') $page_id = $_GET['page'];
				else $page_id = 'slm-facebook-autoposter';
				$callback = admin_url('').'admin.php?page='.$page_id;
				
				require_once dirname(__FILE__).'/../utils/facebook/CustomFacebook.php';
				$config = array(  'appId' => $this->settings['slm_facebook_consumer_key'],
						'secret' => $this->settings['slm_facebook_consumer_secret'],
						'nosession' => true, // my setting
						'cookie' => true );
				$facebook = new CustomFacebook($config);
				$params = array ('redirect_uri' => $callback);
					
				$url = $facebook->getLoginUrl($params);
				$url .= '&scope=scope=publish_actions,manage_pages,publish_pages,user_posts,user_photos,user_groups';
				if (!isset($this->settings['slm_facebook_code']) || !isset($this->settings['slm_facebook_token'])  || !isset($this->settings['slm_facebook_user'])) {
					$html .= '<a href="'.$url.'">Authorize</a> ';
				} else {
					if (!trim($this->settings['slm_facebook_code']) || !trim($this->settings['slm_facebook_token'])  || !trim($this->settings['slm_facebook_user'])) {
						$html .= '<a href="'.$url.'">Authorize</a> ';
					} else $html .= '<a href="'.$url.'">Re-Authorize</a> ';
				}
			}
			return $html;
		}
		
		
	}
}
?>