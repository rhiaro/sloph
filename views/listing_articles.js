
  var yearSelect = document.getElementById("yfilter");
  var monthSelect = document.getElementById("mfilter");
  var years = document.querySelectorAll("main div");
  var months = document.querySelectorAll("main ul");
  var sub = document.querySelector("input[type='submit']");
  sub.parentNode.removeChild(sub);

  yearSelect.addEventListener("change", function(e){
    years.forEach(function(ydiv){
      if(ydiv.id != e.target.value && e.target.value != "0"){
        ydiv.style.display = "none";
      }else{
        ydiv.style.display = "block";
      }
    });
  });

  monthSelect.addEventListener("change", function(e){
    months.forEach(function(mdiv){
      if(mdiv.id != e.target.value && e.target.value != "0"){
        mdiv.style.display = "none";
        mdiv.previousElementSibling.style.display = "none";
      }else{
        mdiv.style.display = "block";
        mdiv.previousElementSibling.style.display = "block";
      }
    });
  });