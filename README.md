# Tumblr_grabbr

Grab Tumblr posts

---


## Note

To avoid implementing entire OAuth flow, *Tumblr_grabbr* uses [Tumblr API v1](http://www.tumblr.com/docs/en/api/v1). Please note that this lib can be broken any time when they decide to deprecate *old* API.

---


## Requirements

- [Laravel 1.5.x](http://laravel.com)
- cURL


---


## Installation

- Copy *tumblr_grabbr.php* to *libraries* folder


---

## Basic usage


- fetch 10 posts from *demo.tumblr.com* using cache (if available)

		$page = Input::get('page', 1);
		$data = Tumblr_grabbr::grab('demo', $page, 10);
		if ($data){ ... }


- You can turn off cache by passing 4th argument `FALSE`
		
		$data = Tumblr_grabbr::grab('demo', 1, 10, FALSE);



- Configured your Tumblr using your own domain?
		
		$data = Tumblr_grabbr::grab('http://yourwebsite.com', 1, 5);


- fetch single post from *demo.tumblr.com*
		
		$post = Tumblr_grabbr::grab_one('demo', 192341);
		if ($post){ ... }

---

## View
	

	$data = Tumblr_grabbr::grab('demo', 1, 10);
	return View::make('blog_listing')->bind('data', $data);


In your *blog_listing.php* view file, play with `$data['posts']`

	foreach ($data['posts'] as $post)
	{
		// print_r($post);
	}


---


## Post type & properties

See [Tumblr API v1 docs](http://www.tumblr.com/docs/en/api/v1)


---


## Pagination

*Tumblr_grabbr* utilizes Laravel's `Paginator` class. 


	$data = Tumblr_grabbr::grab('demo', 1, 10);
	
	if ($data['pagination')
	{
		echo $data['pagination']->links();

		// ...or...
		// echo $data['pagination']->previous();
		// echo $data['pagination']->next();
	}


---

## Summary, Thumbnail support

`Tumblr_grabbr::grab` adds `summary` and `thumbnail-url` property to every *text* type post.


- *The First two paragraphs*(`<p>`) becomes `summary` (if available)
- *URL of the first image*(`<img>`) becomes `thumbnail-url` (if available)

this is the reason why I made Tumblr_grabbr :/


