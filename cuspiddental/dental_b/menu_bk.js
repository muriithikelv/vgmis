//this will add commas to a number for formating
function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}





//this checks password lenght and composition
function passcheck(pass){
							//	var pass = $(this).val();
								var length = pass.length;
								//alert('pass ni ' + pass + ' na lentgh ni ' + length);
								//(password itself, number of characters) VALUES FROM INPUT TAG
								//IF PASSWORD PRESENT, SHOW MESSAGE
								if(pass)
								{
								if(length>=8){ //PASSWORD MIN/MAX NUMBER OF CHARACTERS
									var alpha = /^[a-zA-Z]+$/; //PATTERN FOR ALPHABETS
									var number = /^[0-9]+$/; //PATTERN FOR NUMBERS

									//LOOPS THRU PASSWORD TO CHECK FOR AT LEAST ONE OF EACH PATTERN
									for(i=0; i<length; i++){
									if(pass.substr(i, 1).match(alpha)){
									var letters = true; //AT LEAST ONE LETTER EXISTS
									}
									if(pass.substr(i, 1).match(number)){
									var numbers = true; //AT LEAST ONE NUMBER EXISTS
									}
									}
									//IF BOTH LETTERS AND NUMBERS ARE PRESENT...
									if(letters==true && numbers==true){return true;} 
									else { return false;}
									
								}
								else {return false;}
								}
								else //IF NO PASSWORD PRESENT, NULL THE MESSAGE
								{return false;}
			}
			
function remove_commas(var1){
	return var1.replace(/,/g, '');
    }
	
function IsNumeric_jq(sText)

{
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;

 
   for (i = 0; i < sText.length && IsNumber == true; i++) 
      { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
         IsNumber = false;
	// alert(atext);
	 return false;	
         }
      }
   return IsNumber;
   
}

function error_dialog(element, error_message){
				
				$("#dialogs").html('<p>' + error_message + '</p>');
				$("#dialogs").dialog({
						//open: function(){$(this).empty();},
						close: function(){$(this).empty();element.focus();},
		title: 'ERROR MESSAGE',
		height: 200,
		width: 600,
		modal: true});
		//element.focus();

}

jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", Math.max(0, (($(window).outerHeight() - $(this).outerHeight()) / 2) + 
                                                $(window).scrollTop()) + "px");
    this.css("left", Math.max(0, (($(window).outerWidth() - $(this).outerWidth()) / 2) + 
                                                $(window).scrollLeft()) + "px");
    return this;
}

//this will get total cost of xray referals
function get_xray_ref_total_cost(){
	var total_cost = total_self = total_point =0;
	//alert('ggg');
	var exit_flag = false;
	//get xray added totals
	$('.xray_ref_cost').each(function(){
		//insurance
		//if($(this).parent().prev().prev().text() == 'Insurance' ){
			//check if cost  is numeric
			if(( IsNumeric_jq( remove_commas($(this).val()) )) ){			
					//do math
					if( $(this).val() != ''){
						var cost = $(this).val();
					}
					else{var cost = 0;}
					total_cost = parseFloat(total_cost) + parseFloat(cost) ;
				}
			else{exit_flag = true;}
		//}
	});
	if(exit_flag == false){
		$('.xray_ref_cost_total').text(addCommas(total_cost));
	}
	else if(exit_flag == true){
		$('.xray_ref_cost_total').text('Invalid number found');
	}
}

function get_treatment_plan_total_cost(){
	var total_insurance = total_self = total_point =0;
	//alert('ggg');
	var exit_flag = false;
	//get xray added totals
	$('.add_xray_to_tplan:checked').each(function(){
		//insurance
		if($(this).parent().prev().prev().text() == 'Insurance' ){
			//check if cost  is numeric
			if(( IsNumeric_jq( remove_commas($(this).parent().prev().text()) )) ){			
					//do math
					if( $(this).parent().prev().text() != ''){
						var cost = $(this).parent().prev().text().replace(/,/g, '');
					}
					else{var cost = 0;}
					total_insurance = parseFloat(total_insurance) + parseFloat(cost) ;
				}
			else{exit_flag = true;}
		}	
		//self
		else if($(this).parent().prev().prev().text() == 'Self' ){
			//check if cost  is numeric
			if(( IsNumeric_jq( remove_commas($(this).parent().prev().text()) )) ){			
					//do math
					if( $(this).parent().prev().text() != ''){
						var cost = $(this).parent().prev().text().replace(/,/g, '');
					}
					else{var cost = 0;}
					total_self = parseFloat(total_self) + parseFloat(cost) ;
				}
			else{exit_flag = true;}
		}
		//points
		else if($(this).parent().prev().prev().text() == 'Points' ){
			//check if cost  is numeric
			if(( IsNumeric_jq( remove_commas($(this).parent().prev().text()) )) ){			
					//do math
					if( $(this).parent().prev().text() != ''){
						var cost = $(this).parent().prev().text().replace(/,/g, '');
					}
					else{var cost = 0;}
					total_point = parseFloat(total_point) + parseFloat(cost) ;
				}
			else{exit_flag = true;}
		}		
	});
	
	
	$('.pay_method').each(function(){

		//insurance
		if($(this).find(":selected").text() == 'Insurance' ){
			//check if cost and discount is numeric
			if( IsNumeric_jq( remove_commas($(this).parent().next().children('.tplan_cost').val()) ))  {			
					//do math
					if( $(this).parent().next().children('.tplan_cost').val() != ''){
						var cost = $(this).parent().next().children('.tplan_cost').val();
					}
					else{var cost = 0;}
					
					total_insurance = parseFloat(total_insurance) + parseFloat(cost) ;
				}
			else{exit_flag = true;}
		}//self
		if($(this).find(":selected").text() == 'Self' ){
			//check if cost and discount is numeric
			if( IsNumeric_jq( remove_commas($(this).parent().next().children('.tplan_cost').val()) ))  {			
					//do math
					if( $(this).parent().next().children('.tplan_cost').val() != ''){
						var cost = $(this).parent().next().children('.tplan_cost').val();
					}
					else{var cost = 0;}
					
					total_self = parseFloat(total_self) + parseFloat(cost);
				}
			else{exit_flag = true;}
		}//points
		if($(this).find(":selected").text() == 'Points' ){
			//check if cost and discount is numeric
			if( IsNumeric_jq( remove_commas($(this).parent().next().children('.tplan_cost').val()) ))  {			
					//do math
					if( $(this).parent().next().children('.tplan_cost').val() != ''){
						var cost = $(this).parent().next().children('.tplan_cost').val();
					}
					else{var cost = 0;}
					
					total_point = parseFloat(total_point) + parseFloat(cost) ;
				}
			else{exit_flag = true;}
		}		
			
	}); 	
	if(exit_flag == false){
		$('#treatment_plan_insurance_total').text(addCommas(total_insurance));
		$('#treatment_plan_self_total').text(addCommas(total_self));
		$('#treatment_plan_sum').text(addCommas(parseFloat(total_insurance) + parseFloat(total_self) ));
		$('#treatment_plan_points_total').text(addCommas(total_point));
	}
	else if(exit_flag == true){
		$('#treatment_plan_insurance_total').text('Invalid number found');
		//$('#treatment_plan_self_total').text(addCommas(total_self));
		//$('#treatment_plan_sum').text(addCommas(parseFloat(total_insurance) + parseFloat(total_self) ));
		//$('#treatment_plan_points_total').text(addCommas(total_point));
	}	
	
}

//this will check if payment has cleared all balance otr not
function check_pay_vs_balance(){
	var pay_type1 = $('.payment_type').val();
	var amount1 = $('.self_amount').val();
	var token_ninye1 = $('#token_ninye').val();
	var element = $('.next_payment_div');
	if(pay_type1!='' && amount1 !='' && token_ninye1 !=''){
		var form_data = {pay_type1: pay_type1, amount1: amount1, token_ninye1: token_ninye1}
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert("g An Error occured, unable to complete form submission");
		e.preventDefault();
		},
		success: function(data) {
		//alert(data);
			var x = data.split('good');
			if(x.length == 2){
					element.empty().append(x[1]).slideDown('fast');
			}
			else if(data == 'no'){ element.empty().slideUp('fast');}
		},
		complete: function() {
		}
		});	
	}
}

//this will be used for edit tplan
function get_treatment_plan_total_cost2(){
	var total_insurance = total_self = total_point =0;
	//alert('ggg');
	var exit_flag = false;
	//get cost of started procedures
	$('.old_pay_type').each(function(){
		//alert('gggggg ' + $(this).next().text());
		//insurance
		if($(this).text() == 'Insurance' ){
			//check if cost  is numeric
			if(( IsNumeric_jq( remove_commas($(this).next().text()) )) ){			
					//do math
					if( $(this).next().text() != ''){
						var cost = $(this).next().text();
					}
					else{var cost = 0;}
					total_insurance = parseFloat(total_insurance) + parseFloat(cost) ;
				}
			else{exit_flag = true;}
		}	
		//self
		else if($(this).text() == 'Self' ){
			//check if cost  is numeric
			if(( IsNumeric_jq( remove_commas($(this).next().text()) )) ){			
					//do math
					if( $(this).next().text() != ''){
						var cost = $(this).next().text();
					}
					else{var cost = 0;}
					total_self = parseFloat(total_self) + parseFloat(cost) ;
				}
			else{exit_flag = true;}
		}
		//points
		else if($(this).text() == 'Points' ){
			//check if cost  is numeric
			if(( IsNumeric_jq( remove_commas($(this).next().text()) )) ){			
					//do math
					if( $(this).next().text() != ''){
						var cost = $(this).next().text();
					}
					else{var cost = 0;}
					total_point = parseFloat(total_point) + parseFloat(cost) ;
				}
			else{exit_flag = true;}
		}		
	});
	
	
	$('.pay_method').each(function(){

		//insurance
		if($(this).find(":selected").text() == 'Insurance' ){
			//check if cost and discount is numeric
			if(( IsNumeric_jq( remove_commas($(this).parent().next().children('.tplan_cost2').val()) ))  ){			
					//do math
					if( $(this).parent().next().children('.tplan_cost2').val() != ''){
						var cost = remove_commas($(this).parent().next().children('.tplan_cost2').val());
					}
					else{var cost = 0;}

					
					total_insurance = parseFloat(total_insurance) + parseFloat(cost);
				}
			else{exit_flag = true;}
		}//self
		if($(this).find(":selected").text() == 'Self' ){
			//check if cost and discount is numeric
			if(( IsNumeric_jq( remove_commas($(this).parent().next().children('.tplan_cost2').val()) ))  ){			
					//do math
					if( $(this).parent().next().children('.tplan_cost2').val() != ''){
						var cost = remove_commas($(this).parent().next().children('.tplan_cost2').val());
					}
					else{var cost = 0;}

					
					total_self = parseFloat(total_self) + parseFloat(cost) ;
				}
			else{exit_flag = true;}
		}//points
		if($(this).find(":selected").text() == 'Points' ){
			//check if cost and discount is numeric
			if(( IsNumeric_jq( remove_commas($(this).parent().next().children('.tplan_cost2').val()) ))  ){			
					//do math
					if( $(this).parent().next().children('.tplan_cost2').val() != ''){
						var cost = remove_commas($(this).parent().next().children('.tplan_cost2').val());
					}
					else{var cost = 0;}

					
					total_point = parseFloat(total_point) + parseFloat(cost);
				}
			else{exit_flag = true;}
		}		
			
	}); 	
	if(exit_flag == false){
		$('#treatment_plan_insurance_total').text(addCommas(total_insurance));
		$('#treatment_plan_self_total').text(addCommas(total_self));
		$('#treatment_plan_sum').text(addCommas(parseFloat(total_insurance) + parseFloat(total_self) ));
		$('#treatment_plan_points_total').text(addCommas(total_point));
	}
	else if(exit_flag == true){
		$('#treatment_plan_insurance_total').text('Invalid number found');
		//$('#treatment_plan_self_total').text(addCommas(total_self));
		//$('#treatment_plan_sum').text(addCommas(parseFloat(total_insurance) + parseFloat(total_self) ));
		//$('#treatment_plan_points_total').text(addCommas(total_point));
	}	
	
}

function  mainmenu(){

//thius will get total for treatment plan costs
$('.procedure_container , table.unbilled_xrays tr td').on('mouseleave', ' .tplan_cost, .add_xray_to_tplan', function(){
	//alert('224');
	if( $(this).val()!='' ){get_treatment_plan_total_cost();}
});

//this will prompt calculation of xray ref total cost
$('.xray_ref_cost').mouseleave(function(){
	//alert('224');
	if( $(this).val()!='' ){get_xray_ref_total_cost();}
});

//thius will get total for treatment plan in edit tplan
$('.procedure_container2').on('mouseleave', '.tplan_cost2', function(){
	//alert('224');
	if( $(this).val()!='' ){get_treatment_plan_total_cost2();}
});


$(".dentition").change(function(){
	var val = $(this).val();
	if( val == 'adult'){
		$('#adult').show();
		$('#mixed').hide(); 
		$('#pedo').hide(); 
	}
	else if( val == 'mixed'){
		$('#adult').hide();
		$('#mixed').show(); 
		$('#pedo').hide(); 
	}
	else if( val == 'pedo'){
		$('#adult').hide();
		$('#mixed').hide(); 
		$('#pedo').show(); 
	}	
	
});


//this will remove page loader
$('#employer_insurance_page_loader').hide();
$('.employer_form_div').fadeIn();
$('.show_loader').hide(); 
//$('.feedback').hide(); 
//$('.show_spin').find('   <img src="dental_b/ajax-loader-spinner.gif" />').remove();

//check if another invoice has already been raised that day for the patient and ask if this invoice should be 
//appeneded to that one or anew one should be added
$('.raise_invoice').change(function(){
	if(this.checked){
		var jina= $(this).attr('name');
		$(this).siblings('.' + jina).prop('disabled', true);

		//check if another invoice has been raised on the same day and give oprion to add this to it
		var var1 = $(this).next().attr('name');
		//alert('var1 is ' + var1);
		var element = $(this);
		var form_data = {var1: var1}
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
			alert(" An Error occured, unable to complete form submission");
			e.preventDefault();
		},
		success: function(data) {
		//alert(data);
			if(data != ''){
				//show bloody table in dialog
				var width_x = 400;//
				$('#append_invoice').empty().css('backgroundColor',' #15212F').dialog({
				title: 'Select invoice for this treatment',
				height: 300,
				width: width_x,
				modal: true}).html(data)
			}
		},
		complete: function() {
		}
		});	
		
	}
	else{
		var jina= $(this).attr('name');
		$(this).siblings('.' + jina).prop('disabled', false);
		$(this).next().val('');
	}
});

//this will disable invoice payment when cash is chosen
$('.lipa_cash').change(function(){
	if(this.checked){
		var jina= $(this).attr('name');
		$(this).siblings('.' + jina).prop('disabled', true);

	
	}
	else{
		var jina= $(this).attr('name');
		$(this).siblings('.' + jina).prop('disabled', false);
		$(this).next().val('');
	}
});

//check if another quotation has already been raised that day for the patient and ask if this quotation should be 
//appeneded to that one or anew one should be added
$('.raise_quotation').change(function(){
	if(this.checked){
		//alert('checked');
		//check if another quotation has been raised on the same day and give oprion to add this to it
		var var1q = $(this).next().attr('name');
		//alert('var1 is ' + var1);
		var element = $(this);
		var form_data = {var1q: var1q}
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
			alert(" An Error occured, unable to complete form submission");
			e.preventDefault();
		},
		success: function(data) {
		//alert(data);
			if(data != ''){
				//show bloody table in dialog
				var width_x = 400;//
				$('#append_invoice').empty().css('backgroundColor',' #15212F').dialog({
				title: 'Select quotation for this treatment',
				height: 300,
				width: width_x,
				modal: true}).html(data)
			}
		},
		complete: function() {
		}
		});	
		
	}
	else{
		$(this).next().val('');
	}
});


//this will perform patient search for allocations
$('.search_pbbatient_2').click(function(){
	var search_by = $(this).parent().parent().find('.search_by').val();
	var search_ciretia = $(this).parent().parent().find('.search_ciretia').val();
	//alert('found ' + search_ciretia + ' jjjjjj  ' + search_by);
		//now check ni db if the patint exists
		var element = $(this);
		var form_data = {search_by: search_by, search_ciretia: search_ciretia}
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to complete form submission");
		e.preventDefault();
		},
		success: function(data) {
		//alert(data);
			var x = data.split('#');
			if(x[0] == 'good' || x[0] == 'bad'  ){element.parent().next().html(x[1]);}
			else{
				//show bloody table in dialog
				var width_x = $('.get_width').show().width();
				$('.div_shower').dialog({
				title: 'Patient Search Results',
				height: 500,
				width: width_x,
				modal: true}).html(data)
			}
		},
		complete: function() {
		}
		});	
	/*$.post("dental_b/", $(this).serialize())
	 .done(function(data) {
		alert("Data Loaded: " + data);
	if(data != ''){
		var x = data.split('#');
		if(x[0] == 'good'){$(this).parent().append().html(data);}
	}		
	/*if(action == "#completion"){$('#ui-tabs-6').load("completion/index.php");}
	else if(action == "#diseases"){$('#ui-tabs-5').load("diseases/index.php");}	
	else if(action == "#female-patients"){$('#ui-tabs-4').load("female-patients/index.php");}	
	else if(action == "#medical-information"){$('#ui-tabs-3').load("medical-information/index.php");}		
	else if(action == "#dental-information"){$('#ui-tabs-2').load("dental-information/index.php");}
	else if(action == "#treatment-plan"){$('#ui-tabs-8').load("treatment-plan/index.php");}		
	else if(action == "#treatment-done"){$('#ui-tabs-9').load("treatment-done/index.php");}		
	else if(action == "#examination"){$('#ui-tabs-7').load("examination/index.php");}		
	else if(action == "lab_prescription_form"){window.location = "?id=lab-prescription-form";}*/
	//});
});

