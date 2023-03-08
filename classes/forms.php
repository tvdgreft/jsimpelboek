<?php
namespace SIMPELBOEK;
class Forms
{
	/*
		default values for inputfields
	*/
	public $formdefaults = array(
		'name' => '',
		'value' => '',
		'width' => '100%',
		'height' => '35px',
		'rows' => '4',				#number of rows of textarea
		'readonly' => FALSE,
		'collabel' => 'col-md-3',	#size of label
		'colinput' => 'col-md-6',	#size of inputfield
		'col' => 'col-md-3',
		'required' => TRUE,
		'autofocus' => FALSE,	#set cursur on input field
		'uploaderror' => '',
		'inline' => TRUE,			#label and input in one line
		'group' => TRUE,			#label and input will not be followed by other items
		'dateformat' => 'yy-mm-dd',	#format of date
		'checkclass' => '',
		'error' => 'input error',
		'type' => 'text',
		'popover' => '',			#text for a popover
		'placeholder' => '',		#placeholder input field
		'choose' => 'maak een keuze',	#keuze tekst
		'onchange' => '',			#jscript function after changing the input
		'confirm' => '',			#confirm message.
		'submit' => FALSE			# submit after changing input
	);
	#
	# button variables
	public $buttons = array();
	public $buttonclass =  "prana-button";
	public $buttoncol = "col-md-12";
	public $status = '';
	#
	# button
	#
	public function DisplayButtons()
    {
        $html = '';
		#$html .= '<div class="' . $this->buttoncol . '">';	
		foreach ($this->buttons as $m)
        {
			$id = $m['id'];
            $value = $m['value'];
			$status = isset($m['status']) ? $m['status'] : $this->status;
			$onclick= isset($m['onclick']) ? $m['onclick'] : "";
			$class= isset($m['class']) ? $m['class'] : "";
			$html .= '<button id="' . $id . '" class="' . $this->buttonclass . ' ' . $class . '" name="' . $id . '" value="' . $id . '"';
			if($onclick) { $html .= 'onclick="' . $onclick . ';"'; }
			$html .= ' ' . $status . '>' . $value;
			$html .= '</button>';
			$html .= '&nbsp';
		}
		#$html .= '</div>';
        return $html;
    }
	/**
	 * Text - invoerveld voor tekst
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 'value' => (string) beginwaarde van het invoerveld
	 * 'type' => type of input (default is 'text'
	 * 'required' => (Boolean) Input is required (default) or not
	 * 'readonly' => (Boolean) field is readonly
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * 'group' => (Boolean) textbox is a group (only when sinline = TRUE)
	 * 'width' => size of inputfield
	 * 'checkclass' => (string) classname for checking content (see: forms.js)
	 * 'error' => (string) message in case of error
	 * 'popover' => (string) text for a popover
	 * 'placeholder' => placeholder for the inputfield
	 * ]
	 */
	public function Text($args)
	{
		$args = $this->parse_args( $args, $this->formdefaults );
		
		$html='';
		if(!$args["name"]) { $args["name"] = $args["id"]; }		# als name niet gedefinieerd is name = id
		if($args['required']) { $args["label"] .= "*"; }
		if($args['inline']== TRUE) 
		{
			if($args["group"] == TRUE) { $html .= '<div class="form-group row">'; }
			$html .= 	'<div class="' . $args["collabel"] .'">';
		}
		else 
		{ 
			$html .= '<div class="' . $args["col"] . '">';
			$html .= '<div class="control-label">';
		}
		$html .= '<label for="' . $args["id"] . '"';
		/*
		if($args["popover"]) 
		{
			$html .= 'data-toggle="popover" data-placement="top" title="popover on top" data-content="content"';
		}
		*/
		#{ $html .= ' class="hasPopover"  title="' . $args["popover"] . '"'; }
		$html .= '>' .  $args["label"] . '</label>';
		$html .= '</div>';
		if($args['inline'] == TRUE) { $html .= '<div class="' . $args['colinput'] . '">'; }
		else { $html .= '<div class="controls">';}
		$args['name'] = $args['name'] ? $args['name'] : $args['id']; # name is id if not defined
		$html .= 	'<input class="form-control ' . $args['checkclass'] .'" type="' . $args["type"] . '" id="' . $args["id"] . '" name="' . $args["name"] . '" value="' . $args['value'] . '"';
		$html .= ' style="width:' . $args['width'] . '; height:' . $args['height'] . ';"';
		if($args['required']== TRUE) { $html .= ' required="required"'; }
		if($args['readonly'] == TRUE) { $html .= ' readonly="readonly"'; }
		if($args['autofocus'] == TRUE) { $html .= ' autofocus="autofocus"'; }
		if($args["placeholder"]) $html .= 'placeholder="' . $args["placeholder"] . '"';
		if($args['submit'] == TRUE) { $html .= ' onchange="submit()"'; }  #submit after change the input
		$html .= '>';
		$html .= '<span class="error_hide">'.$args['error'].'</span>'; #span for error field see: forms.js
		$html .= '</div>';
		if($args["group"] == TRUE) { $html .= '</div>'; }
		return($html);
	}
	/**
	 * TextArea - invoerbox voor tekst
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 'value' => (string) beginwaarde van het invoerveld
	 * 'type' => type of input (default is 'text'
	 * 'required' => (Boolean) Input is required (default) or not
	 * 'readonly' => (Boolean) field is readonly
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * 'width' => size of inputfield (...px)
	 * 'checkclass' => (string) classname for checking content (see: forms.js)
	 * 'error' => (string) message in case of error
	 * 'popover' => (string) text for a popover (todo)
	 * 'placeholder' => placeholder for the inputfield
	 * 
	 */
	public function TextArea($args)
	{
		$args = $this->parse_args( $args, $this->formdefaults );
		if(!$args["name"]) { $args["name"] = $args["id"]; }		# als name niet gedefinieerd is name = id
		$html='';
		if($args['required']) { $args["label"] .= "*"; }
		$html .= '<div class="form-group row">';
		$html .= 	'<div class="' . $args["collabel"] .'">';
		$html .= 		'<label for="' . $args["id"] . '"';
		$html .= '>' .  $args["label"] . '</label>';
		$html .= '</div>';
		$html .= 	'<div class="' . $args["colinput"] . '">';
		$html .= 	'<textarea class="form-control' . $args["checkclass"] .'" type="text" id="' . $args["id"] . '" name="' . $args["id"] . '"';
		$html .= ' rows="' . $args["rows"] .'"';
		$html .= ' style="width:' . $args["width"] . ';"';
		if($args["required"]) { $html .= ' required="required"'; }
		if($args["readonly"]) { $html .= ' readonly="readonly"'; }
		if($args["placeholder"]) $html .= 'placeholder="' . $args["placeholder"] . '"';
		$html .= '>';
		$html .= $args["value"];
		$html .= '</textarea>';
		$html .= 	'<span class="error_hide">'.$args["error"].'</span>';
		$html .= 	'</div>';
		$html .= '</div>';
		return($html);
	}
	/**
	 * Radio - maak een keuze mbv radio buttons.
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 'value' => (string) beginwaarde van het invoerveld
	 *  options = array of options objects to choose e.g. array[labelvalue1=>$value1,labelvalue2=$value2,...]
	 * 'required' => (Boolean) Input is required (default) or not
	 * 'readonly' => (Boolean) field is readonly
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * ]
	 */
	public function Radio($args)
	{
		$html='';
		$args = $this->parse_args( $args, $this->formdefaults );
		if($args['required']) { $args["label"] .= "*"; }
		$id=$args["id"];
		$html='';
		$html .= '<div class="form-group row">';
		$html .= 	'<div class="' . $args['collabel'] .'">';
		$html .= 		'<label for="radios">' . $args["label"] . '</label>';
		$html .= 	'</div>';
		$html .= 	'<div class="' . $args['colinput'] . '">';
		foreach($args["options"] as $key => $value)
		{
			$selected="";
			if($value == $args['value']) { $selected = " checked";}
			if($args['inline'] == TRUE) { $html .= '<div class="form-check form-check-inline">'; }
			if($args['inline'] == FALSE) { $html .= '<div class="form-check">'; }
			$rid = $args["id"] . '_' . $value;
			$html .= 	'<input class="form-check-input" type="radio" id="' . $rid . '" name="' . $args['id'] . '" value="' . $value . '"' . $selected;
			if($args['required']) { $html .= ' required'; }
			if($args['readonly']) { $html .= ' disabled="disabled"'; }
			$html .= '>';
			$html .= '<label class="form-check-label" for="'. $args['id'] . '">' . '&nbsp;&nbsp;' . $key . '</label>';
			$html .=		'</div>';
		}
		$html .=		'</div>';
		$html .=		'</div>';
		return($html);
	}
	/**
	 * Check - make checkbox
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 'value' => (string) beginwaarde van het invoerveld
	 * 'checked' => (Boolean) default is checked
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * 'error' => (string) message in case of error
	 * ]
	 */
	public function Check($args)
	{
		$args = $this->parse_args( $args, $this->formdefaults );
		
		$html='';
		$args['name'] = $args['name'] ? $args['name'] : $args['id']; # name is id if not defined
		$html .= '<div class="form-group row">';
		$html .= 	'<div class="' . $args["collabel"] .'">';
		$html .= 		'<label for="' . $args["id"] . '"';
		$html .= '>' .  $args["label"] . '</label>';
		$html .= '</div>';
		$html .= 	'<div class="' . $args["colinput"] .'">&nbsp;&nbsp;&nbsp;&nbsp;';
		$html .= '<input class="form-check-input" type="checkbox" id="' . $args["id"] . '" name="' . $args["name"] . '" value="' . $args['value'] . '"';
		if($args['checked']== TRUE) { $html .= ' checked'; }
		if($args['confirm']) { $html .= 'onclick="return confirm(\'' . $args["confirm"] . '\')"'; }
		$html .= '>';
		$html .= '<span class="error_hide">'.$args['error'].'</span>'; #span for error field see: forms.js
		$html .= '</div>';
		$html .= '</div>';
		return($html);
	}
	/**
	 * Check - make checkboxes check 0ne or more boxes.
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 	$options=array() e.g. ["appel","peer","banaan"]
	 * 'value' => encoded choosen options
	 * 'row' => set boxes in one row
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * 'error' => (string) message in case of error
	 * ]
	 */
	
