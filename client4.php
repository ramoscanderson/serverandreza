
<script>
//var socket = new WebSocket('ws://179.184.92.74:3396');
var socket = new WebSocket('ws://192.168.1.32:8000');

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
    
    
}

function enviar(msg){
    waitForSocketConnection(socket, function(){
        console.log("message sent!!!");
        socket.send(msg);
    });
    //alert(socket.readyState);    
}



</script>

<html>
<input type='text' id='mensagem' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6MSwiZGF0ZV9jcmVhdGUiOiIyMDE4LTAzLTA3IiwiYWRtaW4iOmZhbHNlLCJkZXZlbG9wZXIiOmZhbHNlfQ.FAN05uhrALOn0vQ_wAcz2taOnaZaYnovp9CSYPjefWQxBD0bDAQkRHACRwPvjvGXNnsg9Vh3oGj7hyl9vCAhZJt8uePQiput5namQBZmrSLazdZYB-pywownV6ZrkUsdlfbdgM4EzP9fEVVeIgyQc53evOX_47AXlHTqRdR0ruc","request":{"id":"1234","status":"200","version":"1.0.0","method":"updateScheduleByDay","data":{"date":"2018-02-27"}}}'><button onclick="enviar(document.getElementById('mensagem').value)">updateScheduleByDay</button><br><br>
<input type='text' id='mensagem2' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJ1c2VyIjoxLCJkYXRlX2NyZWF0ZSI6IjIwMTgtMDMtMDYiLCJhZG1pbiI6ZmFsc2UsImRldmVsb3BlciI6ZmFsc2V9.aU_nqjC_5jP78PLuecOkkYSIY-i_I6jv0XvHo9J7uU6XQI-YU11UNDsFJ6eN3ZK57G5a8j8miahQQkwZTL4znHi9NaBTLjD-WomKftsmMQEYdGCH1dR4BjSFZfk5umtkGyP9WcZYo5vAJzum8HQNf8l_G3pxtISMZxTXFPd0cFM","request":{"id":"1235","status":"200","version":"1.0.0","method":"setSchedule","data":{"date":"2018-02-27", "hour":"14h00 as 15h00"}}}'><button onclick="enviar(document.getElementById('mensagem2').value)">setSchedule</button><br><br>
<input type='text' id='mensagem3' value='{"token":"Anderson","request":{"id":"1235","status":"200","version":"1.0.0","method":"cancelSchedule","data":{"id":"2", "date":"2018-02-27"}}}'><button onclick="enviar(document.getElementById('mensagem3').value)">cancelSchedule</button><br><br>
<input type='text' id='mensagem4' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6IjQiLCJkYXRlX2NyZWF0ZSI6IjIwMTgtMDMtMDciLCJhZG1pbiI6ZmFsc2UsImRldmVsb3BlciI6ZmFsc2V9.anp1W1SD521Bn3980enUkNXbBzwxga5HKtTPgGw1YjqARElwvXQFee_w5WrS10F1fNFH446BT3dI2Rr50rdPJhevDy3nVNnwiHDEd-fPqd0e1_6K_BRlK1Cmrv0eoG_WuTrHd9SWLPCzPlT7PXwBDMuytMSpVTLIDlIVDdhbIGA","request":{"id":"1235","status":"200","version":"1.0.0","method":"jwt","client":"1","data":{"id":"2", "date":"2018-02-27"}}}'><button onclick="enviar(document.getElementById('mensagem4').value)">Teste</button><br><br>
<input type='text' id='mensagem5' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6MSwiZGF0ZV9jcmVhdGUiOiIyMDE4LTAzLTA3IiwiYWRtaW4iOmZhbHNlLCJkZXZlbG9wZXIiOmZhbHNlfQ.FAN05uhrALOn0vQ_wAcz2taOnaZaYnovp9CSYPjefWQxBD0bDAQkRHACRwPvjvGXNnsg9Vh3oGj7hyl9vCAhZJt8uePQiput5namQBZmrSLazdZYB-pywownV6ZrkUsdlfbdgM4EzP9fEVVeIgyQc53evOX_47AXlHTqRdR0ruc","request":{"id":"1235","status":"200","version":"1.0.0","method":"setUser","data":{"client":"1", "name":"Anderson Ramos", "cpf":"11111111111", "phone":"48984049673", "email":"ticion@gmail.com", "password":"12345"}}}'><button onclick="enviar(document.getElementById('mensagem5').value)">setUser</button><br><br>
<input type='text' id='mensagem6' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6MSwiZGF0ZV9jcmVhdGUiOiIyMDE4LTAzLTA3IiwiYWRtaW4iOmZhbHNlLCJkZXZlbG9wZXIiOmZhbHNlfQ.FAN05uhrALOn0vQ_wAcz2taOnaZaYnovp9CSYPjefWQxBD0bDAQkRHACRwPvjvGXNnsg9Vh3oGj7hyl9vCAhZJt8uePQiput5namQBZmrSLazdZYB-pywownV6ZrkUsdlfbdgM4EzP9fEVVeIgyQc53evOX_47AXlHTqRdR0ruc","request":{"id":"1235","status":"200","version":"1.0.0","method":"signin","data":{"user":"08433629956", "password":"12345"}}}'><button onclick="enviar(document.getElementById('mensagem6').value)">signin</button><br><br>
<input type='text' id='mensagem7' value='{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6IjQiLCJkYXRlX2NyZWF0ZSI6IjIwMTgtMDMtMDciLCJhZG1pbiI6ZmFsc2UsImRldmVsb3BlciI6ZmFsc2V9.anp1W1SD521Bn3980enUkNXbBzwxga5HKtTPgGw1YjqARElwvXQFee_w5WrS10F1fNFH446BT3dI2Rr50rdPJhevDy3nVNnwiHDEd-fPqd0e1_6K_BRlK1Cmrv0eoG_WuTrHd9SWLPCzPlT7PXwBDMuytMSpVTLIDlIVDdhbIGA","request":{"id":"1235","status":"200","version":"1.0.0","method":"signin","data":{}}}'><button onclick="enviar(document.getElementById('mensagem7').value)">signinTOKEN</button><br><br>
<div id='texto'></div>
</html>