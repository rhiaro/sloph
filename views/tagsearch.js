var ul = document.getElementById("tagslist");
var li = document.getElementsByTagName("li");
var search = document.getElementById("tagsearch");

search.addEventListener('keyup', function(event){
	var filter = search.value.toLowerCase();
	for (i = 0; i < li.length; i++) {
		a = li[i].getElementsByTagName("a")[0];
		if(a.innerHTML.toLowerCase().includes(filter)){
	    	li[i].style.display = "";
		} else {
	    	li[i].style.display = "none";
	    }
	 }
});
