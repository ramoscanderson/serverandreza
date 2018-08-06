
<script>
//var socket = new WebSocket('ws://179.184.92.74:3396');
var socket = new WebSocket('ws://192.168.1.32:8000?token=teste');

socket.onopen = function(event){
    console.log('conectado');
}

socket.onmessage = function(event){ 
    post(event.data); 
    
    
}; 

function waitForSocketConnection(socket, callback){
    setTimeout(
        function () {
            if (socket.readyState === 1) {
                console.log("Connection is made")
                if(callback != null){
                    callback();
                }
                return;

            } else {
                console.log("wait for connection... (" + socket.readyState + ")")
                waitForSocketConnection(socket, callback);
            }

        }, 5); // espera 5 milisegundos para conecção...
}

function post(msg){ 
    //alert(""+msg+"");
    document.getElementById('texto').innerHTML += msg + "<br>";
    var obj = JSON.parse(msg);
    
    if(obj.request.method == "connection"){
        //alert("chegou");
        socket.send(msg);
    }else{
        //alert("n chegou");
    }
    
    if(obj.request.method == "testeimg"){
        alert("CHEGOU!");
        var image = obj.request.data.img;
        //var image = new Image();
        //image.src = 'data:image/png;base64,' + obj.request.data.img;
        document.body.appendChild(image);
        //document.getElementById('texto').innerHTML = "<img src='" +  + "'>" + "<br>";
        document.getElementById('texto').innerHTML = "";
    }else{
        //alert("n chegou");
    }
    
    
}

function enviar(msg){
    waitForSocketConnection(socket, function(){
        console.log("message sent!!!");
        socket.send(msg);
    });
    //alert(socket.readyState);    
}


var Base64 = {
    characters: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=" ,

    encode: function( string )
    {
        var characters = Base64.characters;
        var result     = '';

        var i = 0;
        do {
            var a = string.charCodeAt(i++);
            var b = string.charCodeAt(i++);
            var c = string.charCodeAt(i++);

            a = a ? a : 0;
            b = b ? b : 0;
            c = c ? c : 0;

            var b1 = ( a >> 2 ) & 0x3F;
            var b2 = ( ( a & 0x3 ) << 4 ) | ( ( b >> 4 ) & 0xF );
            var b3 = ( ( b & 0xF ) << 2 ) | ( ( c >> 6 ) & 0x3 );
            var b4 = c & 0x3F;

            if( ! b ) {
                b3 = b4 = 64;
            } else if( ! c ) {
                b4 = 64;
            }

            result += Base64.characters.charAt( b1 ) + Base64.characters.charAt( b2 ) + Base64.characters.charAt( b3 ) + Base64.characters.charAt( b4 );

        } while ( i < string.length );

        return result;
    } ,

    decode: function( string )
    {
        var characters = Base64.characters;
        var result     = '';

        var i = 0;
        do {
            var b1 = Base64.characters.indexOf( string.charAt(i++) );
            var b2 = Base64.characters.indexOf( string.charAt(i++) );
            var b3 = Base64.characters.indexOf( string.charAt(i++) );
            var b4 = Base64.characters.indexOf( string.charAt(i++) );

            var a = ( ( b1 & 0x3F ) << 2 ) | ( ( b2 >> 4 ) & 0x3 );
            var b = ( ( b2 & 0xF  ) << 4 ) | ( ( b3 >> 2 ) & 0xF );
            var c = ( ( b3 & 0x3  ) << 6 ) | ( b4 & 0x3F );

            result += String.fromCharCode(a) + (b?String.fromCharCode(b):'') + (c?String.fromCharCode(c):'');

        } while( i < string.length );

        return result;
    }
};

var formData = JSON.stringify(encodeURIComponent(window.btoa(document.getElementById("arq").value)));


</script>

