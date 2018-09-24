<?php 

?>


<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title>predmeti po razredima i jezicima</title>
  </head>
  <body>
    <div class="container mt-4">
    	<h3>Uredjivanje predmeta po razredima i jezicima nastave</h3>
    	
    </div>
    <div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    	
    	<div class="row">
    		<div class="col-md-3">
    			<h5>Razred</h5>
    			<div class="container p-0">
    				<select class="custom-select" name="raz" id="razred" style="width: 150px;">
    					
    				</select>
    			</div>
    		</div>
    		<div class="col-md-5">
    			<h5>Jezik</h5>
    			<div class="container p-0">
    				<select class="custom-select" name="jez" id="jezik"  style="width: 200px">
    					
    				</select>
	
    				
    			</div>
    		</div>
    		<div class="col-md-4">
    			<div class="container text-center mt-4">
					<input type="button" class="btn btn-primary" value="Ubaci u bazu" id="sub">
		    		<p  id="poruka" class="text-success mt-3"></p>
		    	</div>
    		</div>
    	</div>
    	<div class="row mt-4">
    		<div class="col-md-12">
    			<h5>Predmet</h5>
    			<div class="container mt-3 p-0" id="predmet" style="column-count: 3; font-size: 18px; column-rule: 1px solid lightgray;">
    				
    			</div>
    		</div>
    	</div>
    </div>
    <hr>
    <div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    	<h3 class="mb-4">Dodavanje predmeta</h3>
    	<div class="row">
    		<div class="col-md-8">
    			<input type="text" class="form-control" id="predmet_unos" placeholder="Upisite predmet">
    		</div>
    		<div class="col-md-4 text-center">
    			<input type="button" class="btn btn-primary" value="Ubaci novi predmet u bazu" id="sub_predmet">
		    	<p  id="poruka_unos" class="text-success mt-3"></p>
    		</div>
    	</div>
    </div>
    <hr>
    <div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    	<h3 class="mb-4">Uklanjanje sa liste aktivnih predmeta</h3>
    	<div class="row">
    		<div class="col-md-8">
    			<select class="custom-select" name="pred-brisanje" id="predmet_brisanje">
    					
    			</select>
    		</div>
    		<div class="col-md-4 text-center">
    			<input type="button" class="btn btn-primary" value="Ukloni predmet" id="del_predmet">
		    	<p  id="poruka_del" class="text-success mt-3"></p>
    		</div>
    	</div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

<script>
var predmeti_br = 0;
jQuery(document).ready(function($) {

	getRazredi();
	getJezici();
	getPredmeti();

	$('#razred').change(function(event) {
		var razred = $(this).val();
		var jezik = $('#jezik').val();
		resetCheck();
		console.log(razred);
		console.log(jezik);

		if (razred > 0 && jezik > 0) {
			izabraniPredmeti(razred, jezik);
		}
	});

	$('#jezik').change(function(event) {
		var jezik = $(this).val();
		var razred = $('#razred').val();
		resetCheck();
		console.log(razred);
		console.log(jezik);

		if (razred > 0 && jezik > 0) {
			izabraniPredmeti(razred, jezik);
		}
	});


	$('#sub').click(function(event) {
		var razred = $('#razred').val();
		var jezik = $('#jezik').val();
		if (razred > 0 && jezik > 0) {
			var nizPredmeta = [];
			for (var i = 0; i < predmeti_br; i++) {
				var x = $('#predmet'+i);
				if (x.is(':checked')){
				    nizPredmeta.push(Number(x.val()));
      			}
			}
			console.log(nizPredmeta);
		upisUBazu(razred, jezik, nizPredmeta);
		}
	});


	$('#sub_predmet').click(function(event) {
		var novi_predmet = $('#predmet_unos').val();
		if (novi_predmet !== "") {
			noviPredmet(novi_predmet);
		}

	});


	$('#del_predmet').click(function(event) {
		var del_predmet = $('#predmet_brisanje').val();
		console.log(del_predmet);
		if (del_predmet !== "") {
			delPredmet(del_predmet);
		}

	});


});

function getRazredi() {
	$.post('ws_predmeti.php', {
			key: 'razredi'			
		},
		function(data, textStatus) {
			//data = jQuery.parseJSON(data);
			console.log(data);
			
			var display_razred = '<option value="0">Izaberi...</option>';

			for (var i = 0; i < data.length; i++) {
				display_razred += '<option value="'+data[i].razred_id+'">'+data[i].naziv+'</option>';
			}

			$("#razred").html(display_razred);
			
			

	}, "json");
}

