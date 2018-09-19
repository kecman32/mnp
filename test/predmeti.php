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
    
    <div class="container-fluid" style="margin-top: 50px; margin-bottom: 50px;">
    	<div class="row">
    		<div class="col-md-2">
    			<h4>Razred</h4>
    			<div class="container">
    				<select class="custom-select" name="raz" id="razred">
    					
    				</select>
    			</div>
    		</div>
    		<div class="col-md-3">
    			<h4>Jezik</h4>
    			<div class="container">
    				<select class="custom-select" name="jez" id="jezik">
    					
    				</select>

    				<input type="button" class="btn btn-danger" value="Ubaci u bazu" id="sub" style="margin-top: 150px;">
    			</div>
    		</div>
    		<div class="col-md-7">
    			<h4>Predmet</h4>
    			<div class="container" id="predmet">
    				
    			</div>
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

			for (var i = 0; i < data.length; i++) {
				display_predmeti += '<input type="checkbox" name="pred'+i+'" id="predmet'+i+'" value="'+data[i].predmet_id+'"> '+data[i].naziv+'</br>';
			}
			predmeti_br = i;
			$("#predmet").html(display_predmeti);
			
			

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
			
			

	}, "json");
}
</script>
  </body>
</html>