$('.patient_form2, .patient_form').submit(function(e){
//alert('324');
//$(document.body).on('submit', '.patient_form', function(e){
//	if($(this).hasClass('search_patient_2')){alert ('yyyyyyyyyyyyyyyyyyyyyyyyyy');}
//	else{alert ('nnnnnnnnnnnnnnnnnnnnnn');}
	var action = $(this).attr("action");
	var tab_id= $(this).closest('.ui-tabs-panel').attr("id");
	var element = $(this);
	e.preventDefault();
	//$('.covered_company').prop('disabled', false);
	if(action == "#patient-contacts"){
		$('.covered_company').prop('disabled', false);//enale disabled inputs so that value can be sent to submission
	}
	else if(action == "#send_email_on_same_page"){
		$('.feedback').removeClass('hide_element').addClass('orange_background').empty().append('<label class=label>Sending emails, please wait </label><img src="dental_b/ajax-loader-spinner.gif" />').show();;//enale disabled inputs so that value can be sent to submission
	}
	else if(action == "#xray_refs"){
		$('.xray_ref_cost').prop('disabled', false);//enale disabled inputs so that value can be sent to submission
	}	
	

	$(this).attr('disabled', 'disabled');
	$('.show_loader').empty().removeClass('success_response').removeClass('error_response').addClass('background_yellow');
	/*$('.show_loader').dialog({
		title: 'Submitting details',
		width: 400,
		modal: true}).append('Loading  ').append('<img src="dental_b/ajax-loader.gif" />').show();*/
		
	$.post("dental_b/", $(this).serialize())
	 .done(function(data) {
	//alert('data is ' + data);
		var r1 = data.split('type');
		if( r1.length == 2){
			$('.feedback').empty()
						.removeClass('error_response hide_element orange_background')
						.addClass('success_response')
						.append(data)
						.show();
		}
		
	//	$('.show_loader').dialog('close');
		var x = data.split('#');
		if(x[0] == "good"){
			if(action == "#patient-contacts"){
				$('.covered_company').prop('disabled', true);//disable after form is submitted
				var get_fam = 'yes';
				$('#family_div').load('dental_b/', {'get_fam': get_fam });
			}
			else if(action == "#dispatched_lab_work_from_tdone"){
				var undispatched_finished_labs = 'yes';
				$('#undispatched_labs').load('dental_b/', {'undispatched_finished_labs': undispatched_finished_labs });
				
			}

		//alert('action is ' + action);
			if(x[1] == "treatment-done"){
				$('#'+ tab_id).load("treatment-done/index.php");
			}
			else if(x[1] == "on-examination"){
				$('#ui-tabs-7').load("examination/index.php");
				$('html, body').animate({scrollTop: '0px'}, 500);
			}
			else if(x[1] == "add_user"){
				window.location = "?id=add-user";
			}
			else if(x[1] == "cadcam"){
				window.location = "?id=cadcam-type";
			}			
			else if(x[1] == "waiver_approval"){
				window.location = "?id=waiver-approval";
			}	
			else if(x[1] == "follow_up_comment"){
				window.location = "?id=follow-ups";
			}				
			else if(x[1] == "roles"){
				window.location = "?id=roles";
			}
			else if(x[1] == "loyalty_points"){
				window.location = "?id=loyalty-points";
				
			}	
			else if(x[1] == "add_procedure"){
				window.location = "?id=procedures";
			}			
			else if(x[1] == "xray-referal"){
				window.location = "?id=xray-referal";
			}
			else if(x[1] == "authorise_invoice"){
				window.location = "?id=invoice-authorisation";
			}			
			else if(x[1] == "self_payment"){
				window.location = "?id=self-payments";
			}			
			else if(x[1] == "add_technician"){
				window.location = "?id=lab-technician";
			}
			else if(x[1] == "add_cadcam_referrer"){
				window.location = "?id=cadcam-referrers";
			}
			else if(x[1] == "book_appointment"){
				window.location = "?id=book-appointment";
			}			
			else if(x[1] == "user_privileges"){
				window.location = "?id=user-privileges";
			}			
			else if(x[1] == "add_referrer"){
				window.location = "?id=xray-referrer";
			}
			else if(x[1] == "lab_work"){
				window.location = "?id=lab-prescription-form";
			}	
			else if(x[1] == "patient_allocation"){
				window.location = "?id=allocate-patients";
			}
			else if(x[1] == "go_to_examination"){
				window.location = "?id=patient#examination" ;
			
			}
			else if(x[1] == "lab-payments"){
				window.location = "?id=lab-payments";
			}	
			else if(x[1] == "invoice-payments"){
				window.location = "?id=insurance-payments";
			}
			else if(x[1] == "treatment_plan_reload"){
				$('#ui-tabs-8').load("treatment-plan/index.php");
				$('html, body').animate({scrollTop: '0px'}, 500);
			}
			else if(x[1] == "hii_ni_pt_contact"){
				window.location = "?id=patient";
			}
			else if(x[1] == "patient_notes"){
				//window.location = "?id=patient_notes";
				$('.feedback').remove();
					var width_x = $('.div_shower').parent().width();
					$('.div_shower').empty().css('backgroundColor',' #15212F').dialog({
						title: 'PATIENT NOTE',
						height: 500,
						width: width_x,
						modal: true}).append(x[2]); 
						/*
				$('.feedback').empty()
				.removeClass('error_response success_response hide_element orange_background')
				.append(x[2])
				.show();*/
				//exit;
			}
			else{
				if(action == "#treatment-plan"){
				window.location = "?id=insurance-payments";
					var get_unbilled_xray = 'unbilled_xray';
					$('#unbilled_xrays_div').load('dental_b/', {'get_unbilled_xray': get_unbilled_xray });
					//empty treatment plan so that the guy does not add another one with the same treatments by clicking submit
					element.find("input[type=text], textarea, select").val("");
					$('#treatment_plan_insurance_total').text('');
					$('#treatment_plan_self_total').text('');
					$('#treatment_plan_sum').text('');
					$('#treatment_plan_points_total').text('');					
				}

											$('.feedback').empty()
											.removeClass('error_response hide_element orange_background')
											.addClass('success_response')
											.append(x[1])
											.show();
						$('html, body').animate({scrollTop: '0px'}, 300);					

				}//end else		
		}//end good if
						
		else if(x[0] == "bad"){
			if(x[1] == "new_procedure"){
				$('#new_procedure_form_div .feedback').empty()
							.removeClass('success_response hide_element orange_background')
							.addClass('error_response')
							.append(x[2])
							.show();
				//$('html, body').animate({scrollTop: '0px'}, 0);	
			}	
			else if(x[1] == "date_clear_bal"){
				$('.feedback').empty()
							.removeClass('success_response hide_element orange_background')
							.addClass('error_response')
							.append(x[2])
							.show();
				$('.next_payment_div').slideDown('fast');			
				$('html, body').animate({scrollTop: '0px'}, 0);	
			}			
			else{
				$('.feedback').empty()
							.removeClass('success_response hide_element')
							.addClass('error_response')
							.append(x[1])
							.show();
				$('html, body').animate({scrollTop: '0px'}, 0);	
			}
		}
		else{
						//show bloody table in dialog
			var x2 = data.split('wagonjwawengi');
		//	if(typeof x2[1] === 'undefined'){}
		//	else{
			if(typeof x2[1] !== 'undefined'){
			
				var width_x = $('.get_width').show().width();
				$('.div_shower').dialog({
				dialogClass:'dialog_bg',
				title: 'Patient Search Results',
				height: 500,
				width: width_x,
				modal: true}).html(x2[1]);
				
				$('.selected_pt').click(function() {
					
					var selected_pt = $(this).prev().val();
					//alert('selected is  ' + selected_pt);
					  element.append('<input type=hidden name=selected_patient class=selected_patient_input value=' + selected_pt + ' />');
				element.submit();
				$('.div_shower').dialog("close");
				});		
			}
		}

		
		});


	
});


//submit patient forms via jquery
//$('.patient_form').submit(function(e){
$('#cadcam_tabs4, #appointment_divr2, #append_invoice, .pt_contact_shower, #cadcam_tabs, #cadcam_tabs3, #ins_price_ed, .dialog_with_tab ,.div_shower44,  #appointment_div2,  #edit_waiting_list, .procedure_container2').on('submit', '.patient_form', function(e){
//alert('oo1');
//	else{alert ('nnnnnnnnnnnnnnnnnnnnnn');}
	var action = $(this).attr("action");
	var element = $(this);
	if(action == "#append_invoice"){
		//alert(element.closest('div').attr("id"));
		element.closest('div').dialog('close');
	}
	else if(action == "#prescribe_drug"){
		$('.drug_price').prop('disabled', false);//enale disabled inputs so that value can be sent to submission
		//alert('rrr');
	}		
		//$('#append_invoice').dialog('close');}
	e.preventDefault();
	$(this).attr('disabled', 'disabled');
	$('.show_loader').empty().removeClass('success_response').removeClass('error_response').addClass('background_yellow');
	$('.show_loader').dialog({
		title: 'Submitting details',
		width: 400,
		modal: true}).append('Loading  ').append('<img src="dental_b/ajax-loader.gif" />').show();
	$.post("dental_b/", $(this).serialize())
	 .done(function(data) {
	//alert(data);
		$('.show_loader').dialog('close');
		var x = data.split('#');
		if(x[0] == "good"){
		//alert('action is ' + action);
		//alert(x[2] + ' -- ' + x[3]);
			if(x[1] == "treatment-done"){
				$('#ui-tabs-9').load("treatment-done/index.php");
			}
			else if(action == '#edit_tplan'){
				element.closest('div').dialog('close');
							
				$('[name="search_by"]').val('patient_number');
				var form1 = $('[name="search_by"]').closest('form');
				$('[name="search_ciretia"]').val(x[2]);		
				$('.find_pt1').click();
			//	$.post(window.location, form1.serialize())
				//								.done(function(data) {element.closest('div').dialog('close');});				
			//	window.location = "?id=edit-treatment-plan";
				//$('html, body').animate({scrollTop: '0px'}, 0);	
			}
			else if(action == "#lab_form_tdone"){
				var lab_request = 'yes';
				$('.div_shower44').load('dental_b/', {'lab_request': lab_request });//enale disabled inputs so that value can be sent to submission
				$('.div_shower44').animate({scrollTop: '0px'}, 0);							
				$('html, body').animate({scrollTop: '0px'}, 0);	
			}	
			else if(x[1] == "add_user"){
				window.location = "?id=add-user";
			}
			else if(x[1] == "follow_up"){
				var follow_up ='yes';
				$('.div_shower44').load('dental_b/', {'follow_up': follow_up });
			}
			else if(x[1] == "appointment_re_appointed"){
				$('#appointment_divr2').dialog('close');
				$('.re_appoint_div').dialog('close');
				$('.feedback').empty()
							.removeClass('error_response hide_element')
							.addClass('success_response')
							.append(x[3])
							.show();
				$('.current_reappoint').text(x[2]);
			}
			else if(x[1] == "append"){
				$('[name="' + x[2] + '"]').val(x[3]);
				
			}
			else if(x[1] == "ins_price"){
				$('#ins_price_ed').dialog('close');
				
			}			
			else if(x[1] == "noappend"){
				$('[name="' + x[2] + '"]').val('');
				
			}
			else if(x[1] == "roles"){
				window.location = "?id=roles";
			}
			else if(x[1] == "patient_prescription"){
				var prescribe='';
				$(".div_shower44").empty().load('dental_b/', {'prescribe': prescribe });	
			}
			else if(x[1] == "family_pt"){
				$('.pt_contact_shower').dialog("close");
				var get_fam = 'yes';
				$('#family_div').load('dental_b/', {'get_fam': get_fam });
			}					
			else if(x[1] == "loyalty_points"){
				window.location = "?id=loyalty-points";
			}			
			else if(x[1] == "add_technician"){
				window.location = "?id=lab-technician";
			}
			else if(x[1] == "book_appointment"){
				window.location = "?id=book-appointment";
			}			
			else if(x[1] == "user_privileges"){
				window.location = "?id=user-privileges";
			}			
			else if(x[1] == "add_referrer"){
				window.location = "?id=xray-referrer";
			}
			else if(x[1] == "cadcam-referal"){
			//	alert('tt');
				window.location = "?id=cadcam-referal";
			//	location.reload();
			}
			
			else if(x[1] == "lab_work"){
				window.location = "?id=lab-prescription-form";
			}	
			else if(x[1] == "patient_allocation"){
				window.location = "?id=allocate-patients";
			}				
			else if(x[1] == "lab-payments"){
				window.location = "?id=lab-payments";
			}
			else if(x[1] == "stock_usage"){
				//reload so that new stock quantity is seen
				var current_index = $("#cadcam_tabs2").tabs("option","selected");
				$("#cadcam_tabs2").tabs('load',current_index);
				//alert(current_index);
				//empty the fields so that the guy does not add twice by mistake
				/*element.find("input[type=text]").val("");
				element.prev().empty()
							.removeClass('error_response hide_element')
							.addClass('success_response')
							.append(x[2])
							.show();*/
				
			}
			else if(x[1] == 'edit_invoice'){
				
				$('.div_shower2 .feedback').empty()
							.removeClass('error_response hide_element')
							.addClass('success_response')
							.append(x[2])
							.show();
				
				$('.div_shower2').animate({scrollTop: '0px'}, 0);
				$('html, body').animate({scrollTop: '0px'}, 0);	
			}			
			else{
			if(action == "#treatment-plan"){
				//empty treatment plan so that the guy does not add another one with the same treatments by clicking submit
				element.find("input[type=text], textarea, select").val("");
			}
			else if(action == "#block_stock_in"){
				//empty the fields so that the guy does not add twice by mistake
				element.find("input[type=text]").val("");
			}

			
											$('.feedback').empty()
											.removeClass('error_response hide_element')
											.addClass('success_response')
											.append(x[1])
											.show();
						$('html, body').animate({scrollTop: '0px'}, 300);					

				}//end else		
		}//end good if
						
		else if(x[0] == "bad"){
			if(action == '#edit_tplan'){
				$('.div_shower2 .feedback').empty()
								.removeClass('success_response hide_element')
								.addClass('error_response')
								.append(x[1])
								.show();
				$('html, body').animate({scrollTop: '0px'}, 0);	
			}
			else if(action == "#lab_form_tdone"){
				$('.feedback').empty()
								.removeClass('success_response hide_element')
								.addClass('error_response')
								.append(x[1])
								.show();
				$('.div_shower44').animate({scrollTop: '0px'}, 0);							
				$('html, body').animate({scrollTop: '0px'}, 0);					
			}
			else if(x[1] == 'patient_prescription'){
				$('.feedback2').empty()
								.removeClass('success_response hide_element')
								.addClass('error_response')
								.append(x[2])
								.show();
				$('html, body').animate({scrollTop: '0px'}, 0);	
			}
			else if(x[1] == 'follow_up'){
				$('.div_shower44 .feedback').empty()
								.removeClass('success_response hide_element')
								.addClass('error_response')
								.append(x[2])
								.show();
				$('html, body').animate({scrollTop: '0px'}, 0);	
			}
			else if(x[1] == 'edit_invoice'){
				$('.div_shower2 .feedback').empty()
							.removeClass('success_response hide_element')
							.addClass('error_response')
							.append(x[2])
							.show();				
				$('.div_shower2').animate({scrollTop: '0px'}, 0);
				$('html, body').animate({scrollTop: '0px'}, 0);
				
			}
			else if(x[1] == 'stock_usage'){
				element.prev().empty()
							.removeClass('success_response hide_element')
							.addClass('error_response')
							.append(x[2])
							.show();				
				$('.div_shower2').animate({scrollTop: '0px'}, 0);
				$('html, body').animate({scrollTop: '0px'}, 0);
				
			}
			
			else{
					$('.feedback').empty()
								.removeClass('success_response hide_element')
								.addClass('error_response')
								.append(x[1])
								.show();
				$('html, body').animate({scrollTop: '0px'}, 0);	
				//check if the form has selected_patient that may need deletion
				element.find('.selected_patient_input').remove();
				if(element.hasClass('.check_selected_patient')){
					//alert('ddd');
					element.find('.selected_patient_input').remove();
				}
				}
		}
		else{
			//show bloody table in dialog
			var x2 = data.split('wagonjwawengi');
		//	if(typeof x2[1] === 'undefined'){}
		//	else{
			if(typeof x2[1] !== 'undefined'){
			
				var width_x = $('.get_width').show().width();
				$('.div_shower').dialog({
				dialogClass:'dialog_bg',
				title: 'Patient Search Results',
				height: 500,
				width: width_x,
				modal: true}).html(x2[1]);
				
				$('.selected_pt').click(function() {
					
					var selected_pt = $(this).prev().val();
					alert('selected is  ' + selected_pt);
					  element.append('<input type=hidden name=selected_patient class=selected_patient_input value=' + selected_pt + ' />');
				element.submit();
				$('.div_shower').dialog("close");
				});		
			}
			
			
				//family search
				var x2 = data.split('familymbinge');
			//	if(typeof x2[1] === 'undefined'){}
			//	else{
				if(typeof x2[1] !== 'undefined'){
					var width_x = $('.get_width').show().width();
					$('.div_shower').dialog({
					dialogClass:'dialog_bg',
					title: 'Family Group Search Results',
					height: 500,
					width: width_x,
					modal: true}).html(x2[1]);
					
					$('.selected_pt').click(function() {
						
						var selected_fm = $(this).prev().val();
						//alert('selected is  ' + selected_pt);
						  element.append('<input class=selected_fm type=hidden name=selected_fm  value=' + selected_fm + ' />');
					element.submit();
					element.find('.selected_fm').remove();
					$('.div_shower').dialog("close");
					});		
				}
			
			//show output for one family group serach
			var x2 = data.split('inakwatafamily');
		//	if(typeof x2[1] === 'undefined'){}
		//	else{
			if(typeof x2[1] !== 'undefined'){
				//var width_x = $('.get_width').show().width();
				$('#imwe_family').html(x2[1]);
				
			/*	$('.selected_pt').click(function() {
					
					var selected_fm = $(this).prev().val();
					//alert('selected is  ' + selected_pt);
					  element.append('<input type=hidden name=selected_fm  value=' + selected_fm + ' />');
				element.submit();
				$('.div_shower').dialog("close");
				});	*/	
			}			
		}
		
		});


	
});



