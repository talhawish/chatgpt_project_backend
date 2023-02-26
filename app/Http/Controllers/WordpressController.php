<?php

namespace App\Http\Controllers;

use App\Models\Post;


use Illuminate\Http\Request;
use Illuminate\View\View as View;
use Illuminate\Support\Facades\Crypt;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Http;

class WordpressController extends Controller
{
    
	/**
	 * Guzzle client for requests.
	 *
	 * @since 1.0.0
	 *
	 * @var \GuzzleHttp\Client
	 */
	private $client;

	/**
	 * Guzzle Headers
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $headers = [];


    /**
	 * User Credentials
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $credentials = [];


     /**
	 * User Credentials
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $connected = false;

	/**
	 * Site URL
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $site_url = '';

	/**
	 * Site Categories
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $categories = [];

	/**
	 * Constructor
	 *
	 * @author GACHTOU Noureddine <n.gachtou@hotmail.com>
	 * @since  NEXT
	 */
	public function __construct($url, $username, $password) {
		$this->site_url = $url;
        $this->credentials = [
            'username' => $username,
            // 'password' => Crypt::decryptString($password),
            'password' => $password,
        ];
		$this->client = new \GuzzleHttp\Client();
		$this->authenticate_user();
		$this->categories = $this->get_categories();
	}


  


	/**
	 * Publish post to website.
	 *
	 * @author GACHTOU Noureddine <n.gachtou@hotmail.com>
	 * @since  NEXT
	 * @param $request Request object containing form fields.
	 */
	public function upload_post ( $request ) {
		
		if(!$request->published_status) {
			
			// unset($request['wp_post_id']);
			// dd($this->client->post($this->site_url . '/wp-json/wp/v2/posts/', ['headers' => $this->headers, 'form_params' => $this->format_post($request)]));
			$post = $this->client->post( $this->site_url . '/wp-json/wp/v2/posts/', [ 'headers' => $this->headers, 'form_params' => $this->format_post( $request ) ] ); 
			$post  = json_decode( $post->getBody() );

		
			$this->update_post($request->id, $post);
			
			return $post;
		}

	}


	public function edit_post ( $request ) {
		
		if(!empty($request['wp_post_id'])) {
			
					
			
			$post = json_decode($this->client->request('post', $this->site_url . '/wp-json/wp/v2/posts/'.$request['wp_post_id'], [ 'headers' => $this->headers, 'form_params' => $this->format_edit_post( $request ) ] )->getBody()->getContents() );


			$this->update_post($request['id'], $post);
			
			return $post;
		}

	}


	public function deletePost($postId) : void
    {
		
		$this->client->delete($this->site_url . '/wp-json/wp/v2/posts/'.$postId, ['headers' => $this->headers]) ;
		
    } 


	/**
	 * Get's Bearer Token for Site.
	 *
	 * @author GACHTOU Noureddine <n.gachtou@hotmail.com>
	 * @since  NEXT
	 */
	private function authenticate_user() : void {
		// dd($this->credentials);
			
		
		$response = json_decode($response = $this->client->post( $this->site_url . '/wp-json/api-bearer-auth/v1/login', [ 
				\GuzzleHttp\RequestOptions::JSON => $this->credentials,
			] )->getBody()) ;


		
		

		$this->headers = [
			'Authorization' => 'Bearer ' . $response->access_token,
			'Accept'        => 'application/json',
		];

		
		if(!empty($response)){
			$this->connected = true;
		}

		
	}

	


	/**
	 * Return all categories from site.
	 *
	 * @author GACHTOU Noureddine <n.gachtou@hotmail.com>
	 * @since  NEXT
	 * @return array
	 */
	public function get_categories() : array {
		$categories = [];
		$request = json_decode( $this->client->get($this->site_url . '/wp-json/wp/v2/categories/', ['headers' => $this->headers, 'form_params'])->getBody() );
		$raw_categories =  $request;
		foreach ( $raw_categories as $category ) {
			$categories[] = [
				'id'   => $category->id,
				'name' => $category->name,
			];
		}
		
		return $categories;
	}

