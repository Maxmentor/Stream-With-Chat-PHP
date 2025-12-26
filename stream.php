<?php
include "config/config.php";

/* Admin Settings */
$settings = $conn->query("SELECT * FROM admin_settings LIMIT 1")->fetch_assoc();

/* Session check */
if(!isset($_SESSION['username'])){
    header("Location: userset.php");
    exit;
}

/* Default room */
if(!isset($_SESSION['room_id'])){
    $_SESSION['room_id'] = 'WORLD001';
    $_SESSION['room_name'] = 'WORLD';
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CrickMax Stream</title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://vjs.zencdn.net/8.8.0/video-js.css" rel="stylesheet" />

<script src="https://vjs.zencdn.net/8.8.0/video.min.js"></script>
<script src="https://unpkg.com/videojs-contrib-quality-levels/dist/videojs-contrib-quality-levels.min.js"></script>
<script src="https://unpkg.com/videojs-hls-quality-selector/dist/videojs-hls-quality-selector.min.js"></script>

<style>


/* ===============================
   SHARE POPUP MODAL
================================ */
.share-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.55);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:9999;
}

.share-modal{
    background:#fff;
    width:92%;
    max-width:420px;
    border:4px solid #000;
    padding:1.5rem;
    box-shadow:
      rgba(0,0,0,.35) 0px 10px 25px;
}

.share-content p{
    margin:.5rem 0;
    font-size:1rem;
    color:#000;
}

.share-content strong{
    font-size:1.05rem;
}

.share-link{
    color:#000;
    font-weight:600;
}

.room-id{
    margin-top:1rem;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:.5rem;
}

.copy-id{
    border:1px solid #000;
    background:#fff;
    padding:.25rem .5rem;
    cursor:pointer;
}

/* ---------- Buttons ---------- */
.share-actions{
    margin-top:1.5rem;
    display:flex;
    justify-content:space-between;
    gap:.75rem;
}

.share-actions button{
    flex:1;
    padding:.6rem 0;
    border:1.5px solid #000;
    background:#fff;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
}

.share-actions button:hover{
    background:#000;
    color:#fff;
}

/* ---------- Mobile ---------- */
@media(max-width:650px){
    .share-modal{
        width:94%;
    }
}



    button{
border-radius: .6px !important;
}

.vjs-modal-dialog-content{
   display: none;
}

.vjs-error .vjs-error-display:before{
   
    display: none;
}


.file-btn{
  display:flex;
  align-items:center;
  justify-content:center;
  width:48px;
  height:48px;
  background:black;
  border-radius:50%;
  cursor:pointer;
  font-size:30px;
  transition:.2s;
padding-left:-.6rem:
}

.file-btn:hover{
  background:#00e676;
  color:#000;
}

.file-btn:active{
  transform:scale(0.95);
}

. file input popup {
background-color:black;
}

</style>
</head>
<body>

<!-- TOP BAR BUTTONS FROM ADMIN -->
<div class="top-bar card">
    <?php for($i=1;$i<=4;$i++): 
        $text = $settings["btn{$i}_text"];
        $url  = $settings["btn{$i}_url"];
        if($text && $url):
    ?>
        <button class="btn" onclick="loadStream('<?=$url?>')"><?=$text?></button>
    <?php endif; endfor; ?>

    <?php if(!empty($_SESSION['is_creator'])): ?>
        <button class="btn" onclick="openShare()">SHARE ROOM</button>
    <?php endif; ?>
   
    <a href="logout.php"><button class="btn">EXIT</button></a>
</div>

<!-- MAIN CONTENT -->
<div class="main">
    <!-- VIDEO PLAYER -->
    <div class="video-box card">
        <video style="width:100%;"
  id="my_video"
  class="video-js vjs-default-skin"
  poster="https://i.ibb.co/3mpx8Hnw/dppp.jpg"
  controls
  preload="auto"
  width="100%"
  height="500"
  poster="https://bitdash-a.akamaihd.net/content/sintel/poster.png"
  data-setup='{}'
>
    <p class="vjs-no-js">
      To view this video please enable JavaScript.
    </p>