//this will show form to edit tplan
$('.edit_tplan').click(function(){
		var edit_tplan = $(this).next().val();
		var width_x = $(this).parent().parent().parent().width();
		var height_y = 780;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$(".div_shower2").empty().css('backgroundColor',' #15212F').dialog({
		title: 'Edit Treatment Plan',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'edit_tplan': edit_tplan });

});
/*
$('.btest').click(function(){
	alert('xxxxxxxxxxxxxxxx');
});*/


$("a.link_clor" ).css({
	"color": "#E1182F",
	"text-decoration": "none",
	"font-weight": "700"
	});
$("a.link_clor" ).on( "mouseenter", function() {
	$( this ).css({
	"color": "#E1182F",
	"text-decoration": "none",
	"font-weight": "700"
	});
	}).on( "mouseleave", function() {
	var styles = {
	"color" : "#EDB90C",
	"text-decoration": "none",
	"font-weight": "700"
	};
	$( this ).css( styles );
});

 //this will edit corrporate cover
 $('.edit_corporate_cover').click(function(){
	var edit_corporate = $(this).next().val();
		var width_x = $(this).parent().parent().width();
		var height_y = 580;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$("#edit_ins_cover").empty().dialog({
		title: 'Edit Procedures covered by Insurance',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'edit_corporate': edit_corporate });
 });
 
 $('#start_date_edit_ins').datepicker( { dateFormat: "yy-mm-dd" , 
								changeMonth: true,
								changeYear: true,
								minDate: 0});
								
  //showinf option int treatment done report
 $('.rtype1').change(function(){
	var val= $(this).val();
	if(val == 'detailed'){
		$('.summarized_sel1').slideUp('fast');
		$('.detailed_td').slideDown('fast');
	}
	else if(val == 'summary'){
		$('.detailed_td').slideUp('fast');
		$('.summarized_sel1').slideDown('fast');
		
	}
 });
 //showinf option int treatment done report
  $('.summarized_type').change(function(){
	var val= $(this).val();
	if(val == 'by_doc'){
		$('.summary_td').slideDown('fast');
		$('.summary_td_doc').slideDown('fast');
	}
	else if(val == 'by_procedure'){
		$('.summary_td_doc').slideUp('fast');
		$('.summary_td').slideDown('fast');
		
	}
 });
 
 //this will edit insurer price for procedures
 $('.ins_price_edit').click(function(){
	//alert('ff');
	var insurer_id_price = $(this).prev().val();
	//alert(invoice_disp_num);
		var width_x = $('#ins_price_ed').parent().width();
		var height_y = 580;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y); .css('backgroundColor',' #15212F').
		$("#ins_price_ed").empty().dialog({
		title: 'INSURANCE PROCEDURE PRICE',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'insurer_id_price': insurer_id_price });
 });  
 
//display invoice added dynamically 
  $('#append_invoice').on('click','.invoice_no',function(){
	//alert('ff');
	var invoice_disp_num = $(this).val();
	//alert(invoice_disp_num);
		var width_x = $('.div_shower2').parent().width();
		var height_y = 580;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$(".div_shower2").empty().css('backgroundColor',' #15212F').dialog({
		title: 'INVOICE: ' + invoice_disp_num,
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'invoice_disp_num': invoice_disp_num });
 }); 
 
 //display quotation added dynamically 
  $('#append_invoice').on('click','.quotation_no',function(){
	//alert('ff');
	var quotation_disp_num = $(this).val();
		var width_x = $('.div_shower2').parent().width();
		var height_y = 580;
		$(".div_shower2").empty().css('backgroundColor',' #15212F').dialog({
		title: 'QUOTATION: ' + quotation_disp_num,
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'quotation_disp_num': quotation_disp_num });
 });
 
 //this will dispaly an quotation
 $('.quotation_no').click(function(){
	//alert('ff');
	var quotation_disp_num = $(this).val();
	//alert(invoice_disp_num);
		var width_x = $('.div_shower2').parent().width();
		var height_y = 580;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$(".div_shower2").empty().css('backgroundColor',' #15212F').dialog({
		title: 'QUOTATION: ' + quotation_disp_num,
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'quotation_disp_num': quotation_disp_num });
 }); 
 
 //this will dispaly an invoice
 $('.invoice_no').click(function(){
	//alert('ff');
	var invoice_disp_num = $(this).val();
	//alert(invoice_disp_num);
		var width_x = $('.div_shower2').parent().width();
		var height_y = 580;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$(".div_shower2").empty().css('backgroundColor',' #15212F').dialog({
		title: 'INVOICE: ' + invoice_disp_num,
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'invoice_disp_num': invoice_disp_num });
 }); 
 
  //this will dispaly an invoice that has been deleted
 $('.invoice_no_deleted').click(function(){
	//alert('ff');
	var invoice_disp_num_deleted = $(this).val();
	//alert(invoice_disp_num);
		var width_x = $(this).parent().parent().width();
		var height_y = 580;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$(".div_shower44").empty().css('backgroundColor',' #15212F').dialog({
		title: 'INVOICE: ' + invoice_disp_num_deleted,
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'invoice_disp_num_deleted': invoice_disp_num_deleted });
 }); 
 
 //this will dispaly an invoice
 $('.div_shower').on('click','.invoice_no',function(){
	//alert('ff');
	var invoice_disp_num = $(this).val();
	//alert(invoice_disp_num);
		var width_x = $('.div_shower2').parent().width();
		var height_y = 580;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$(".div_shower2").empty().css('backgroundColor',' #15212F').dialog({
		title: 'INVOICE: ' + invoice_disp_num,
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'invoice_disp_num': invoice_disp_num });
 }); 
 
  //this will dispaly a prescription
 //$('.div_shower').on('click','.invoice_no',function(){
 $('.div_shower44').on('click','.show_prescription',function(){
 //$('.show_prescription').click(function(){
	//alert('ff');
	var prescription_num = $(this).val();
	//alert(invoice_disp_num);
		var width_x = $('.div_shower2').parent().width();
		var height_y = 580;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$(".dialog_with_tab").empty().css('backgroundColor',' #15212F').dialog({
		title: 'PRESCRIPTION: ' + prescription_num,
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'prescription_num': prescription_num }); 
 });
 
 //this will show prescriptions in re-print report
  $('.show_prescription').click(function(){
 //$('.show_prescription').click(function(){
	//alert('ff');
	var prescription_num = $(this).val();
	//alert(invoice_disp_num);
		var width_x = $('.div_shower2').parent().width();
		var height_y = 580;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$(".dialog_with_tab").empty().css('backgroundColor',' #15212F').dialog({
		title: 'PRESCRIPTION: ' + prescription_num,
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'prescription_num': prescription_num }); 
 });
  //this will show pt invoices in tdone
 $('.tdone-invoice').click(function(e){
	e.preventDefault();
	//alert('ff');
	var tdone_invoice = 'yes';
	//alert(invoice_disp_num);
		var width_x = $('.div_shower').parent().width();
		var height_y = 580;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$(".div_shower").empty().css('backgroundColor',' #15212F').dialog({
		title: 'INVOICES RAISED: ',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'tdone_invoice': tdone_invoice });
 });


 
  //showinf option int treatment done report
 // $('#insured_yes_no').change(function(){
$(document.body).on('change', '.dialog_form  #insured_yes_no', function(e){  
	var val= $(this).val();
	if(val == 'NO'){
		$(this).closest('form').find('.insurer_input').val('').prop('disabled', true);
		$(this).closest('form').find('.pre_smart').val('NO');
	}
	else if(val == 'YES'){
		$(this).closest('form').find('.insurer_input').val('').prop('disabled', false);
		$(this).closest('form').find('.date_picker_no_past').attr("placeholder", "yyyy-mm-dd");
	}
 });
 
//submit a form from a dialog box via jquery
$(document.body).on('submit', '.dialog_form', function(e){
//$('.dialog_form').submit(function(e){
	//e.preventDefault();
	//alert('ffff');
	var action = $(this).attr("action");
	var element = $(this);
	e.preventDefault();
	$(this).attr('disabled', 'disabled');
	/*$('.show_loader').empty().removeClass('success_response').removeClass('error_response').addClass('background_yellow');
	$('.show_loader').dialog({
		title: 'Submitting patient diesease details',
		width: 400,
		modal: true}).append('Loading  ').append('<img src="dental_b/ajax-loader.gif" />').show();*/
	$(this).closest('form').find('.insurer_input').prop('disabled', false);
	$.post("dental_b/", $(this).serialize())
	 .done(function(data) {
	// alert(data);
		//$('.show_loader').dialog('close');
		$('.feedback_dialog').remove();
		$('<div class=feedback_dialog ></div>').insertBefore("#edit_covered_procedure");
		var x = data.split('#');
		//alert(data);
		if(x[0] == "good"){
			//refresh the div to capture new details
			var edit_corporate = x[2];
			$("#edit_covered_procedure").load('dental_b/', {'edit_corporate': edit_corporate });
					
											$(".feedback_dialog").empty()
											.removeClass('error_response hide_element')
											.addClass('success_response')
											.append(x[1])
											.show();
					//	$('html, body').animate({scrollTop: '0px'}, 300);					

		//		}//end else		
		}//end good if
		//this is for editing corprate details e.g.pre-auth needed
		else if(x[0] == "good2"){
			//refresh the div to capture new details
			var edit_corporate2 = x[2];
			$("#edit_covered_procedure").load('dental_b/', {'edit_corporate2': edit_corporate2 });
					
											$(".feedback_dialog").empty()
											.removeClass('error_response hide_element')
											.addClass('success_response')
											.append(x[1])
											.show();
					//	$('html, body').animate({scrollTop: '0px'}, 300);					

		//		}//end else		
		}//end good if
						
		else if(x[0] == "bad"){$('.feedback_dialog').empty()
												.removeClass('success_response hide_element')
												.addClass('error_response')
												.append(x[1])
												.show();
								//$('html, body').animate({scrollTop: '0px'}, 0);									
								}
		
		});


	
});
/*
$('.div_shower31a').on('submit', '.search_form2a', function(e){
	alert('f');
	$('.div_shower31a').off('submit', '.search_form2a');
//$(this).find('.show_spin').append('   <img src="dental_b/ajax-loader-spinner.gif" />');
	//$('.show_spin').append('   <img src="dental_b/ajax-loader-spinner.gif" />');
	e.preventDefault();
	var action = $(this).attr("action");
	$.post("dental_b/", $(this).serialize())
	 .done(function(data) {
		if(action == "#completion"){$('#ui-tabs-6').load("completion/");}
		else if(action == "#diseases"){$('#ui-tabs-5').load("diseases/");}	
		else if(action == "#female-patients"){$('#ui-tabs-4').load("female-patients/");}	
		else if(action == "#medical-information"){$('#ui-tabs-3').load("medical-information/");}		
		else if(action == "#dental-information"){$('#ui-tabs-2').load("dental-information/");}
		else if(action == "#treatment-plan"){$('#ui-tabs-8').load("treatment-plan/");}		
		else if(action == "#treatment-done"){$('#ui-tabs-9').load("treatment-done/");}		
		else if(action == "#examination"){$('#ui-tabs-7').load("examination/");}		
		else if(action == "#contacts"){$('#ui-tabs-1').load("patient-contacts/");}		
		else if(action == "lab_prescription_form"){window.location = "?id=lab-prescription-form";}
	});
	$('.div_shower31a').dialog("close");
});*/

/*
//search for a aprtient and get their details
$('.search_form').submit(function(e){
	//alert('x');
	$(this).find('.show_spin').append('   <img class=spining_image src="dental_b/ajax-loader-spinner.gif" />');
	//$('.show_spin').append('   <img src="dental_b/ajax-loader-spinner.gif" />');
	e.preventDefault();
	var action = $(this).attr("action");
	$.post("dental_b/", $(this).serialize())
	 .done(function(data) {
		//alert("Data Loaded: " + data);
	var	x = data.split('muwaumbinge');

	
	if(x.length  == 1){
		//alert('1111');
		if(action == "#completion"){$('#ui-tabs-6').load("completion/");}
		else if(action == "#diseases"){$('#ui-tabs-5').load("diseases/");}	
		else if(action == "#female-patients"){$('#ui-tabs-4').load("female-patients/");}	
		else if(action == "#medical-information"){$('#ui-tabs-3').load("medical-information/");}		
		else if(action == "#dental-information"){$('#ui-tabs-2').load("dental-information/");}
		else if(action == "#treatment-plan"){$('#ui-tabs-8').load("treatment-plan/");}		
		else if(action == "#treatment-done"){$('#ui-tabs-9').load("treatment-done/");}		
		else if(action == "#examination"){$('#ui-tabs-7').load("examination/");}		
		else if(action == "#contacts"){$('#ui-tabs-1').load("patient-contacts/");}		
		else if(action == "lab_prescription_form"){window.location = "?id=lab-prescription-form";}

	}
	//now show table for multiple patient serach
	else if(x.length  == 2){
		var width_x = $(".div_shower2").parent().width();
		var height_y = 580;
		$(".div_shower31a").empty().css('backgroundColor',' #15212F').dialog({
		title: 'Patient Search Results',
		height: height_y,
		width: width_x,
		modal: true}).append(x[1]);
		$(this).find('.show_spin').empty();
		
		$('.div_shower31a').on('click', '.selected_pt2', function(e){
			e.preventDefault();
			$('.div_shower31a').off('click', '.selected_pt2');
			var action = $(this).parent().attr("action");
			
			//var selected_pt = $(this).parent().prev().text();
			//alert('selected is  ' + selected_pt);
			//$('.sc').val('patient_number');
			//$('.sv').val(selected_pt);
			$.post("dental_b/", $(this).parent().serialize())
	 .done(function(data) {
		if(action == "#completion"){$('#ui-tabs-6').load("completion/");}
		else if(action == "#diseases"){$('#ui-tabs-5').load("diseases/");}	
		else if(action == "#female-patients"){$('#ui-tabs-4').load("female-patients/");}	
		else if(action == "#medical-information"){$('#ui-tabs-3').load("medical-information/");}		
		else if(action == "#dental-information"){$('#ui-tabs-2').load("dental-information/");}
		else if(action == "#treatment-plan"){$('#ui-tabs-8').load("treatment-plan/");}		
		else if(action == "#treatment-done"){$('#ui-tabs-9').load("treatment-done/");}		
		else if(action == "#examination"){$('#ui-tabs-7').load("examination/");}		
		else if(action == "#contacts"){$('#ui-tabs-1').load("patient-contacts/");}		
		else if(action == "lab_prescription_form"){window.location = "?id=lab-prescription-form";}
	});
			
		$('.div_shower31a').dialog("close");
		});
	}
	});
});*/

//search for a aprtient and get their details
$('.search_form').submit(function(e){
	//alert('x');
	$(this).find('.show_spin').append('   <img class=spining_image src="dental_b/ajax-loader-spinner.gif" />');
	//$('.show_spin').append('   <img src="dental_b/ajax-loader-spinner.gif" />');
	e.preventDefault();
	var action = $(this).attr("action");
	var tab_id= $(this).closest('.ui-tabs-panel').attr("id");
	//alert('li_var is ' + li_var);
	$.post("dental_b/", $(this).serialize())
	 .done(function(data) {
		//alert("Data Loaded: " + data);
	var	x = data.split('muwaumbinge');

	
	if(x.length  == 1){
		//alert('1111');
		/*if(action == "#completion"){$('#ui-tabs-6').load("completion/");}
		else if(action == "#diseases"){$('#ui-tabs-5').load("diseases/");}	
		else if(action == "#female-patients"){$('#ui-tabs-4').load("female-patients/");}	
		else if(action == "#medical-information"){$('#ui-tabs-3').load("medical-information/");}		
		else if(action == "#dental-information"){$('#ui-tabs-2').load("dental-information/");}
		else if(action == "#treatment-plan"){$('#ui-tabs-8').load("treatment-plan/");}		
		else if(action == "#treatment-done"){$('#ui-tabs-9').load("treatment-done/");}		
		else if(action == "#examination"){$('#ui-tabs-7').load("examination/");}		
		else if(action == "#contacts"){$('#ui-tabs-1').load("patient-contacts/");}	*/	
		if(action == "#completion"){$('#'+ tab_id ).load("completion/");}
		else if(action == "#diseases"){$('#'+ tab_id ).load("diseases/");}	
		else if(action == "#female-patients"){$('#'+ tab_id ).load("female-patients/");}	
		else if(action == "#medical-information"){$('#'+ tab_id ).load("medical-information/");}		
		else if(action == "#dental-information"){$('#'+ tab_id ).load("dental-information/");}
		else if(action == "#treatment-plan"){$('#'+ tab_id ).load("treatment-plan/");}		
		else if(action == "#treatment-done"){$('#'+ tab_id ).load("treatment-done/");}		
		else if(action == "#examination"){$('#'+ tab_id ).load("examination/");}		
		else if(action == "#contacts"){$('#'+ tab_id ).load("patient-contacts/");}
		else if(action == "lab_prescription_form"){window.location = "?id=lab-prescription-form";}

	}
	//now show table for multiple patient serach
	else if(x.length  == 2){
		var width_x = $(".div_shower2").parent().width();
		var height_y = 580;
		$(".div_shower31a").empty().css('backgroundColor',' #15212F').dialog({
		title: 'Patient Search Results',
		height: height_y,
		width: width_x,
		modal: true}).append(x[1]);
		$(this).find('.show_spin').empty();
		
		$('.div_shower31a').on('click', '.selected_pt2', function(e){
			e.preventDefault();
			$('.div_shower31a').off('click', '.selected_pt2');
			var action = $(this).parent().attr("action");
			
			//var selected_pt = $(this).parent().prev().text();
			//alert('selected is  ' + selected_pt);
			//$('.sc').val('patient_number');
			//$('.sv').val(selected_pt);
			$.post("dental_b/", $(this).parent().serialize())
	 .done(function(data) {
		if(action == "#completion"){$('#'+ tab_id ).load("completion/");}
		else if(action == "#diseases"){$('#'+ tab_id ).load("diseases/");}	
		else if(action == "#female-patients"){$('#'+ tab_id ).load("female-patients/");}	
		else if(action == "#medical-information"){$('#'+ tab_id ).load("medical-information/");}		
		else if(action == "#dental-information"){$('#'+ tab_id ).load("dental-information/");}
		else if(action == "#treatment-plan"){$('#'+ tab_id ).load("treatment-plan/");}		
		else if(action == "#treatment-done"){$('#'+ tab_id ).load("treatment-done/");}		
		else if(action == "#examination"){$('#'+ tab_id ).load("examination/");}		
		else if(action == "#contacts"){$('#'+ tab_id ).load("patient-contacts/");}		
		else if(action == "lab_prescription_form"){window.location = "?id=lab-prescription-form";}
	});
			
		$('.div_shower31a').dialog("close");
		});
	}
	});
});