	public function get_tags() : array {
		$tags = [];
		$request = $this->client->get($this->site_url . '/wp-json/wp/v2/tags/', ['headers' => $this->headers, 'form_params']);
		$raw_tags = json_decode( $request->getBody() );
		foreach ( $raw_tags as $tag ) {
			$tags[] = [
				'id'   => $tag->id,
				'name' => $tag->name,
			];
		}
		
		return $tags;
	}
	

	/**
	 * Formats incoming request data into proper api format.
	 *
	 * @author GACHTOU Noureddine <n.gachtou@hotmail.com>
	 * @since  NEXT
	 * @param  $Data
	 * @return array
	 */
	private function format_post($data) : array {
		
		$post_format = [
			
			'title'      => $data['title'],
			'content'    => $data['content'],
			'status'     => $data['status'],
			'categories' => $data['categories'],
			'slug' => $data['slug'],
			'date' => $data['date'],
			'date_gmt' => $data['date_gmt'],
			'guid' => $data['guid'],
			'type' => $data['type'],
			'link' => $data['link'],
			'excerpt' => $data['excerpt'],
			'comment_status' => $data['comment_status'],
			'ping_status' => $data['ping_status'],
			'meta' => $data['meta'],
			'tags' => $data['tags'],

		];
		
		return $post_format;
	}



	

	private function update_post($postId, $data): void
	{

		$post_format = [
			'wp_post_id' => $data->id,
			'author' => $data->author,
			'slug' => $data->slug,
			'date' => $data->date,
			'date_gmt' => $data->date_gmt,
			'guid' => $data->guid->rendered,
			'type' => $data->type,
			'link' => $data->link,
			'status' => $data->status,
			'excerpt' => $data->excerpt->rendered,
			'comment_status' => $data->comment_status,
			'ping_status' => $data->ping_status,
			// 'meta' => empty($data->meta) ? "": $data->meta,
			'tags' => empty($data->tags) ? "" :empty($data->tags),
			'title' => $data->title->rendered,
			'content' => $data->content->rendered,
			'categories' => $data->categories,
			'meta_title' => $data->yoast_head_json->og_title,
			'meta_description' => $data->yoast_head_json->og_description,
			'published_status' => true,
			'needs_update' => false,
		];

		
		Post::whereId($postId)->update($post_format);
	}


	private function format_edit_post( $data )
	{


		// dd($data->tags);
		// dd(str_replace('"', '', $data->tags));
		

		return [
			// 'wp_post_id' => $data->id,
			// 'date' => $data->date,
			'title' => $data->title,
			'content' => $data->content,
			'excerpt' => $data->excerpt,
			'slug' => $data->slug,
			'author' => $data->author,
			'guid' => $data->guid,
			'type' => $data->type,
			'link' => $data->link,
			'status' => $data->status,
			'comment_status' => $data->comment_status,
			'ping_status' => $data->ping_status,
			'meta' => $data->meta,
			'tags' => $data->tags,
			'categories' => $data->categories,
		];

		
		
	}



	/**
	 * Return all Posts from site.
	 *
	 * @author GACHTOU Noureddine <n.gachtou@hotmail.com>
	 * @since  NEXT
	 * @return array
	 */
	public  function get_posts() : array {
		$categories = [];

		$raw_posts = json_decode( $this->client->get( $this->site_url . '/wp-json/wp/v2/posts/', [ 'headers' => $this->headers, 'form_params' ] )->getBody() );
		
		foreach ( $raw_posts as $post ) {
			$posts[] = [
				'id'   => $post->id,
				'name' => $post->name,
			];
		}

		return $posts;
	}

	public  function get_post($id)  {
		
		$raw_post = json_decode( $this->client->get( $this->site_url . '/wp-json/wp/v2/posts/'.$id, [ 'headers' => $this->headers, 'form_params' ] )->getBody() );
	
		return $raw_post;
	}



}
