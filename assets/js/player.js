document.addEventListener("DOMContentLoaded", () => {
  const player = videojs('my_video', {
    fluid: true,
    controlBar: {
      volumePanel: {inline: false},
      children: [
        'playToggle',
        'volumePanel',
        'currentTimeDisplay',
        'timeDivider',
        'durationDisplay',
        'progressControl',
        'liveDisplay',
        'fullscreenToggle'
      ]
    }
  });

  // enable quality selector
  player.hlsQualitySelector({
    displayCurrentQuality: true
  });

  // default stream
  let defaultSrc = "";
  player.src({
    src: defaultSrc,
    type: "application/x-mpegURL"
  });

  // GLOBAL LOAD STREAM FUNCTION
  window.loadStream = function(url) {
    player.src({ src: url, type: "application/x-mpegURL" });
    player.play();
  };

  window.player = player; // global
});



player.addRemoteTextTrack({
  kind: 'captions',
  src: 'https://example.com/subtitles-en.vtt',
  srclang: 'en',
  label: 'English'
}, false);

player.addRemoteTextTrack({
  kind: 'captions',
  src: 'https://example.com/subtitles-hi.vtt',
  srclang: 'hi',
  label: 'Hindi'
}, false);