$('.div_shower31a').bind('dialogclose', function(event) {
     $('.spining_image').css({display: "none"});//alert('closed');
 });

//$(".teeth_div").css({display: "none"});$('.select_user')
/*
//this will clear none insured payments previous patient search
$('.clear_pid2').click(function(e){
		e.preventDefault();
		var clear_pid2 = 'yes';
		var form_data = {clear_pid2: clear_pid2}
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		//alert(" An Error occured, unable to complete form submission");
		//e.preventDefault();
		},
		success: function(data) {
		alert(data);
		},
		complete: function() {
		}
		});
		//alert('fff');
}); */

//this will show form to add invoices to dispatch note
$('.add_dispatch').click(function(){
	$(".show_to_undispatch").css({display: "none"});
	$(this).removeClass('.add_dispatch');
	$('.add_inv_to_disp').show();
});


//this will clear a form
$('.clear_form').submit(function(e){
	e.preventDefault();
	var action = $(this).attr("action");
	var form_class = $('.patient_form');
	//alert('1');
	$.post("dental_b/", { clear_form: "clear_form" })
	 .done(function(data) {
	//	alert('data is ' + data);
	
	if(action == "#completion"){$('#ui-tabs-6').load("completion/index.php");}
	else if(action == "#diseases"){$('#ui-tabs-5').load("diseases/index.php");}		
	else if(action == "#female-patients"){$('#ui-tabs-4').load("female-patients/index.php");}	
	else if(action == "#medical-information"){$('#ui-tabs-3').load("medical-information/index.php");}
	else if(action == "#dental-information"){$('#ui-tabs-2').load("dental-information/index.php");}	
	else if(action == "#treatment-plan"){$('#ui-tabs-8').load("treatment-plan/index.php");}		
	else if(action == "#examination"){$('#ui-tabs-7').load("examination/index.php");}	
	else if(action == "#contacts"){$('#ui-tabs-1').load("patient-contacts/");}			
	});
	//alert('now reloading tab');
	//var current_index = $("#tabs").tabs("option","selected");
	//$("#tabs").tabs('load',current_index);
	//alert('fff');
});
		/*$(".admin_class").hover(function(){
			$("#admin_menu").toggle("fast");
			});*/
/*
$(".admin_class").click(function(){
$(".servicesdropped").toggle("fast");
});*/
		$(".dropdown_2columns").hover(function(){
			$(this).prev().css('backgroundColor','#121923');
			
		},function(){
				$(this).prev().css('backgroundColor','#C00001');
		});
		
		$(".li_div3").hover(function(){
			$(this).parent().prev().css({backgroundColor: "#D9D9D9",color: "#0C121B"});
			
		},function(){
				$(this).parent().prev().css({backgroundColor: "#121923",color: "#fff"});
		});		
		


/*
		$("admin_class").hover(function(){
		$(".servicesdropped").css({visibility: "visible",display: "none"}).show(400);
		},function(){
		$(".servicesdropped").css({visibility: "hidden"});
		});	*/		
		//this is for menu drop down
		/*$(" #nav1 ul ").css({display: "none"}); // Opera Fix
		$(".nav_tabs ul ").css({display: "none"}); // Opera Fix
		$(" #nav1 li").hover(function(){
		$(this).find('ul:first').css({visibility: "visible",display: "none"}).show(400);
		},function(){
		$(this).find('ul:first').css({visibility: "hidden"});
		});

		//for mulit level menu
		$(" #nav1 li ul").hover(function(){
		$(this).find('ul:first').css({visibility: "visible",display: "none"}).show(400);
		},function(){
		$(this).find('ul:first').css({visibility: "hidden"});
		});	*/
		
/*	//$('.tab_form').submit(function(){alert('ff');});	
$('.tab_form').submit(function(e){

var var_form = e;
var var_tabs = $('#tabs').tabs();
var set_tab_id = var_tabs.tabs('option', 'selected'); 
		var form_data = {set_tab_id: set_tab_id}
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert("g An Error occured, unable to complete form submission");
		e.preventDefault();
		},
		success: function(data) {
		//alert(data);
		//	if (data == 'set') {var_form.submit();}
		},
		complete: function() {
		}
		});	
});*/
	//var pathname = $(this).attr("action");
	//alert('ddd pathname is ' + pathname);
	/*
$('.tab_link').click(function(){	
	var pathname = document.URL;
	var	x = pathname.split('?');
	alert ('path is ' + x[0] + ' part 2 is ' + x[1]);
		if(x[1] == 'open=patient'){
			alert('xxxxx');
			 $("#tabs").tabs({
				// cache: true,
				 active: 4,
				beforeLoad: function( event, ui ) {
					ui.jqXHR.error(function() {
					ui.panel.html("ERROR: Unable to load content");
					});
				}

			});
	}

});	*/
/*
 $("#tabs").tabs({
 			   select: function(event, ui) {
						var selectedTabTitle = $(ui.tab).text();
						var url= $(ui.tab).attr("href")
						//alert('url is ' + urlis);
						if(url == '#patient-contacts'){
							var load_customer_invoice ='yes';
							$('.patient-contacts').empty();
							//$('.patient-contacts').append('<img src="inventory_jquery/ajax-loader.gif" />');
							$('.patient-contacts').load('patient-contacts/index.php'); 
						}
						else if(url == '#dental-information'){
							var load_customer_contacts ='yes';
							$('.dental-information').empty();
							//$('.dental-information').append('<img src="inventory_jquery/ajax-loader.gif" />');
							$('.dental-information').load('dental-information/index.php'); 
						}
						}
 
 });*/

 //$("table tbody ").delegate("tr", "click", function() {
 
$('table tbody').on('click', 'tr', function(){
	//check if this is allocations table and apply diffenre  background class
	if($(this).parent().parent().hasClass('allocations')){ 
		$(this).toggleClass("heading_bg_click");
		$(this).siblings().removeClass("heading_bg_click");
	}
	else {  
		$(this).toggleClass("table_row_click_bg");
		$(this).children('td.td_div_holder').children('.tplan_table').toggleClass("table_row_click_bg");
		$(this).siblings().removeClass("table_row_click_bg");}
		$(this).siblings().children('td.td_div_holder').children('.tplan_table').removeClass("table_row_click_bg");
});



$('.procedure_container2').on('click', '#undo_edit_invoice', function(){
//$('#undo_edit_invoice').click(function(){
	
	var edit_invoice= $(this).prev().val();
	//alert('edit invoice is ' + edit_invoice);
	$(".div_shower2").empty().load('dental_b/', {'edit_invoice': edit_invoice });
});
//this is for showing dialog to edit invoice
$('.edit_inv_div').on('click', '.edit_invoice', function(e){
	e.preventDefault();
	var edit_invoice= $(this).attr("href");
	var width_x = $(this).parent().width();
	var height_y = 780;
	$(".div_shower2").empty().css('backgroundColor',' #15212F').dialog({
		title: 'Edit Invoice',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'edit_invoice': edit_invoice });
	//alert('token is ' + token);
});

//this will check if amount paid is less than cash balance due and ask pt for date of next payment
$('.self_amount').blur(function(){
check_pay_vs_balance();

});


//this is for showing dialog to edit invoice from menu in invoices
$('.edit_invoice2').click(function(){
	
	var edit_invoice= $(this).next().val();
	var width_x = $(".div_shower2").parent().width();
	var height_y = 780;
	//alert('val is ' + edit_invoice);
	//exit;
	$(".div_shower2").empty().css('backgroundColor',' #15212F').dialog({
		title: 'Edit Invoice',
		height: height_y,
		width: width_x,
		close: function(event, ui){ 
			//alert('tttttttttt');
			var pathname = window.location.href;
			var data = pathname.split('?');
			if(data[1] == 'id=edit-invoice'){
					$('[name="search_by"]').val('patient_number');
					var pt = $('.spt').text();
					//alert('pt is ' + pt);
					$('[name="search_ciretia"]').val(pt);
					$('.find_pt1').click();
					//window.location.reload();
			}
		//top.opener.location.reload(true); 
		},
		modal: true}).load('dental_b/', {'edit_invoice': edit_invoice });
	//alert('token is ' + token);
});

//this is for showing prescriptions
$('.prescribe').click(function(e){
	e.preventDefault();
	var prescribe= $(this).attr("href");
	var width_x = $(this).parent().width();
	var height_y = 780;
	$(".div_shower44").empty().css('backgroundColor',' #15212F').dialog({
		title: 'Prescriptions',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'prescribe': prescribe });
	//alert('token is ' + token);
});

//this is for showing treatment history in tdone
$('.treatment_history').click(function(e){
	e.preventDefault();
	var treatment_history= 'yes';
	//var width_x = $(this).parent().width();
	var width_x = $('.div_shower').parent().width();
	var height_y = 780;
	$(".div_shower44").empty().css({'backgroundColor':'#15212F',
									'padding':'0',}).dialog({
		title: 'Treatment History',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'treatment_history': treatment_history });
	//alert('token is ' + token);
});
		


		


//this is for showing cadcam in tdone
$('.tdone-cadcam').click(function(e){
	e.preventDefault();
	var tdone_cadcam = 'yes';
	var width_x = $(this).parent().width();
	var height_y = 780;
	$(".dialog_with_tab").empty()
		.css('backgroundColor',' #15212F').dialog({
		title: 'CADCAM',
		height: height_y,
		width: width_x,
		 open: function( event, ui ) {},
		modal: true}).load('dental_b/', {'tdone_cadcam': tdone_cadcam },function() {
  				$('#cadcam_tabs2').tabs({
					beforeLoad: function( event, ui ) {
					//alert('tab is ' + check_tab_to_go_to());
						ui.jqXHR.error(function() {
						ui.panel.html("ERROR: Unable to load content");
						});
					},
				});
				$('#cadcam_tabs2 .ui-tabs-nav li').removeClass('ui-corner-top');
				$('#cadcam_tabs2 .ui-tabs-nav').removeClass('ui-corner-all');

});
		


});
		

//this is for showing prescriptions
$('.pt_statement').click(function(e){
	e.preventDefault();
	var pt_statement= 'yes';
	var width_x = $(this).parent().width();
	var height_y = 780;
	$(".div_shower44").empty().css('backgroundColor',' #15212F').dialog({
		title: 'Statement',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'pt_statement': pt_statement });
	//alert('token is ' + token);
});

//this is for creating new follow up date in t done
$('.follow_up').click(function(e){
	e.preventDefault();
	var follow_up= 'yes';
	var width_x = $(this).parent().width();
	var height_y = 450;
	$('html, body').animate({scrollTop: '0px'}, 0);	
	$(".div_shower44").empty().css('backgroundColor',' #15212F').dialog({
		title: 'Follow Up',
		height: height_y,
		width: width_x,
		position: 'top',
		modal: true}).load('dental_b/', {'follow_up': follow_up });
	$('.div_shower44').animate({scrollTop: '0px'}, 0);	
});


//this will serach criteria in payment deletion
$('.src').change(function(){
	var selec = $(this).val();
	if(selec == 'patient_number' || selec == 'first_name' || selec == 'middle_name' || selec == 'last_name'){
		$('.search_by_date').slideUp('fast');
		$('.search_by_patient').slideDown('fast');
	}
	else if(selec == 'date_range'){
		$('.search_by_patient').slideUp('fast');
		$('.search_by_date').slideDown('fast');
	} 
});


//this is for showing pt balance from waiver approvals
$('.pt_statement_a').click(function(e){
	e.preventDefault();
	var pt_statement_a = $(this).prev().val();
	var width_x = $('.waiver_table_row2').parent().width();
	if (width_x <= 0 ){width_x = $(this).parent().parent().width();}
	var height_y = 780;
	$(".div_shower44").empty().css('backgroundColor',' #15212F').dialog({
		title: 'Statement',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'pt_statement_a': pt_statement_a });
	//alert('token is ' + token);
});

//this is for cancelling a prescription
$('.div_shower44').on('click', '.prescribe_cancel', function(e){
	var prescribe ='';
	$(".div_shower44").empty().load('dental_b/', {'prescribe': prescribe });
});

//$('.div_shower').unbind('click');
//this is for showing new form for prescription
$('.div_shower44').on('click', '.new_prescription2', function(e){
//$('.new_prescription2').on('click',  function(e){
//e.stopImmediatePropagation();

//alert('R422');
//$('.new_prescription2').click(function(e){
	$(".new_prescribe").slideDown('fast');
	
});
		
$('.pt_contact_shower').on('change', '.new_family_action', function(){		
//$('.new_family_action').change(function(){
	var val1 = $(this).val();
	if(val1 == 'new'){
		$('.new_fam_grp').slideDown('fast');
		$('.old_fam_grp').slideUp('fast').find('input:text, select').val('');
		$('#imwe_family.old_fam_grp').empty();
	}
	else if(val1 != 'new'){
		$('.new_fam_grp').slideUp('fast').find('input:text, select').val('');
		$('.old_fam_grp').slideDown('fast');
	}
})			
			
//this is for enabling/disableing pt prescription inputs
$('.div_shower44').on('change', '.drug_name', function(){
//$('.pay_method').change(function(){
		//var selec = $(this).children("option:selected").text(); 
		//alert(selec);
		//empty other fields when payment method
	//	$(this).parent().next().children('.tplan_cost').val('');
	//	$(this).parent().next().next().children('.tplan_discount').val('');
	//	get_treatment_plan_total_cost();
	//this will ensure that in tplan only aviallable fields are not disabled
			$(this).parent().next().children('.drug_details').val('');		
			$(this).parent().next().next().children('.drug_presc_type').val('');		
			$(this).parent().next().next().next().children('.drug_price').val('');			
		if( $(this).val() !=''){
			$(this).parent().next().children('.drug_details').prop('disabled', false);		
			$(this).parent().next().next().children('.drug_presc_type').prop('disabled', false);
			$(this).parent().next().next().next().children('.drug_price').prop('disabled', true);					
		}
		else{
			$(this).parent().next().children('.drug_details').prop('disabled', true);		
			$(this).parent().next().next().children('.drug_presc_type').prop('disabled', true);
			$(this).parent().next().next().next().children('.drug_price').prop('disabled', true);					
		} 
});	

//this is for showing fields in cash balance report
$('.balance_range').change(function(){
	if($(this).val() == 'no_range'){
		$('.balance_input_range').slideUp('fast');
		$('.balance_input').slideUp('fast');
	}
	else if($(this).val() == 'greater_than' || $(this).val() == 'less_than' ){
		$('.balance_input_range').slideUp('fast');
		$('.balance_input').slideDown('fast');
	}
	else if($(this).val() == 'range'){
		$('.balance_input').slideUp('fast');
		$('.balance_input_range').slideDown('fast');
	}
	
});

//will check all pts to send email to into pt cash balances
$('.check_all_email').click(function () {
   $(this).closest('form').find(':checkbox').prop('checked',true);

 // alert('ff');
});

//prevenet parent menu from going nowehere when clicked
$('.parent_menu').click(function(e){
	e.preventDefault();
});
//get recommended selleing price of drug
$('.div_shower44').on('change', '.drug_presc_type', function(){
//$('.pay_method').change(function(){
		var drug_id = $(this).parent().prev().prev().children('.drug_name').val(); 
		//alert(drug_id);
		var selec_text = $(this).children("option:selected").text(); 
		//alert(selec_text);
		$(this).parent().next().children('.drug_price').val('');	
		if( selec_text == 'Prescribe'){
			$(this).parent().next().children('.drug_price').prop('disabled', true);					
		}
		else if( selec_text == 'Sell'){
			$(this).parent().next().children('.drug_price').prop('disabled', false);					
			//get drug price
		
			var form_data = {drug_id: drug_id}
			element = $(this);
			$.ajax({
			type: "POST",
			url: "dental_b/",
			data: form_data,
			error: function() {
			alert(" An Error occured, unable to get selling price for prescription druga");
			e.preventDefault();
			},
			success: function(data) {
			//alert('data is ' + data);
				element.parent().next().children('.drug_price').val(data);	
			},
			complete: function() {
			}
			});
		}

});

//this will add new prescription row
$('.div_shower44').on('click', '.add_drug', function(){
//$('.add_drug').click(function(){
	//var extra_procedure = $('.procedure_count').last().text();
	//alert ('i is ' + i);
	//alert('ff');
		var add_drug = 'yes';
		var form_data = {add_drug: add_drug}
		element = $(this);
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to add extra prescription drug");
		e.preventDefault();
		},
		success: function(data) {
	//	alert('data is ' + data);
			$('.presc_container').append(data);
		},
		complete: function() {
		}
		});	
	//$('.procedure_container').append().load('dental_b/', {'extra_procedure': extra_procedure });;
});
$('.insurance_column').css('backgroundColor','red');

