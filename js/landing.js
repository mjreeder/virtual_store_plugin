function getTime(value){
  var wareHouseEndDate = new Date(Date.parse(value));
  var calcNewYear = setInterval(function(){
       date_now = new Date();

       seconds = Math.floor((wareHouseEndDate - (date_now))/1000);
       minutes = Math.floor(seconds/60);
       hours = Math.floor(minutes/60);
       days = Math.floor(hours/24);

       hours = hours-(days*24);
       minutes = minutes-(days*24*60)-(hours*60);
       seconds = seconds-(days*24*60*60)-(hours*60*60)-(minutes*60);

       $("#remaining-time").text(days + ":" + hours + ":" + minutes + ":" + seconds);
   },1000);
}
var currentlyPlayingID = null;
function setDisplayVideo(video, caption, id){
  document.getElementById("video").innerHTML = video;
  document.getElementById("caption").innerHTML = caption;
  if(id !== undefined){
    if(currentlyPlayingID == null ){
      currentlyPlayingID = id;
      document.getElementById(0).classList.remove('currentlyPlaying');
      document.getElementById(id).classList.add('currentlyPlaying');

    }
    else{
      document.getElementById(currentlyPlayingID).classList.remove('currentlyPlaying');
      currentlyPlayingID = id;
      document.getElementById(id).classList.add('currentlyPlaying');
    }
  }


}
