function  mainmenu2(){
	//check if the timer has expired
		/*	var timer_expire = 'yes';
			var form_data = {timer_expire: timer_expire}
			element = $(this);
			$.ajax({
			type: "POST",
			url: "dental_b/",
			data: form_data,
			error: function() {
			alert(" An Error occured, unable to check inactivity period");
			e.preventDefault();
			},
			success: function(data) {
			alert('data is ' + data);
				if(data == 'unset'){
					window.location = "?";
					exit;
				};	
			},
			complete: function() {
			}
			});*/
			
	//check destination tab first
	//alert('fff');
	var re = /#\w+$/;
	var match = re.exec(document.location.toString());
	//alert('re is ' + match);
	var match;
	if(match == '#examination'){var selected_tab = $('#tabs a[href="examination/"]').parent().index();}
	else if(match == '#patient_contacts'){
		var selected_tab = $('#tabs a[href="patient-contacts/"]').parent().index();
	}
	else if(match == '#treatment-done'){
		//location.reload(); 
		//alert('dd');
		var selected_tab = $('#tabs a[href="treatment-done/"]').parent().index();
	}
	else{var selected_tab = 0;}
	//var tab_index = check_tab_to_go_to();
	//alert('tab is ' + tab_index);
				//this is for tabs
				 $( "#tabs" ).tabs({
				 
					selected: selected_tab,
					beforeLoad: function( event, ui ) {
						//alert(ui.newPanel.attr('id'));
						//alert(ui.newPanel[0].id);
				//alert('tab is ' + check_tab_to_go_to());
						ui.jqXHR.error(function() {
						ui.panel.html("ERROR: Unable to load content");
						});
					},
					activate: function(event, ui) {
						var tab_id = ui.newPanel[0].id;
						//alert(tab_id);
						if(ui.newPanel[0].id == 'ui-tabs-9'){
						var n = $( ".div_shower44" ).length;
						//alert('n2 is ' + n);
						if(n > 0){$( ".div_shower44" ).remove();}
						$('#'+ tab_id).load("treatment-done/index.php");
						}
					},
				});	
				
				//this is for cadcam tabs
				 $( " .div_shower44 #cadcam_tabs,  #cadcam_tabs4" ).tabs({
					//selected: selected_tab,
				//	var selectedPanel = $("#cadcam_tabs div.ui-tabs-panel:not(.ui-tabs-hide)");
				//	alert(selectedPanel);
					beforeLoad: function( event, ui ) {
					//alert('tab is ' + check_tab_to_go_to());
						ui.jqXHR.error(function() {
						ui.panel.html("ERROR: Unable to load content");
						});
					},
				});	
				
				//this is for cadcam tabs
				 $( "#cadcam_tabs3" ).tabs({
				 
					selected: selected_tab,
				//	var selectedPanel = $("#cadcam_tabs div.ui-tabs-panel:not(.ui-tabs-hide)");
				//	alert(selectedPanel);
					beforeLoad: function( event, ui ) {
					//alert('tab is ' + check_tab_to_go_to());
						ui.jqXHR.error(function() {
						ui.panel.html("ERROR: Unable to load content");
						});
					},
				});		
			
}

	
 $(document).ready(function(){	
	//passcheck();
	//test();
	mainmenu2();
	
});