//this is for adding pt to family
$('#family_div').on('click', '.new_family', function(){
//$('.new_family').click(function(){
		//var edit_tplan = $(this).next().val();
		var width_x = $('.pt_contact_shower').parent().width();
		var new_family = 'yes';
		var height_y = 350;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$(".pt_contact_shower").empty().css('backgroundColor',' #15212F').dialog({
		title: 'Family Group',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'new_family': new_family });

});
 
 //highlight textfields when mouse hover the row containing them
 $('tr').hover(function(){$(this).find('input, select').addClass('text_field_highlight'); },
	function(){$(this).find('input, select').removeClass('text_field_highlight'); }
 );
 
  //put background color on tr with css table inside
 $('tr.has_css_div').hover(function(){
	if($(this).hasClass('table_row_click_bg')){}
	else{$(this).find('.tplan_table').addClass('background_for_div_in_td'); }
	},
	function(){
		if($(this).hasClass('table_row_click_bg')){}
		else{$(this).find('.tplan_table').removeClass('background_for_div_in_td'); }
	}
 );
 
 /*//this will remove borders from td that have css div ctable
 $('table.ecr1 tr.has_css_div td.td_div_holder').hover(function(){$(this).find('.tplan_table').addClass('background_for_div_in_td'); },
	function(){$(this).find('.tplan_table').removeClass('background_for_div_in_td'); }
 );*/
 //this will reprint a receipt
 $('.reprint_receipt').click(function(){
	var receipt1 = $(this).prev().val();
	//alert(receipt1);
	var width_x = $('.div_shower').parent().width();
	$('.div_shower').empty().css('backgroundColor',' #15212F').dialog({
		title: 'RECEIPT',
		height: 500,
		width: width_x,
		modal: true}).load('dental_b/', {'receipt1': receipt1 });
					
 });

 
 
//this is for styling the tabs for some reason it's not working from css file #15212F'
$("div.ui-tabs-panel").css('padding','5px 0px');
$("div.ui-tabs-panel").css('backgroundColor',' #15212F');
$("div.ui-tabs-panel").css('border','0px');
//$("div.ui-widget-content").css('border','0px');
//$("div.ui-tabs").css('padding','0px');
//$("div.ui-widget-content").css('color','#ffffff');
$("div.ui-widget-content").addClass("ui-widget-content_custom");
//$("div.ui-tabs-panel").addClass("ui-tabs-panel_custom");
$("div.ui-#tabs").addClass("ui-tabs_custom");
$('#tabs .ui-tabs-nav li.ui-state-default a, #cadcam_tabs .ui-tabs-nav li.ui-state-default a').css('color','#000000');
$('#tabs .ui-tabs-nav li.ui-state-default , #cadcam_tabs .ui-tabs-nav li.ui-state-default').css({'backgroundColor':' #D9D9D9',
											'border':'none',
											
											'border-left':'1px solid #ffffff',
											});
$('#tabs .ui-tabs-nav li,  #cadcam_tabs .ui-tabs-nav li ,  #cadcam_tabs3 .ui-tabs-nav li ,  #cadcam_tabs4 .ui-tabs-nav li ').removeClass('ui-corner-top');
$('#tabs .ui-tabs-nav, #cadcam_tabs .ui-tabs-nav, #cadcam_tabs3 .ui-tabs-nav, #cadcam_tabs4 .ui-tabs-nav').removeClass('ui-corner-all');

$('#tabs .ui-tabs-nav li.ui-state-active, #cadcam_tabs .ui-tabs-nav li.ui-state-active').css('backgroundColor','#15212F');//css('backgroundColor','#15212F');
//$('#cadcam_tabs33 .ui-tabs-nav li.ui-state-active').css('backgroundColor','#FFFFFF');//css('backgroundColor','#15212F');
//$('#cadcam_tabs4 .ui-tabs-nav li.ui-state-active').css('backgroundColor','#FFFFFF');//css('backgroundColor','#15212F');


$('#tabs .ui-tabs-nav li.ui-state-active a, #cadcam_tabs .ui-tabs-nav li.ui-state-active a').css('color','#FFFFFF');
$('#tabs .ui-tabs-nav li.ui-state-default:hover ,#cadcam_tabs .ui-tabs-nav li.ui-state-default:hover').css('color','#FFFFFF');
/*$('#tabs .ui-tabs-nav li.ui-state-active').css('border','#15212F');
,
											'border-left':'1px solid #15212F',
											'border-bottom':'1px solid #15212F'
*/
$('.tab_link:hover').css('color','#FFFFFF');
//	$(document.body).on('change', '.product_id_quote', function(){
	//this is for datepicket
$('.date_picker').datepicker( { dateFormat: "yy-mm-dd" , 
								changeMonth: true,
								changeYear: true});	
$('.div_shower44').on('focus','.date_picker',function(){
	$(this).datepicker( { dateFormat: "yy-mm-dd" , 
								changeMonth: true,
								changeYear: true});	
							//	alert('fd');
});		
$('.div_shower44, .next_payment_div').on('focus','.date_picker_no_past',function(){
	 //this will disabel past dates in date_picker
	 $(this).datepicker( { dateFormat: "yy-mm-dd" , 
									changeMonth: true,
									changeYear: true,
									minDate: 0});
});								

//this will show mpesa/visa/chequre number for cadcam referals payments
$('#cadcam_tabs3').on('change','.payment_type',function(){
	var selec = $(this).children("option:selected").text(); 
	//alert(selec);
    if(selec == 'Mpesa' ) {
        $('.mpesa_number').show();
		$('.visa_number').css({display: "none"});	
		$('.cheque_number').css({display: "none"});	
		$('.waiver_reason').css({display: "none"});	
		$('.eft_number').css({display: "none"});
		$('.credit_transfer').css({display: "none"});		
    }
	else if(selec == 'VISA' ) {
        $('.mpesa_number').css({display: "none"});	
		$('.visa_number').show();
		$('.cheque_number').css({display: "none"});	
		$('.waiver_reason').css({display: "none"});	
		$('.eft_number').css({display: "none"});
		$('.credit_transfer').css({display: "none"});		
    }
	else if(selec == 'Cheque' ) {
        $('.mpesa_number').css({display: "none"});	
		$('.visa_number').css({display: "none"});	
		$('.cheque_number').show();
		$('.waiver_reason').css({display: "none"});	
		$('.eft_number').css({display: "none"});	
		$('.credit_transfer').css({display: "none"});
    }
	else if(selec == 'Waive' ) {
        $('.mpesa_number').css({display: "none"});	
		$('.visa_number').css({display: "none"});	
		$('.cheque_number').css({display: "none"});	
		$('.waiver_reason').show();
		$('.eft_number').css({display: "none"});
		$('.credit_transfer').css({display: "none"});
    }
	else if(selec == 'EFT' ) {
        $('.mpesa_number').css({display: "none"});	
		$('.visa_number').css({display: "none"});	
		$('.cheque_number').css({display: "none"});	
		$('.waiver_reason').css({display: "none"});
		$('.eft_number').show();
		$('.credit_transfer').css({display: "none"});
    }	
	else if(selec == 'Credit Transfer' ) {
        $('.mpesa_number').css({display: "none"});	
		$('.visa_number').css({display: "none"});	
		$('.cheque_number').css({display: "none"});	
		$('.waiver_reason').css({display: "none"});
		$('.eft_number').css({display: "none"});
		var ninye1 = $('#token_ninye').val();
		//alert('ninye1 is ' + ninye1)
		$('.credit_transfer').load('dental_b/', {'ninye1': ninye1 }).show();
    }	
	else{
        $('.mpesa_number').css({display: "none"});	
		$('.visa_number').css({display: "none"});	
		$('.cheque_number').css({display: "none"});	
		$('.waiver_reason').css({display: "none"});	
		$('.eft_number').css({display: "none"});
		$('.credit_transfer').css({display: "none"});		
    }	
});	
								
//this will show mpesa/visa/chequre number for payments
$('.payment_type').change(function(){
	var selec = $(this).children("option:selected").text(); 
	if(selec == 'Mpesa' || selec == 'VISA' || selec == 'Cheque' || selec == 'Cash' ||  selec == 'Credit Transfer'  ){
	check_pay_vs_balance();
	}
	else{$('.next_payment_div').empty().slideUp('fast');}
	//alert(selec);
    if(selec == 'Mpesa' ) {
        $('.mpesa_number').show();
		$('.visa_number').css({display: "none"});	
		$('.cheque_number').css({display: "none"});	
		$('.waiver_reason').css({display: "none"});	
		$('.eft_number').css({display: "none"});
		$('.credit_transfer').css({display: "none"});		
    }
	else if(selec == 'VISA' ) {
        $('.mpesa_number').css({display: "none"});	
		$('.visa_number').show();
		$('.cheque_number').css({display: "none"});	
		$('.waiver_reason').css({display: "none"});	
		$('.eft_number').css({display: "none"});
		$('.credit_transfer').css({display: "none"});		
    }
	else if(selec == 'Cheque' ) {
        $('.mpesa_number').css({display: "none"});	
		$('.visa_number').css({display: "none"});	
		$('.cheque_number').show();
		$('.waiver_reason').css({display: "none"});	
		$('.eft_number').css({display: "none"});	
		$('.credit_transfer').css({display: "none"});
    }
	else if(selec == 'Waive' ) {
        $('.mpesa_number').css({display: "none"});	
		$('.visa_number').css({display: "none"});	
		$('.cheque_number').css({display: "none"});	
		$('.waiver_reason').show();
		$('.eft_number').css({display: "none"});
		$('.credit_transfer').css({display: "none"});
    }
	else if(selec == 'EFT' ) {
        $('.mpesa_number').css({display: "none"});	
		$('.visa_number').css({display: "none"});	
		$('.cheque_number').css({display: "none"});	
		$('.waiver_reason').css({display: "none"});
		$('.eft_number').show();
		$('.credit_transfer').css({display: "none"});
    }	
	else if(selec == 'Credit Transfer' ) {
        $('.mpesa_number').css({display: "none"});	
		$('.visa_number').css({display: "none"});	
		$('.cheque_number').css({display: "none"});	
		$('.waiver_reason').css({display: "none"});
		$('.eft_number').css({display: "none"});
		var ninye1 = $('#token_ninye').val();
		//alert('ninye1 is ' + ninye1)
		$('.credit_transfer').load('dental_b/', {'ninye1': ninye1 }).show();
    }	
	else{
        $('.mpesa_number').css({display: "none"});	
		$('.visa_number').css({display: "none"});	
		$('.cheque_number').css({display: "none"});	
		$('.waiver_reason').css({display: "none"});	
		$('.eft_number').css({display: "none"});
		$('.credit_transfer').css({display: "none"});		
    }	
});									
								
$('.role_priv_check').css({display: "none"});							
$('.select_user').css({display: "none"});	
$('#role_privileges').css({display: "none"});	
$('#individual_privileges').css({display: "none"});	
$('#show_surgery').css({display: "none"});		
			
//this will show users when adding a user
$('.add_user_action').change(function(){
//$(".tooth_checkbox").change(function() {
	//alert($(this).val());
    if($(this).val() == 'edit_user' || $(this).val() == 'edit_role' ) {
        $('.select_user').slideDown();
    }
	else {
        $('.select_user').slideUp();
    }
});	
		
//this will show surgery when editing waiting list
$('.edit_type').change(function(){
//$(".tooth_checkbox").change(function() {
	//alert($(this).val());
    if($(this).val() == 'change_surgery') {
        $('#show_surgery').slideDown();
    }
	else {
       $('#show_surgery').slideUp();
    }
});			
		
//this will select action type when allocating patients
$('.allocate_action').change(function(){
//$(".tooth_checkbox").change(function() {
	//alert($(this).val());
    if($(this).val() == 'add'){
		$('#add_to_waiting_list').slideDown();
        $('#edit_waiting_list').slideUp();
    }
	else if($(this).val() == 'edit'){
		$('#add_to_waiting_list').slideUp();
        $('#edit_waiting_list').slideDown();
    }
});	



//this will select registered or unregistered patient when allocating a patient to a surgery
$('.allocate_patient_type').change(function(){
//$(".tooth_checkbox").change(function() {
	//alert($(this).val());
    if($(this).val() == 'registered'){
		$('#allocate_registered').slideDown();
        $('#allocate_unregistered').slideUp();
    }
	else if($(this).val() == 'unregistered'){
		$('#allocate_registered').slideUp();
        $('#allocate_unregistered').slideDown();
    }
});
		
//this will role or individual privilege assignment
$('.privilege_type').change(function(){
   if($(this).val() == 'individual' ) {
		$('.role_priv_check').empty().css({display: "none"});;
        $('#role_privileges').css({display: "none"});
		$('#individual_privileges').show();
		//check if the user has any roles assigned
		var check_for_roles = 'check_for_roles'; 
		var form_data = {check_for_roles: check_for_roles}
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to check for user roles");
		e.preventDefault();
		},
		success: function(data) {
			//alert(data);
			if (data == 'has_role') {$('.role_priv_check').addClass('error_response')
												.append('This user has at least one role assigned, the role(s) will be lost if privileges are granted individually')
												.show();}
		},
		complete: function() {
		}
		});

												
    }
	else if($(this).val() == 'role' ) {
		//check if the user has any privileges assigned
		$('.role_priv_check').empty().css({display: "none"});;
		var check_for_individual_privileges = 'check_for_individual_privileges'; 
		var form_data = {check_for_individual_privileges: check_for_individual_privileges}
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to check for user privileges");
		e.preventDefault();
		},
		success: function(data) {
			//alert(data);
			if (data == 'has_privilege') {$('.role_priv_check').addClass('error_response')
												.append('This user has at least one individual privilege assigned, the privilege(s) will be lost if privileges are granted by role(s)')
												.show();}
		},
		complete: function() {
		}
		});
		$('#role_privileges').show();
		$('#individual_privileges').css({display: "none"});
    }
});									
								
//this will highlight the selected tooth
$('.procedure_container , .procedure_container2 , .xray_tooth').on('change', '.tooth_checkbox2', function(){
	//alert('934');
//$(".tooth_checkbox").change(function() {
    if(this.checked) {
        $(this).parent().addClass('highlight');//Do stuff
    }
	else {
        $(this).parent().removeClass('highlight');//Do stuff
    }
});		

$('.tooth_checkbox').change(function(){
	//alert('945');
//$(".tooth_checkbox").change(function() {
    if(this.checked) {
        $(this).parent().addClass('highlight');//Do stuff
    }
	else {
        $(this).parent().removeClass('highlight');//Do stuff
    }
});		

//this will add new procedure
$('.add_new_procedure').click(function(){
	var extra_procedure = $('.procedure_count').last().text();
	//alert ('i is ' + i);
		var form_data = {extra_procedure: extra_procedure}
		element = $(this);
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to add extra procedure");
		e.preventDefault();
		},
		success: function(data) {
	//	alert('data is ' + data);
			$('.procedure_container').append(data);
		},
		complete: function() {
		}
		});	
	//$('.procedure_container').append().load('dental_b/', {'extra_procedure': extra_procedure });;
});



//this will add new procedure for editing  invoice
$('.procedure_container2').on('click', '.add_new_procedure_edit_tplan_no_invoice2', function(){
	//var extra_procedure_invoice = $('.procedure_count').last().text();
	//alert('procedure count is ' + extra_procedure_invoice);
	var extra_procedure_invoice = $(this).prev().val();
//	alert ('extra_procedure_tplan_no_invoice is ' + extra_procedure_tplan_no_invoice);
		var form_data = {extra_procedure_invoice: extra_procedure_invoice}
		element = $(this);
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to add extra procedure");
		e.preventDefault();
		},
		success: function(data) {
		//alert('data is ' + data);
			x = data.split('nonye');
			$('#nimeana').val(x[0]);
			$('#edit_tplan_no_invoice tr:last').after(x[1]);
		},
		complete: function() {
		}
		});	
	//$('.procedure_container').append().load('dental_b/', {'extra_procedure': extra_procedure });;
});	


//this will add new procedure for editing tplan with no invoice
$('.procedure_container2').on('click', '.add_new_procedure_edit_tplan_no_invoice', function(){
	var procedure_count = $('.procedure_count').last().text();
	//alert('procedure count is ' + procedure_count);
	var extra_procedure_tplan_no_invoice = $(this).prev().val();
//	alert ('extra_procedure_tplan_no_invoice is ' + extra_procedure_tplan_no_invoice);
		var form_data = {extra_procedure_tplan_no_invoice: extra_procedure_tplan_no_invoice}
		element = $(this);
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to add extra procedure");
		e.preventDefault();
		},
		success: function(data) {
		//alert('data is ' + data);
			x = data.split('ninye');
			$('#nimeana').val(x[0]);
			$('#edit_tplan_no_invoice tr:last').after(x[1]);
		},
		complete: function() {
		}
		});	
	//$('.procedure_container').append().load('dental_b/', {'extra_procedure': extra_procedure });;
});	
	
//this is for enabling/disableing tplan cost and tplan discount
$('.procedure_container').on('change', '.pay_method', function(){
//$('.pay_method').change(function(){
		var selec = $(this).children("option:selected").text(); 
		//alert('selec is ' + selec);
		//empty other fields when payment method
		$(this).parent().next().children('.tplan_cost').val('');
		get_treatment_plan_total_cost();
	//this will ensure that in tplan only aviallable fields are not disabled
		if( $(this).val() !='' && selec == 'Insurance' || selec == 'Self' || selec == 'Points'){
			$(this).parent().next().children('.tplan_cost').prop('disabled', false);		
			//check if we can get price for this
			var get_ins_price = $(this).parent().prev().prev().children('.select_procedure').val();
			//alert('get_ins_price is ' + get_ins_price);
			//alert('selec is ' + selec);
			//$(this).parent().next().children('.tplan_cost').load('dental_b/', {'get_ins_price': get_ins_price });
					var element  = $(this).parent().next().children('.tplan_cost');
					var form_data = {get_ins_price: get_ins_price, selec:selec }
					$.ajax({
						type: "POST",
						url: "dental_b/",
						data: form_data,
						error: function() {
							alert(" An Error occured");
							e.preventDefault();
						},
						success: function(data) {
						//alert('data is ' + data);
							var x = data.split('#');
							if (x[0] == "good"){element.val(x[1]);}
							else if (x[0] == "bad"){alert(x[1]);}
						},
						complete: function() {
						}
					});	
		}
		else if( $(this).val() !='' ){
			$(this).parent().next().children('.tplan_cost').prop('disabled', false);		
			
		}
		else{
			$(this).parent().next().children('.tplan_cost').prop('disabled', true);		
		

		} 
});		

