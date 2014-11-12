/*
*  simply javascript function for
*  Woocommerce-POS-Synchronizer
*  Autor:Emil Petrow
*/
/*^^^^^^^^^^^^^^^^^^^^^^^^^^ ADD EVENTS ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ */
window.onload = function(){
   document.getElementById("endbtn").onclick =paid; 
   document.getElementById("skusearch").onfocus =dashClear; 
   document.getElementById("btn").onclick = get_product_by_sku;
   document.getElementById("void").onclick = wps_empty_cart;
   /* ensure cart is empty when window loaded */
   var mess={
		 'operation':'empty_cart',
		 'action':'wps_ajax'
		};
		 jQuery.ajax({
           type : "post",
		   dataType : "json",
           url: adminajax.url,
           data: mess,
		   success: function(responce) {
		   	 if(responce.alert=='ok'){
			  //do nothing
			 }else{
			  // if cart not empty
			  alert('Load window again or manually empties cart!');					
			 }
		   }
		});
  }
   //empty cart when unload window
  window.onunload=function(){
  	document.getElementById("skusearch").value='';
	var mess={
		 'operation':'empty_cart',
		 'action':'wps_ajax'
		};
	
	 jQuery.ajax({
           type : "post",
		   dataType : "json",
           url: adminajax.url,
           data: mess,
		   success: function(responce) {
		   	 //do nothing	
			}
		});
  }
  //refreshing orders view
  if(exdata.refresh=='yes'){
   window.setInterval(function(){refresh()},exdata.interval);//120000
  }
   /*^^^^^^^^^^^^^^^^^^^^^^^^^ COMMON FUNCTIONS ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^*/
   /*
   * clear dashboard from paid icon
   */
   function dashClear(){
   var paidicon=document.getElementById("paid");
               paidicon.style.display = "none";
	document.getElementById("skusearch").value='';
	document.getElementById('notice').innerHTML='';		
	document.getElementById('notice').setAttribute('class','');
 }
 /*
 * empty cart when Void button is pressed
 */
 function wps_empty_cart(){
  refresh();
     var rows=document.getElementById('tbl').rows.length;
	  if(rows<=1){
	  var notice= document.getElementById('notice');
	  notice.setAttribute('class','updated');
	   notice.innerHTML='<p>No product to void!</p>';
		return;
	  }
    var aboard=document.getElementById("action_board");
	aboard.style.opacity=.2;
    var loadingUPD=document.getElementById("cart_update");
    loadingUPD.style.display = "block";
    var mess={
		 'operation':'empty_cart',
		 'action':'wps_ajax'
		};
		 jQuery.ajax({
           type : "post",
		   dataType : "json",
           url: adminajax.url,
           data: mess,
		   success: function(responce) {
		   	 if(responce.alert=='ok'){
			  tableClear("tbl");
			  document.getElementById("subtotal").innerHTML=0;
		      document.getElementById("taxlabel").innerHTML=0;
		      document.getElementById("sm").innerHTML=0;
			  document.getElementById("skusearch").value='';
			  loadingUPD.style.display = "none";
			  aboard.style.opacity=1;
			 }else{
			   alert('Try again,cart is not empty!');					
			 }
		  			
		   }
		});
 }
  /*
  * check whether the entered value is greater or less than stock
  * and set admissible value in table
  */
  function validateQuantity(_element){
     var tabl=document.getElementById("tbl");
	 var _row=tabl.rows[_element.parentNode.parentNode.parentNode.rowIndex];//get parent row index
     var qval=_element.value;
     if(qval!="" ){
       var minq=_element.getAttribute("min"); //min value attribute
       var maxq=_element.getAttribute("max"); //max value attribute
       if(parseFloat(qval)< parseFloat(minq)){
        _element.value=minq;
       }
      if(parseFloat(qval)> parseFloat(maxq)){
       _element.value=maxq;
       }
	 var floatVal=parseFloat(_element.value)*parseFloat(_row.cells[6].innerHTML);
	 _row.cells[7].innerHTML=parseFloat(floatVal).toFixed(2);
	 return _element.value;
    }
  }
  
	 /* table clear function 
     *  argument-string table id
     */
     function tableClear(element){
      var currTable=document.getElementById(element);
      var count=currTable.rows.length;
	  if(element=='order_tbl'){
	    for(var b=count-1;b >= 0;b-- ){
          currTable.deleteRow(b);
        }
	  }else{
      for(var b=count-1;b > 0;b-- ){
          currTable.deleteRow(b);
        }
	   }
      }
	 	 
	  /********************************* delete_Row **********************
      * delete row
      */
	   function delete_Row(x){
      //todo ajax handler to delete product from cart
	  var drow=x.parentNode.parentNode;
	    var _info=drow.getAttribute('data-variation');
		_info=JSON.parse(_info);
		if(_info.cart_item_key!=null){
		 var aboard=document.getElementById("action_board");
	     aboard.style.opacity=.2;
	     var loadingUPD=document.getElementById("cart_update");
         loadingUPD.style.display = "block";
	     var _mess={
		    'operation':'delete_from_cart',
			'cart_item_key':_info.cart_item_key,
		    'action':'wps_ajax'
		  };
		  jQuery.ajax({
           type : "post",
		   dataType : "json",
           url: adminajax.url,
           data: _mess,
		   success: function(responce) {
		      document.getElementById("subtotal").innerHTML=responce.subtotal;
		      document.getElementById("taxlabel").innerHTML=responce.formatted_amount;
		      document.getElementById("sm").innerHTML=responce.order_total;
			  loadingUPD.style.display="none";
		      aboard.style.opacity=1;
		     }
		   });
		}
        delet=1;
        document.getElementById("tbl").deleteRow(x.parentNode.parentNode.rowIndex);
        var tabl=document.getElementById("tbl");
         _count--;
        
      }
	  //------------- variation choice ----------------
	  function finalChoice(x){
	  var _vdata=x.options[x.selectedIndex];
	
	  var aboard=document.getElementById("action_board");
	   aboard.style.opacity=.2;
	   var loadingUPD=document.getElementById("cart_update");
       loadingUPD.style.display = "block";
	   var tabl=document.getElementById("tbl");
	   var wdata=_vdata.getAttribute("data-quantity");
	   var _row=tabl.rows[x.parentNode.parentNode.rowIndex];
	   _row.cells[4].innerHTML=_vdata.text;
	   	var inpt=_row.getElementsByTagName('input')[0];
	   //set possible quantity
	   inpt.setAttribute('max',wdata);
	   inpt.setAttribute('title','max-quantity-'+wdata);
	   inpt.setAttribute('min',1);
	   inpt.value=1;
	   var mq=_row.getElementsByTagName('p')[0];
	   mq.innerHTML='('+wdata+')';
	 	/*
		* add variation/update cart via ajax
		*/
		var pdatarow=_row.getAttribute("data-variation");
	    pdatarow=JSON.parse(pdatarow);
		 var adata={
		    'operation':'update_cart',
		     'parent_id':_vdata.getAttribute("data-parent_id"),
			 'var_id': _vdata.value,
			 'type': 'variation',
			 'quantity':inpt.value,
			 'slug':_vdata.getAttribute("data-slug"),
			 'attribute_name':_vdata.getAttribute("data-att_name"),
			 'cart_item_key':_vdata.getAttribute("data-cart_item_key"),
			 'action':'wps_ajax'
		 };
		 jQuery.ajax({
           type : "post",
		   dataType : "json",
           url: adminajax.url,
           data: adata,
		   success: function(responce) {
		  // alert(responce);
		   loadingUPD.style.display="none";
		   aboard.style.opacity=1;
		   pdatarow.cart_item_key=responce.cart_item_key;
		   var rdata=JSON.stringify(pdatarow);
		   _row.setAttribute("data-variation",rdata);
		   document.getElementById("subtotal").innerHTML=responce.subtotal;
		   document.getElementById("taxlabel").innerHTML=responce.formatted_amount;
		   document.getElementById("sm").innerHTML=responce.order_total;
		   var fsum=parseFloat(inpt.value)*parseFloat(_row.cells[6].innerHTML);
		   _row.cells[7].innerHTML=fsum.toFixed(2);
		   }
         });
	  }
	  /*
	  * change quantity of product
	  */
	  function setQty(x){
	   var aboard=document.getElementById("action_board");
	   aboard.style.opacity=.2;
	   var loadingUPD=document.getElementById("cart_update");
       loadingUPD.style.display = "block";
	   var tabl=document.getElementById("tbl");
	  // alert(x.parentNode.parentNode.parentNode);
	   var _row=tabl.rows[x.parentNode.parentNode.parentNode.rowIndex];//get parent row index
	   var newQuantity= validateQuantity(x);
	   var dataVar=_row.getAttribute('data-variation');
	   dataVar=JSON.parse(dataVar);
	   var _ndata={
	     'operation':'change_item',
		 'cart_item_key':dataVar.cart_item_key,
		 'quantity':newQuantity,
		 'action':'wps_ajax'
	   };
		jQuery.ajax({
           type : "post",
		   dataType : "json",
           url: adminajax.url,
           data: _ndata,
		   success: function(responce) {
		   // alert(JSON.stringify(responce));
		   loadingUPD.style.display="none";
		   aboard.style.opacity=1;
		   dataVar.quantity=newQuantity;
		   var rdata=JSON.stringify(dataVar);
		   _row.setAttribute("data-variation",rdata);
		   document.getElementById("subtotal").innerHTML=responce.subtotal;
		   document.getElementById("taxlabel").innerHTML=responce.formatted_amount;
		   document.getElementById("sm").innerHTML=responce.order_total;
		   }
         });
		
	  }

	  /*
      *  clear input[type=text]
      * arg-tbox-id of input[type=text]
      */
      function clearTbox(tbox){
        document.getElementById(tbox).value="";
       }
      /*
      * quantity label factori
      */	   
	 function qty_label(quantity){
	 var _label='';
       if(quantity==null){
	    _label=' <div style=\"display:inline\"><input type=\"number\" value=\"0\" min=\"0\" max=\"0\" '+
		  'class=\"input-mini mytext-left\" onchange=\"setQty(this)\" ><p class=\"gray\"></p></div>';
	   }else{
	   _label=' <div style=\"display:inline\"><input type=\"number\" title=\"'+quantity+'\" value=\"1\" min=\"1\" max=\"'+quantity+
		   '\" class=\"input-mini mytext-left\" onchange=\"setQty(this)\" ><p class=\"gray\">('+quantity+')</p></div>';
	   }
	   return _label;
      }	 
	  /*
	  * loading searching product
	  */
	  function load_product(obj){
	        _position=obj.cpos;
           _symbol=obj.currency;
         // alert(obj.cart_item_key);
           if(obj.msg=='no'){
           alert('Maybe a sku is not valid?');
            loading.style.display="none";
            clearTbox('skusearch');
             return;
           }
            if(obj.msg=='hidden'){
           alert('Product is out of stock!');
            loading.style.display="none";
            clearTbox('skusearch');
             return;
           }
            //get product table object
           var table=document.getElementById("tbl");//products
           var row = table.insertRow(1);
           row.style.verticalAlign="middle";
            
          indx=row.rowIndex;//-------------------
           //get quantity
		   var current_value=0;
		  var wps_qty=qty_label(null);
          if(obj.type=="simple"){
		  current_value=1;
           wps_qty=qty_label(obj.quantity);
           }
             var dataArr=[];  //array key-rowIndex,value-quantity
          dataArr[0]=obj.type;
           dataArr[1]=obj.id;
		   //data-info
		   var infoData={
		     name:obj.title,
		     id:obj.id,
			 sku:obj.sku,
			 price:obj.price,
			 cpos:obj.cpos,
			 currency:obj.currency,
			 cart_item_key:obj.cart_item_key
		   };
		   infoData=JSON.stringify(infoData);
           var att=document.createAttribute("data-variation");
           att.value=infoData;//dataArr;
           row.setAttributeNode(att);
           row.title=dataArr;
                  
         var cells = row.insertCell(0);
         cells.innerHTML="<image src=\""+exdata.dir+"images/delete-icon.png\" id=\"dl\" onclick=\"delete_Row(this)\" />";
         var cell0 = row.insertCell(1);
         cell0.innerHTML = obj.image ;
         var cell1 = row.insertCell(2);
         cell1.innerHTML = obj.sku ;
         var cell2=row.insertCell(3)
         cell2.innerHTML = obj.title;
         var cell3 = row.insertCell(4);
         cell3.innerHTML =obj.select;//"---";
		 var cell4=row.insertCell(5);
         cell4.innerHTML =wps_qty; //1;
         cell4.style.textAlign="right";
         var cell5=row.insertCell(6);
         var p=parseFloat(obj.price).toFixed(2);
         cell5.innerHTML = p;
         cell5.style.textAlign="center";
         var cell7=row.insertCell(7);
         cell7.innerHTML = (p*parseFloat(current_value)).toFixed(2);
         cell7.style.textAlign="center";
         var but=document.getElementById("dl");
         but.title="delete row";
         // _count++;
		  if(obj.info!=null){
           document.getElementById("taxlabel").innerHTML=obj.info.formatted_amount;
           document.getElementById("subtotal").innerHTML=obj.info.subtotal;
		   document.getElementById("sm").innerHTML=obj.info.order_total;
		   }
           loading.style.display="none";
            clearTbox('skusearch');
			document.getElementById("skusearch").value='';
	  }

 //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ AJAX ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
  /*
   *  get product by unique sku
   *  using ajax
  */ 
 function get_product_by_sku(){
 	   var aboard=document.getElementById("action_board");
	   aboard.style.opacity=.2;
       var loading=document.getElementById("loading");
	   loading.style.display = "block";
      //get search text
      var text=document.getElementById('skusearch').value;
       if(text==""){
         alert("Please,enter a valid SKU.");
         loading.style.display="none";
        }else{
			
		 var _data={
		  'operation':'get_product',
		  'text':text,
		  'action':'wps_ajax'
		  };
		 
		 //ajax core
		 jQuery.ajax({
           type : "post",
		   dataType : "json",
           url: adminajax.url,
           data: _data,
		   success: function(responce) {
		    if(responce.msg=='yes'){
		     loading.style.display="none";
			 aboard.style.opacity=1;
		     load_product(responce);
			}else if(responce.msg=='wrong'){
			loading.style.display="none";
			aboard.style.opacity=1;
			 alert('This Sku is not valid.Check it.');
			 document.getElementById('skusearch').value='';
			 return;
			 
			}
		   }
         });
		}
      }
	
	  //complete payment
    function paid(){
	  var rows=document.getElementById('tbl').rows.length;
	  if(rows<=1){
	  var notice= document.getElementById('notice');
	  notice.setAttribute('class','updated');
	   notice.innerHTML='<p>No product to sale!</p>';
		return;
	  }
	  var aboard=document.getElementById("action_board");
	  aboard.style.opacity=.2;
	  var loader=document.getElementById("sale_procces");
          loader.style.display = "block";
	  document.getElementById("subtotal").innerHTML=0;
	  document.getElementById("taxlabel").innerHTML=0;
	  document.getElementById("sm").innerHTML=0;
	 
	  var pdata={
	   'operation':'payment',
	   'action':'wps_ajax'
	  };
	  jQuery.ajax({
           type : "post",
		   dataType : "json",
           url: adminajax.url,
           data: pdata,
		   success: function(responce) {
		   	
		    if(responce._msg=="ok"){
		     tableClear('tbl');	
		    var ord_table=document.getElementById('order_tbl');
		    var trow=ord_table.insertRow(0);
		    var _cell=trow.insertCell(0);
		    _cell.innerHTML='<a href=\"'+responce.wps_order_url+'\" >'+responce.name+responce.order_id+'('+responce.order_date+')</a>';
		    var _cell1=trow.insertCell(1);
		    _cell1.innerHTML=responce.total;
		  	loader.style.display = "none";
		    aboard.style.opacity=1;
		    var paidicon=document.getElementById("paid");
		    paidicon.style.display="block";
			refresh();
		    }else{
		      alert(responce._msg);
			loader.style.display = "none";
			aboard.style.opacity=1;	
		    	
		    }	
		}
         });
	  }
	  /*
	  * refresh total sum section and orders view
	  */
	  function refresh(){
	  var _tabl=document.getElementById('order_tbl');
	 // tableClear('order_tbl');
	   var ref={
	      'operation':'refresh',
	    'action':'wps_ajax'
	   };
	  jQuery.ajax({
           type : "post",
		   dataType : "json",
           url: adminajax.url,
           data: ref,
		   success: function(responce) {
		  // alert(responce.rows);
		     document.getElementById('total_sum').innerHTML='Total sum-'+responce.total_sum;
			 _tabl.innerHTML=responce.rows;
		   }
		 });
	  }
