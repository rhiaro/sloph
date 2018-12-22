  var photos = document.querySelectorAll('article img');
  if (typeof proxyUrl === 'undefined') {
    var proxyUrl = null; 
  }

  function imageProxy(imgUrl, proxyUrl, width){
    // Sometimes the image clicked is a proxied thumbnail.
    // But we want the larger size to display.
    if(proxyUrl === null){
      return imgUrl;
    }
    var urlParts = imgUrl.split(proxyUrl);
    var sizeAndImg = urlParts[1];
    var params = sizeAndImg.split('/http');
    var sizeParams = params[0].split('/');
    var actualImgUrl = 'http'+params[1]
    var oldWidth = sizeParams[0];
    var oldHeight = sizeParams[1];
    if(width === 0){
      // Use original image size
      return actualImgUrl;
    }else{
      // Reuse the proxy with the new width
      return proxyUrl + width + '/' + oldHeight + '/' + actualImgUrl;
    }
  }

  function stripProxy(imgUrl){
    var parts = imgUrl.split('/http');
    if(typeof parts[1] !== 'undefined'){
      return parts[1];
    }else{
      return imgUrl;
    }
  }

  function changeImg(ele, x, y, imgs, proxyUrl, width){
    
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
      if(stripProxy(current) == stripProxy(imgs[i].src)){
        j = i;
      }
    }

    if(y < Math.abs(ele.offsetTop)+64){
      console.log(y);
      console.log(Math.abs(ele.offsetTop));
      par.removeChild(ele);
    }else if(w/2 > x){
      if(imgs[j-1] != undefined){
        ele.querySelector('img').src = imageProxy(imgs[j-1].src, proxyUrl, width);
        counter.innerText = j+"/"+imgs.length;
      }else{
        ele.querySelector('img').src = imageProxy(imgs[imgs.length-1].src, proxyUrl, width);
        counter.innerText = imgs.length+"/"+imgs.length;
      }
    }else{
      if(imgs[j+1] != undefined){
        ele.querySelector('img').src = imageProxy(imgs[j+1].src, proxyUrl, width);
        counter.innerText = j+2+"/"+imgs.length;
      }else{
        ele.querySelector('img').src = imageProxy(imgs[0].src, proxyUrl, width);
        counter.innerText = "1/"+imgs.length;
      }
    }
  }

  var bigWidth = 1024;

  photos.forEach(function(p, i, photos){
    p.addEventListener('click', function(e){
      e.preventDefault();
      var article = document.querySelector('article');
      var imgUrl = imageProxy(p.src, proxyUrl, bigWidth);
      article.insertAdjacentHTML('afterbegin', '<div class="imgholder"><img src="'+imgUrl+'" /><p>'+(i+1)+'/'+photos.length+'</p></div>');

      var holder = document.querySelector('div.imgholder');
      holder.style.top = -holder.getBoundingClientRect().top+'px';
      holder.addEventListener('click', function(e){
        changeImg(e.target, e.pageX, e.pageY, photos, proxyUrl, bigWidth);
      });

    });

  });

  document.addEventListener('keyup', function(e){
    var holder = document.querySelector('div.imgholder');
    if(holder != null){
      if(e.keyCode == 27){
        changeImg(holder, 0, 0, photos, proxyUrl, bigWidth);
      }else if(e.keyCode == 39){
        changeImg(holder, holder.offsetWidth, holder.offsetHeight + holder.offsetTop, photos, proxyUrl, bigWidth);
      }else if(e.keyCode == 37){
        changeImg(holder, 2, holder.offsetHeight + holder.offsetTop, photos, proxyUrl, bigWidth);
      }
    }
  });
  document.addEventListener('click', function(e){
    var holder = document.querySelector('div.imgholder');
    if(holder != null){
      if(e.target != holder && e.target.parentNode != holder){
        changeImg(holder, 0, 0, photos, proxyUrl, bigWidth);
      }
    }
  }, true);