//this is for enabling/disableing tplan cost in edit tplan
$('.procedure_container2').on('change', '.pay_method', function(){
//$('.pay_method').change(function(){
//alert('10301');		
		var selec = $(this).children("option:selected").text(); 
	//	alert(selec);
		//empty other fields when payment method
		$(this).parent().next().children('.tplan_cost2').val('');
		get_treatment_plan_total_cost2();
	//this will ensure that in tplan only aviallable fields are not disabled
		if( $(this).val() !='' && selec == 'Insurance' || selec == 'Self'){
			$(this).parent().next().children('.tplan_cost2').prop('disabled', false);		
			
		}
		else if( $(this).val() !='' && selec == 'Points'){
			$(this).parent().next().children('.tplan_cost2').prop('disabled', false);		
			
		}
		else{
			$(this).parent().next().children('.tplan_cost2').prop('disabled', true);		
			

		} 
});	

//willget price of xray in on exam
$('.pay_method_exam').change(function(){
			var selec =  $(this).children("option:selected").text(); 
			if(selec == 'Self' || selec =='Insurance' || selec =='Points'){
				var get_xray_ins_price = $(this).parent().prev().children('.select_xray').val();
			//$(this).parent().next().children('.tplan_cost').load('dental_b/', {'get_ins_price': get_ins_price });
					var element  = $(this).parent().next().children('.xray_examination_input');
					var form_data = {get_xray_ins_price: get_xray_ins_price, selec: selec}
					$.ajax({
						type: "POST",
						url: "dental_b/",
						data: form_data,
						error: function() {
							alert(" An Error occured");
							e.preventDefault();
						},
						success: function(data) {
						//alert('data is ' + data);
							var x2 =  data.split('#');
							if(x2[0] == 'bad'){alert(x2[1]);}
							else if (x2[0] == 'good'){ element.val(x2[1]);}
						},
						complete: function() {
						}
					});
			}
			else {$(this).parent().next().children('.xray_examination_input').val('');}
});					

//this is for showing/hiding the authorised cost in edit invoice
$('.procedure_container2').on('change', '.pay_method_inv', function(){
//$('.pay_method').change(function(){
//alert('10301');		
		var selec = $(this).children("option:selected").text(); 
	//	alert(selec);
		//empty other fields when payment method
		//$(this).parent().next().children('.tplan_cost2').val('');
		//get_treatment_plan_total_cost2();
	//this will ensure that in tplan only aviallable fields are not disabled
		if( $(this).val() !='' && selec == 'Insurance' ){
			$(this).parent().next().next().children('span').slideUp('fast');		
			$(this).parent().next().next().children('input').slideDown('fast');		
		}
		else if( $(this).val() !='' && selec != 'Insurance'){
			$(this).parent().next().next().children('span').slideDown('fast');		
			$(this).parent().next().next().children('input').val('').slideUp('fast');	
			
		}

});

//this is for showinfg patient note type
$('.note_type').change(function(){
//alert($(this).val());
	var selec = $(this).val(); 
	if( $(this).val() !='' && selec == 'review_date' ){
			$('.date_criteria').slideUp('fast');	
			$('.single_date').slideDown('fast');	
	}
	else	if( $(this).val() !='' && selec == 'sick_off' ){
			$('.date_criteria').slideDown('fast');	
			$('.single_date').slideUp('fast');	
	}
});

//this is for showinfg income report oprions
$('.income_report_criteria').change(function(){
//$('.pay_method').change(function(){
//alert('10301');		
		var selec = $(this).children("option:selected").text(); 
	//alert(selec);
		//empty other fields when payment method
		//$(this).parent().next().children('.tplan_cost2').val('');
		//get_treatment_plan_total_cost2();
	//this will ensure that in tplan only aviallable fields are not disabled
		if( $(this).val() !='' && selec == 'Pay Type' ){
			$('.date_criteria').slideUp('fast');	
			$('.single_date').slideUp('fast');	
			$('.pay_type_criteria').slideDown('fast');		
			$('.date_criteria').slideDown('fast');	
			
		}
		else if( $(this).val() !='' && selec == 'Date Range' || selec == 'Date Range Summary'){
			$('.pay_type_criteria').slideUp('fast');
			$('.single_date').slideUp('fast');		
			$('.date_criteria').slideDown('fast');	
		}
		else if( $(this).val() !='' && selec == 'Date' || selec == 'Date Summary'){
			$('.pay_type_criteria').slideUp('fast');		
			$('.date_criteria').slideUp('fast');
			$('.single_date').slideDown('fast');			
		}

});

//this is for patient list report
$('.patient_list_report').change(function(){
		var selec = $(this).val(); 
		if( $(this).val() !='' && selec == 'registered' ){
			$('.last_seen_div').css({display: "none"}).slideUp('fast');	
			$('.registration_div').slideDown('fast');	
		}
		else if( $(this).val() !='' && selec == 'last_seen'){
			$('.registration_div').css({display: "none"}).slideUp('fast');
			$('.last_seen_div').slideDown('fast');				
		}

});

//this is for showing/hiding the insurer in new corporates
$('#insured_yes_no').change(function(){
		//employer_form_divalert('ff');
		var selec = $(this).val();
		if( $(this).val() == 'NO' ){	$('.insurer_input').prop('disabled', true);	}
		else if( $(this).val() =='YES' ){	$('.insurer_input').prop('disabled', false);	}

});

//this is for showing/hiding the insurer in old corporates
$('.insured_yes_no_old').change(function(){
		alert($(this).val());
		var selec = $(this).val();
		if( $(this).val() == 'NO' ){
			$(this).parent().next().children().val('').prop('disabled', true);	
			$(this).parent().next().next().children().val('NO').prop('disabled', true);	
			$(this).parent().next().next().next().children().val('NO').prop('disabled', true);	
			$(this).parent().next().next().next().next().children().val('').prop('disabled', true);	
			$(this).parent().next().next().next().next().next().children().val('').prop('disabled', true);	
			$(this).parent().next().next().next().next().next().next().children().val('').prop('disabled', true);	
			$(this).parent().next().next().next().next().next().next().next().children().val('').prop('disabled', true);	
			$(this).parent().next().next().next().next().next().next().next().next().children().val('').prop('disabled', true);	
			$(this).parent().next().next().next().next().next().next().next().next().next().children().val('').prop('disabled', true);	
			$(this).parent().next().next().next().next().next().next().next().next().next().next().children().slideUp('fast');	
		}
		else if( $(this).val() =='YES' ){
			$(this).parent().next().children().val('').prop('disabled', false);	
			$(this).parent().next().next().children().val('NO').prop('disabled', false);	
			$(this).parent().next().next().next().children().val('NO').prop('disabled', false);	
			$(this).parent().next().next().next().next().children().val('').prop('disabled', false);	
			$(this).parent().next().next().next().next().next().children().val('').prop('disabled', false);	
			$(this).parent().next().next().next().next().next().next().children().val('').prop('disabled', false);	
			$(this).parent().next().next().next().next().next().next().next().children().val('').prop('disabled', false);	
			$(this).parent().next().next().next().next().next().next().next().next().children().val('').prop('disabled', false);	
			$(this).parent().next().next().next().next().next().next().next().next().next().children().val('').prop('disabled', false);	
			$(this).parent().next().next().next().next().next().next().next().next().next().next().children().slideDown('fast');	
		
		}

});

//disable xray field in examination
$('.xray_examination_input').prop('disabled', true);

//this is for showing teeth when selecting an xray
//$(document.body).off();
$('.select_xray_ref2').click(function(e){
	element = $(this);
	var xray_type = $(this).val();
	var get_xray_ins_price = $(this).val();
	var selec = 'self'
   if ($(this).is(':checked')) {
		
		var form_data2 = {get_xray_ins_price: get_xray_ins_price, selec: selec}
		//get price
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data2,
		error: function() {
		alert(" An Error occured, unable to select teeth for this x-ray");
		e.preventDefault();
		},
		success: function(data) {
		//alert('data is ' + data);
						var x2 =  data.split('#');
							if(x2[0] == 'bad'){alert(x2[1]);}
							else if (x2[0] == 'good'){ element.parent().next().children('input').val(x2[1]);}
		},
		complete: function() {
		}
		});	
		
		var form_data = {xray_type: xray_type}
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to select teeth for this x-ray");
		e.preventDefault();
		},
		success: function(data) {
	//	alert('data is ' + data);
			if (data == 'show_teeth') {element.parent().next().next().next().next().next().children('div').first().addClass('teeth_body').slideDown("slow");}
			
		},
		complete: function() {
		}
		});	
		
		//enable the concenred xray fields
		element.parent().next().children().val('').prop('disabled', false);
    }
	else {
       element.parent().next().next().next().next().next().children('div').first().slideUp("slow").removeClass('teeth_body').find(':checked').each(function() {
																	$(this).removeAttr('checked');
																	$(this).parent().removeClass('highlight');
																	});
		//disable the concenred xray fields
		element.parent().next().children().val('').prop('disabled', true);
	}
}//end function

);


//this is for showing teeth when selecting an xray
//$(document.body).off();
$('.select_xray').click(function(e){
	element = $(this);
	var xray_type = $(this).val();
   if ($(this).is(':checked')) {
		var form_data = {xray_type: xray_type}
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to select teeth for this x-ray");
		e.preventDefault();
		},
		success: function(data) {
		//alert('data is ' + data);
			if (data == 'show_teeth') {element.parent().next().next().next().next().next().children('div').first().addClass('teeth_body').slideDown("slow");}
			
		},
		complete: function() {
		}
		});	
		
		//enable the concenred xray fields
		element.parent().next().children().val('').prop('disabled', false);
		element.parent().next().next().children().val('').prop('disabled', false);
		element.parent().next().next().next().children().val('').prop('disabled', false);
    }
	else {
       element.parent().next().next().next().next().next().children('div').first().slideUp("slow").removeClass('teeth_body').find(':checked').each(function() {
																	$(this).removeAttr('checked');
																	$(this).parent().removeClass('highlight');
																	});
		//disable the concenred xray fields
		element.parent().next().children().val('').prop('disabled', true);
		element.parent().next().next().children().val('').prop('disabled', true);
		element.parent().next().next().next().children().val('').prop('disabled', true);																	
    }
}//end function

);

/*
//this will be used to show teeth for selected xray in edit tplan
$('.procedure_container2').on('change', '.select_xray_etp', function(e){
		var add_procedure3 = $(this).val();
		var form_data = {add_procedure3: add_procedure3}
		//var new_name = element.parent().next().children().attr('name');
		
		element = $(this);
		//var new_name = element.parent().next().children(":first-child").attr('name');
		//alert('name is ' +  new_name);
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to select teeth for this procedure");
		e.preventDefault();
		},
		success: function(data) {
	//	alert('data is ' + data);
			//var x = data.split('ninye');
			if (data == 'show_teeth') {element.next().addClass('teeth_body').slideDown("slow");}
			else  {element.next().
													slideUp("slow").
													removeClass('teeth_body').
													find(':checked').each(function() {
																	$(this).removeAttr('checked');
																	$(this).parent().removeClass('highlight');
																	});
													
			}
		},
		complete: function() {
		}
		});	
	//e.stopPropagation();
return false;


	}//end function 

);*/

//this will be used to manipulate the edit tplan form
$('.procedure_container2').on('change', '.select_procedure2', function(e){
//$('.select_procedure').change(function(){
//$('.select_procedure').bindlive("change", function(e){
//alert('998');

			var add_procedure2 = $(this).val();
			//alert('996');
		//empty other fields when procedure chnges
		$(this).parent().next().children('.tplan_details').val('');
		$(this).parent().next().next().children('.pay_method').val('');
		$(this).parent().next().next().next().children('.tplan_cost2').val('');
		$(this).parent().next().next().next().next().children('.tplan_remove').prop('checked', false);
		get_treatment_plan_total_cost2();
	//this will ensure that in tplan only aviallable fields are not disabled
		//alert('value is ' + $(this).val());
		if( $(this).val() !='' ){
			//details
			$(this).parent().next().children('.tplan_details').prop('disabled', false);
			//payment method
			$(this).parent().next().next().children('.pay_method').prop('disabled', false);		
			$(this).parent().next().next().next().children('.tplan_cost2').prop('disabled', true);
			$(this).parent().next().next().next().next().children('.tplan_remove').prop('disabled', true);				
		}
		else{
			$(this).parent().next().children('.tplan_details').prop('disabled', true);
			$(this).parent().next().next().children('.pay_method').prop('disabled', true);
			$(this).parent().next().next().next().children('.tplan_cost2').prop('disabled', true);
			$(this).parent().next().next().next().next().next().children('.tplan_remove').prop('checked', false);	

		}

	//alert('vals is ' + add_procedure);

		var form_data = {add_procedure2: add_procedure2}
		//var new_name = element.parent().next().children().attr('name');
		
		element = $(this);
		var new_name = element.parent().next().children(":first-child").attr('name');
	//	alert('name is ' +  new_name);
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to select teeth for this procedure");
		e.preventDefault();
		},
		success: function(data) {
	//	alert('data is ' + data);
			var x = data.split('ninye');

					if (x[0] == 'show_teeth') {element.next().addClass('teeth_body').slideDown("slow");}
					else  {element.next().
															slideUp("slow").
															removeClass('teeth_body').
															find(':checked').each(function() {
																			$(this).removeAttr('checked');
																			$(this).parent().removeClass('highlight');
																			});
															
					}

		},
		complete: function() {
		}
		});	
	//e.stopPropagation();
return false;


	}//end function

);

//this is for showing teeth when selecting a procedure
//$(document.body).off();
$('.procedure_container').on('change', '.select_procedure', function(e){
//$('.select_procedure').change(function(){
//$('.select_procedure').bindlive("change", function(e){
//alert('998');
$(this).parent().next().next().children('.pay_method').prop('disabled', false);	
 	


			var add_procedure = $(this).val();
			//alert('996');
		//empty other fields when procedure chnges
		$(this).parent().next().children('.tplan_details').val('');
		$(this).parent().next().next().children('.pay_method').val('');
		$(this).parent().next().next().next().children('.tplan_cost').val('');
		get_treatment_plan_total_cost();
	//this will ensure that in tplan only aviallable fields are not disabled
		//alert('value is ' + $(this).val());
		if( $(this).val() !='' ){
			//details
			$(this).parent().next().children('.tplan_details').prop('disabled', false);
			//payment method
			$(this).parent().next().next().children('.pay_method').prop('disabled', false);		
			$(this).parent().next().next().next().children('.tplan_cost').prop('disabled', true);
						
		}
		else{
			$(this).parent().next().children('.tplan_details').prop('disabled', true);
			$(this).parent().next().next().children('.pay_method').prop('disabled', true);
			$(this).parent().next().next().next().children('.tplan_cost').prop('disabled', true);
			

		}

element = $(this);
	//alert('vals is ' + add_procedure);
	//check if the procedure is in loyalty program
		var check_procedure_in_points = add_procedure ;
		var form_data = {check_procedure_in_points: check_procedure_in_points}
		pay_method_select = $(this).parent().next().next().children('.pay_method');
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to select teeth for this procedure");
		e.preventDefault();
		},
		success: function(data) {
		//alert('data is ' + data);
			var x2 = data.split('#');
			if (x2[0] == 'yes') {
				//alert('yes');
				var points_found = false;
				$('option ' ,  pay_method_select).each(function(){
					if($(this).text() == 'Points'){	
						 points_found = true;
					}
				});
				if(!points_found){pay_method_select.append(x2[1]);}
			}
			else if (x2[0] == 'no') {
				//alert('no points');
				$('option ' ,  pay_method_select).each(function(){
				//	alert($(this).text())
					if($(this).text() == 'Points'){	
				//		alert('removing points')
						$(this).remove();
					}
				});
			}
		},
		complete: function() {
		}
		});	

		var form_data = {add_procedure: add_procedure}
		
		$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to select teeth for this procedure");
		e.preventDefault();
		},
		success: function(data) {
		//alert('data is ' + data);
			if (data == 'show_teeth') {element.next().addClass('teeth_body').slideDown("slow");}
			else if (data == 'do_not_show_teeth') {element.next().
													slideUp("slow").
													removeClass('teeth_body').
													find(':checked').each(function() {
																	$(this).removeAttr('checked');
																	$(this).parent().removeClass('highlight');
																	});
													
													}
		},
		complete: function() {
		}
		});	
	//e.stopPropagation();
return false;


	}//end function

);								
								
//this is to keep table headers fixed
//$('table.normal_table').fixedHeaderTable({ height: '400', altClass: 'odd', themeClass: 'fancyDarkTable' });/*
//$('table.normal_table').fixedHeaderTable({ height: '400',footer: true,   fixedColumn: false, autoShow: true });
/*
var tableOffset = $(".normal_table").offset().top;
var $header = $(".normal_table > thead").clone();
var $fixedHeader = $("#header-fixed").append($header);

$(window).bind("scroll", function() {
    var offset = $(this).scrollTop();

    if (offset >= tableOffset && $fixedHeader.is(":hidden")) {
        $fixedHeader.show();
    }
    else if (offset < tableOffset) {
        $fixedHeader.hide();
    }
});		*/	
/*    function scrolify(tblAsJQueryObject, height){
        var oTbl = tblAsJQueryObject;

        // for very large tables you can remove the four lines below
        // and wrap the table with <div> in the mark-up and assign
        // height and overflow property  
        var oTblDiv = $("<div/>");
        oTblDiv.css('height', height);
        oTblDiv.css('overflow','scroll');               
        oTbl.wrap(oTblDiv);

        // save original width
        oTbl.attr("data-item-original-width", oTbl.width());
        oTbl.find('thead tr td').each(function(){
            $(this).attr("data-item-original-width",$(this).width());
        }); 
        oTbl.find('tbody tr:eq(0) td').each(function(){
            $(this).attr("data-item-original-width",$(this).width());
        });                 


        // clone the original table
        var newTbl = oTbl.clone();

        // remove table header from original table
        oTbl.find('thead').remove();    
		oTbl.find('caption').remove();    		
        // remove table body from new table
        newTbl.find('tbody').remove();   

        oTbl.parent().parent().prepend(newTbl);
        newTbl.wrap("<div/>");

        // replace ORIGINAL COLUMN width                
        newTbl.width(newTbl.attr('data-item-original-width'));
        newTbl.find('thead tr td').each(function(){
            $(this).width($(this).attr("data-item-original-width"));
        });     
        oTbl.width(oTbl.attr('data-item-original-width'));      
        oTbl.find('tbody tr:eq(0) td').each(function(){
            $(this).width($(this).attr("data-item-original-width"));
        });                 
    }
scrolify($('.normal_table'), 260); // 160 is height			*/
/*
 $(".normal_table").chromatable({
	width: "100%",
    height: "400px",
    scrolling: "yes" 
 });*/
 
