  var photos = document.querySelectorAll('article img');
  if (typeof proxyUrl === 'undefined') {
    var proxyUrl = null; 
  }

  var imgFragment = window.location.hash.split("#")[1] || "";

  function imageProxy(imgUrl, proxyUrl, width){
    // Sometimes the image clicked is a proxied thumbnail.
    // But we want the larger size to display.
    // Assume URL format is proxyUrl/width/height/image
    if(proxyUrl === null){
      // If no proxy is known, nothing I can do.
      return imgUrl;
    }
    // Disassemble URL into proxy, dimensions and image.
    var urlParts = imgUrl.split(proxyUrl);
    if(urlParts.length > 1){
      var sizeAndImg = urlParts[1];
      var params = sizeAndImg.split('/http');
      var sizeParams = params[0].split('/');
      var actualImgUrl = 'http'+params[1]
      var oldWidth = sizeParams[0];
      var oldHeight = sizeParams[1];
    }else{
      // This image wasn't actually proxied.
      var actualImgUrl = imgUrl;
      var oldHeight = 0;
    }
    if(width === 0){
      // Use original image size without proxy.
      return actualImgUrl;
    }else{
      // Reuse the proxy with the new width.
      return proxyUrl + width + '/' + oldHeight + '/' + actualImgUrl;
    }
  }

  function stripProxy(imgUrl){
    var parts = imgUrl.split('/http');
    if(typeof parts[1] !== 'undefined'){
      return 'http' + parts[1];
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
    
    var nextImg = findImgNo(current, imgs);

    if(y < Math.abs(ele.offsetTop)+64){
      par.removeChild(ele);
      window.location.hash = "";
    }else if(w/2 > x){
      if(imgs[nextImg-1] != undefined){
        var newImg = imgs[nextImg-1].src;
        var counterText = nextImg;
      }else{
        var newImg = imgs[imgs.length-1].src;
        var counterText = imgs.length;
      }

      ele.querySelector('img').src = imageProxy(newImg, proxyUrl, width);
      counter.innerText = counterText+"/"+imgs.length;
      window.location.hash = stripProxy(newImg);

    }else{
      if(imgs[nextImg+1] != undefined){
        var newImg = imgs[nextImg+1].src;
        var counterText = nextImg+2;
      }else{
        var newImg = imgs[0].src;
        var counterText = '1';
      }

      ele.querySelector('img').src = imageProxy(newImg, proxyUrl, width);
      counter.innerText = counterText+"/"+imgs.length;
      window.location.hash = stripProxy(newImg);
      
    }

  }

  function openImg(i, imgUrl, photos, proxyUrl, bigWidth){
    if(i == null){
      i = findImgNo(imgUrl, photos);  
    }
    if(i == null){
      window.location.hash = "";
      console.log("Image does not exist on this page [" + imgUrl + "]");
      return;
    }
    
    var article = document.querySelector('article');
    var imgUrl = imageProxy(imgUrl, proxyUrl, bigWidth);

    article.insertAdjacentHTML('afterbegin', '<div class="imgholder"><img src="'+imgUrl+'" /><p>'+(i+1)+'/'+photos.length+'</p></div>');

    var holder = document.querySelector('div.imgholder');
    holder.style.top = -holder.getBoundingClientRect().top+'px';
    
    holder.addEventListener('click', function(e){
      changeImg(e.target, e.pageX, e.pageY, photos, proxyUrl, bigWidth);
    });
  }

  function findImgNo(imgUrl, photoEles){
    for(var i=0; i<photoEles.length; i=i+1){
      if(stripProxy(imgUrl) == stripProxy(photoEles[i].src)){
        return i;
      }
    }
    return null;
  }

  var bigWidth = 1024;

  photos.forEach(function(p, i, photos){
    p.addEventListener('click', function(e){
      e.preventDefault();
      var imgUrl = imageProxy(p.src, proxyUrl, bigWidth);
      openImg(i, imgUrl, photos, proxyUrl, bigWidth);
      window.location.hash = stripProxy(imgUrl);
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

  if(imgFragment !== ""){
    openImg(null, imgFragment, photos, proxyUrl, bigWidth);
  }