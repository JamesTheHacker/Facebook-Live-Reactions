#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

$shoutouts = require __DIR__ . '/shoutouts.php';
$settings = require __DIR__ . '/settings.php';

/*
 * Ensure the correct settings are set in order to run the script
 */
if(empty($settings['ACCESS_TOKEN'])) {
    die('Please provide an ACCESS_TOKEN');
} elseif(empty($settings['APP_ID'])) {
    die('Please provide an APP_ID');
} elseif(empty($settings['APP_SECRET'])) {
    die('Please provide an APP_SECRET');
}

$FBConfig = new \Facebook\Facebook([
    'app_id' => $settings['APP_ID'],
    'app_secret' => $settings['APP_SECRET'],
    'default_graph_version' => 'v2.8'
]);

$fb = new \FBReactions\Facebook($FBConfig, $settings['ACCESS_TOKEN']);

while (true) {

    $image = Intervention\Image\ImageManagerStatic::make($settings['VIDEO_BG']);
    $renderer = new \FBReactions\Renderer($image, $settings);

    /*
    * Fetch the reactions count
    */
    $reactions = $fb->reactionCount(
        $settings['POST_ID'],
        $settings['REACTION_SETTINGS']['REACTIONS']
    );

    if(count($reactions) > 0) {

        /*
         * Draw reactions on image
         */
        $renderer->drawReactionCount($reactions);
    } else {

        /*
         * No reactions found, display 0 for each
         */
        $renderer->drawReactionCount(
            array_map(function() { return 0; }, $settings['REACTION_SETTINGS']['REACTIONS'])
        );
    }

    /*
    * Fetch latest comments
    */
    $comments = $fb->commentsByKeyword(
        $settings['POST_ID'],
        $settings['KEYWORD']
    );

    if(!empty($settings['POST_ID']) && count($comments) > 0) {

        $latestComment = $comments[0];

        /*
        * Download user profile image from Facebook. Image saves/overwrites to ./images/profile.jpeg
        */
        $fb->saveProfileImage(
            $latestComment['from']['id'],
            $settings['SHOUTOUT_SETTINGS']['PROFILE_IMAGE']['WIDTH'],
            $settings['SHOUTOUT_SETTINGS']['PROFILE_IMAGE']['HEIGHT'],
            __DIR__ . '/images/profile.jpg'
        );

        /*
         * Draw shoutout on image
         */
        $renderer->drawShoutout(
            $latestComment['from']['name'],
            $shoutouts[array_rand($shoutouts)],
            $settings['SHOUTOUT_SETTINGS']['SHOUTOUT_TEXT']
        );
    } else {

        /*
         * Draw default shoutout on image
         */
        $renderer->drawShoutout(
            $settings['SHOUTOUT_SETTINGS']['DEFAULT_SHOUTOUT_NAME'],
            $settings['SHOUTOUT_SETTINGS']['DEFAULT_SHOUTOUT'],
            $settings['SHOUTOUT_SETTINGS']['SHOUTOUT_TEXT']
        );
    }

    /*
    * Add profile image to shoutout box
    */
    $renderer->drawProfileImage(
        __DIR__ . '/images/profile.jpg',
        $settings['SHOUTOUT_SETTINGS']['PROFILE_IMAGE']['XPOS'],
        $settings['SHOUTOUT_SETTINGS']['PROFILE_IMAGE']['YPOS']
    );
    
    /*
    * Save image, and move. This is required so ffmpeg doesn't stall.
    * If you directly overwrite stream.jpg ffmpeg seems to have issues.
    */
    $image->save('images/stream.tmp.jpg');
    system('mv images/stream.tmp.jpg images/stream.jpg');

    /*
     * Pause for x number of seconds
     */
    sleep($settings['FRAME_RATE']);
}
