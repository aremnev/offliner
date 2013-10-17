  function getXmlHttp() {
    var xmlhttp;
    try {
      xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
    try {
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (E) {
      xmlhttp = false;
    }
    }
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
      xmlhttp = new XMLHttpRequest();
    }
    return xmlhttp;
  }
  

function signinCallback(authResult) {
	  if (authResult['access_token']) {
			gapi.auth.setToken(authResult); // Сохраняет возвращенный токен.
			var name;
			var email;
			gapi.client.load('plus','v1', function(){
				var request = gapi.client.plus.people.get({
					'userId': 'me'
				});
				request.execute(function(resp) {
					name = resp.displayName;
					gapi.client.load('oauth2', 'v2', function() {
						var request = gapi.client.oauth2.userinfo.get();
						request.execute(function getInfo(obj){
							email = obj['email'];
							xmlhttp = getXmlHttp();
							    var xmlhttp = getXmlHttp(); // Создаём объект XMLHTTP
								xmlhttp.open('POST', 'login/'+encodeURIComponent(name)+'/'+encodeURIComponent(email), true); // Открываем асинхронное соединение
								xmlhttp.send(); // Отправляем POST-запрос
								xmlhttp.onreadystatechange = function() { // Ждём ответа от сервера
								  if (xmlhttp.readyState == 4) { // Ответ пришёл
									if(xmlhttp.status == 200) { // Сервер вернул код 200 (что хорошо)
									  console.log("successfull login "); // Выводим ответ сервера
									  window.location.replace('goList');
									}else{
									  console.log("login troubles");
									}
								  }
								};
						});
					});
				});
			});

			document.getElementById('signinButton').setAttribute('style', 'display: none');
	  }
}
        (function() {
            var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
            po.src = 'https://apis.google.com/js/client:plusone.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
        })();
