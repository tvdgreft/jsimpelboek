<?php
namespace SIMPELBOEK;
/**
 * Functies t.b.v. modals
 *
 * @copyright 2022 pranamas
 */

/** Toon een help in een modal
 * $args["docdir"] - root directory of help files
 * $args['manual'] - filename of general manual
 * $args['extension'] =  filename of detail mnual of current page. If not avalable, the general manual will be displayed
 * The manuals should be written in html
 */
function HelpModal($manual) : string 
{
	$html = '';
	/*
	$main = $args["docdir"] . $args["manual"] . ".html";
	$part = "part";
	if(isset($args["extension"])) { $part= $args["docdir"] . $args["manual"] . "_" . $args["extensiom"] . ".html"; }
	if(file_exists($part)) 		{ $manual = $part; }
	elseif(file_exists($main))	{ $manual = $main; }
	else { return($html); }
	*/
	if(!file_exists($manual)) { return($html); }
	$fh = fopen($manual, 'r');
	$help = fread($fh, filesize($manual));
	fclose($fh);
	$html .= ModalScroll('<i class="prana-button" style="font-size:24px;">help</i>',$help);
	$html .= '<br><br>';
	return($html);
}
function ModalScroll($link,$content)
{
	$m = '';
	$m .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
	$m .= '<style>
		/* The Modal (background) */
		.modal {
			display: none; /* Hidden by default */
			position: fixed; /* Stay in place */
			z-index: 1; /* Sit on top */
			padding-top: 200px; /* Location of the box */
			left: 0;
			top: 0;
			width: 100%; /* Full width */
			height: 50%; /* Full height */
			overflow: auto; /* Enable scroll if needed */
			background-color: rgb(0,0,0); /* Fallback color */
			background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
		}
			/* Modal Content */
		.modal-content {
			background-color: #fefefe;
			margin: auto;
			padding: 20px;
			border: 1px solid #888;
			width: 80%;
		}
			/* The Close Button */
		.close {
			color: #a80707;
			float: right;
			font-size: 28px;
			font-weight: bold;
			background-color: #9ad0ae;
		}
		.close:hover,
		.close:focus {
			color: #a80707;
			text-decoration: none;
			cursor: pointer;
		}
		</style>';
	
		#$m .= '<h2>' . $title . '</h2>';
		# Trigger/Open The Modal
	$m .= '<button id="myBtn" class="pbtnok">' . $link . '</button>';
		//$m .= '<button id="' . $link . '">' . $link . '</button>';

		#The Modal -->
	$m .= '<div id="myModal" class="modal">';
	$m .= '	<div class="modal-content">';
	$m .= '		<span class="close">close</span>';
	$m .= $content;
	$m .= '</div>';
	$m .= '</div>';
	$m .= '
		<script>
		// Get the modal
		var modal = document.getElementById("myModal");
			// Get the button that opens the modal
		var btn = document.getElementById("myBtn");
		//var btn = document.getElementById("' . $link . '");
			// Get the <span> element that closes the modal
		var span = document.getElementsByClassName("close")[0];
			// When the user clicks the button, open the modal 
		btn.onclick = function() {
			modal.style.display = "block";
		}
			// When the user clicks on <span> (x), close the modal
		span.onclick = function() {
			modal.style.display = "none";
		}
			// When the user clicks anywhere outside of the modal, close it
		window.onclick = function(event) {
			if (event.target == modal) {
				modal.style.display = "none";
			}
		}
		</script>';
		
	return($m);
}
