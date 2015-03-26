<?php
if (!class_exists('SLMFacebookScheduler')) {
	class SLMFacebookScheduler {
		
		private $hook_name;
		private $settings;

		public function __construct() {
			$this->hook_name = str_replace('_action','',SLMFacebookPlugin::slm_facebook_hook);
			add_filter('cron_schedules', array (
			$this,
			'scheduleCronJobManager'
					));
		}
		public function scheduleCronJobManager($schedules) {
			$schedules [$this->hook_name] = array (
					'interval' => 90,
					'display' => __( 'Facebook SLM')
			);
				
			return $schedules;
		}
		public function scheduleTasks() {
			add_action(SLMFacebookPlugin::slm_facebook_hook, array (
			$this,
			'executeSocialTriggerTask'
					));
				
			if (!wp_next_scheduled(SLMFacebookPlugin::slm_facebook_hook)) {
				wp_schedule_event(time(), $this->hook_name, SLMFacebookPlugin::slm_facebook_hook);
			}
		}
		
		public function executeSocialTriggerTask() {
			$this->settings = get_option('wp_maxv_st_facebook_settings');
			$this->settings['debug'] = 1;update_option('wp_maxv_st_facebook_settings',$this->settings);
			if (isset($this->settings ['share_auto_share_enabled']) && $this->settings ['share_auto_share_enabled'] == 'false') {
			} else {
				$this->settings['debug'] = 2;update_option('wp_maxv_st_facebook_settings',$this->settings);
				if (isset($this->settings['slm_facebook_url']) && $this->settings['slm_facebook_url'] &&
					isset($this->settings['slm_facebook_consumer_key']) && $this->settings['slm_facebook_consumer_key'] &&
					isset($this->settings['slm_facebook_consumer_secret']) && $this->settings['slm_facebook_consumer_secret'] &&
					isset($this->settings['slm_facebook_token']) && $this->settings['slm_facebook_token'] &&
					isset($this->settings['slm_facebook_content_format']) && $this->settings['slm_facebook_content_format']) {
					$this->settings['debug'] = 3;update_option('wp_maxv_st_facebook_settings',$this->settings);
					$time = time() - (int)substr(date('O'),0,3)*60*60;
					/*$this->settings['slm_facebook_next_schedule'] = 0;*/
					if (!isset($this->settings['slm_facebook_next_schedule']) || (isset($this->settings['slm_facebook_next_schedule']) && $this->settings['slm_facebook_next_schedule']<$time)) {
						$this->settings['debug'] = 4;update_option('wp_maxv_st_facebook_settings',$this->settings);
						$this->sharePosts();
					}
				}
			}
		}
		
		public function sharePosts() {
			if (!isset($this->settings))
				$this->settings = get_option('wp_maxv_st_facebook_settings');
			$xPosts = (isset($settings['slm_facebook_max_posts']))?$settings['slm_facebook_max_posts']:'1';
			$perXHours = (isset($settings['slm_facebook_max_posts_hours']))?$settings['slm_facebook_max_posts_hours']:'24';
			$nextTime = time() - (int)substr(date('O'),0,3)*60*60;
			$this->settings['slm_facebook_next_schedule'] = $this->getNextTime($xPosts, $perXHours, $nextTime);
			update_option('wp_maxv_st_facebook_settings',$this->settings);
			$nextAcc = new stdClass();
			$nextAcc->settings = new stdClass();
			$nextAcc->type = 'facebook';
			$nextAcc->id = $this->settings['slm_facebook_acc_id'];
			$nextAcc->settings->consumer_key = $this->settings['slm_facebook_consumer_key'];
			$nextAcc->settings->consumer_secret = $this->settings['slm_facebook_consumer_secret'];
			$nextAcc->settings->token = $this->settings['slm_facebook_token'];
			$nextAcc->settings->token_secret = $this->settings['slm_facebook_token_secret'];
			$nextAcc->settings->page_id = $this->settings['slm_facebook_url'];
			$nextAcc->settings->title_format = '';
			$nextAcc->settings->content_format = $this->settings['slm_facebook_content_format'];
			$nextAcc->settings->user = $this->settings['slm_facebook_user'];
			
			$next_post = $this->getNewPostForSharing($nextAcc);
				
			if (isset($next_post->ID) && $next_post->ID) {
				require_once dirname(__FILE__) . '/utils/Utils.php';
				$data = getDataFromWPPost($next_post, $this->settings['share_excerpt_length'], 'facebook');
				$data = replaceSubstitutionsV2($data, $nextAcc);
				$options = getOptionsForPostingV2($data, $nextAcc);
				
				$postResponse = $this->createFacebookPost($options);
				$result = new stdClass();
				if ($postResponse['error']) {
					$result->status = 'Error';
					$result->content = $postResponse['error'];
				} else {
					$result->status = 'Success';
					$result->content = 'Post ID: '.$postResponse['postID'];
					$result->content .= ' Post URL: '.$postResponse['postURL'];
					$result->url = $postResponse['postURL'];
				}
				if ($result->status=='Success' && isset($result->url) && $result->url) {
					$settings = $this->settings;
				
					if (isset($settings['share_use_backlinksindexer']) && $settings['share_use_backlinksindexer'] &&
						isset($settings['share_use_backlinksindexer_key']) && $settings['share_use_backlinksindexer_key']) {
						
						$params = 'key='.urlencode($settings['share_use_backlinksindexer_key']);
						$params .= '&urls='.urlencode($result->url);
						$this->get_page_now('http://backlinksindexer.com/api.php', $params);
						
						
					}
				}
				$this->saveLogRecord ( $next_post->ID, $nextAcc->id, $result->status, $result->url, $result->content );
				
			}
					
		}
		
		private static function get_page_now($url,$params='') {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") );
			curl_setopt($ch, CURLOPT_NOBODY, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			if ($params == '') {
				curl_setopt ($ch, CURLOPT_POST, false);
				curl_setopt ($ch, CURLOPT_POSTFIELDS, '');
				curl_setopt ($ch, CURLOPT_HTTPGET, true);
			} else {
				curl_setopt ($ch, CURLOPT_POST, true);
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
			}
				
			$result= curl_exec ($ch);
			curl_close ($ch);
			return $result;
		}
		
		private static function getNextTime($xPosts, $perXHours, $currentTime = 0) {
			$postPerseconds = round(($perXHours * 60 * 60) / $xPosts);
			if ($currentTime) {
				$h = date('G', $currentTime);
				$m = date('i', $currentTime);
				$s = date('s', $currentTime);
			} else {
				$h = date('G');
				$m = date('i');
				$s = date('s');
			}
			$hourInSec = ($h * 60 + $m + 1) * 60;
			$nextTime = 0;
			while($nextTime < $hourInSec) {
				$nextTime = $nextTime + $postPerseconds;
			}
			$nextSec = $nextTime + mt_rand(round($postPerseconds / 3), $postPerseconds);
			if ($currentTime)
				$nextTimeResult = strtotime(date('Y-m-d', $currentTime)) + $nextSec;
			else
				$nextTimeResult = strtotime(date('Y-m-d') . ' 00:00:00') + $nextSec;
			return $nextTimeResult;
		}
		
		private function getNewPostForSharing($socialAccount) {
			global $wpdb;
			$socialAccId = $socialAccount->id;
				
			$result = '';
				
			if (isset($this->settings['share_min_content_length']) && intval($this->settings['share_min_content_length'])>0) $content_length = intval($this->settings['share_min_content_length']);
			else $content_length = 50;
		
			$where = "where LENGTH(post_content)>={$content_length} and post_status='publish'";
				
			if (isset($this->settings ['wp_maxv_st_share_post_types']) && is_array($this->settings ['wp_maxv_st_share_post_types']) && count($this->settings ['wp_maxv_st_share_post_types']) > 0) {
				$post_types = get_post_types(array (
						"public" => true
				));
				$post_type_where = '';
				$pt = 1;
				foreach($post_types as $post_type) {
					if ($post_type != 'attachment') {
						$tmp = (((isset($this->settings ['wp_maxv_st_share_post_types'] [$post_type]) && $this->settings ['wp_maxv_st_share_post_types'] [$post_type]) || ! isset($this->settings ['wp_maxv_st_share_post_types'])) ? "post_type='{$post_type}'" : "");
						if ($post_type_where && $tmp) {
							$post_type_where .= ' or ' . $tmp;
						} elseif ($tmp) {
							$post_type_where = $tmp;
						}
					}
				}
		
				if ($post_type_where) {
					$where .= " and ({$post_type_where})";
				}
			} else {
		
				$post_type = '';
		
				if (! isset($this->settings ['share_posts_or_pages']) || $this->settings ['share_posts_or_pages'] == '' || $this->settings ['share_posts_or_pages'] == 'all') {
					$post_type = '';
				} elseif (isset($this->settings ['share_posts_or_pages'])) {
					$post_type = $this->settings ['share_posts_or_pages'];
				}
		
				if ($post_type) {
					$where .= " and post_type='" . $post_type . "'";
				} else {
					$where .= " and (post_type='post' or post_type='page')";
				}
			}
			if (isset($this->settings['slm_postcats'])) {
				$args = array(
						'orderby' => 'name',
						'hide_empty'=> 0
				);
					
				$cats = get_categories($args);
				$post_cats_where = '';
				foreach ($cats as $nextCat) {
					if (isset($this->settings['slm_postcats'][$nextCat->term_taxonomy_id]) && $this->settings['slm_postcats'][$nextCat->term_taxonomy_id]) {
						if ($post_cats_where) {
							$post_cats_where .= ' or term_taxonomy_id='.$nextCat->term_taxonomy_id;
						} elseif ($tmp) {
							$post_cats_where = 'term_taxonomy_id='.$nextCat->term_taxonomy_id;
						}
					}
				}
				if ($post_cats_where) {
					$where .= ' and (exists (select object_id from '.$wpdb->prefix.'term_relationships where object_id=p.ID and ((p.post_type=\'post\' and ('.$post_cats_where.')) or p.post_type!=\'post\') limit 1) or not exists (select object_id from '.$wpdb->prefix.'term_relationships where object_id=p.ID and p.post_type!=\'post\' limit 1)) ';
				}
		
			}
			if (isset($this->settings ['share_old_posts_date']) && $this->settings ['share_old_posts_date']) {
				$where .= " and post_date>='" . $this->settings ['share_old_posts_date'] . " 00:00:00'";
			}
		
			$where .= " and not exists (select postid from " . $wpdb->prefix . "maxv_social_accounts_log where accountid='" . $socialAccId . "' and postid=p.ID limit 1)";
			$where .= " and not exists (select post_id from " . $wpdb->prefix . "postmeta where meta_key='social-acc-auto-share-post' and meta_value='false' and post_id=p.ID limit 1)";
		
			$sql = 'SELECT ID,post_content from ' . $wpdb->posts . ' p ' . $where;
				
			if ($this->settings ['share_posts_order'] == 'oldest') {
				$sql .= ' order by post_date ASC';
			} elseif ($this->settings ['share_posts_order'] == 'random') {
				$sql .= ' order by RAND()';
			} else {
				$sql .= ' order by post_date DESC';
			}
			$sql .= ' limit 1';
				
			$result = '';
			$data = $wpdb->get_results($sql);
			if (count($data) > 0) {
				$result = get_post($data [0]->ID);
			}
			return $result;
		}

		public function createFacebookPost($options) {
			$postinfo = '';
			if ($options->content &&
			$options->app_id &&
			$options->app_secret &&
			$options->token) {
		
				$options->content = html_entity_decode($options->content,ENT_COMPAT,'UTF-8');
		
				require_once dirname(__FILE__).'/utils/facebook/CustomFacebook.php';
		
				$config = array(
						'appId' => $options->app_id,
						'secret' => $options->app_secret,
						'cookie' => true
				);
		
				$page_id = '';
				$feed_name = 'feed';
				$facebook = new CustomFacebook($config);
				switch ($options->type) {
					case '':
					case 'text':
						$page_id = $options->page_id;
				}
		
				if ($page_id) {
					$params = array('access_token' => $options->token );
					$params['message'] = $options->content;
					if (isset($options->source) && $options->source && $options->source!='undefined') $params['link'] = $options->source;
					if (isset($options->image) && $options->image && $options->image!='undefined') $params['picture'] = $options->image;
					$profileLink = '';
					try {
						$postinfo = $facebook->api("/$page_id/".$feed_name, "post", $params);
						if (trim($page_id)=='me') {
							$params = array('access_token' => $options->token );
							$user_profile = $facebook->api('/me','get',$params);
							if(isset($user_profile['link']) && $user_profile['link']) $profileLink = $user_profile['link'];
						} else {
							$params = array('access_token' => $options->token );
							$user_profile = $facebook->api('/'.$page_id,'get',$params);
		
						}
					} catch (MV_FacebookApiException $e) {
						$postinfo['error'] = $e->getMessage();
					}
					if (isset($postinfo['id']) && $postinfo['id']!='') {
						if (isset($postinfo['post_id']) && strpos($postinfo['post_id'],'_')!==false) {
							$fbPostID = $postinfo['post_id'];
						} else {
							$fbPostID = $postinfo['id'];
						}
						$pgg = explode('_', $fbPostID);
						$postID = $pgg[1];
						if ($profileLink) $postURL = $profileLink.'/posts/'.$postID;
						else $postURL = 'http://www.facebook.com/'.$page_id.'/posts/'.$postID;
						$postinfo['postID'] = $fbPostID;
						$postURL = str_replace('/app_scoped_user_id/', '/', $postURL);
						$postURL = str_replace('//posts', '/posts', $postURL);
						$postinfo['postURL'] = $postURL;
						$postinfo['postDate'] = date('Y-m-d H:i:s');
					}
				}
			} else {
				$msg = '';
				if (!isset($options->content) || !$options->content) $msg=' (content is missing)';
				elseif (!isset($options->app_id) || !$options->app_id) $msg=' (App ID is missing)';
				elseif (!isset($options->app_secret) || !$options->app_secret) $msg=' (App Secret is missing)';
				elseif (!isset($options->token) || !$options->token) $msg=' (Token is missing. You need to authorize your application.)';
				$postinfo['error'] = 'Please provide all necessary data.'.$msg;
			}
			return $postinfo;
		}		
		
		public static function saveLogRecord($postId, $accountId, $status, $post_url, $message, $second_tire_id=0, $log_type='post', $original_url_or_id='') {
			require_once dirname ( __FILE__ ) . '/data/SocialAccountsLog.php';
			$log = new SocialAccountsLog ();
			$log->postid = $postId;
			$log->accountid = $accountId;
			$settings = get_option('wp_maxv_st_facebook_settings');
			$time = time() - (int)substr(date('O'),0,3)*60*60;
			if (isset($settings['share_timezone']) && $settings['share_timezone']) {
				$dtz = new DateTimeZone($settings['share_timezone']);
				$dt = new DateTime('now', $dtz);
				$time = $time + $dt->getOffset();
			}
			$log->time = $time;
			$log->status = $status;
			$log->post_url = $post_url;
			$log->message = $message;
			$log->second_tire_id = $second_tire_id;
			$log->type = $log_type;
			$log->original_url_or_id = $original_url_or_id;
			$log->save();
		}
		
	}
}
?>