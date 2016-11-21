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
    // Like counter
    $image->text(
        $reactions['LIKE']['count'],
        $reactions['LIKE']['xpos'],
        $reactions['LIKE']['ypos'],
        function ($font) use ($fontSettings) {
            $font->file($fontSettings['FAMILY']);
            $font->size($fontSettings['SIZE']);
            $font->color($fontSettings['COLOR']);
            $font->align('center');
        }
    );

    // Love counter
    $image->text(
        $reactions['LOVE']['count'],
        $reactions['LOVE']['xpos'],
        $reactions['LOVE']['ypos'],
        function ($font) use ($fontSettings) {
            $font->file($fontSettings['FAMILY']);
            $font->size($fontSettings['SIZE']);
            $font->color($fontSettings['COLOR']);
            $font->align('center');
        }
    );

    // Haha counter
    $image->text(
        $reactions['HAHA']['count'],
        $reactions['HAHA']['xpos'],
        $reactions['HAHA']['ypos'],
        function ($font) use ($fontSettings) {
            $font->file($fontSettings['FAMILY']);
            $font->size($fontSettings['SIZE']);
            $font->color($fontSettings['COLOR']);
            $font->align('center');
        }
    );

    // Wow counter
    $image->text(
        $reactions['WOW']['count'],
        $reactions['WOW']['xpos'],
        $reactions['WOW']['ypos'],
        function ($font) use ($fontSettings) {
            $font->file($fontSettings['FAMILY']);
            $font->size($fontSettings['SIZE']);
            $font->color($fontSettings['COLOR']);
            $font->align('center');
        }
    );

    return $image;
}

/*
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
}