	public function Checkboxes($args)
	{
		$args = $this->parse_args( $args, $this->formdefaults );
		if($args["required"]) { $args["label"] .= "*"; } 
		$id=$args["id"];
		$html='';
		if($args["inline"] == TRUE) 
		{
			$html .= '<div class="form-group row">'; 
			$html .= 	'<div class="' . $args["collabel"] .'">';
		}
		else 
		{ 
			$html .= '<div class="' . $args["col"] . '">';
			$html .= '<div class="control-label">';
		}
		$html .= 		'<label for="checkbox">' . $args["label"] . '</label>';
		$html .= 	'</div>';
		$html .= 	'<div class="' . $args["colinput"] . '">';
		$values = [];
		if($args["value"]) { $values = json_decode($args["value"]); }	#get current values]
		foreach ($args["options"] as $option)
		{
			$selected = "";
			foreach($values as $value)
			{
				if($option == $value) { $selected="checked"; }
			}
			$html .= '<div class="form-check">';
			$rid = $args["id"] . '_' . $option;
			$html .= 	'<input class="form-check-input" type="checkbox" id="' . $rid . '" name="' . $id . '[]" value="' . $option . '" ' . $selected;
			if($args["readonly"]) { $html .= ' disabled="disabled"'; }
			$html .= '>';
			$html .=  '&nbsp;&nbsp;' . $option . '&nbsp;&nbsp;';
			#$html .= '<label class="form-check-label" for="'. $args["id"] . '">' . $option . '</label>';
			$html .=		'</div>';
		}
		$html .=		'</div>';
		$html .=		'</div>';
		return($html);
	}
	/**
	 * Date - invoerveld voor een datum
	 * @param array $args[
	 * label => (string) Label van het invoerveld
	 * value => (string) beginwaarde van het invoerveld
	 * format => (string) datum formaat
	 * colinput => (string) size of inputfield
	 * width => size of inputfield (...px)
	 * ]
	 * datepicker gedefinieerd in forms.js
	 */
	public function Date($args)
	{
		$args = $this->parse_args( $args, $this->formdefaults );
		if($args['required']) { $args["label"] .= "*"; } 
		$checkclass = isset($args["check"]) ? ' ' . $args["check"] : ''; # add check class if given so that javascript can test the content
		
		$html='';
		$html .= '<div class="form-group row">';
		$html .= 	'<div class="' . $args['collabel'] .'">';
		$html .= 		'<label for="' . $args["id"] . '"';
		#if(isset($args["popover"])) { $html .= ' class="hasPopover"  title="' . $args["popover"] . '"'; }
		$html .= 		'>' .  $args["label"] . '</label>';
		$html .=	'</div>';
		$html .= 	'<div class="' . $args['colinput'] . '">';
		$html .= '<input class="form-control ' . $args["checkclass"] . ' datepicker" type="text" id="' . $args["id"] . '" name="' . $args["id"]. '" style="width:' . $args["width"] . '" value="' . $args["value"] .'"';
		#$html .= '<input class="form-control" type="date" placeholder="dd-mm-yyyy" min="1900-01-01" max="2022-01-01" id=" ' . $args["id"] . '" name="' . $args["id"]. '" style="width:' . $args["width"] . '" value="' . $args["value"] .'"';
		#
		# set event on classes checkclass and datepicker in jquery !!
		#
		if($args['required']) { $html .= ' required="required"'; }
		if($args['readonly']) { $html .= ' readonly="readonly"'; }
		if(isset($args["placeholder"])) { $html.= ' placeholder="' . $args["placeholder"] . '"'; }
		
		$html .= 	'>';
		$html .= 	'<span class="error_hide">'.$args['error'].'</span>';
		$html .= 	'</div>';
		$html .= '</div>';
		
		return($html);
	}
	
