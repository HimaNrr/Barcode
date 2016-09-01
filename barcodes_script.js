<script type="text/javascript">
	$(document).ready(function(){
		/* Script to highlight selected tab,borders */
		$(".left-nav").css("border-color","#E8A317"); 
		$(".right-content").css("border-color","#E8A317");
		$("#BC").children().css("color","#fff");
                $('#BC').addClass('TabbedPanelsTabActive');
       
       /* Script to highlight selected tag, fetch blank form to create new Barcode */
		$("#new-barcode").click(function(event){
			event.preventDefault();
			$("#new-barcode").css("background-color","#E8A317");
			
			$.post("barcodes/newBarcode.php",{newbarcode:''},function(response){
				$(".new-edit").html(response);
			});
		});

		/* Script to highlight selected tag, fetch blank form to create new Barcode */
		$("#BC").click(function(event){
			event.preventDefault();
			$.post("barcodes/newBarcode.php",{newbarcode:''},function(response){
				$(".new-edit").html('');					
			});
		});

        /* Script to fetch prefilled form with details of barcode that was clicked on*/
		$(".clickable-row").on("click",function(event){	
			barcode = $(this).find(".barcode-name").html();         	
			$.post("barcodes/newBarcode.php",{barcodeName:barcode},function(response){
                		$(".new-edit").html(response);
			});
		});//closing clickable-row onclick
                
                $(".globalBarcode").on("click",function(event){	
			barcode = $(this).find(".barcode-name").html();         	
			$.post("barcodes/addGlobalBarcode.php",{barcodeName:barcode},function(response){
                            alert(barcode);
                		$(".new-edit").html(response);
			});
		});
                
    /* validate Barcode form before merge*/	
		$(document).on('click',"#create-barcode",function(e){
			$("#merge-barcode").validate({		   
				rules: {
					barcode_name:"required", 
                                        pwd:"required", 
//					barcode_type: "required",
                                        text_size: {
                                            range: [8, 18]
                                        },
                                        text_rotation: {
                                            range: [0, 360]
                                        },
                                        barcode_rotation: {
                                            range: [0, 360]
                                        }
				},
				messages: {
					barcode_name: "Uh Oh! Barcode Name is missing",
                                        pwd: "Uh Oh! Password is missing",
//					barcode_type: "Uh Oh! Barcode Type is missing",
                                        text_size: "Please enter size of text between 8-18",
                                        text_rotation: "Please enter text rotation angle between 0-360 degrees only",
                                        barcode_rotation:"Please enter barcode rotation angle between 0-360 degrees only"
				},
				errorClass : "fieldError",		
				errorElement:"div",
				highlight: function (element, errorClass) {
					return false;
				},
				unhighlight: function (element, errorClass) {
					return false;
				},
				errorPlacement: function(error, element) {
					element.parent("td").append(error);
				}     	        
			});
		});		
                
                /*tool tip for Barcode appearance in a page */                    
                $(function() {
                    $( document ).tooltip();
                  });
                
                /* Placing text at cursor location*/
		jQuery.fn.extend({
			insertAtCaret: function(myValue){
  				return this.each(function(i) {
    					if (document.selection) {
     						//For browsers like Internet Explorer
      						this.focus();
      						var sel = document.selection.createRange();
      						sel.text = myValue;
      						this.focus();
    					}else if (this.selectionStart || this.selectionStart == '0') {
      						//For browsers like Firefox and Webkit based
      						var startPos = this.selectionStart;
      						var endPos = this.selectionEnd;
      						var scrollTop = this.scrollTop;
      						this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
      						this.focus();
      						this.selectionStart = startPos + myValue.length;
      						this.selectionEnd = startPos + myValue.length;
      						this.scrollTop = scrollTop;
    					} else {
      						this.value += myValue;
      						this.focus();
    					}
  				});
			}
		});
                 
                    // generate new row
                    var resultTable =[];
                    var dynIndx = 0;
                        var counter = 1;
                        $(document).on('click', '#addButton', function(e) {
                            counter++;       
                            if ($("#barcodeGlobals").val() == null) {
                                                alert("Select Global on left to add in Barcode.");
                            } else {
                                var addValue = true;
                                var rowCount = $('.order-list tbody tr').length;
                                if(rowCount == 0) {
                                    var newRow = $('<tr style="font-weight: normal;">');
                                                       var cols = "";
                                                       cols += '<td>'+$("#barcodeGlobals").val()+'</td>\n\
                                                               <td><input type="text" style="width: 45px;" maxlength="3" name="order" id="order" onkeypress="return isNumberKey(event)"/></td>\n\
                                                               <td><input type="text" style="width: 45px;" maxlength="3" name="length' + counter + '" id="length" onkeypress="return isNumberKey(event)"/></td>\n\
                                                               <td><input type="radio" name="' +$("#barcodeGlobals").val()+ '_pad" class="pad" id="' +$("#barcodeGlobals").val()+ '_left" value="L"/>Left <br/>\n\
                                                                   <input type="radio" name="' +$("#barcodeGlobals").val()+ '_pad" class="pad" id="' +$("#barcodeGlobals").val()+ '_right" value="R"/>Right</td>\n\\n\
                                                                <td><input type="text" style="width: 45px;" name="batch" id="batch" /></td>\n\\n\
                                                                <td><input type="text" style="width: 45px;" name="file_position" id="file_position" /></td>\n\\n\
                                                                <td><textarea name="desc" id="desc" /></td>\n\
                                                               <td><button class="ImgDelete" id="deleteButton">Delete</button></a></td>';
                                                       newRow.append(cols);

                                    $("table.order-list").append(newRow);          

                                   resultTable[dynIndx] = {};
                                   resultTable[dynIndx].col1 = $("#barcodeGlobals").val();
                                   dynIndx++;
                                } else { 
                                    for(i=0; i<resultTable.length;i++) {    
                                        if(resultTable[i].col1 == $("#barcodeGlobals").val()) {
                                            alert("Uh Oh! Selected Global Name already exists in table.");
                                            addValue = false;
                                        }
                                    }
                                    if(addValue) {
                                        resultTable[dynIndx] = {};
                                        resultTable[dynIndx].col1 = $("#barcodeGlobals").val();
                                        dynIndx++;
                                        var newRow = $('<tr style="font-weight: normal;">');
                                                       var cols = "";
                                                       cols += '<td>'+$("#barcodeGlobals").val()+'</td>\n\
                                                               <td><input type="text" style="width: 45px;" maxlength="3" name="order" id="order" onkeypress="return isNumberKey(event)"/></td>\n\
                                                               <td><input type="text" style="width: 45px;" maxlength="3" name="length' + counter + '" id="length" onkeypress="return isNumberKey(event)"/></td>\n\
                                                               <td><input type="radio" name="' +$("#barcodeGlobals").val()+ '_pad" class="pad" id="' +$("#barcodeGlobals").val()+ '_left" value="L"/>Left <br/>\n\
                                                                   <input type="radio" name="' +$("#barcodeGlobals").val()+ '_pad" class="pad" id="' +$("#barcodeGlobals").val()+ '_right" value="R"/>Right</td>\n\\n\
                                                                <td><input type="text" style="width: 60px;" name="batch" id="batch" /></td>\n\\n\
                                                                <td><input type="text" style="width: 60px;" name="file_position" id="file_position" /></td>\n\\n\
                                                                <td><textarea name="desc" id="desc" ></textarea></td>\n\
                                                               <td><button class="ImgDelete" id="deleteButton">Delete</button></a></td>';
                                                       newRow.append(cols);
                                            $("table.order-list").append(newRow);                  
                                    }
                               }                      
                            }
                        });

                         $('table.order-list td button.ImgDelete').live('click',function () {
                            $(this).parent().parent().remove();// this.parent is td this.parent.parent means tr 
                            calculateGrandTotal();
                        }); 
                        
                    //order placing in array when sample bttn is clicked 
                    $(document).on('click', '#sample', function(e) {
                        var inputs = document.querySelectorAll("input[name='order']");
                        var result = [];
                        for (var i = 0; field = inputs[i]; i++) {
                            result.push(field.value);
                        }
                        var inputOrder = document.querySelectorAll("input[name='order']");
                        var result1 = [];
                        for (var i = 0; field = inputOrder[i]; i++) {
                            result1.push(field.value);
                        }
                        var inputLength = document.querySelectorAll("input[id='length']");
                        var resultLength = [];
                        for (var j = 0; field = inputLength[j]; j++) {
                            resultLength.push(field.value);
                        }

                        var get_pad = $('input[class="pad"]:checked').map(function() {
                            return this.value;
                        }).get();

                        result.sort(sortmyway);
                        var rowCount = $('.order-list tbody tr').length;
                        var resultArray = [];
                        resultArray = makeArray(rowCount, function(i) {
                            return i + 1;
                        });
                        var a1 = result.toString();
                        var a2 = resultArray.toString();
                        if (a1 == a2) {
                            var counter = 1;
                            var str = "";
                            for (var x = 0; x < rowCount; x++)
                            {
                                if (result1[x] == counter)
                                {
                                    for (y = 0; y < resultLength[x]; y++)
                                    {
                                        str += get_pad[x];
                                    }
                                    counter++;
                                    x = -1;
                                }
                            }
                            $("#barcode_sample").html(str);

                        } else {
                            $("#barcode_sample").html("NULL");
                        }
                    }); 
                    
//                    $('#textReq').hide();
//            $('input[name="text_barcode"]').bind('change', function() {
//                    var showOrHide = ($(this).val() == 'Y') ? true : false;
//                    $('#textReq').toggle(showOrHide);
//                });
        
		/* Show success message on insert/update of barcode*/
		$(".merge-result1").dialog({autoOpen : true, modal : true, minWidth: 250,minHeight: 250,draggable: false});
	});
        function sortmyway(data_A, data_B)
        {
            return (data_A - data_B);
        }
        function makeArray(count, content) {
           var result = [];
           if(typeof(content) == "function") {
              for(var i=0; i<count; i++) {
                 result.push(content(i));
              }
           } else {
              for(var i=0; i<count; i++) {
                 result.push(content);
              }
           }
           return result;
        }
        function calculateGrandTotal() {
            var grandTotal = 0;
            $("table.order-list").find('input[name^="length"]').each(function () {
                grandTotal += +$(this).val();
            });
            $("#grandtotal").text(grandTotal.toFixed(0));
        }
        function isNumberKey(evt){
            var charCode = (evt.which) ? evt.which : event.keyCode
            if (charCode > 31 && (charCode < 48 || charCode > 57))
                return false;
            return true;
        }  
        
        function promptMsg() {
            var alertMsg = prompt("Making changes to an existing barcode will affect all letters. To make changes, please re-enter your password: ", "");
            if(alertMsg == null) {
                alert("Password incorrect. Please re-enter correct password to make changes to barcode.");
            }
        }
</script>