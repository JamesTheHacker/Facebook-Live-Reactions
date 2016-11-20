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
