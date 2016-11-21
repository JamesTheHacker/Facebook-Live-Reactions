<?php

/*
* Gets the LIKE, LOVE, HAHA and WOW reaction counts from an object
*/
function reactionCount($fb, $objectID, $accessToken)
{
    $request = $fb->request(
        'GET',
        "/?ids={$objectID}&fields=reactions.type(LIKE).limit(0).summary(total_count).as(LIKE), " .
            'reactions.type(LOVE).limit(0).summary(total_count).as(LOVE), ' .
            'reactions.type(HAHA).limit(0).summary(total_count).as(HAHA), ' .
            'reactions.type(WOW).limit(0).summary(total_count).as(WOW)',
        [],
        $accessToken
    );

    try {
        $response = $fb->getClient()->sendRequest($request);
        $reactions = json_decode($response->getBody(), true);
    } catch (\Exception $e) {
        // die('Error getting reactions: ' . $e);
        return [];
    }

    $reactions = $reactions[key($reactions)];
    unset($reactions['id']);
    return $reactions;
}

/*
* Gets the latest comments from an object
*/
function comments($fb, $objectID, $accessToken)
{
    $request = $fb->request('GET', "/{$objectID}/comments?filter=stream&order=reverse_chronological", [], $accessToken);

    try {
        $response = $fb->getClient()->sendRequest($request);
        $response = json_decode($response->getBody(), true);
        return $response;
    } catch (\Exception $e) {
        // die('Error getting comments: ' . $e);
        return [];
    }
}


/*
* Fetches the users image
*/
function downloadProfileImage($uid, $width, $height)
{
    copy("http://graph.facebook.com/{$uid}/picture?width={$width}&height={$height}", __DIR__ . '/images/profile.jpg');
}

/*
 * Adds reaction counts to an image
 */
function drawReactionCount($image, $reactions, $fontSettings)
{
    $activeReactions = ['LIKE', 'LOVE', 'HAHA', 'WOW'];
    foreach ($activeReactions as $reaction) {
        $image->text(
            $reactions[$reaction]['count'],
            $reactions[$reaction]['xpos'],
            $reactions[$reaction]['ypos'],
            function ($font) use ($fontSettings) {
                $font->file($fontSettings['FAMILY']);
                $font->size($fontSettings['SIZE']);
                $font->color($fontSettings['COLOR']);
                $font->align('center');
            }
        );
    }

    return $image;
}

/*
<<<<<<< HEAD
 * Draws the shoutout text onto the image
 */
function drawShoutout($image, $user, $shoutout, $settings) {

  $shout = "@{$user}, {$shoutout}";
  $image->text($shout,
               $settings['XPOS'],
               $settings['YPOS'],
               function($font) use ($settings) {
                  $font->file($settings['FONT']['FAMILY']);
                  $font->size($settings['FONT']['SIZE']);
                  $font->color($settings['FONT']['COLOR']);
                  $font->align('left');
  });

  return $image;
=======
* Draws the shoutout text onto the image
*/
function drawShoutout($image, $user, $shoutout, $settings)
{
    $shout = "@{$user}, {$shoutout}";
    $image->text(
        $shout,
        $settings['XPOS'],
        $settings['YPOS'],
        function ($font) use ($settings) {
            $font->file($settings['FONT']['FAMILY']);
            $font->size($settings['FONT']['SIZE']);
            $font->color($settings['FONT']['COLOR']);
            $font->align('left');
        }
    );

    return $image;
>>>>>>> Lint_PSR2_Guidelines
}