	/**
	 * Dropdown - maak een keuze mbv een dropdown box
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 'value' => (string) beginwaarde van het invoerveld
	 *  options = array of options objects to choose e.g. array[labelvalue1=>$value1,labelvalue2=$value2,...]
	 * 'required' => (Boolean) Input is required (default) or not
	 * 'readonly' => (Boolean) field is readonly
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * 'choose' => keuze tekst bv Maak een keuze
	 * ]
	 */
	public function Dropdown($args)
	{
		$args = $this->parse_args( $args, $this->formdefaults );
		if(!$args["name"]) { $args["name"] = $args["id"]; }		# als name niet gedefinieerd is name = id
		if($args['required']) { $args["label"] .= '*'; } 
		$html='';
		if($args['inline']== TRUE) 
		{
			if($args["group"] == TRUE) { $html .= '<div class="form-group row">'; }
			$html .= 	'<div class="' . $args["collabel"] .'">';
		}
		else 
		{ 
			$html .= '<div class="' . $args["col"] . '">';
			$html .= '<div class="control-label">';
		}
		$html .= 		'<label for="checkbox">' . $args["label"] . '</label>';
		$html .= 	'</div>';
		if($args['inline'] == TRUE) { $html .= '<div class="' . $args['colinput'] . '">'; }
		else { $html .= '<div class="controls">';}
		$options = "";
		$options .= '<option value="" selected>' . $args["choose"] . '</option>';   # keuze tekst
		foreach($args["options"] as $key => $value)
		{
			$selected = $value == $args["value"] ? " selected=selected" : "";
			$options .= '<option value="' . $value . '" ' . $selected . '>' . $key . '</option>';
		}
		$html .= '<select id="' . $args['id'] . '" name="' . $args['name'] . '" style="padding:0px;width:' . $args['width'] . ';height:' . $args['height'] . ';"';
		if($args['required']) { $html .= ' required'; }
		if($args['readonly']) { $html .= ' readonly="readonly"'; }
		if($args['submit'] == TRUE) { $html .= ' onchange="submit()"'; }  #submit after change the input
		$html .= '>';
		$html .= $options;
		$html .= '</select>';
		$html .= '</div>';
		if($args["group"] == TRUE) { $html .= '</div>'; }
		return($html);
	}

