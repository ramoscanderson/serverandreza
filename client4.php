
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
    document.getElementById('texto').innerHTML += msg;
    
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
<input type='text' id='mensagem'><button onclick="enviar(document.getElementById('mensagem').value)">Enviar</button>
<div id='texto'></div>
</html>