//this will show form for adding patient refferes
	$('#add_new_patient_referrer').click(function(){
		var width_x = $("#add_new_patient_referrer").parent().width();
		var height_y = 260;//$("#employer_form_div").height();
	//	alert(' width is ' + width_x + ' height is ' + height_y);
		$("#patient_referrer_form_div").dialog({
		open: function(){
		   $(this).find("input[type=text], textarea, select").val("");
		},
		close: function() {
		   $(this).find("input[type=text], textarea, select").val("");
		},
		title: 'New Patient Referrer',
		height: height_y,
		width: width_x,
		modal: true});
	});	 
 
 //this will show a lab
 $('.view_lab').click(function(){
	var view_lab = $(this).val();
		var width_x = $(this).parent().parent().width();
		var height_y = 500;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$("#view_lab").empty().dialog({
		title: 'LAB WORK NUMBER: ' + view_lab,
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'view_lab': view_lab });
 });
 
 /*//this will edit corrporate cover
 $('.edit_corporate_cover').click(function(){
	var edit_corporate = $(this).next().val();
		var width_x = $(this).parent().parent().width();
		var height_y = 580;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$("#edit_ins_cover").empty().dialog({
		title: 'Edit Procedures covered by Insurance',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'edit_corporate': edit_corporate });
 });*/
 
 //this will edit corrporate 
 $('.edit_corporate').click(function(){
	var edit_corporate2 = $(this).next().val();
		var width_x = $(this).parent().parent().width();
		var height_y = 580;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$("#edit_ins_cover").empty().dialog({
		close: function() {
		   window.location = "?id=employer";
		},
		title: 'Edit Corprate',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'edit_corporate2': edit_corporate2 });
 });
 

//this will show book appointment div
/*
$('.book_appointment').click(function(){
	var appointment_date = $(this).parent().prev().children('input').val();
	if(appointment_date == ''){alert('Please specify the appointment date');}
	else{
		//alert('date is ' + appointment_date);
		$('#appointment_div').show().load('dental_b/', {'appointment_date': appointment_date });
	}
});*/

$('.appointment_date_date').change(function(){
 //$(document.body).on('input', '.appointment_date_date', function(){
	//alert('dd');
	var appointment_date = $(this).val();
	if(appointment_date == ''){alert('Please specify the appointment date');}
	else{
		//alert('date is ' + appointment_date);
		$('#appointment_div').show().load('dental_b/', {'appointment_date': appointment_date });
	}
});

//this is for re-appointemtn
$('.appointment_date_date2').change(function(){
 //$(document.body).on('input', '.appointment_date_date', function(){
	//alert('dd');
	var appointment_date = $(this).val();
	if(appointment_date == ''){alert('Please specify the appointment date');}
	else{
		//alert('date is ' + appointment_date);
		$('#appointment_divr1').show().load('dental_b/', {'appointment_date': appointment_date });
	}
});

$('#appointment_div2').on('change','.appointment_doctor',function(){
 //$(document.body).on('input', '.appointment_date_date', function(){
	//alert('dd');
	var appointment_doctor = $(this).val();
	//alert(appointment_doctor);
	if(appointment_doctor == ''){alert('Please specify the doctor for the appointment');}
	else{
		//alert('date is ' + appointment_date);
		$('.show_doc_appointments').load('dental_b/', {'appointment_doctor': appointment_doctor });
	}
});

//this will be making a re-appointment
$('#appointment_divr2').on('change','.appointment_doctor',function(){
 //$(document.body).on('input', '.appointment_date_date', function(){
	//alert('dd');
	var appointment_doctor = $(this).val();
	//alert(appointment_doctor);
	if(appointment_doctor != ''){
		//alert('date is ' + appointment_date);
		$('.show_doc_appointments').load('dental_b/', {'appointment_doctor': appointment_doctor });
	}
});

 //this will show appointment date for re-appointment
 $('.set_appointment_status').change(function(){
	var selec_text = $(this).children("option:selected").text(); 
	if(selec_text == 'RE-APPOINTED'){
		$(this).parent().next().empty().append("<input type=button class='button_style button_in_table_cell new_appointment_button' value='Schedule new appointment' />");
	}
	else{
		$(this).parent().next().empty();
	}
 //$('.create_appointment').click(function(){
	//alert('fff');
	/*var selec_text = $(this).children("option:selected").text(); 
	var appointment_id = $(this).val();
	//alert(selec_text + '       ' + appointment_date);
	var form_data = {appointment_id: appointment_id}
	$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to complete action");
		e.preventDefault();
		},
		success: function(data) {},
		complete: function() {}
	});
	if(selec_text == 'RE-APPOINTED'){
		var width_x = $('.get_width').width();
		var height_y = 600;
		$(".re_appoint_div").css('backgroundColor',' #15212F').dialog({
		title: 'New Patient Appointment',
		height: height_y,
		width: width_x,
		modal: true})
	}*/
});


 $('.re_appoint_td').on('click','.new_appointment_button',function(){
 //$('.create_appointment').click(function(){
	//alert('fff');
	var selec_text = $(this).parent().prev().children('select').children("option:selected").text(); 
	var appointment_id = $(this).parent().prev().children('select').val();
	$('.re_appoint_td').removeClass('current_reappoint');
	$(this).parent().addClass('current_reappoint');
	var form_data = {appointment_id: appointment_id}
	$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to complete action");
		e.preventDefault();
		},
		success: function(data) {},
		complete: function() {}
	});
	if(selec_text == 'RE-APPOINTED'){
		var width_x = $('.get_width').width();
		var height_y = 600;
		$(".re_appoint_div").css('backgroundColor',' #15212F').dialog({
		title: 'New Patient Appointment',
		height: height_y,
		width: width_x,
		modal: true})
	}
});

 //this will show docotr pop up for re-appoint ment of appointment
 $('#appointment_divr1').on('click', '.create_appointment', function(){
 //$('.create_appointment').click(function(){
	//alert('fff');
	var new_appointment = $(this).prev().val();
	var form_data = {new_appointment: new_appointment}
	$.ajax({
		type: "POST",
		url: "dental_b/",
		data: form_data,
		error: function() {
		alert(" An Error occured, unable to complete action");
		e.preventDefault();
		},
		success: function(data) {},
		complete: function() {
			var get_re_appoint_doc = 'yes';
			var width_x = $('#appointment_divr1').width();
			var height_y = 500;
			$("#appointment_divr2").empty().css('backgroundColor',' #15212F').dialog({
			title: 'Re-Appointment',
			height: height_y,
			width: width_x,
			modal: true}).load('dental_b/', {'get_re_appoint_doc': get_re_appoint_doc });			
		}
	});
		//alert('cc');

 });

 //this will clear password fileds
 $("input[type='password']").val('');
 
 
 //this will disabel past dates in date_picker
 $('.date_picker_no_past').datepicker( { dateFormat: "yy-mm-dd" , 
								changeMonth: true,
								changeYear: true,
								minDate: 0});	
 
 //this will show pop up form to create an appointment
 $('#appointment_div').on('click', '.create_appointment', function(){
 //$('.create_appointment').click(function(){
	//alert('fff');
	var create_appointment = $(this).prev().val();
	//	alert('create_appointment is ' + create_appointment);
		var width_x = $('#appointment_div').width();
		var height_y = 600;
		$("#appointment_div2").empty().dialog({
		title: 'New Patient Appointment',
		height: height_y,
		width: width_x,
		modal: true}).load('dental_b/', {'create_appointment': create_appointment });
 });
 
  //this will show a search box to search for patient type in appointments
 $(document.body).on('change', '.appointment_patient_type', function(){
	var appointment_patient_type = $(this).val();
		//alert('create_appointment is ' + create_appointment);
		//var width_x = $('#appointment_patient_search').width();
		//var height_y = 280;
		if(appointment_patient_type == 'registered'){title = 'Registered Patient Search';}
		else if(appointment_patient_type == 'unregistered'){title = 'Un-registered Patient Details';}
		$("#appointment_patient_search").empty().show().load('dental_b/', {'appointment_patient_type': appointment_patient_type });
 });

	//hide the dialogs div
		$('#dialogs').hide();
		
 		//this will check insured compnaies when adding a new corprate
		$(document.body).on('submit', '#employer_form', function(){
					var exit_flag = false;
					var employer = document.employer_form.employer_name;
					var ins_name = document.employer_form.ins_name;
				//	$('.old_ins').each(function(){
					var co_pay_type = document.employer_form.co_pay;
					var co_pay_value =document.employer_form.co_pay_value;
					var start_date = document.employer_form.start_date;
					var end_date =document.employer_form.end_date;
					var cover_type = document.employer_form.cover_type;
					var cover_limit = document.employer_form.cover_limit;	
					var insured_yes_no = document.employer_form.insured_yes_no;	
				if(insured_yes_no.value == 'YES' && ins_name.value == ''){
					error_dialog(insured_yes_no,'This patient type is insured but no insurer has been specified');
							exit_flag = true;
				}
				if(!exit_flag && ins_name.value != ''){
					

					//check co-pay type and value 
					if(co_pay_type.value !='' && co_pay_value.value == '' ){
							error_dialog(co_pay_value,'Co-Pay Type has been set but no corresponding value has been set');
							exit_flag = true;
					}
					//check co-pay-value and type 
					if(co_pay_type.value =='' && co_pay_value.value != '' ){
							error_dialog(co_pay_type,'Co-Pay Value has been set but no corresponding Co-Pay Type has been set');
							exit_flag = true;
					}
					//check start date 
					if(start_date.value =='' || start_date.value == null ){
							error_dialog(start_date,'Please specify when the insurance cover starts');
							exit_flag = true;
					}
					//check end date 
					if(end_date.value == '' || end_date.value == null ){
							error_dialog(end_date,'Please specify when the insurance cover will end');
							exit_flag = true;
					}
					//check cover type 
					if(cover_type.value == '' || cover_type.value == null ){
							error_dialog(cover_type,'Please specify the insurance cover type');
							exit_flag = true;
					}
					//check cover limit 
					if(cover_limit.value == '' || cover_limit.value == null ){
							error_dialog(cover_limit,'Please specify the insurance cover limit');
							exit_flag = true;
					}//var n=data.split("#");		
					//check cover limit 
					if(!IsNumeric_jq(cover_limit.value) ){
							error_dialog(cover_limit,'The cover limit specified is not a valid number');
							exit_flag = true;
					}					
					if(!IsNumeric_jq(co_pay_value.value )){
							error_dialog(co_pay_value,'The Co-Pay Value specified is not a valid number');
							exit_flag = true;
					}	
					//	if (!IsNumeric_jq($(this).value)){alert($(this).value + ' is not a valid number');return false;}
				//	sum += parseFloat($(this).value.replace(",",""));
					//alert('sum ndani is ' + sum);
				}
				//now check when ins is emoty
				/*else if(!exit_flag && ins_name.value == ''){
					//insurer must be specified
							error_dialog(ins_name,'An insurer must be specified for each corprate.');
							exit_flag = true;					
					//check co-pay type and value 
					/*if(co_pay_type.value !=''  ){
							error_dialog(co_pay_value,'Co-Pay Type has been set for an uninsured company');
							exit_flag = true;
					}
					//check co-pay-value and type 
					if( co_pay_value.value != '' ){
							error_dialog(co_pay_value,'Co-Pay Value has been set for an uninsured company');
							exit_flag = true;
					}
					//check start date 
					if(start_date.value !=''  ){
							error_dialog(start_date,'Insurance cover start date has been set for an uninsured company');
							exit_flag = true;
					}
					//check end date 
					if(end_date.value !=  ''){
							error_dialog(end_date,'Insurance cover end date has been set for an uninsured company');
							exit_flag = true;
					}
					//check cover type 
					if(cover_type.value != ''  ){
							error_dialog(cover_type,'Insurance cover type has been set for an uninsured company');
							exit_flag = true;
					}
					//check cover limit 
					if(cover_limit.value != '' ){
							error_dialog(cover_limit,'Insurance cover limit has been set for an uninsured company');
							exit_flag = true;
					}	*/
					//	if (!IsNumeric_jq($(this).val())){alert($(this).val() + ' is not a valid number');return false;}
				//	sum += parseFloat($(this).val().replace(",",""));
					//alert('sum ndani is ' + sum);
			//	}				
			//});*/


			if (!exit_flag ){	return true;}
			else{return false;}
		});
		
 		//this will check insured compnaies subsmissions if they are okay
	/*	$(document.body).on('submit', '#insured_companies', function(){
					var exit_flag = false;
					$('.old_ins').each(function(){
					var co_pay_type = $(this).parent().next().next().next().children('select');
					var co_pay_value = $(this).parent().next().next().next().next().children('input');
					var start_date = $(this).parent().next().next().next().next().next().children('input');
					var end_date = $(this).parent().next().next().next().next().next().next().children('input');
					var cover_type = $(this).parent().next().next().next().next().next().next().next().children('select');
					var cover_limit = $(this).parent().next().next().next().next().next().next().next().next().children('input');
					var insured_yes_no = document.employer_form.insured_yes_no;	
					
				if(insured_yes_no.value == 'YES' && ins_name.value == ''){
					error_dialog(insured_yes_no,'This patient type is insured but no insurer has been specified');
							exit_flag = true;
				}
				if($(this).val() != ''){
					

					//check co-pay type and value 
					if(co_pay_type.val() !='' && co_pay_value.val() == '' ){
							error_dialog(co_pay_value,'Co-Pay Type has been set but no corresponding value has been set');
							exit_flag = true;
					}
					//check co-pay-value and type 
					if(co_pay_type.val() =='' && co_pay_value.val() != '' ){
							error_dialog(co_pay_type,'Co-Pay Value has been set but no corresponding Co-Pay Type has been set');
							exit_flag = true;
					}
					//check start date 
					if(start_date.val() =='' || start_date.val() == null ){
							error_dialog(start_date,'Please specify when the insurance cover starts');
							exit_flag = true;
					}
					//check end date 
					if(end_date.val() == '' || end_date.val() == null ){
							error_dialog(end_date,'Please specify when the insurance cover will end');
							exit_flag = true;
					}
					//check cover type 
					if(cover_type.val() == '' || cover_type.val() == null ){
							error_dialog(cover_type,'Please specify the insurance cover type');
							exit_flag = true;
					}
					//check cover limit 
					if(cover_limit.val() == '' || cover_limit.val() == null ){
							error_dialog(cover_limit,'Please specify the insurance cover limit');
							exit_flag = true;
					}//var n=data.split("#");		
					//check cover limit 
					if(!IsNumeric_jq(cover_limit.val()) ){
							error_dialog(cover_limit,'The cover limit specified is not a valid number');
							exit_flag = true;
					}					
					if(!IsNumeric_jq(co_pay_value.val() )){
							error_dialog(co_pay_value,'The Co-Pay Value specified is not a valid number');
							exit_flag = true;
					}	
					//	if (!IsNumeric_jq($(this).val())){alert($(this).val() + ' is not a valid number');return false;}
				//	sum += parseFloat($(this).val().replace(",",""));
					//alert('sum ndani is ' + sum);
				}
				//now check when ins is emoty
				else if($(this).val() == ''){
					//check co-pay type and value 
					if(co_pay_type.val() !=''  ){
							error_dialog(co_pay_value,'Co-Pay Type has been set for an uninsured company');
							exit_flag = true;
					}
					//check co-pay-value and type 
					if( co_pay_value.val() != '' ){
							error_dialog(co_pay_value,'Co-Pay Value has been set for an uninsured company');
							exit_flag = true;
					}
					//check start date 
					if(start_date.val() !=''  ){
							error_dialog(start_date,'Insurance cover start date has been set for an uninsured company');
							exit_flag = true;
					}
					//check end date 
					if(end_date.val() !=  ''){
							error_dialog(end_date,'Insurance cover end date has been set for an uninsured company');
							exit_flag = true;
					}
					//check cover type 
					if(cover_type.val() != ''  ){
							error_dialog(cover_type,'Insurance cover type has been set for an uninsured company');
							exit_flag = true;
					}
					//check cover limit 
					if(cover_limit.val() != '' ){
							error_dialog(cover_limit,'Insurance cover limit has been set for an uninsured company');
							exit_flag = true;
					}	
					//	if (!IsNumeric_jq($(this).val())){alert($(this).val() + ' is not a valid number');return false;}
				//	sum += parseFloat($(this).val().replace(",",""));
					//alert('sum ndani is ' + sum);
				}				
			});


			if (!exit_flag ){	return true;}
			else{return false;}
		});*/

//this will show dialog for adding new patient company
//	$('#employer_form_div').hide();
	$('#add_new_patient_employer').click(function(){
		var width_x = $("#add_new_patient_employer").parent().width();
		var height_y = 410;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$("#employer_form_div").dialog({
		open: function(){
		   $(this).find("input[type=text], textarea, select").val("");
		},
		close: function() {
		   $(this).find("input[type=text], textarea, select").val("");
		},
		title: 'New Patient Type',
		height: height_y,
		width: width_x,
		modal: true});
	});
	
