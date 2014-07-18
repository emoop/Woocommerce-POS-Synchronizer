window.onload = function(){
   document.getElementById("refcheck").onclick =_change; 
   if(document.getElementById("refcheck").checked==true){
	 document.getElementById("form").style.display='block';
	}else{
	 document.getElementById("form").style.display='none';
	}
   }
   
   function _change(){
    var chbox=document.getElementById("refcheck");
	var input=document.getElementById("form");
	 var numb=document.getElementById("_interval");
	 var status='';
	if(chbox.checked==true){
	  input.style.display='block';
	 status='yes';
	}else{
	  input.style.display='none';
	   status='no';
	}
	
	var _data={
	    'operation':'update_refresh',
		'value':parseFloat(numb.value)*60000,
		'status':status,
		'action':'wps_ajax'
	 
	 };
	  jQuery.ajax({
           type : "post",
		   dataType : "json",
           url: adminajax.url,
           data: _data,
		   success: function(responce) {
		   
		   }
		   
		   });
   }