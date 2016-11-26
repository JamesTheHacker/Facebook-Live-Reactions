<?php

namespace FBReactions;

class Facebook {

	private $fb;
	private $accessToken;

	public function __construct($fb, $accessToken) {

		$this->fb = $fb;
		$this->accessToken = $accessToken;
	}

	public function reactionCount($objectID, $reactions) {

    	foreach ($reactions as $key => $position) {
        	$fields[] = "reactions.type({$key}).limit(0).summary(total_count).as({$key})";
    	}

    	$reactionParams = ['ids' => $objectID, 'fields' => join(',', $fields)];
    	$endpoint = '/?' . http_build_query($reactionParams);
    	$request = $this->fb->request('GET', $endpoint, [], $this->accessToken);

        /*
         * Fetch the reactions count from Facebook
         */
    	try {
        	$response = $this->fb->getClient()->sendRequest($request);
        	$reactions = json_decode($response->getBody(), true);
            $reactions = current($reactions);
    	} catch (\Exception $e) {
        	// die('Error getting reactions: ' . $e);
        	return [];
    	}

        /*
         * We don't need the id element. Remove it.
         */
        unset($reactions['id']);

        /*
         * We're only interested in the reaction count
         */
        array_walk($reactions, function(&$reaction) {
            $reaction = $reaction['summary']['total_count'];
        });

    	return $reactions;
	}

	/*
	 * Fetch latest comments
	 */
	function comments($objectID)
    {
    	$commentParams = ['filter' => 'stream', 'order' => 'reverse_chronological'];
    	$request = $this->fb->request(
        	'GET',
        	"/{$objectID}/comments?" . http_build_query($commentParams),
        	[],
        	$this->accessToken
    	);

    	try {
        	$response = $this->fb->getClient()->sendRequest($request);
        	return json_decode($response->getBody(), true)['data'];
    	} catch (\Exception $e) {
        	// die('Error getting comments: ' . $e);
        	return [];
    	}
	}

	/*
	 * Returns an array of comments that contains a specific keyword
	 */
	function commentsByKeyword($objectID, $keyword, $caseSensitive = false)
    {
        $comments = $this->comments($objectID);

        return array_filter($comments, function ($comment) use ($keyword, $caseSensitive) {

            $message = $comment['message'];

            if ($caseSensitive) {
                return strpos($message, $keyword) > -1;
            } else {
                return strpos(strtolower($message), strtolower($keyword)) > -1;
            }
        });

    }

    /*
     * Downloads and saves public profile image
     */
    function saveProfileImage($uid, $width, $height, $filename)
    {
        $profileImageParams = [
            'width' => $width,
            'height' => $height,
        ];

        $endpoint = "http://graph.facebook.com/{$uid}/picture?" . http_build_query($profileImageParams);
        copy($endpoint, $filename);
    }
}