	/**
	 * File - Read a file.
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 'value' => (string) beginwaarde van het invoerveld
	 *  options = array of options objects to choose e.g. array[labelvalue1=>$value1,labelvalue2=$value2,...]
	 * 'required' => (Boolean) Input is required (default) or not
	 * 'readonly' => (Boolean) field is readonly
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * 'accept' =>  comma-separated list of one or more file types, describing which file types to allow
	 * 'width' , 'height' => size of inputfield (...px)
	 * ]
	 */
	public function File($args)
	{
		$args = $this->parse_args( $args, $this->formdefaults );
		if(!$args["name"]) { $args["name"] = $args["id"]; }		# als name niet gedefinieerd is name = id
		if($args['required']) { $args["label"] .= '*'; } 
		$html='';
		$r='';
		$checkclass = isset($args["check"]) ? ' ' . $args["check"] : ''; # javascript test input on this class
		$html .= '<div class="form-group row">';
		$html .= 	'<div class="' . $args["collabel"] .'">';
		$html .= 		'<label for="' . $args["id"] . '"';
		$html .= 		'>' .  $args["label"] . '</label>';
		$html .=	'</div>';
		$html .= 	'<div class="' . $args["colinput"] . '">';
		$html .= '<input type="file" id="' . $args["id"] . $checkclass .'" name="' . $args['name'] . '" value="' . $args["value"] . '"';
		$html .= 'style="width:' . $args["width"] . '; height:' . $args['height'] . ';"';
		if($args["required"]) { $html .= ' required="required"'; }
		if($args["readonly"]) { $html .= ' readonly="readonly"'; }
		if($args["onchange"]) { $html .= ' onchange="' . $args["onchange"] . '"'; }
		if($args["accept"]) { $html .= ' accept="' . $args["accept"] . '"'; }
		$html .= $args["placeholder"] ? '' : ' placeholder="' . $args["placeholder"] . '"';
		$html .= 	'>';
		$html .= 	'</div>';
		$html .= '</div>';
		return($html);
	}
	#
	# Image
	# upload an image and show it directly
	# $args["uploads"] - upload map of images
	# $args["value"] - current image
	# $args["label"] = label of text box
	# $args["id"] = id and name
	# $args["width"] = width of image
	# $args["heigth"] = width of image
	# $args["required"] = 1 if the box is required
	# $args["collabel"] = bootstrap position label 
	# $args["accept"] = Only accept certain files (e.g. ".jpg,.jpeg")
	#
	public function Image($args)
	{
		$html='';
		$args = $this->parse_args( $args, $this->formdefaults );
		if($args['required']) { $args["label"] .= '*'; } 
		
		if($args['inline']== TRUE) 
		{
			if($args["group"] == TRUE) { $html .= '<div class="form-group row">'; }
			$html .= 	'<div class="' . $args["collabel"] .'">';
		}
		else 
		{ 
			$html .= '<div class="' . $args["col"] . '">';
			$html .= '<div class="control-label">';
		}
		$html .= 		'<label for="checkbox">' . $args["label"] . '</label>';
		$html .= 	'</div>';
		if($args['inline'] == TRUE) { $html .= '<div class="' . $args['colinput'] . '">'; }
		else { $html .= '<div class="controls">';}
		#
		# image element to place image in it
		#
		$uploads = $args['uploads'];
		$photo_url = \JURI::base() . $uploads  . '/' . $args["value"];
		$photo_file = JPATH_SITE  . '/' . $uploads  . '/' . $args["value"];
		echo $photo_file;
		$html .= '<img id="showimage" src="' . $photo_url . '?' . filemtime($photo_file) .'" width="' . $args["width"] .'" height="' . $args["height"] . '" alt="foto">';
		#
		# input the file
		# the class showimage is trigger for javascript ShowImage to show the image in the img above
		#
		$html .= '<div class="' . $args["colinput"] . '">';
		$html .= '<input type="file" id="' . $args["id"] . '" class="form-control showimage" name="' . $args["id"] . '" value="' . $args["value"] . '"';
		$html .= ' style="' . $args["width"];
		if($args["required"]) { $html .= ' required="required"'; }
		if(isset($args["accept"])) { $html .= ' accept="' . $args["accept"] . '"'; }
		$html .= '>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		return($html);
	}
	/*
	* upload the selected file
	* @param array $args[
	* targetdir = directory to put the file in
	* file = file element in $_FILES
	* filetypes = legal filetypes seperated by , e.g.: doc,docx,pdf
	* maxkb = maximum size of file in Kb
	* overwrite=1 (overwrite existing file allowed)
	* filename = filename (without extension), if not defined the original filename of the uploaded file is given
	*			extension is extension of original file
	* prefix=unique prefix to force unique filename (optional)
	* return value TRUE or FALSE
	* If FALSE message in uploaderror:
	*  Bad filetype
	*  file exists
	*  file too big
	*  File cannot be uploaded
	*/
	public function UploadFile($args) : bool
	{
		if(!isset($args["file"])) { $this->uploaderror = "file attribute not defined"; return(FALSE); }
		$file = $args["file"];
		$prefix = isset($args["prefix"]) ? $args["prefix"] : "";
		$overwrite = isset($args["overwrite"]) ? $args["overwrite"] : FALSE;
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
		$filename = isset($args["filename"]) ? $args["filename"] . '.' . $ext : basename($file["name"]);
		$targetfile = $args["targetdir"] . '/' . $prefix . $filename;
		if(isset($args["filetypes"]))
		{
			$types=explode(",",$args['filetypes']);
			$found = FALSE;
			foreach($types as $t) { if($t == $ext) { $found=TRUE; } }
			if($found == FALSE) { $this->uploaderror = "bad filetype"; return(FALSE); }
		}
		
		if(isset($args["maxkb"]))
		{
			$fileSize = $file["size"];
			$maxsize = $args["maxkb"] * 1000;
			if($fileSize > $maxsize) { $this->uploaderror = " file too big"; return(FALSE); }
		}
		if($overwrite == FALSE && file_exists($targetfile)) { $this->uploaderror = "file exists"; return(FALSE); }
		if (!move_uploaded_file($file['tmp_name'], $targetfile)) { $this->uploaderror = "cannot upload"; return(FALSE); }
		return(TRUE);
	}
	#
	# download a file
	#
	public function DownloadFile($file)
	{
		$html = '';
		if(!file_exists($file))
		{
			$error = sprintf(prana_Ptext('nofile','bestand %s bestaat niet'),$file);
			$html .= '<div class="isa_error" >' . $error . '</div>';
			return($html);
		}
		$pathinfo=pathinfo($file);
		#print_r($pathinfo);
		$extension=$pathinfo['extension'];
		$basename =$pathinfo['basename'];
		#echo "extension=" . $extension;
		#$form .= '<br>basename='.$basename.'extension="'.$extension.'"';
		if($extension == "txt") { $type = "Content-type: text/plain"; }
		elseif($extension == "pdf") { $type = "Content-type:application/pdf"; }
		else					{ $type = "application/octet-stream"; }
		#$form .= '<br>basename='.$basename.'extension="'.$extension.'"'.'type=' . $type;
		//ob_end_clean(); - Potential fix underneath.
		if (ob_get_contents()) ob_end_clean();
		$app = \JFactory::getApplication();
		header('Pragma: public');
		header('Expires: 0');
		# caching doesnot know if important
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename="'.$basename.'"');
		header($type);
		header('Content-Length: '.filesize($file));
		#ob_clean();
		#ob_end_flush();
		readfile($file);
		$app->close();
		return($html);
	}
	#
	# StoreImage
	# store the image in the map 
	# $args["uploads"] - upload map of images
	# $args["id"] = id and name
	# $args["name"] = name of image
	# $args["width"] = wiidth of image
	public function StoreImage($args)
	{
		$map = ABSPATH . $args["uploads"];
		echo '<br>map=' . $map . 'id=' . $args['id'];
		echo '<br>';
		#print_r($_FILES);
		if(isset($_FILES[$args["id"]]))
		{
			echo '<br>startupload';
			if (move_uploaded_file($_FILES[$args["id"]]['tmp_name'], $map))
			{
				return(TRUE);
			}
			return(FALSE);
		}
		return(FALSE);
	}
	#
	# resize the image 
	#
	public function resize_image($file, $w, $h, $crop=FALSE) 
	{
		list($width, $height) = getimagesize($file);
		$r = $width / $height;
		if ($crop) 
		{
			if ($width > $height) 
			{
				$width = ceil($width-($width*abs($r-$w/$h)));
			} 
			else 
			{
				$height = ceil($height-($height*abs($r-$w/$h)));
			}
			$newwidth = $w;
			$newheight = $h;
		} 
		else 
		{
			if ($w/$h > $r) 
			{
				$newwidth = $h*$r;
				$newheight = $h;
			} 
			else 
			{
				$newheight = $w/$r;
				$newwidth = $w;
			}
		}
		$src = imagecreatefromjpeg($file);
		$dst = imagecreatetruecolor($newwidth, $newheight);
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		imagejpeg( $dst, $file );
		return;
	}
	function parse_args($nargs,$default)
	{
		$args=$default;
		foreach ($nargs as $arg=>$value)
		{
			$args[$arg] = $value;
		}
		return($args);
	}
}

?>