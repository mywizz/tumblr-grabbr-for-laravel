<?php
/**
 * Tumblr_grabbr
 *
 * @author     Yunseok Kim <mywizz@gmail.com>
 * @copyright  Copyright (c) 2011, Yunseok Kim
 */

class Tumblr_grabbr {
	/**
	 * Cache lifetime
	 *
	 * @var  integer  minutes to live
	 */
	
	public static $CACHE_LIFETIME = 60;
	
	// ---------------------------------------------------------------------
	
	/**
	 * Get posts
	 *
	 * @param   string   $blog_name
	 * @param   integer  $page
	 * @param   integer  $perpage
	 * @param   bool     $useCache
	 * @return  mixed|NULL
	 */
	public static function grab($blog_name, $page = 1, $perpage = 10, $useCache = TRUE)
	{
		$cache = 'tumblr_posts_' . $page . '_' . $perpage;
		
		if ($useCache === TRUE and Cache::has($cache))
		{
			return Cache::get($cache);
		}
			
		$params = http_build_query(array(
		        'start' => ($page - 1) * $perpage,
		        'num' => $perpage
		));
		
		$url = preg_match('/^http/i', $blog_name) ? $blog_name : 'http://' . $blog_name . '.tumblr.com';
		
		$c = curl_init($url . '/api/read/json?' . $params);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);
		$res = curl_exec($c);
		$status = curl_getinfo($c, CURLINFO_HTTP_CODE);
		curl_close($c);
		
		if ($status !== 200)
		{
			return NULL;
		}
		
		$data = json_decode(str_replace(array('var tumblr_api_read = ', ';'), '', $res), TRUE);

		foreach ($data['posts'] as $k => $post)
		{
			if ($post['type'] == 'regular')
			{
				// capture first two paragraphs (if any) for summary
				$summary = NULL;
				
				preg_match('/(<p>.+?<\/p>)(<p>.+?<\/p>)?/i', str_replace("\n", '', $post['regular-body']), $match);
				if ($match)
				{
					$summary = empty($match[2]) ? $match[1] : $match[1] . $match[2];
					$summary = strip_tags($summary, '<p><i><s><del><ins><b><em><a>');
				}
				
				$data['posts'][$k]['summary'] = $summary;
				
				// capture first image url (if any) for thumbnail view
				preg_match('/<img src="(.+?)"/i', $post['regular-body'], $img);
				
				$data['posts'][$k]['thumbnail-url'] = $img ? $img[1] : NULL;
			}
		}
		
		$data['pagination'] = \System\Paginator::make($data['posts'], $data['posts-total'], $perpage);
		
		Cache::put($cache, $data, self::$CACHE_LIFETIME);
		return $data;
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Get a single post
	 *
	 * @param   string   $blog_name
	 * @param   integer  $id
	 * @return  mixed|NULL
	 */
	public static function grab_one($blog_name, $id)
	{
		$url = preg_match('/^http/i', $blog_name) ? $blog_name : 'http://' . $blog_name . '.tumblr.com';
		
		$c = curl_init($url . '/api/read/json?id=' . $id);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);
		$res = curl_exec($c);
		$status = curl_getinfo($c, CURLINFO_HTTP_CODE);
		curl_close($c);
		
		if ($status !== 200)
		{
			return NULL;
		}
		
		$data = json_decode(str_replace(array('var tumblr_api_read = ', ';'), '', $res), TRUE);
		$data['pagination'] = FALSE;
		return empty($data['posts']) ? NULL : $data['posts'][0];
	}
}