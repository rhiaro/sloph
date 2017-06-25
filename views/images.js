  var photos = document.querySelectorAll('article img');

  function changeImg(ele, x, y, imgs){
    
    var w = ele.offsetWidth;
    var par = ele.parentNode;
    if(par.className == "imgholder"){
      ele = par;
      par = par.parentNode;
    }
    current = ele.querySelector('img').src;
    counter = ele.querySelector('p');

    j = 0;
    for(var i=0; i<imgs.length; i=i+1){
      if(current == imgs[i].src){
        j = i;
      }
    }

    if(y < 64){
      par.removeChild(ele);
    }else if(w/2 > x){
      if(imgs[j-1] != undefined){
        ele.querySelector('img').src = imgs[j-1].src;
        counter.innerText = j+"/"+imgs.length;
      }else{
        ele.querySelector('img').src = imgs[imgs.length-1].src;
        counter.innerText = imgs.length+"/"+imgs.length;
      }
    }else{
      if(imgs[j+1] != undefined){
        ele.querySelector('img').src = imgs[j+1].src;
        counter.innerText = j+2+"/"+imgs.length;
      }else{
        ele.querySelector('img').src = imgs[0].src;
        counter.innerText = "1/"+imgs.length;
      }
    }
  }

  photos.forEach(function(p, i, photos){
    p.addEventListener('click', function(e){
      var article = document.querySelector('article');
      article.insertAdjacentHTML('afterbegin', '<div class="imgholder"><img src="'+p.src+'" /><p>'+(i+1)+'/'+photos.length+'</p></div>');

      var holder = document.querySelector('div.imgholder');
      holder.addEventListener('click', function(e){
        changeImg(e.target, e.pageX, e.pageY, photos);
      });

    });

  });

  document.addEventListener('keyup', function(e){
    var holder = document.querySelector('div.imgholder');
    if(holder != null){
      if(e.keyCode == 27){
        changeImg(holder, 0, 0, photos);
      }else if(e.keyCode == 39){
        changeImg(holder, holder.offsetWidth, 65, photos);
      }else if(e.keyCode == 37){
        changeImg(holder, 2, 65, photos);
      }
    }
  });
  document.addEventListener('click', function(e){
    var holder = document.querySelector('div.imgholder');
    if(holder != null){
      if(e.target != holder && e.target.parentNode != holder){
        changeImg(holder, 0, 0, photos);
      }
    }
  }, true);