<?php

namespace FBReactions;

class Renderer {

	private $image;
    private $settings;

	public function __construct($image, $settings) {
		$this->image = $image;
        $this->settings = $settings;
	}

	/*
	 * Adds reaction counts to an image
	 */
	public function drawReactionCount($reactions)
	{
        $reactionSettings = $this->settings['REACTION_SETTINGS'];

    	foreach ($reactions as $index => $reactionCount)
    	{
        	$this->image->text(
            	$reactionCount,
           		$reactionSettings['REACTIONS'][$index]['XPOS'],
                $reactionSettings['REACTIONS'][$index]['YPOS'],
            	function ($font) use ($reactionSettings) {
                	$font->file($reactionSettings['FONT']['FAMILY']);
                	$font->size($reactionSettings['FONT']['SIZE']);
                	$font->color($reactionSettings['FONT']['COLOR']);
                	$font->align('center');
            	}
        	);
    	}
	}

	/*
	 * Draws the shoutout text to image
	 */
	public function drawShoutout($user, $shoutout)
	{
    	$shout = "@{$user}, {$shoutout}";
    	$this->image->text(
        	$shout,
        	$this->settings['SHOUTOUT_SETTINGS']['SHOUTOUT_TEXT']['XPOS'],
            $this->settings['SHOUTOUT_SETTINGS']['SHOUTOUT_TEXT']['YPOS'],
        	function ($font) {
           		$font->file($this->settings['SHOUTOUT_SETTINGS']['SHOUTOUT_TEXT']['FONT']['FAMILY']);
            	$font->size($this->settings['SHOUTOUT_SETTINGS']['SHOUTOUT_TEXT']['FONT']['SIZE']);
            	$font->color($this->settings['SHOUTOUT_SETTINGS']['SHOUTOUT_TEXT']['FONT']['COLOR']);
            	$font->align('left');
        	}
    	);
	}

	/*
	 * Draws the profile image onto the shoutbox
	 */
	public function drawProfileImage($filename, $xpos, $ypos) 
	{
		$this->image->insert($filename, 'bottom-left', $xpos, $ypos);
	}

	/*
	 * I don't like this! Might be better to return $this->image instead
	 */
	public function save($filename)
    {
        return $this->image->save($filename);
    }
}