</video>   </div>

    <!-- CHAT SECTION -->
    <div class="chat-box card" id="chatBox">
        <div class="chat-header"  style="width:100%; display:flex;"> 
          <div style="width:60%;"> 
            <p><b>Room Name:</b> <?= htmlspecialchars($_SESSION['room_name']) ?></p>
            <h3>Room ID: <span id="roomIdText"><?= htmlspecialchars($_SESSION['room_id']) ?></span></h3>
      
        </div>
        <div style="width:30%;float:right;align-item:center;"> 
                  <button style="width:100%;" class="btn" onclick="copyRoomID()">Copy Room ID</button>
        </div>
        </div>
        <div class="chat-messages" id="messages"></div>
        <div class="bottom-bar">
            <input type="text" id="msgInput" placeholder="Type message">
            <input type="file" id="fileInput" accept="image/*">
            <button class="btn" id="sendBtn">Send</button>
        </div>
    </div>
</div>

<!-- FLOATING CHAT BUTTON -->
<div class="float-chat" onclick="openChat()">💬</div>

<!-- MOBILE CHAT POPUP -->
<div class="popup" id="chatPopup">
<div class="popup-content ">
    
    <div class="chat-header">
         <p><b>Room Name:</b> <?= htmlspecialchars($_SESSION['room_name']) ?></p>
            <h3>Room ID: <span id="roomIdText"><?= htmlspecialchars($_SESSION['room_id']) ?></span></h3>
 
    </div>
    <div class="div" style="float:right;"><button style="width:50px; float:right;margin-top:-3.4rem;" class="btn" onclick="closeChat()">×</button></div>
    <div class="chat-messages" id="messagesPopup"></div>
    <div class="bottom-bar">
        <input type="text" id="msgInputPopup" placeholder="Type message">
   <label class="file-btn">
<input type="file" id="fileInputPopup" class="fileInputPopup"  accept="image/*">
     🗃️ </label>
  <button class="btn" id="sendBtnPopup">Send</button>
    </div>
</div>
</div>

<!-- SHARE POPUP -->

<div class="share-overlay" id="sharePopup">
  <div class="share-modal">

    <div id="shareTextBox">
<?= htmlspecialchars($_SESSION['room_id']) ?>
    </div>

    <!-- HIDDEN INPUT (KEY PART) -->
    <input type="text" style="width:100%;height:40px;" id="copyInput" value="Hey Please Join My Room
 Go To https://locallhot/join-room.php &nbsp;

Room ID: <?= htmlspecialchars($_SESSION['room_id']) ?>" />

    <div class="share-actions">
      <button onclick="copyShareText()">COPY</button>
      <button onclick="shareText()">SHARE</button>
      <button onclick="closeShare()">CLOSE</button>
    </div>

  </div>
</div>


<!-- JS LIBS -->

<script src="assets/js/player.js"></script>
<script src="assets/js/chat.js"></script>

<script>

function openShare(){
    document.getElementById("sharePopup").style.display = "flex";
}

function closeShare(){
    document.getElementById("sharePopup").style.display = "none";
}

/* ===== COPY (GUARANTEED) ===== */
function copyShareText(){
    const input = document.getElementById("copyInput");
    input.focus();
    input.select();
    input.setSelectionRange(0, 99999); // mobile

    try{
        document.execCommand("copy");
        alert("Copied!");
    }catch(e){
        alert("Copy failed. Please copy manually.");
    }
}

/* ===== SHARE ===== */
function shareText(){
    const text = document.getElementById("copyInput").value;

    if(navigator.share){
        navigator.share({
            title: "Join My Room ",
            text: text
        }).catch(()=>{});
    }else{
        copyShareText();
        alert("Share not supported, text copied instead.");
    }
}




    function copyRoomID(){
    const roomID = document.getElementById('roomIdText').innerText;
    navigator.clipboard.writeText(roomID).then(() => {
        alert("Room ID copied!");
    });
}
/* ===== CHAT POPUP ===== */
function openChat(){ document.getElementById('chatPopup').style.display='flex'; }
function closeChat(){ document.getElementById('chatPopup').style.display='none'; }

/* ===== SHARE POPUP ===== */
function openShare(){ document.getElementById('sharePopup').style.display='flex'; }
function closeShare(){ document.getElementById('sharePopup').style.display='none'; }
function copyShare(){ 
    const t = document.getElementById('shareText');
    t.select();
    document.execCommand("copy");
    alert("Copied!");
}
function shareNative(){
    if(navigator.share){
        navigator.share({ text: document.getElementById('shareText').value });
    }else{
        alert("Use Copy option");
    }
}

/* USERNAME */
const USERNAME = "<?= $_SESSION['username'] ?>";
</script>

</body>
</html>
