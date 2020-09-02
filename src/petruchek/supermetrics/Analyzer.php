<?php

namespace petruchek\supermetrics;

/**
 * analyze posts, produce stats
 *
 * This class generates stats after analyzing array of $posts
 *
 * @author petruchek@gmail.com
 */

class Analyzer
{
	const ENCODING = 'UTF-8';
	const AVERAGE_FORMAT = "%.2f";

	/**
	 * Analyze $posts, produce $stats
	 *
	 * This function will loop through array of $posts and generate array of $stats.
     * It expects $posts to be an array of arrays; each of sub-arrays is expected to 
     * have the following keys:
     *
     * - from_id
     * - message
     * - created_time
     * - id
	 *
	 * @param  array  $posts  Array of posts
	 * @return array          returns array of stats and their description
	 */

	public static function analyze($posts)
	{
		$result = [
			'a' => [
				'info' => "Average character length of posts per month. Data keys: 'year-month' (ISO 8601)",
				'data' => [],
			],
			'b' => [
				'info' => "Longest post by character length per month. Data keys: 'year-month' (ISO 8601)",
				'data' => [],
			],
			'c' => [
				'info' => "Total posts split by week number. Data keys: 'year\Wweek' (ISO 8601)",
				'data' => [],
			],
			'd' => [
				'info' => "Average number of posts per user per month. Data keys: 'user_id'",
				'data' => [],
				'average' => 0.00, //I'm not sure if the task asks for this number only or for individual averages as well, so I'm computing both
			],
		];
		
		$earliest_post = PHP_INT_MAX;
		$latest_post = 0;
		
		foreach ($posts as $post)
		{
			$uid = $post['from_id'];
			$len = mb_strlen($post['message'], self::ENCODING);
	
			$ts = strtotime($post['created_time']);
			$week = date('o\WW',$ts); //ISO 8601 Year+Week
			$month = date('Y-m',$ts); //ISO 8601 Year+Month
	
			if ($ts < $earliest_post)
				$earliest_post = $ts;
			if ($ts > $latest_post)
				$latest_post = $ts;
	
			if (!array_key_exists($month,$result['a']['data']))
				$result['a']['data'][$month] = [$len];
			else
				$result['a']['data'][$month][] = $len;
	
			if (!array_key_exists($month,$result['b']['data']))
				$result['b']['data'][$month] = ['id'=>$post['id'],'max_length'=>$len];
			elseif ($len > $result['b']['data'][$month]['max_length'])
				$result['b']['data'][$month] = ['id'=>$post['id'],'max_length'=>$len];
	
			if (!array_key_exists($week,$result['c']['data']))
				$result['c']['data'][$week] = 1;
			else
				$result['c']['data'][$week]++;
	
			if (!array_key_exists($uid,$result['d']['data']))
				$result['d']['data'][$uid] = 1;
			else
				$result['d']['data'][$uid]++;
		}
		
		//only need averages for any month with at least 1 post
		foreach ($result['a']['data'] as &$values)
			$values = sprintf(self::AVERAGE_FORMAT,array_sum($values)/count($values));
		
		/*
			This is tricky. Task says "1000 posts over a six month period". But what if we get 1000 posts within last month only? 
			Hard-coding 6 seems to be too optimistic, so instead we record the earliest post and the latest post from the feed.
			Then we calculate an approximate (and decimal) number of months between two timestamps ($months).
			So then we divide number or posts by $months.
			(during development I got data feed that led to $months being slightly greater than 6.2)
			This way this metrics depends on data only, not on an external promise.
		*/
		
		$months = ($latest_post - $earliest_post)/2628288;
		foreach ($result['d']['data'] as &$value)
			$value = sprintf(self::AVERAGE_FORMAT,$value/$months);
		ksort($result['d']['data']); //no real need to do it, but it looks nicer this way
		$result['d']['average'] = sprintf(self::AVERAGE_FORMAT,array_sum($result['d']['data'])/count($result['d']['data']));
		
		return $result;
	}
}
