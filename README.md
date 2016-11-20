Facebook Live Reactions
=======================

Facebook Lie Reactions is a Linux script for creating Facebook Live Streams that contains interactive reaction counts. It also includes an interactive shoutout feature that gives live shoutouts to users who typed "shared" into the comment box.

Like this ...

![Facebook Live Reactions](http://i.imgur.com/Rm5JEOG.png)

Dependencies
------------

* Linux or OSX
* PHP (Built with 7, should work with 5.6)
* PHP GD or ImageMagik
* FFMPEG (must be compiled with aac and rtmp)
* Photoshop (if you plan to modify the image)

Installing
----------

It's best to install the script on a server. [Get $10 free from Digital Ocean!](https://m.do.co/c/dde4646baa31)

Clone the repo:

    git clone http://github.com/JamesTheHacker/facebook-live-reactions
    cd facebook-live-reactions

Install PHP dependencies ([Composer](https://getcomposer.org/) must be installed):

    composer install

Configuration
-------------

A few things need to be configured to make Facebook Live Reactions work.

**Adding an audio file**

Facebook Live requires an audio stream. I haven't included any audio with the repo as it would increase the size. The audio file must be at least 4 hours in length (Facebook Live streams can only last 4 hours). If the audio file is shorter then this the live stream will stop when the audio ends.

Download an audio stream from YouTube using [youtube-dl](https://github.com/rg3/youtube-dl):

    youtube-dl --extract-audio --audio-format mp3 https://www.youtube.com/watch?v=15uF7r2rCQk

Rename the download to `audio.mp3`

    mv "Vintage Christmas Songs from the 20's & 30's Playlist-15uF7r2rCQk.mp3" audio.mp3

This will download the `.mp3` of the video. In this case it's a playlist of classic 1920's and 1930's Christmas songs.

The audio file above is 20 minutes long. It needs to be looped so it's at least 4 hours in length. Lets use [SoX](http://sox.sourceforge.net/) to loop the audio file.

    sox audio.mp3 audio-loop.mp3 repeat 15

This will take a while to run. Once complete a single `audio-loop.mp3` file will be produced. Copy `audio-loop.mp3` to the `data` directory.

**Edit Settings**

Before modifying the settings you need to create a Facebook application. If you don't already have one [create one here](https://developers.facebook.com/apps/). The application is used to connect to the Graph API to fetch the reactions and comments from the video. When setting up the application you only need to provide basic information.

All settings are stored in the `settings.php` file. To get things working you only need to modify the settings below:

    "POST_ID"       => "",
    "ACCESS_TOKEN"  => "",
    "APP_ID"        => "",
    "APP_SECRET"    => ""

Once you've got your app setup get the apps access token using the [Access Token Tool](https://developers.facebook.com/tools/accesstoken/). Copy the access token into the settings. Also copy the app ID and app secret.

Ignore `POST_ID` for the moment. We'll move onto that.

Create The Live Stream
-----------------------

After you've modified the settings next you need to create a new live stream on Facebook. Go to a page you own, press the "Publishing Tools" tab, and then click "Videos". Press the "Live" button and wait for the popup to load.

Next you should see "Server or stream URL". Copy this URL and paste it to the bottom of `fblive.sh`. Paste it between the `"..."` (keeping the quotes!). Like this:

    ffmpeg \
    -re -y \
    -loop 1 \
    -f image2 \
    -i images/stream.jpg \
    -i data/audio-loop.mp3 \
    -acodec libfdk_aac \
    -ac 1 \
    -ar 44100 \
    -b:a 128k \
    -vcodec libx264 \
    -pix_fmt yuv420p \
    -vf scale=640:480 \
    -r 30 \
    -g 60 \
    -f flv \
    "rtmp://rtmp-api.facebook.com:80/rtmp/1343774358979842?ds=1&s_l=1&a=AaaWtwcn05wdmMCp"

Open a new terminal, navigate to the root directory and run (you may need to run `chmod +x fblive.sh`:

    ./fblive.sh

This will start streaming. Press the "next" button and wait for Facebook to acknowledge the live stream.

By default a blank image will be streamed. You won't see the reactions or shoutouts just yet. This is because we haven't yet started the other script to update the image. We'll move onto that now.

Once the stream loads in preview press "Go Live". Another box should pop up that contains video stats. On this page there is a "view permalink" link. Click that and you will be navigated to the Facebook post that contains the live stream.

In the URL there's a unique ID that conists of a bunch of numbers. Grab this ID and paste it into `settings.php`. Like so:

    "POST_ID" => "90823402348502302894",

Nearly done!

Updating Reactions and Shoutouts
--------------------------------

Open another terminal, navigate to the root directory and run the following command:

    php fblive.php

This will run silently. Do not quit the process! Every 5 seconds it will grab the reactions count and update the live stream. It will also grab the latest comment that contains the word "share" and give a random shoutout to that user.

All done. Your stream should now be live. Leave a reaction, or type the word "shared" into the comment and wait for the video to update.

FAQ
---

**How do I modify the image?**

I've include the `.psd` file in `images/background.psd`. Open this file and change the text, or background image. **DO NOT MOVE THE REACTIONS IMAGES!** Always save the image as `images/background.jpg`

**What if I want to move the reactions images?**

If you move the reactions images you also need to update the `XPOS` and `YPOS` for each reaction in `settings.php`. This is because the script uses these values to draw the reaction counts in the correct place.

**What is the stream.jpg file?**

Do not touch this file. The PHP script will modify this file to include the updated stats and shoutouts. This file is passed directly to `ffmpeg` and is used to generate the live stream.

**How do I add my own shoutout messages?**

To edit the shoutouts modify the `shoutouts.php` file. Keep them short, otherwise they will not render correctly on the live stream.

Contributions
-------------

If the documentation is unclear, or you have any issues please file a new issue. If you want to contribute submit a pull request.