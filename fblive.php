#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

use Intervention\Image\ImageManagerStatic as Image;

$shoutouts = require __DIR__ . '/shoutouts.php';
$settings = require __DIR__ . '/settings.php';

$fb = new \Facebook\Facebook([
  'app_id' => $settings['APP_ID'],
  'app_secret' => $settings['APP_SECRET'],
  'default_graph_version' => 'v2.8'
]);

while(true) {

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
  array_walk($reactions, function(&$reaction, $key) use ($settings) {
    $reaction = [ 
      'count' => $reaction['summary']['total_count'],
      'xpos'  => $settings['REACTIONS'][$key]['XPOS'],
      'ypos'  => $settings['REACTIONS'][$key]['YPOS']
    ];
  });

  /*
   * Fetch latest comments
   */
  $comments = comments($fb, $settings['POST_ID'], $settings['ACCESS_TOKEN']);

  /*
   * Loop through the comments and extract the comments that contain the word share
   */
  $comments = array_filter($comments['data'], function($comment) {
    if(strpos(strtolower($comment['message']), 'share') > -1) {
      return true;
    }
  });

  $latestShareComment = $comments[0];

  /*
   * Download user profile image from Facebook. Image saves/overwrites to ./images/profile.jpeg
   */
  downloadProfileImage($latestShareComment['from']['id'], 
                       $settings['SHOUTOUT_IMAGE']['WIDTH'],
                       $settings['SHOUTOUT_IMAGE']['HEIGHT']);

  /*
   * Add profile image to shoutout box
   */
  $image->insert(__DIR__ . '/images/profile.jpg',
                 'bottom-left',
                 $settings['SHOUTOUT_IMAGE']['XPOS'],
                 $settings['SHOUTOUT_IMAGE']['YPOS']
  );

  /*
   * Draw reactions on image
   */
  drawReactionCount($image, $reactions, $settings['REACTIONS_FONT']);

  /*
   * Draw shoutout on image
   */
  drawShoutout($image, $latestShareComment['from']['name'], $shoutouts[array_rand($shoutouts)], $settings['SHOUTOUT_TEXT']);


  /*
   * Save image, and move. This is required so ffmpeg doesn't stall. If you directly overwrite stream.jpg ffmpeg seems to have issues.
   */
  $image->save('images/stream.tmp.jpg');
  system('mv images/stream.tmp.jpg images/stream.jpg');
  sleep(5);
}
