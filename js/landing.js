function getTime(value) {
  var dateSplit = value.split(/[^0-9]/);
  var wareHouseEndDate=new Date (dateSplit[0],dateSplit[1]-1,dateSplit[2],dateSplit[3],dateSplit[4],dateSplit[5] );
  if (Object.prototype.toString.call(wareHouseEndDate) === "[object Date]") {
    // it is a date
    if (isNaN(wareHouseEndDate.getTime())) { // wareHouseEndDate.valueOf() could also work
      // date is not valid
      console.log(value);
    } else {
      // date is valid
      var calcNewYear = setInterval(function() {
        date_now = new Date();

        seconds = Math.floor((wareHouseEndDate - (date_now)) / 1000);
        minutes = Math.floor(seconds / 60);
        hours = Math.floor(minutes / 60);
        days = Math.floor(hours / 24);

        hours = hours - (days * 24);
        minutes = minutes - (days * 24 * 60) - (hours * 60);
        seconds = seconds - (days * 24 * 60 * 60) - (hours * 60 * 60) - (minutes * 60);

        $("#remaining-time").text(days + "d:" + hours + "h:" + minutes + "m");
      }, 1000);
    }
  } else {
    // not a date
  }
}

var changeVideo = function(){
  var id = $(this).attr('data-video-id');
  var video = getVideoByID(id);
  $('#video').empty().html(video.embed);
  $('#caption').html(video.description);
  $('#videos').find('li').removeClass('currentlyPlaying');
  $(this).addClass('currentlyPlaying');
  $.post(
	  ajax_url,
      {
          'action': 'video_progress',
          'data':   {videoID:id}
      },
      function(response){
          return;
      }
  );
  $('.helpVideoList').animate({scrollTop:0}, 300);
};

var getVideoByID = function(id){
  for( var i = 0; i < videos.length; i++){
  	if( videos[i].id == id ){
  	  return videos[i];
    }
  }
};

var populateVideoList = function(){
  console.log(videos);
  var $videos = $('#videos');
  for( var i = 0; i < videos.length; i++){
    $video = '<li data-video-id="'+videos[i].id+'">'+videos[i].title+'<span>'+videos[i].duration+'</span></li>';
    $videos.append($video);
  }
  setTimeout(function(){ //cause why not?
      if( lastVideo ){
		  $videos.find('li[data-video-id='+lastVideo+']').trigger('click');
      } else {
		  $videos.find('li').eq(0).trigger('click');
      }
  }, 100);
};

(function($){
	$(function(){
		populateVideoList();
		$('#videos').on('click','li',changeVideo);
	});
})(jQuery);


var currentlyPlayingID = null;

function setDisplayVideo(video, caption, id) {
  document.getElementById("video").innerHTML = video;
  document.getElementById("caption").innerHTML = caption;
  if (id !== undefined) {
    if (currentlyPlayingID == null) {
      currentlyPlayingID = id;
      document.getElementById(0).classList.remove('currentlyPlaying');
      document.getElementById(id).classList.add('currentlyPlaying');

    } else {
      document.getElementById(currentlyPlayingID).classList.remove('currentlyPlaying');
      currentlyPlayingID = id;
      document.getElementById(id).classList.add('currentlyPlaying');
    }
  }


}
