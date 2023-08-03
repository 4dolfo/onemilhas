angular.module('app').run(['$rootScope', '$location', '$window', function($rootScope, $location, $window) {

  $rootScope
      .$on('$locationChangeStart',
            function(event, next, current){
 
                if (!$window.ga) return;
                if( ($location.path() == "") || ($location.path() == "/")  || ($location.path() == "/ ") ) return;

                //send page analytics
                $window.ga('send', 'pageview', { page: $location.path() });
        });

  $rootScope.ValidaCPF = function(cpf) {
      var numeros, digitos, soma, i, resultado, digitos_iguais;
      digitos_iguais = 1;
      if (cpf.length < 11)
            return false;
      for (i = 0; i < cpf.length - 1; i++)
            if (cpf.charAt(i) != cpf.charAt(i + 1))
                  {
                  digitos_iguais = 0;
                  break;
                  }
      if (!digitos_iguais)
            {
            numeros = cpf.substring(0,9);
            digitos = cpf.substring(9);
            soma = 0;
            for (i = 10; i > 1; i--)
                  soma += numeros.charAt(10 - i) * i;
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(0))
                  return false;
            numeros = cpf.substring(0,10);
            soma = 0;
            for (i = 11; i > 1; i--)
                  soma += numeros.charAt(11 - i) * i;
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(1))
                  return false;
            return true;
            }
      else
          return false;
  };

  $rootScope.ValidaCNPJ = function(cnpj) {
      cnpj = cnpj.replace(/[^\d]+/g,'');
   
      if(cnpj == '') return false;
       
      if (cnpj.length != 14)
          return false;
   
      // Elimina CNPJs invalidos conhecidos
      if (cnpj == "00000000000000" || 
          cnpj == "11111111111111" || 
          cnpj == "22222222222222" || 
          cnpj == "33333333333333" || 
          cnpj == "44444444444444" || 
          cnpj == "55555555555555" || 
          cnpj == "66666666666666" || 
          cnpj == "77777777777777" || 
          cnpj == "88888888888888" || 
          cnpj == "99999999999999")
          return false;
           
      // Valida DVs
      tamanho = cnpj.length - 2
      numeros = cnpj.substring(0,tamanho);
      digitos = cnpj.substring(tamanho);
      soma = 0;
      pos = tamanho - 7;
      for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2)
              pos = 9;
      }
      resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
      if (resultado != digitos.charAt(0))
          return false;
           
      tamanho = tamanho + 1;
      numeros = cnpj.substring(0,tamanho);
      soma = 0;
      pos = tamanho - 7;
      for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2)
              pos = 9;
      }
      resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
      if (resultado != digitos.charAt(1))
            return false;
             
      return true;
  };

  $rootScope.formatNumber = function(number, decimalsLength, decimalSeparator, thousandSeparator) {
     var n = number,
         decimalsLength = isNaN(decimalsLength = Math.abs(decimalsLength)) ? 2 : decimalsLength,
         decimalSeparator = decimalSeparator == undefined ? "," : decimalSeparator,
         thousandSeparator = thousandSeparator == undefined ? "." : thousandSeparator,
         sign = n < 0 ? "-" : "",
         i = parseInt(n = Math.abs(+n || 0).toFixed(decimalsLength)) + "",
         j = (j = i.length) > 3 ? j % 3 : 0;

     return sign +
         (j ? i.substr(0, j) + thousandSeparator : "") +
         i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousandSeparator) +
         (decimalsLength ? decimalSeparator + Math.abs(n - i).toFixed(decimalsLength).slice(2) : "");
  }    

  $rootScope.formatServerDate = function(date, format) {
    if (!(date == undefined)) {
      if (format=='dd/mm/yyyy') {
        return $rootScope.pad(date.getDate()) + "/" + $rootScope.pad((date.getMonth()+1)) + "/" + date.getFullYear();
      }
      return date.getFullYear() + "-" + $rootScope.pad((date.getMonth()+1)) + "-" + $rootScope.pad(date.getDate());
    }
  }

  $rootScope.formatServerDateTime = function(date, format) {
      return date.getFullYear() + "-" + $rootScope.pad((date.getMonth()+1)) + "-" + $rootScope.pad(date.getDate()) + " " +
      $rootScope.pad(date.getHours()) + ":" + $rootScope.pad(date.getMinutes()) + ":" + $rootScope.pad(date.getSeconds());
  }

  $rootScope.formatClientDate = function(time) {
    var r = time.match(/^\s*([0-9]+)\s*-\s*([0-9]+)\s*-\s*([0-9]+)(.*)$/);
    return r[3]+"/"+r[2]+"/"+r[1]+r[4];
  }  

  $rootScope.findDate = function(date) {
    if(date != '' && date != undefined && typeof date == 'string') {
      var t = date.split(/[- :]/);

      // Apply each element to the Date function
      var d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
      var actiondate = new Date(d);
      return actiondate;
    }
    return '';
  }

  $rootScope.pad = function(n, width, z) {
    if (width == undefined) {
      width = 2;
    }
    z = z || '0';
    n = n + '';
    return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
  } 

  $rootScope.setOnlineOrder = function(row) {
    $rootScope.onlineOrder = row;
  } 

  $rootScope.setOnlineFlights = function(rows) {
    $rootScope.onlineFlight = rows;
  } 

  $rootScope.pushNotificationDesktop = function(title, body, voice) {
    var options = {
      body: body,
      icon: 'images/gsrmlogo.jpg'
    }

    if('speechSynthesis' in window) {
      var msg = new SpeechSynthesisUtterance();
      msg.voiceURI = 'native';
      msg.volume = 1;
      msg.lang = 'pt-BR';
    }

    // check browser suport
    if ("Notification" in window) {

      // permission granted
      if (Notification.permission === "granted") {

        // push notification

        if(voice) {
          if(msg) {
            msg.text = voice;
            speechSynthesis.speak(msg);
          }
        }

        // var audio = new Audio('sounds/notification.mp3');
        var notification = new Notification(title, options);
        // audio.play();
        setTimeout(function() { notification.close() }, 7000);
      }
    }
  };

  $rootScope.pushNotification = function(note) {
    $rootScope.notifications.push(note);
  };

  $rootScope.pushNotificationInfo = function(note) {
    $rootScope.notificationsInfo.push(note);
  };

  $rootScope.connectToSocket = function(hash) {

    $rootScope.socket = io('http://18.205.131.149', { query: {
      hashId: hash
    }});

    $rootScope.socket.on('connect', function(){});

    $rootScope.socket.on('disconnect', function(){
      $rootScope.pushNotificationDesktop('Notificações', 'Perca de Comunicação com servidor!');
    });

    $rootScope.socket.on('event', function(data){
      $rootScope.pushNotification(data.message);
      $rootScope.pushNotificationDesktop('Notificações', data.message, data.voice);
    });

    $rootScope.socket.on('status-analysis', function(data){
      $rootScope.clientStatus = data.dataset;
      $rootScope.$digest();
    });

    $rootScope.socket.on('status-order-waiting', function(data){
      $rootScope.orderWaiting = data.dataset;
      $rootScope.$digest();
    });

    $rootScope.socket.on('wellcome', function(data){
      $rootScope.pushNotificationDesktop('Notificações', 'Notificações SRM Ativadas!');
    });
  };

  $rootScope.validEmails = function(emails) {
    if(!emails) {
      return true;
    }
    var arrayEmails = emails.split(';');
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i
    if(arrayEmails.length > 1) {
      for(var i in arrayEmails) {
        if(!re.test(arrayEmails[i])) {
          return false;
        }
      }
    } else {
      if(!re.test(emails)) {
        return false;
      }
    }
    return true;
  };

  $rootScope.mobilecheck = function() {
    if( navigator.userAgent.match(/Android/i)
      || navigator.userAgent.match(/webOS/i)
      || navigator.userAgent.match(/iPhone/i)
      || navigator.userAgent.match(/iPad/i)
      || navigator.userAgent.match(/iPod/i)
      || navigator.userAgent.match(/BlackBerry/i)
      || navigator.userAgent.match(/Windows Phone/i)
      ){
      return true;
    }
    return false;
  }

}]);