function getJezici() {
	$.post('ws_predmeti.php', {
			key: 'jezici'			
		},
		function(data, textStatus) {
			//data = jQuery.parseJSON(data);
			console.log(data);
			
			var display_jezici = '<option value="0">Izaberi...</option>';

			for (var i = 0; i < data.length; i++) {
				display_jezici += '<option value="'+data[i].jezik_id+'"> '+data[i].naziv+'</option>';
			}

			$("#jezik").html(display_jezici);
			
			

	}, "json");
}	


function getPredmeti() {
	$.post('ws_predmeti.php', {
			key: 'predmeti'			
		},
		function(data, textStatus) {
			//data = jQuery.parseJSON(data);
			console.log(data);
			
			var display_predmeti = '';
			var display_predmeti_brisanje = '<option value="0">Izaberi...</option>';


			for (var i = 0; i < data.length; i++) {
				display_predmeti += '<input class="mb-2" type="checkbox" name="pred'+i+'" id="predmet'+i+'" value="'+data[i].predmet_id+'"> '+data[i].naziv+'</br>';
				display_predmeti_brisanje += '<option value="'+data[i].predmet_id+'"> '+data[i].naziv+'</option>';
			}
			predmeti_br = i;
			$("#predmet").html(display_predmeti);
			$("#predmet_brisanje").html(display_predmeti_brisanje);
			
			

	}, "json");
}	


function izabraniPredmeti(razred, jezik) {
	$.post('ws_predmeti.php', {
			key: 'izabrani-predmeti',
			razred_id: razred,
			jezik_id: jezik			
		},
		function(data, textStatus) {
			//data = jQuery.parseJSON(data);
			console.log(data);
			var niz = new Array;
			for (var i = 0; i < data.length; i++) {
				niz.push(data[i].predmet_id);
			}
			console.log(niz);

			for (var i = 0; i < predmeti_br; i++) {
				var ppp = $('#predmet'+i);
				var vrednost = Number(ppp.val());
				if ( $.inArray(vrednost, niz) != -1) {
					console.log(vrednost);
					ppp.attr('checked', true);
				}
				else {
					ppp.removeAttr('checked');
				}

			}
			
			// var display_predmeti = '';

			// for (var i = 0; i < data.length; i++) {
			// 	display_predmeti += '<input type="checkbox" name="predmet'+i+'" value="'+data[i].predmet_id+'"> '+data[i].naziv+'</br>';
			// }
			// predmeti_br = i;
			// $("#predmet").html(display_predmeti);
			
			

	}, "json");
}	

function resetCheck() {
	$('#poruka').html('');
	$('#poruka_unos').html('');
	$('#poruka_del').html('');

	for (var i = 0; i < predmeti_br; i++) {
		var ppp = $('#predmet'+i);
		ppp.removeAttr('checked');
		
				

	}
}


function upisUBazu(razred, jezik, nizPredmeta) {
	//var nizz = JSON.stringify(nizPredmeta);
	$.post('ws_predmeti.php', {
			key: 'upis',
			razred_id: razred,
			jezik_id: jezik,
			nizz: nizPredmeta			
		},
		function(data, textStatus) {
			console.log(data);
			if (data.status) {
				$('#poruka').html(data.msg);
			}
			
	}, "json");
}


function noviPredmet(novi_predmet) {
	$.post('ws_predmeti.php', {
			key: 'novi_predmet',
			novi_predmet: novi_predmet			
		},
		function(data, textStatus) {
			console.log(data);
			if (data.status) {
				$('#poruka_unos').removeClass('text-danger').addClass('text-success').html(data.msg);
				getPredmeti();

			}
			else {
				$('#poruka_unos').removeClass('text-success').addClass('text-danger').html(data.msg);

			}
			
	}, "json");
}

function delPredmet(del_predmet) {
	$.post('ws_predmeti.php', {
			key: 'del_predmet',
			del_predmet: del_predmet			
		},
		function(data, textStatus) {
			console.log(data);
			if (data.status) {
				$('#poruka_del').removeClass('text-danger').addClass('text-success').html(data.msg);
				getPredmeti();

			}
			else {
				$('#poruka_del').removeClass('text-success').addClass('text-danger').html(data.msg);

			}
			
	}, "json");
}




</script>
  </body>
</html>