<html> updateNews
<input type='text' id='mensagem' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6MSwiZGF0ZV9jcmVhdGUiOiIyMDE4LTAzLTA3IiwiYWRtaW4iOmZhbHNlLCJkZXZlbG9wZXIiOmZhbHNlfQ.FAN05uhrALOn0vQ_wAcz2taOnaZaYnovp9CSYPjefWQxBD0bDAQkRHACRwPvjvGXNnsg9Vh3oGj7hyl9vCAhZJt8uePQiput5namQBZmrSLazdZYB-pywownV6ZrkUsdlfbdgM4EzP9fEVVeIgyQc53evOX_47AXlHTqRdR0ruc","request":{"id":"1234","status":"200","version":"1.0.0","method":"updateScheduleByDay","client":"1","data":{"date":"2018-08-05"}}}'><button onclick="enviar(document.getElementById('mensagem').value)">updateScheduleByDay</button><br><br>
<input type='text' id='mensagem2' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJ1c2VyIjoxLCJkYXRlX2NyZWF0ZSI6IjIwMTgtMDMtMDYiLCJhZG1pbiI6ZmFsc2UsImRldmVsb3BlciI6ZmFsc2V9.aU_nqjC_5jP78PLuecOkkYSIY-i_I6jv0XvHo9J7uU6XQI-YU11UNDsFJ6eN3ZK57G5a8j8miahQQkwZTL4znHi9NaBTLjD-WomKftsmMQEYdGCH1dR4BjSFZfk5umtkGyP9WcZYo5vAJzum8HQNf8l_G3pxtISMZxTXFPd0cFM","request":{"id":"1235","status":"200","version":"1.0.0","method":"setSchedule","data":{"date":"2018-02-27", "hour":"14h00 as 15h00"}}}'><button onclick="enviar(document.getElementById('mensagem2').value)">setSchedule</button><br><br>
<input type='text' id='mensagem3' value='{"token":"Anderson","request":{"id":"1235","status":"200","version":"1.0.0","method":"cancelSchedule","data":{"id":"2", "date":"2018-02-27"}}}'><button onclick="enviar(document.getElementById('mensagem3').value)">cancelSchedule</button><br><br>
<input type='text' id='mensagem4' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6IjQiLCJkYXRlX2NyZWF0ZSI6IjIwMTgtMDMtMDciLCJhZG1pbiI6ZmFsc2UsImRldmVsb3BlciI6ZmFsc2V9.anp1W1SD521Bn3980enUkNXbBzwxga5HKtTPgGw1YjqARElwvXQFee_w5WrS10F1fNFH446BT3dI2Rr50rdPJhevDy3nVNnwiHDEd-fPqd0e1_6K_BRlK1Cmrv0eoG_WuTrHd9SWLPCzPlT7PXwBDMuytMSpVTLIDlIVDdhbIGA","request":{"id":"1235","status":"200","version":"1.0.0","method":"jwt","client":"1","data":{"img":'+ formData +', "date":"2018-02-27"}}}'><button onclick="enviar(document.getElementById('mensagem4').value)">Teste</button><br><br>
<input type='text' id='mensagem5' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6MSwiZGF0ZV9jcmVhdGUiOiIyMDE4LTAzLTA3IiwiYWRtaW4iOmZhbHNlLCJkZXZlbG9wZXIiOmZhbHNlfQ.FAN05uhrALOn0vQ_wAcz2taOnaZaYnovp9CSYPjefWQxBD0bDAQkRHACRwPvjvGXNnsg9Vh3oGj7hyl9vCAhZJt8uePQiput5namQBZmrSLazdZYB-pywownV6ZrkUsdlfbdgM4EzP9fEVVeIgyQc53evOX_47AXlHTqRdR0ruc","request":{"id":"1235","status":"200","version":"1.0.0","method":"setUser","client":"1","data":{"client":"1", "name":"Anderson Ramos", "cpf":"08433629956", "phone":"48984049673", "email":"ticion@gmail.com", "password":"12345"}}}'><button onclick="enviar(document.getElementById('mensagem5').value)">setUser</button><br><br>
<input type='text' id='mensagem6' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6MSwiZGF0ZV9jcmVhdGUiOiIyMDE4LTAzLTA3IiwiYWRtaW4iOmZhbHNlLCJkZXZlbG9wZXIiOmZhbHNlfQ.FAN05uhrALOn0vQ_wAcz2taOnaZaYnovp9CSYPjefWQxBD0bDAQkRHACRwPvjvGXNnsg9Vh3oGj7hyl9vCAhZJt8uePQiput5namQBZmrSLazdZYB-pywownV6ZrkUsdlfbdgM4EzP9fEVVeIgyQc53evOX_47AXlHTqRdR0ruc","request":{"id":"1235","status":"200","version":"1.0.0","method":"signin","data":{"user":"08433629956", "password":"12345"}}}'><button onclick="enviar(document.getElementById('mensagem6').value)">signin</button><br><br>
<input type='text' id='mensagem7' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6IjQiLCJkYXRlX2NyZWF0ZSI6IjIwMTgtMDMtMDciLCJhZG1pbiI6ZmFsc2UsImRldmVsb3BlciI6ZmFsc2V9.anp1W1SD521Bn3980enUkNXbBzwxga5HKtTPgGw1YjqARElwvXQFee_w5WrS10F1fNFH446BT3dI2Rr50rdPJhevDy3nVNnwiHDEd-fPqd0e1_6K_BRlK1Cmrv0eoG_WuTrHd9SWLPCzPlT7PXwBDMuytMSpVTLIDlIVDdhbIGA","request":{"id":"1235","status":"200","version":"1.0.0","method":"signin","data":{}}}'><button onclick="enviar(document.getElementById('mensagem7').value)">signinTOKEN</button><br><br>
<input type='text' id='mensagem8' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6IjQiLCJkYXRlX2NyZWF0ZSI6IjIwMTgtMDMtMDciLCJhZG1pbiI6ZmFsc2UsImRldmVsb3BlciI6ZmFsc2V9.anp1W1SD521Bn3980enUkNXbBzwxga5HKtTPgGw1YjqARElwvXQFee_w5WrS10F1fNFH446BT3dI2Rr50rdPJhevDy3nVNnwiHDEd-fPqd0e1_6K_BRlK1Cmrv0eoG_WuTrHd9SWLPCzPlT7PXwBDMuytMSpVTLIDlIVDdhbIGA","request":{"id":"1235","status":"200","version":"1.0.0","method":"updateNews","client":"1","data":{"id":"2", "date":"2018-02-27"}}}'><button onclick="enviar(document.getElementById('mensagem8').value)">updateNews</button><br><br>
<input type='text' id='mensagem9' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6IjQiLCJkYXRlX2NyZWF0ZSI6IjIwMTgtMDMtMDciLCJhZG1pbiI6ZmFsc2UsImRldmVsb3BlciI6ZmFsc2V9.anp1W1SD521Bn3980enUkNXbBzwxga5HKtTPgGw1YjqARElwvXQFee_w5WrS10F1fNFH446BT3dI2Rr50rdPJhevDy3nVNnwiHDEd-fPqd0e1_6K_BRlK1Cmrv0eoG_WuTrHd9SWLPCzPlT7PXwBDMuytMSpVTLIDlIVDdhbIGA","request":{"id":"1235","status":"200","version":"1.0.0","method":"updateCategoriesNews","client":"1","data":{"id":"2", "date":"2018-02-27"}}}'><button onclick="enviar(document.getElementById('mensagem9').value)">updateCategoriesNews</button><br><br>
<input type='text' id='mensagem10' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6IjQiLCJkYXRlX2NyZWF0ZSI6IjIwMTgtMDMtMDciLCJhZG1pbiI6ZmFsc2UsImRldmVsb3BlciI6ZmFsc2V9.anp1W1SD521Bn3980enUkNXbBzwxga5HKtTPgGw1YjqARElwvXQFee_w5WrS10F1fNFH446BT3dI2Rr50rdPJhevDy3nVNnwiHDEd-fPqd0e1_6K_BRlK1Cmrv0eoG_WuTrHd9SWLPCzPlT7PXwBDMuytMSpVTLIDlIVDdhbIGA","request":{"id":"1235","status":"200","version":"1.0.0","method":"updateMySchedules","client":"1","data":{"id":"2", "date":"2018-02-27"}}}'><button onclick="enviar(document.getElementById('mensagem10').value)">updateMySchedules</button><br><br>
<input type='text' id='mensagem11' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6IjQiLCJkYXRlX2NyZWF0ZSI6IjIwMTgtMDMtMDciLCJhZG1pbiI6ZmFsc2UsImRldmVsb3BlciI6ZmFsc2V9.anp1W1SD521Bn3980enUkNXbBzwxga5HKtTPgGw1YjqARElwvXQFee_w5WrS10F1fNFH446BT3dI2Rr50rdPJhevDy3nVNnwiHDEd-fPqd0e1_6K_BRlK1Cmrv0eoG_WuTrHd9SWLPCzPlT7PXwBDMuytMSpVTLIDlIVDdhbIGA","request":{"id":"1235","status":"200","version":"1.0.0","method":"scheduleFollowUp","client":"1","data":{"id":"2", "date":"2018-02-27"}}}'><button onclick="enviar(document.getElementById('mensagem11').value)">scheduleFollowUp</button><br><br>
<input type='text' id='mensagem12' value='CONNECT'><button onclick="new WebSocket('ws://192.168.1.32:8000');">NEW CONNECTION</button><br><br>
<div id='texto'></div>



<form id="formulario" method="post" enctype="#">
    <input type="text" name="campo1" value="hello" />
    <input type="text" name="campo2" value="world" />
    <input name="arquivo" id="arq" type="file" />
    <button>Enviar</button>
</form>

</html>