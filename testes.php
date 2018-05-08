
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
    //document.getElementById('texto').innerHTML += msg + "<br>";
    var obj = JSON.parse(msg);
    analisarPost(obj, msg);
}

function setStatus(method, teste, bool){
    if(bool){
        document.getElementById("tabela_resultados").innerHTML += "<tr><td>" + method + "</td><td>" + teste + "</td><td align='center'><font color='green'>OK</font></td></tr>";
    }else{
        document.getElementById("tabela_resultados").innerHTML += "<tr><td>" + method + "</td><td>" + teste + "</td><td align='center'><font color='red'>FALHOU</font></td></tr>";
    }
}

function enviar(msg){
    waitForSocketConnection(socket, function(){
        console.log("message sent!!!");
        socket.send(msg);
    });
    //alert(socket.readyState);    
}

function msg_encode(method, data){
    var request = {"id":Math.floor(Math.random() * 1001)+"", "status":"200", "version":"1.0.0", "method":method, "client":"1", "data":data};
    var protocol = {"token":document.getElementById("token").value, "request":request};
    //alert(JSON.stringify(protocol));
    //{"token":"","request":{"id":"1235","status":"200","version":"1.0.0","method":"updateMySchedules","client":"1","data":{"id":"2", "date":"2018-02-27"}}}
    return JSON.stringify(protocol);
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

function dataAtualFormatada(){
    var data = new Date();
    var dia = data.getDate();
    if (dia.toString().length == 1)
      dia = "0"+dia;
    var mes = data.getMonth()+1;
    if (mes.toString().length == 1)
      mes = "0"+mes;
    var ano = data.getFullYear();  
    return ano+"-"+mes+"-"+dia;
}





</script>















<script>

function runTest(option){
    switch(option){
        case "1": // TESTE DE AGENDA - VISITANTE
            enviar(msg_encode("updateMySchedules",{}));
        break;
        
        case "2": // TESTE DE AGENDA - VISITANTE
            enviar(msg_encode("updateScheduleByDay",{"date":dataAtualFormatada()}));
        break;

        case "3": // TESTE DE INSERIR AGENDAMENTO - VISITANTE
            enviar(msg_encode("setSchedule",{"date":dataAtualFormatada(), "hour":"07h00 às 08h00"}));
        break;

        case "4": // TESTE DE CANCELAR AGENDAMENTO - VISITANTE
            enviar(msg_encode("cancelSchedule",{"id":"1", "reasonForCancellation":"1"}));
        break;

        case "5": // TESTE DE CANCELAR AGENDAMENTO - VISITANTE
            enviar(msg_encode("updateFoodPlan",{}));
        break;

        case "6": // TESTE DE ADD CONSUMO ALIMENTO - VISITANTE
            enviar(msg_encode("addConsumption",{"mealId":"3","foodId":"2","planId":"1"}));
        break;

        case "7": // TESTE DE CANCEL CONSUMO ALIMENTO - VISITANTE
            enviar(msg_encode("cancelConsumption",{"mealId":"3","foodId":"2","planId":"1"}));
        break;

        case "8": // TESTE DE ATUALIZAR DADOS USUARIO - VISITANTE
            enviar(msg_encode("updateUserData",{"cpf":"08433629956","email":"ticion@gmail.com","name":"Anderson Caciator Ramos","phone":"(48) 98404-9673"}));
        break;

        case "9": // TESTE DE ATUALIZAR NEWS - VISITANTE
            enviar(msg_encode("updateNews",{}));
        break;

        case "10": // TESTE DE CATEGORIES NEWS - VISITANTE
            enviar(msg_encode("updateCategoriesNews",{}));
        break;

        case "11": // TESTE DE CATEGORIES NEWS - VISITANTE
            enviar(msg_encode("updateCategoriesNewsSelected",{}));
        break;
    }
        
}

function analisarPost(obj, msg){

    switch(obj.request.method){
        case "firstConnection"://------------------------ TESTE CONEXÃO
            if(!testar("TOKEN RECEBIDO?", (obj.request.data.token == ""), obj, msg)){
                break;
            }
            break;
            
        case "updateMySchedules": //------------------------ TESTE 1
            if(!testar("MEUS HORÁRIOS - VISITANTE - RETORNOU ARRAY?", (Array.isArray(obj.request.data.updateMySchedules)), obj, msg)){
                break;
            }
            break;
            
        case "updateScheduleByDay": //------------------------ TESTE 2
            if(!testar("AGENDA - VISITANTE - RETORNOU ARRAY?", (Array.isArray(obj.request.data.updateScheduleByDay)), obj, msg)){
                break;
            }
            break;

        case "setSchedule": //------------------------ TESTE 3
            if(!testar("AGENDA INSERIR - VISITANTE - RETORNOU FALSE?", (obj.request.data.isSchedule == "false"), obj, msg)){
                break;
            }
            break;

        case "cancelSchedule": //------------------------ TESTE 4
            if(!testar("AGENDA CANCELAR - VISITANTE - RETORNOU FALSE?", (obj.request.data.isScheduleCanceled == "false"), obj, msg)){
                break;
            }
            break;

        case "updateFoodPlan": //------------------------ TESTE 5
            if(!testar("PLANO ALIMENTAR - VISITANTE - RETORNOU FALSE?", (obj.request.data.isFoodPlan == "false"), obj, msg)){
                break;
            }
            break;

        case "addConsumption": //------------------------ TESTE 6
            if(!testar("ADD CONSUMO ALIMENTO - VISITANTE - RETORNOU FALSE?", (obj.request.data.isConsumption == "false"), obj, msg)){
                break;
            }
            break;

        case "cancelConsumption": //------------------------ TESTE 7
            if(!testar("CANCELAR CONSUMO ALIMENTO - VISITANTE - RETORNOU FALSE?", (obj.request.data.isCanceledConsumption == "false"), obj, msg)){
                break;
            }
            break;

        case "updateUserData": //------------------------ TESTE 8
            if(!testar("ATUALIZAR DADOS USUARIO - VISITANTE - RETORNOU FALSE?", (obj.request.data.isUpdateUserData == "false"), obj, msg)){
                break;
            }
            break;

        case "updateNews": //------------------------ TESTE 9
            if(!testar("NEWS - VISITANTE - RETORNOU FALSE?", (obj.request.data.updateNews == "false"), obj, msg)){
                break;
            }
            break;

        case "updateCategoriesNews": //------------------------ TESTE 10
            if(!testar("CATEGORIES NEWS - VISITANTE - RETORNOU FALSE?", (obj.request.data.updateCategoriesNews == "false"), obj, msg)){
                break;
            }
            break;

        case "updateCategoriesNewsSelected": //------------------------ TESTE 11
            if(!testar("CATEGORIES NEWS SELECTED - VISITANTE - RETORNOU FALSE?", (obj.request.data.isUpdate == "false"), obj, msg)){
                break;
            }
            break;

        case "errorRequest": //------------------------ TESTE DEFAULT
            document.getElementById('texto').innerHTML += msg + "<br>";
            break;
            
            
    }
}

function testar(nome, comparador, obj, msg){
    var teste = nome;
    document.getElementById("contador").value = document.getElementById("contador").value - 1 + 2;
    if(comparador){
        setStatus(obj.request.method , teste, false);
        document.getElementById('texto').innerHTML += msg + "<br>";
        return false;
    }else{
        setStatus(obj.request.method , teste, true);
    }
    if(document.getElementById("token").value == ""){
        document.getElementById("token").value = obj.request.data.token;
    }
    setTimeout(runTest(document.getElementById("contador").value), 1000);
}

</script>




















<html>
<input type='text' id='contador'><br>
<input type='text' id='token'><br>

<h2>TESTE UNITÁRIOS</h2>

<table id="tabela_resultados" border="1">
<tr>
<td align="center"><b>METHOD</b></td>
<td align="center"><b>TESTE</b></td>
<td align="center"><b>STATUS</b></td>
</tr>
</table>
<h3>Debug</h3>
<div id='texto'></div>
</html>