//this will show dialog for adding new teratment procedure
//	$('#employer_form_div').hide();
	$('#add_new_treatment_procedure').click(function(){
		var width_x = $("#add_new_treatment_procedure").parent().width();
		var height_y = 600;//$("#employer_form_div").height();
		//alert(' width is ' + width_x + ' height is ' + height_y);
		$("#new_procedure_form_div .feedback").addClass('hide_element').slideUp('fast');
		$("#new_procedure_form_div").dialog({
		open: function(){
			
		   $(this).find("input[type=text]").val("");
		    $(input[type=radio]).prop('checked', false);
		},
		close: function() {
		   $(this).find("input[type=text]").val("");
		    $(input[type=radio]).prop('checked', false);
		},
		title: 'New Treatment Procedure',
		height: height_y,
		width: width_x,
		modal: true});
	});	
	
//this will show form for adding lab techhnicians
	$('#add_new_lab_technician').click(function(){
	//alert('ff');
		var width_x = $("#add_new_lab_technician").parent().width();
		var height_y = 260;//$("#employer_form_div").height();
	//	alert(' width is ' + width_x + ' height is ' + height_y);
		$("#lab_technician_form_div").dialog({
		open: function(){
		   $(this).find("input[type=text], textarea, select").val("");
		},
		close: function() {
		   $(this).find("input[type=text], textarea, select").val("");
		},
		title: 'New Lab Technician',
		height: height_y,
		width: width_x,
		modal: true});
	});		
	
//this is for appointment report criteria
 $('.search_criteria_apr').change(function(){
		if($(this).val() == 'date_range'){
			$('.apr_individual').slideUp('fast');
			$('.apr_date_range').slideDown('fast');
		}
		else if($(this).val() == 'patient'){
			$('.apr_date_range').slideUp('fast');
			$('.apr_individual').slideDown('fast');
		}
 });

 $('.patient_type_apr').change(function(){
		if($(this).val() == 'registered'){
			$('.unregistered_apr').slideUp('fast');
			$('.registered_apr').slideDown('fast');
		}
		else if($(this).val() == 'unregistered'){
			$('.registered_apr').slideUp('fast');
			$('.unregistered_apr').slideDown('fast');
		}
 });	 
	
	//this will show form for adding cadcam referrrer
	$('#add_new_cadcam_referrer').click(function(){
	//alert('ff');
		var width_x = $("#add_new_cadcam_referrer").parent().width();
		var height_y = 260;//$("#employer_form_div").height();
	//	alert(' width is ' + width_x + ' height is ' + height_y);
		$("#cadcam_referrer_form_div").dialog({
		open: function(){
		   $(this).find("input[type=text], textarea, select").val("");
		},
		close: function() {
		   $(this).find("input[type=text], textarea, select").val("");
		},
		title: 'New CADCAM Referrer',
		height: height_y,
		width: width_x,
		modal: true});
	});	
	
	
	
//this will show form for adding x-ray referrer
	$('#add_new_xray_referrer').click(function(){
	//alert('ff');
		var width_x = $("#xray_refeffer_form_div").parent().width();
		var height_y = 260;//$("#employer_form_div").height();
	//	alert(' width is ' + width_x + ' height is ' + height_y);
		$("#xray_refeffer_form_div").dialog({
		open: function(){
		   $(this).find("input[type=text], textarea, select").val("");
		},
		close: function() {
		   $(this).find("input[type=text], textarea, select").val("");
		},
		title: 'New X-ray Referrer',
		height: height_y,
		width: width_x,
		modal: true});
	});	

//this will show pop up forms for adding xray
	$('.add_xray').click(function(){
		var width_x = $(this).parent().width();
		var height_y = 450;
	//	alert(' width is ' + width_x + ' height is ' + height_y);
		$(this).next().dialog({
		open: function(){
		   $(this).find("input[type=text], textarea, select").val("");
		},
		close: function() {
		   $(this).find("input[type=text], textarea, select").val("");
		},
		//title: 'New Patient Referrer',
		height: height_y,
		width: width_x,
		modal: true});
	});	
	
	
//this will show pop up forms when needed
	$('.pop_up_form').click(function(){
		var width_x = $(this).parent().width();
		var height_y = 150;
	//	alert(' width is ' + width_x + ' height is ' + height_y);
		$(this).next().dialog({
		open: function(){
		   $(this).find("input[type=text], textarea, select").val("");
		},
		close: function() {
		   $(this).find("input[type=text], textarea, select").val("");
		},
		//title: 'New Patient Referrer',
		height: height_y,
		width: width_x,
		modal: true});
	});		
	
//this will lacuch lab form in tdone
$('.lab_request').click(function(e){
	e.preventDefault();
	var width_x = $('#undispatched_labs').width();
	var height_y = 780;
	var lab_request = 'yes';
	$('html, body').animate({scrollTop: '0px'}, 0);	
	$(".div_shower44").empty().css('backgroundColor',' #15212F').dialog({
		title: 'Lab Prescription Form',
		height: height_y,
		width: width_x,
		position: 'top',
		modal: true}).load('dental_b/', {'lab_request': lab_request });
});	
	/*
$("fieldset").hover(function(){	$(this).find('label').addClass('change_label_color');},
function(){	$(this).find('label').removeClass('change_label_color');});*/
$('.covered_company').prop('disabled', true);	
$('.covered_company2').prop('disabled', false);	
$('.undisable_covered_company').prop('disabled', false);	

//this will show the covered companies for a cadcam referals
	$('#cadcam_tabs3').on('change','.ptype',function(){
		var get_company = $(this).val();
		if(get_company == ''){
			$('.covered_company').prop('disabled', true);	
		}
		else if(get_company != ''){
			$('.covered_company').prop('disabled', false);	
			//$(this).closest('form').find(".covered_company").append().load('dental_b/', {'get_company': get_company });
			$(this).closest('form').find(".covered_company").append().load('dental_b/', {'get_company': get_company });
		}
	});	
	
//this will show the covered companies for a slected patien type
	$('.ptype').change(function(){
		var get_company = $(this).val();
		if(get_company == ''){
			$('.covered_company').prop('disabled', true);	
		}
		else if(get_company != ''){
			$('.covered_company').prop('disabled', false);	
			//$(this).closest('form').find(".covered_company").append().load('dental_b/', {'get_company': get_company });
			$(this).closest('form').find(".covered_company").append().load('dental_b/', {'get_company': get_company });
		}
	});	
	
	$('.ptype2').change(function(){
		var get_company2 = $(this).val();
		if(get_company2 == ''){
			$('.covered_company').prop('disabled', true);
			$('.covered_company2').prop('disabled', false);
		}
		else if(get_company2 != ''){
			$('.covered_company').prop('disabled', false);	
			$(this).closest('form').find(".covered_company").append().load('dental_b/', {'get_company2': get_company2 });
			//$(this).closest('form').find(".covered_company").append(load('dental_b/', {'get_company': get_company }));
		}
	});	
	
	//this will show size for selected manufacturer
	$('.manufacurer_l2').change(function(){
		var upper_category = $(this).val();
		 if(upper_category != ''){
			$('.current_size').load('dental_b/', {'upper_category': upper_category });
		}
		 else if(upper_category == ''){
			$('.current_size').empty();
		}		
	});	

	//this will show type for selected size
	$('.size_l3').change(function(){
		var upper_category3 = $(this).val();
		 if(upper_category3 != ''){
			$('.current_type').load('dental_b/', {'upper_category3': upper_category3 });
		}
		 else if(upper_category3 == ''){
			$('.current_type').empty();
		}		
	});	
	
	//this will show size for selected manufacturer at l3
	$('.manufacurer_l3').change(function(){
		var upper_category2 = $(this).val();
		 if(upper_category2 != ''){
			$('.size_l3').load('dental_b/', {'upper_category2': upper_category2 });
		}
		 else if(upper_category == ''){
			$('.size_l3').empty();
		}		
	});	
	
	//this will show size for selected manufacturer at l4
	$('.manufacurer_l4').change(function(){
		var upper_category4 = $(this).val();
		 if(upper_category4 != ''){
			$('.size_l4').load('dental_b/', {'upper_category4': upper_category4 });
		}
		 else if(upper_category4 == ''){
			$('.size_l4').empty();
		}		
	});		
	
	//this will show type for selected size at l4
	$('.size_l4').change(function(){
		var upper_category5 = $(this).val();
		 if(upper_category5 != ''){
			$('.type_l4').load('dental_b/', {'upper_category5': upper_category5 });
		}
		 else if(upper_category4 == ''){
			$('.type_l4').empty();
		}		
	});		

		//this will show type for selected size at l4
	$('.type_l4').change(function(){
		var upper_category6 = $(this).val();
		 if(upper_category6 != ''){
			$('.current_shade').load('dental_b/', {'upper_category6': upper_category6 });
		}
		 else if(upper_category4 == ''){
			$('.current_shade').empty();
		}		
	});
	
//this will add new cadcam manufacturer text box
$('.add_manufacturer, .add_size ,.add_type ,.add_shade').click(function(){
	var add_manufacturer = $(this).val();
	var element = $(this).parent().parent();
		var form_data = {add_manufacturer: add_manufacturer}
		$.ajax({
			type: "POST",
			url: "dental_b/",
			data: form_data,
			error: function() {
			alert(" An Error occured, unable to complete action");
			e.preventDefault();
			},
			success: function(data) {element.append(data);},
			complete: function() {}
		});
	//$('.manuf_container').append().load('dental_b/', {'add_manufacturer': add_manufacturer });
		//$(this).closest('form').find(".covered_company").append(load('dental_b/', {'get_company': get_company }));
	//alert('ff');
});
	
//this is for printing
$(".printment").click(function(){
	$(this).parent().next().printElement();
})	
$(".div_shower2 , .div_shower44 , .div_shower").on('click','.printment',function(event){

	$(this).parent().next().printElement();

});
	
$(".dialog_with_tab,  #view_lab").on('click','.printment',function(){
	$(this).parent().next().printElement();
});
//this will show the pay mode type in insurance payments
$('.pay_mode').change(function(){
		var pay_mode = $(this).val();
		if(pay_mode == ''){
			$('.single_invoice').slideUp('fast');
			$('.multiple_invoice').slideUp('fast');	
		}
		else if(pay_mode == 'single'){
			$('.single_invoice').slideDown('fast');
			$('.multiple_invoice').slideUp('fast');	
		}
		else if(pay_mode == 'multiple'){
			$('.single_invoice').slideUp('fast');
			$('.multiple_invoice').slideDown('fast');	
		}		
});		

//this will show the serach criteria in edit dispatch
$('.edit_dispatch').change(function(){
		var pay_mode = $(this).val();
		if(pay_mode == ''){
			$('.serach_by_individual').slideUp('fast');
			$('.serach_by_ins').slideUp('fast');	
		}
		else if(pay_mode == 'ins'){
			$('.serach_by_ins').slideDown('fast');
			$('.serach_by_individual').slideUp('fast');	
		}
		else if(pay_mode != 'ins'){
			$('.serach_by_ins').slideUp('fast');
			$('.serach_by_individual').slideDown('fast');	
		}		
});	

//this will show the serach criteria in dispatch report
$('.dispatch_r1').change(function(){
		var pay_mode = $(this).val();
		if(pay_mode == ''){
			$('.sdispatched, .serach_by_individual, .serach_by_ins, .serach_by_ins2').slideUp('fast');
			//$('.serach_by_ins').slideUp('fast');	
		}
		else if(pay_mode == 'undispatched'){
			$('.serach_by_ins2').slideDown('fast');
			$('.sdispatched,.serach_by_individual').slideUp('fast');	
		}
		else if(pay_mode == 'dispatched'){
			$('.serach_by_ins2').slideUp('fast');
			$('.sdispatched').slideDown('fast');	
		}		
});	

//this will show the swap option
$('.swap1').change(function(){
		var pay_mode = $(this).val();
		if(pay_mode == ''){
			$('.serach_by_individual1 , .serach_by_individual, .serach_by_ins').slideUp('fast');
			//$('.serach_by_ins').slideUp('fast');	
		}
		else if(pay_mode == 'ins'){
			$('.serach_by_ins').slideDown('fast');
			$('.serach_by_individual1 , .serach_by_individual').slideUp('fast');
		}
		else if(pay_mode != 'ins'){
			$('.serach_by_ins').slideUp('fast');
			$('.serach_by_individual1 , .serach_by_individual').slideDown('fast');
		}		
});
h=0;
var h = $("th.treat_auothorised_cost2").outerHeight();
	//alert('h is ' + h);
 $(".100_height").height(h);	

//this will make heights of diseases equal
$(function(){
	//$(".hide_first").addClass("hide_element");
	//row1
    var H = 0;
    $(".row1").each(function(i){
        var h = $(".row1").eq(i).height();
        if(h > H) H = h;
    });
    $(".row1").height(H);
	//row2
    var H = 0;
    $(".row2").each(function(i){
        var h = $(".row2").eq(i).height();
        if(h > H) H = h;
    });
    $(".row2").height(H);
	//row3
    var H = 0;
    $(".row3").each(function(i){
        var h = $(".row3").eq(i).height();
        if(h > H) H = h;
    });
    $(".row3").height(H);
	//row4
    var H = 0;
    $(".row4").each(function(i){
        var h = $(".row4").eq(i).height();
        if(h > H) H = h;
    });
    $(".row4").height(H);	
	//row5
    var H = 0;
    $(".row5").each(function(i){
        var h = $(".row5").eq(i).height();
        if(h > H) H = h;
    });
    $(".row5").height(H);	
	//row6
    var H = 0;
    $(".row6").each(function(i){
        var h = $(".row6").eq(i).height();
        if(h > H) H = h;
    });
    $(".row6").height(H);	
	//row7
    var H = 0;
    $(".row7").each(function(i){
        var h = $(".row7").eq(i).height();
        if(h > H) H = h;
    });
    $(".row7").height(H);	
	//row8
    var H = 0;
    $(".row8").each(function(i){
        var h = $(".row8").eq(i).height();
        if(h > H) H = h;
    });
    $(".row8").height(H);	
	//row9
    var H = 0;
    $(".row9").each(function(i){
        var h = $(".row9").eq(i).height();
        if(h > H) H = h;
    });
    $(".row9").height(H);	
	//row10
    var H = 0;
    $(".row10").each(function(i){
        var h = $(".row10").eq(i).height();
        if(h > H) H = h;
    });
    $(".row10").height(H);	
	//row11
    var H = 0;
    $(".row11").each(function(i){
        var h = $(".row11").eq(i).height();
        if(h > H) H = h;
    });
    $(".row11").height(H);	
	//row12
    var H = 0;
    $(".row12").each(function(i){
        var h = $(".row12").eq(i).height();
        if(h > H) H = h;
    });
    $(".row12").height(H);	
	//row13
    var H = 0;
    $(".row13").each(function(i){
        var h = $(".row13").eq(i).height();
        if(h > H) H = h;
    });
    $(".row13").height(H);	
	//row14
    var H = 0;
    $(".row14").each(function(i){
        var h = $(".row14").eq(i).height();
        if(h > H) H = h;
    });
    $(".row14").height(H);	
	//row15
    var H = 0;
    $(".row15").each(function(i){
        var h = $(".row15").eq(i).height();
        if(h > H) H = h;
    });
    $(".row15").height(H);	
	//row16
    var H = 0;
    $(".row16").each(function(i){
        var h = $(".row16").eq(i).height();
        if(h > H) H = h;
    });
    $(".row16").height(H);	
	//row17
    var H = 0;
    $(".row17").each(function(i){
        var h = $(".row17").eq(i).height();
        if(h > H) H = h;
    });
    $(".row17").height(H);	
	//row18
    var H = 0;
    $(".row18").each(function(i){
        var h = $(".row18").eq(i).height();
        if(h > H) H = h;
    });
    $(".row18").height(H);	
	//row19
    var H = 0;
    $(".row19").each(function(i){
        var h = $(".row19").eq(i).height();
        if(h > H) H = h;
    });
    $(".row19").height(H);		
	//row1b
    var H = 0;
    $(".row1b").each(function(i){
        var h = $(".row1b").eq(i).height();
        if(h > H) H = h;
    });
    $(".row1b").height(H);
	//row2b
    var H = 0;
    $(".row2b").each(function(i){
        var h = $(".row2b").eq(i).height();
        if(h > H) H = h;
    });
    $(".row2b").height(H);
	//row3b
    var H = 0;
    $(".row3b").each(function(i){
        var h = $(".row3b").eq(i).height();
        if(h > H) H = h;
    });
    $(".row3b").height(H);	
	//row 1examination 1e
    var H = 0;
    $(".row1e").each(function(i){
        var h = $(".row1e").eq(i).height();
        if(h > H) H = h;
    });
    $(".row1e").height(H);
	//row 2examination 2e
    var H = 0;
    $(".row2e").each(function(i){
        var h = $(".row2e").eq(i).height();
        if(h > H) H = h;
    });
    $(".row2e").height(H);	
	//this is for t-done table showing unfinished treatments
	//row 2examination 2e
    var H = 0;
	/*
    $(".row2e").each(function(i){
        var h = $("th.treat_auothorised_cost2").height();
        if(h > H) H = h;
    });
    $(".row2e").height(H);	*/	
	//$(".hide_first").removeClass("hide_element");
});




 //float table header
var off1;
off1 = $(".replace_header").offset();
//alert('off13 is ' + $(".replace_header").offset());
if(off1  == 'undefined'){}//alert('cccc');}
else if(off1  !== 'undefined'){//alert('cccffffc ' + off1.top);
	 var tableOffset = off1.top;
	//var header = $(".float_table_header > thead").clone().addClass('header-fixed');
	//var fixedHeader = $("#header-fixed").append(header);
	var fixedHeader = $(".header-fixed ");
	fixedHeader.width($(".replace_header").width() );
	//fixedHeader.show();
	//alert(fixedHeader.css('display'));
	$(window).bind("scroll", function() {
		var offset = $(this).scrollTop();
		//alert('offset is ' + offset + ' and tableoffet is ' + tableOffset);
		//if (offset >= tableOffset && fixedHeader.is(":hidden")) {
		if (offset >= tableOffset ) {
			fixedHeader.show();
			//alert('cc');
		}
		else if (offset < tableOffset) {
			fixedHeader.hide();
		}
	});
}
}

	
 $(document).ready(function(){	

	mainmenu();
	
});