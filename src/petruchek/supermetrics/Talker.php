<?php

namespace petruchek\supermetrics;

/**
 * talk to the API: register tokens, fetch pages of posts
 *
 * This class requires $client_id for initialization.
 * The only public method besides constructor is fetch_posts()
 * HTTP requests, token management is done automatically
 * requires curl to make HTTP requests to the API
 *
 * @author petruchek@gmail.com
 */

class Talker
{
	const URL_REGISTER = "https://api.supermetrics.com/assignment/register";
	const TOKEN_LIFESPAN = 59*60; //so way we can re-use same token without re-registering it

	const URL_FETCH = "https://api.supermetrics.com/assignment/posts";
	const NUM_PAGES = 10; //how many to fetch by default

	const PARAM_NAME = "Val Petruchek"; 
	const PARAM_EMAIL = "petruchek@gmail.com";

	private $client_id;
	private $sl_token = ""; //the last token registered
	private $sl_token_timestamp = 0; //when was that token registered

	/**
	 * Constructor
	 *
     * Saves $client_id to a private property.
     * Doesn't do anything else.
     *
	 * @param  string $client_id Required later when obtaining sl_token by set_token()
	 */
	
	public function __construct($client_id)
	{
		$this->client_id = $client_id;
	}

	/**
	 * Fetches $pages of posts from API
	 *
     * Loops through 1 to $pages and calls fetch_page() for each iteration.
     * Merges the results returned by page into single array.
     * Returns array of $posts, doesn't verify what's inside.
     *
	 * @param  int    $pages  (optional) Number of pages to fetch. Default is NUM_PAGES
	 * @return array  of $posts
	 */

	public function fetch_posts($pages = self::NUM_PAGES)
	{
		$result = [];
		for ($i=1;$i<=$pages;$i++)
		{
			$posts = $this->fetch_page($i);
			$result = array_merge($result, $posts);
		}
		return $result;
	}

	/**
	 * Fetches one page of posts via API
	 *
     * Forms an array of $params, sends HTTP GET request (via curl) to URL_FETCH, parses response.
     * Throws Exceptions if response is not JSON or doesn't have posts in the expected place.
     * Returns array of $posts, doesn't verify what's inside.
     *
	 * @param  int    $page   (optional) Number of the page to fetch. Default is 1
	 * @return array          of $posts
	 */

	private function fetch_page($page = 1)
	{
		$params = [
			'sl_token' => $this->get_token(),
			'page' => $page,
		];

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, self::URL_FETCH."?".http_build_query($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close ($ch);

		$result = json_decode($response, true);
		if (json_last_error() != JSON_ERROR_NONE)
			throw new \Exception("JSON expected, but not received.");

		if (!array_key_exists('data', $result) || !is_array($result['data']) || !array_key_exists('posts', $result['data']))
			throw new \Exception("Missing 'data' field in the response OR 'posts' field in the data.");

		return $result['data']['posts'];
	}

	/**
	 * Returns a token to use in an API request.
	 *
     * Checks if we there's a token at all.
     * Checks if the token has not expired (against TOKEN_LIFESPAN).
     * Calls set_token() if a new token is required.
     * Returns private property set in set_token.
     *
	 * @return string sl_token
	 */

	private function get_token()
	{
		if ( !$this->sl_token || (time()-$this->sl_token_timestamp > self::TOKEN_LIFESPAN) )
			$this->set_token();
		return $this->sl_token;
	}

	/**
	 * Obtains new token from the API.
	 *
     * Forms an array of $params, sends HTTP POST request (via curl) to URL_REGISTER, parses response.
     * Throws Exceptions if response is not JSON or doesn't have a token in the expected place.
     * Saves token to a private property.
     * Saves token timestamp to another private property.
     *
	 */

	private function set_token()
	{
		$params = [
			'client_id' => $this->client_id,
			'email' => self::PARAM_EMAIL,
			'name' => self::PARAM_NAME,
		]; 

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, self::URL_REGISTER);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close ($ch);

		$result = json_decode($response, true);
		if (json_last_error() != JSON_ERROR_NONE)
			throw new \Exception("JSON expected, but not received.");

		if (!array_key_exists('data', $result) || !is_array($result['data']) || !array_key_exists('sl_token', $result['data']))
			throw new \Exception("Missing 'data' field in the response OR 'sl_token' field in the data.");

		$this->sl_token = $result['data']['sl_token'];
		$this->sl_token_timestamp = time();
	}

}