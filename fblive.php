#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

use Intervention\Image\ImageManagerStatic as Image;

$shoutouts = include __DIR__ . '/shoutouts.php';
$settings = include __DIR__ . '/settings.php';

/*
 * Ensure the correct settings are set in order to run the script
 */
if(!isset($settings['ACCESS_TOKEN']) || empty($settings['ACCESS_TOKEN'])) {
    die('Please provide an ACCESS_TOKEN');
} elseif(!isset($settings['APP_ID']) || empty($settings['APP_ID'])) {
    die('Please provide an APP_ID');
} elseif(!isset($settings['APP_SECRET']) || empty($settings['APP_SECRET'])) {
    die('Please provide an APP_SECRET');
}

$fb = new \Facebook\Facebook([
    'app_id' => $settings['APP_ID'],
    'app_secret' => $settings['APP_SECRET'],
    'default_graph_version' => 'v2.8'
]);

while (true) {

    /*
    * Create an Image instance passing in video background
    */
    $image = Image::make($settings['VIDEO_BG']);

    /*
    * Fetch the reaction count
    */
    $reactions = reactionCount($fb, $settings['POST_ID'], $settings['ACCESS_TOKEN']);

    /*
    * Loop through the array modifying array elements with total reaction count, and X/Y coordinates
    */
    array_walk(
        $reactions,
        function (&$reaction, $key) use ($settings) {
            if (!empty($settings['REACTIONS'][$key]['XPOS'])) {
                $xpos = $settings['REACTIONS'][$key]['XPOS'];
            } else {
                $xpos = calculateXPOS(array_search($key, array_keys($settings['REACTIONS'])));
            }
            $reaction = [
                'COUNT' => $reaction['summary']['total_count'],
                'XPOS'  => $xpos,
                'YPOS'  => $settings['REACTIONS'][$key]['YPOS']
            ];
        }
    );

    /*
    * Fetch latest comments
    */
    $comments = comments($fb, $settings['POST_ID'], $settings['ACCESS_TOKEN']);

    /*
     * If data index isn't set user hasn't added POST_ID
     */
    if(isset($comments['data'])) {

        /*
         * Loop through the comments and extract the comments that contain the needle
         */
        $comments = array_filter(
            $comments['data'],
            function ($comment) use ($settings) {
                if (strpos(strtolower($comment['message']), $settings['COMMENT_NEEDLE']) > -1) {
                    return true;
                }
            }
        );
    } else {

        /*
         * Annoy the user no POST_ID is set
         */
        fwrite(STDERR, "No POST_ID set. Remeber to set it when you go live\n");;
    }

    $latestShoutComment = isset($comments[0]) ? $comments[0] : null;

    /*
     * Ensure we haz some reactions.
     * If user hasn't added POST_ID no reactions will be returned from Facebook.
     */
    if(count($reactions) > 0) {

        /*
         * Draw reactions on image
         */
        drawReactionCount(
            $image,
            $reactions,
            $settings['REACTIONS_FONT']
        );
    }
    

    if ($latestShoutComment !== null) {

       /*
        * Download user profile image from Facebook.
        * Image saves/overwrites to ./images/profile.jpeg
        */
        downloadProfileImage(
            $latestShoutComment['from']['id'],
            $settings['SHOUTOUT_IMAGE']['WIDTH'],
            $settings['SHOUTOUT_IMAGE']['HEIGHT']
        );

        /*
         * Draw shoutout on image
         */
        drawShoutout(
            $image,
            $latestShoutComment['from']['name'],
            $shoutouts[array_rand($shoutouts)],
            $settings['SHOUTOUT_TEXT']
        );
    } else {

         /*
          * Draw default shoutout on image
          */
         drawShoutout(
            $image,
            $settings['DEFAULT_SHOUTOUT_NAME'],
            $settings['DEFAULT_SHOUTOUT'],
            $settings['SHOUTOUT_TEXT']
        );
    }

    /*
    * Add profile image to shoutout box
    */
    $image->insert(
        __DIR__ . '/images/profile.jpg',
        'bottom-left',
        $settings['SHOUTOUT_IMAGE']['XPOS'],
        $settings['SHOUTOUT_IMAGE']['YPOS']
    );
    
    /*
    * Save image, and move. This is required so ffmpeg doesn't stall.
    * If you directly overwrite stream.jpg ffmpeg seems to have issues.
    */
    $image->save('images/stream.tmp.jpg');
    system('mv images/stream.tmp.jpg images/stream.jpg');
    sleep(5);
}
