<?php

define('SETTINGS', include __DIR__ . '/settings.php');
define('IMAGE_WIDTH', 640);

/*
* Gets the LIKE, LOVE, HAHA and WOW reaction counts from an object
*/
function reactionCount($fb, $objectID, $accessToken)
{
    foreach (SETTINGS['REACTIONS'] as $key => $position) {
        $fields[] = "reactions.type({$key}).limit(0).summary(total_count).as({$key})";
    }
    $reactionParams = ['ids' => $objectID, 'fields' => join(',', $fields)];
    $endpoint = '/?' . http_build_query($reactionParams);
    $request = $fb->request('GET', $endpoint, [], $accessToken);

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
    $commentParams = ['filter' => 'stream', 'order' => 'reverse_chronological'];
    $request = $fb->request(
        'GET',
        "/{$objectID}/comments?" . http_build_query($commentParams),
        [],
        $accessToken
    );

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
    $profileImageParams = ['width' => $width, 'height' => $height];
    $endpoint = "http://graph.facebook.com/{$uid}/picture?" . http_build_query($profileImageParams);

    copy($endpoint, __DIR__ . '/images/profile.jpg');
}

/*
* Adds reaction counts to an image
*/
function drawReactionCount($image, $reactions, $fontSettings)
{
    foreach (getActiveReactions() as $index => $reaction) {
        $image->text(
            $reactions[$reaction]['COUNT'],
            $reactions[$reaction]['XPOS'] ?: calculateXPOS($index),
            $reactions[$reaction]['YPOS'],
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

/*
* Pull the active reactions from the settings file
*/
function getActiveReactions()
{
    return array_keys(SETTINGS['REACTIONS']);
}

/*
* Calculate default X Position of a reaction by its index number
*/
function calculateXPOS($index = 0)
{
    $numberOfReactions = count(getActiveReactions());
    $gridCenter = (IMAGE_WIDTH / $numberOfReactions) / 2;
    $textCenter = (SETTINGS['REACTIONS_FONT']['SIZE'] / 2);
    $position = $index++;   // We want the index + 1
    return ($gridCenter + $textCenter) * $position;
}
