
setTimeout(function(){
  var id = getParameterByName('student_id');
  var d = document.getElementById(id);
  d.className += "selectedStudent";
}, 150);

function getParameterByName(name, url) {
    if (!url) {
      url = window.location.href;
    }
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function studentSearch() {
  var studentElements = document.getElementById('students');
  var searchQuery = $("#search").val();
  var possible = [];
  
  for (var i = 0; i < studentElements.children.length; i++) {
    if (studentElements.children[i] !== undefined) {
      var sliced = studentElements.children[i].innerText.slice(0, searchQuery.length);
      if(_.includes(studentElements.children[i].innerText.toLowerCase(), searchQuery.toLowerCase())){
        possible.push(i);
      }
    }
  }

  for (var i = 0; i < studentElements.children.length; i++) {
    if (studentElements.children[i] !== undefined) {
      if(possible.indexOf(i) == -1){
        studentElements.children[i].hidden = true;
      }
      else{
        studentElements.children[i].hidden = false;
      }
    }
  }
}
