<?php
namespace SIMPELBOEK;
class tableform
{
    #########################################################################################################
	# tableform
	#########################################################################################################
    #Variables to be set:
	
	public $class;		#the class which extends tableform
	public $single;		#single name of record
	public $plural;		#plural name of record
	public $table;		#tablename
	public $logtable;	#table for logging
	public $primarykey;		#field with primary key of table
	public $columns = array();	#array of columnnames to be displayed e.g.  [["id","nr","int"],["name","naam","string"]]
	public $joincolumns = array(); #is een kolom met als resultaat van een join met de lopende tabel en een andere tabel
						# kop van kolom,veld in lopende tabel, te joinen tabel, "veld in te joinen tabel, veld te tonen uit andere tabel
						# bv ["gebouw","house","appartments","huisnummer","building"]
	public $searchcolumns;	#columns to be searched by a given searchkey (public search)
	public $onsearch;			#last given searchkey (public seaech)
	public $allcolumns;	#all columns of the table
	public $aligns;		#aligns of the columns (left,right or center)
	public $rows_per_page;	#maximum number of rows per page
	public $num_rows;	#array of number of rows per page
	public $backgroundcolor;	#backgroundcolor of table and forms
	public $filtercolor;	#backgroundcolor of filterbox in table
	public $permissions;	#permissions for maintaining table cr,md,dl,vw,cp(kopie maken)dm(demo records laden)
	public $onpage = 1;	#pagenummer will be changed bij POST value during 
	public $nextpage;	#clicked on nextpage in previuos run
	public $previouspage;	#clicked in previouspage in previuos run
	public $onsort = "id";		#column to be sorted
	public $sortorder = "DESC";	#order of sorting (ASC or DESC)
	public $sortclicked = False;	#sort is clicked
	public $filters;	#user defined filters
	public $filtercolumns;	#Columns to be filtered  e.g. array("soort"=>"soortlabel","type"=>"typelabel")
	public $buttonclass =  "prana-button col"; #default button class
	
	#
	# MaintainTable
	# This function reads the records from the database eventually filtered by the filteroptions.
	# The records are sorted if the headername of the column has been clicked.
	# This function displays a filterbox to make it possible to filter on the fields given in the filterarguments (filtercolumns)
	# The columns, defined in the columns argument (columns)
	# The records are printed in pages. Number of records per page is given as argument (rows_per_page)
	# At the button a button is displayed for creating a new record en a button to export the records to csv file
	#
	# POST values which are made foor forther actions on the list:
	# nextpage : currentpage
	# previouspage : correntpage
	# onsort : current sort column
	# onpage : current page
	 #
    # start er restart tableform
    #
    public function MaintainTable()
    {   
        $html = '';
		#echo "<br>voor<br>";
		#print_r($_POST);
		$this->GetPosts();
		#echo "<br>na<br>";
		#print_r($_POST);
        if(isset($_POST['createrecord'])) 
        { 
            $html .= $this->CreateRecord(); # function in tableform.php
            return($html);
        }
        if(isset($_POST['modifyrecord'])) 
        { 
            $html .= $this->ModifyRecord(); # function in tableform.php
            return($html);
        }
        if(isset($_POST['copyrecord'])) 
        { 
            $html .= $this->CopyRecord(); # function in tableform.php
            return($html);
        }
        #
        # write record to database
        #
        if(isset($_POST['writerecord'])) 
        { 
            $html .= $this->WriteRecord();
            $html .= $this->DisplayTable();
            return($html);
        }
		#
        # write record to database and ask for next record to create
        #
        if(isset($_POST['writerecordandnext'])) 
        { 
            $html .= $this->WriteRecord();
			$html .= $this->CreateRecord(); # function in tableform.php
            return($html);
        }
        if(isset($_POST['deleterecord'])) 
        { 
            $html .= $this->DeleteRecord();
            $html .= $this->DisplayTable();
            return($html);
        }

		/**
		 *  initial load ask for file
		 */
        if(isset($_POST['initialload']))
        {
            $html = $this->InitialLoad();
			return($html);
        }
		/**
		 *  write records to database and display records
		 */
		if(isset($_POST['writeinitialload']))
        {
            $this->WriteInitialLoad();
			$html .= $this->DisplayTable();
			return($html);
        }
		/**
		 * restore the filters
		 */
        $filters = array();
        if(isset($_POST['filter']))
        {
            foreach ($this->filtercolumns as $c => $label)
            {
                if(isset($_POST[$c])) { $filters[$c] = $_POST[$c]; }
                $this->filters = (object)$filters;
            }
			#echo "<br>new filters<br>";
			#print_r($this->filters);
        }
		$html .= $this->DisplayTable();
        
        $html .='<input id="' . $this->class . '" name="' . $this->class . '" type="hidden" />';
		return($html);
	}
	public function DisplayTable()
	{
		$self = new self();
		$dbio = new dbio();		#class for database I/O
		$form = new forms();	#class for formfields
		$html = '';
		if(isset($this->filtercolumns) && count($this->filtercolumns))
		{
			$html .= '<div class="row">';				# set search box at the right
			$html .= '<div class="col-md-6")>';			# left part of window 
			$html .= '<p onclick="ToggleFilters(\'filterbox\')"><a class="prana-button">Toon zoekscherm</a></p>';
			if($this->num_rows)
			{
				$options = array_combine($this->num_rows,$this->num_rows); #convert to assoc array
				$options += array(prana_Ptext("all","alle") => "*");
				$html .= $form->Dropdown(array("label"=>prana_PText("rows_per_page","regels per pagina"), "id"=>"rows_per_page", "value"=>$this->rows_per_page, "collabel"=>"col-md-5", "colinput"=>"col-md-6" ,"options"=>$options, "width"=>"50px", "submit" => TRUE));
			}
			$html .= '</div>';
			$html .= '<div id="filterbox" style="display:none" class="col-md-6 prana-box">'; #from the middle starts the filterbox
			$html .= '<h3>' . prana_Ptext('SEARCH','ZOEKEN') . '</h3>';
			
			# print filterform
			#
			if(isset($this->filtercolumns))
			{
				foreach ($this->filtercolumns as $c => $label)
				{
					$value="";
					#
					# has filters a content?
					#
					if(isset($this->filters->$c))
					{
						$value=$this->filters->$c;
					}
					#echo "<br>before filter" . $value;
					$form->formdefaults['required']=FALSE;
					$form->formdefaults['collabel']="col-md-5";
					$form->formdefaults['colinput']="col-md-7";
				
					$html .= $form->Text(array("label"=>$label, "id"=>$c, "value"=>$value, "popover"=>prana_PText('infosearch','info zoeken')));
				}
			}
			$html .= '<button class="prana-btnsmall" id="filter" name="filter">' .  prana_PText('search','zoeken') . '</button>';
			$html .= '</div>';	# end of filter box
			$html .= '</div>';	# end of row.
		}
		$html .= '<br><br>';
		/**
		 * Display the records
		 */
		$html .= $this->DisplayRecords();
		/**
		 * The footer of the display . create button, exportbutton, initialload button
		 */
		$html .= '<div style="float:right" class="row">';
		/**
		 * Upload records
		 */
		if(in_array("dm", $this->permissions)) #initial load button if there are no records
		{
			$html .= '<button class="' . $this->buttonclass . '" role="button" name="initialload" value="";>'.  prana_Ptext('','upload') . '</button>';
			$html .= '&nbsp;';
		}
		#
		# create new record
		#
		if( in_array("cr", $this->permissions))
		{
			$html .= '<button class="' . $this->buttonclass . '" name="createrecord" value="";>'.  prana_Ptext('new','nieuw record') . '</button>';
			$html .= '&nbsp;';
		}
		$html .= $this->ExportRecords();		# export records in csv file
		$html .= '</div>';
		$html .= '<br><br><br>';
		#
		# set post values
		#
		$this->rows_per_page=0;
		$html .= $this->SetPosts();
		return($html);
	}
	/**
	 * Search the table by public
	 * just one search key for multiple rows
	 */
	public function SearchTable()
    {   
        $html = '';
		/**
		 * search butten clicked
		 */
		if(isset($_POST['search'])) 
		{
			$this->onsearch = $_POST['search'];
		}
		/**
		 * download file clicked
		 */
		if(isset($_POST['download'])) 
        { 
            $html .= $this->DownloadDocument($_POST['download']);
        }
		if(isset($_POST['viewer'])) 
        { 
            $html .= $this->DocumentViewer($_POST['viewer']);
        }
		$html .= $this->DisplayPublic();
        $html .='<input id="' . $this->class . '" name="' . $this->class . '" type="hidden" />';
		return($html);
	}
	/**
	 * Display the search form used by public (frontend)
	 */
	public function DisplayPublic()
	{
		$self = new self();
		$dbio = new dbio();		#class for database I/O
		$form = new forms();	#class for formfields
		$this->GetPosts();
		$html = '';
		/**
		 * display search field
		 */
		if(isset($_POST['search'])) { $value = $_POST['search']; }
		$html .= '<div class="row">';
		$html .= $form->Text(array("submit" => TRUE,"label"=>prana_PText("search","zoeken op"), "id"=>"search", "value"=>$this->onsearch, "autofocus"=>TRUE , "group"=>FALSE,  "collabel"=>"col-md-1", "colinput"=>"col-md-3" ,"width"=>"300px", "required"=>FALSE));
		/**
		 * search button
		 */
		$html .= '<button type="button" class="prana-btnsmall col" id="newsearch" name="newsearch">' .  prana_PText('search','zoeken') . '</button>';
		/**
		 * dropdown box for number of rows per page
		 */
		$html .= '<div class="col-md-3"></div>';
		if($this->num_rows)
		{
			$options = array_combine($this->num_rows,$this->num_rows); #convert to assoc array
			$options += array(prana_Ptext("all","alle") => "*");
			$html .= $form->Dropdown(array("label"=>prana_PText("rows_per_page","regels per pagina"), "id"=>"rows_per_page", "value"=>$this->rows_per_page,  "group"=>FALSE,  "collabel"=>"col-md-2", "colinput"=>"col-md-1" ,"options"=>$options, "width"=>"50px", "submit" => TRUE));
		}
		$html .= '</div>';


		/** 
		 * display records
		 */
		$html .= $this->DisplayRecords();
		
		#
		# set post values
		#
		$this->rows_per_page=0;
		$html .= $this->SetPosts();
		return($html);
	}
	
	/**
	 * Display the rows of a databasetable in a table
	 */
	public function DisplayRecords() : string
	{
		$dbio = new dbio();		#class for database I/O
		$html = '';
		#
		# sort button clicked.
		# get sort column and switch the sortorder
		#
		if(isset($_POST['onsort']))
		{ 	$this->onsort = $_POST['onsort']; 
			$this->sortorder = $_POST['sortorder'];
		}
		if(isset($_POST['filter']) || isset($_POST['newsearch'])) # Er is een nieuwe zoek opdracht gegeven
		{
			$this->onpage = 1;
		}
		$this->sortclicked = FALSE;
        if(isset($_POST['sort'])) 
		{
			$this->onpage = 1;
			$this->onsort = $_POST['sort'];
			if(isset($_POST['sortorder']))
			{
				if($_POST['sortorder'] == "ASC") {$this->sortorder = "DESC";}
				if($_POST['sortorder'] == "DESC") {$this->sortorder = "ASC";}
			}
		}
		if($this->nextpage) { $this->onpage += 1; } # next page given so go the next page
		if($this->previouspage) { $this->onpage -= 1; } # next page given so go the next page
		#
		# count number of records andcalculate number of pages
		#
		#echo "<br>display records";
		$pb = $dbio->ReadRecords(array("table"=>$this->table,"columns"=>$this->columns,"filters"=>$this->filters,"search"=>array($this->searchcolumns,$this->onsearch)));
		$rows=count($pb);
		#echo "<br>".$rows;
		/**
		 * Set the number of rows per page
		 * If number of page not defines: get the first number in array of numbers per page else show all rows
		 */
		if(!$this->rows_per_page) { $this->rows_per_page = isset($this->num_rows[0]) ? $this->num_rows[0] : $rows; }
		if($this->rows_per_page == '*') { $this->rows_per_page = $rows; }   #display all records
		#echo "rpp=".$this->rows_per_page."rows=".$rows.'<br>';
		$sort = $this->onsort . ' ' . $this->sortorder;
		#echo "sort=" . $sort . "onpage=" . $this->onpage . "onsearch=" . $this->onsearch;
		$pb = $dbio->ReadRecords(array("table"=>$this->table,"columns"=>$this->columns,"page"=>$this->onpage,"maxlines"=>$this->rows_per_page,"sort"=>$sort,"filters"=>$this->filters,"search"=>array($this->searchcolumns,$this->onsearch)));
		$pages=ceil($rows/$this->rows_per_page);
		/**
		 * display records
		 */
		$html .= '<br>';
		$html .= '<table class="compacttable">';
		$html .= '<tr class="compacttrh">';
		/**
		 * Set headers of table
		 */
		foreach ($this->columns as $c)
		{
			$thclass = "compactth";
			$type = $c[2] ? $c[2] : "string";	// default type is string
			if($type == "int" || $type == "euro" || $type=="stringright") {$thclass = "compactthright"; }	// getallen rechts aansluiten
			$sortfield='<button class="pbtn-header" name="sort" value="' . $c[0] . '">' . $c[1]  . '</button>';
			$html .= '<th class="' . $thclass .'">' . $sortfield . '</th>';
		}
		/**
		 * Zijn er kolommen die via een join opgezocht moeten worden?
		 * Op deze kolommen kan niet worden gesorteerd.
		 * header,kolom in jointable,type,kolom in current table,jointable
		 */
		foreach ($this->joincolumns as $c)
		{
			$type = $c[2] ? $c[2] : "string";	// default type is string
			if($type == "int" || $type == "euro" || $type=="stringright") {$thclass = "compactthright"; }	// getallen rechts aansluiten
			$html .= '<th class="' . $thclass .'">' . $c[0] . '</th>';
		}

		if (in_array("vw", $this->permissions)) {$html .= '<th class="compactth"></th>';}	#Empty header for view button
		if (in_array("dl", $this->permissions)) {$html .= '<th class="compactth"></th>';}	#Empty header for view button
		if (in_array("md", $this->permissions)) {$html .= '<th class="compactth"></th>';}	#Empty header for view button
		if (in_array("cp", $this->permissions)) {$html .= '<th class="compactth"></th>';}	#Empty header for view button
		$html .= '</tr>';
		#
		# print rows
		#
		$primarykey = $this->primarykey;
		foreach ( $pb as $p )
		{
			$html .= '<tr class="compacttr">';
			/**
			 * display the columns which are defined in grenzenloos.php
			 */
			foreach($this->columns as $c)
			{
				
				$type = $c[2] ? $c[2] : "string";	// default type is string
				$tdclass = "compacttd";
				if($type == "int" || $type == "euro") {$tdclass = "compacttdright"; }	// getallen rechts aansluiten
				/**
				 * If field is a downlod, then show downloadbutton
				 */
				$name = $c[0];
				if($type == "download" && $p->$name)
				{
					/**
					 * Downloadlink only if file exists
					 */
					$ext = substr(strrchr($p->$name, '.'), 1);	#the extension of the file
					if($this->DocumentExist($p->$name))
					{
						$btn = '<i class="fa fa-download" style="font-size:24px;color:blue;"></i>-'.$ext;
						$html .= '<td class="compacttd"><button type="submit" id="download" name="download" class="btn btn-link btn-xs" value="' . $p->$name . '">'.$btn.'</button></td>';
					}
					else
					{
						$btn = '<i class="fa fa-download" style="font-size:24px;"></i>-'.$ext;
						$html .= '<td class="compacttd">' . $btn . '</td>';
					}
				}
				elseif($type == "viewer" && $p->$name)
				{
					$ext = substr(strrchr($p->$name, '.'), 1);	#get the extension of the file
					#$btn = '<i class="fa fa-file-pdf-o" style="font-size:24px;color:red;"></i>'.$ext;
					if($this->DocumentExist($p->$name))
					{
						$btn = '<i class="fa fa-eye" style="font-size:24px;color:blue;"></i>-'.$ext;
						$html .= '<td class="compacttd"><button type="submit" id="viewer" name="viewer" class="btn btn-link btn-xs" value="' . $p->$name . '">' . $btn . '</button></td>';
				
					}
					else
					{
						$btn = '<i class="fa fa-eye" style="font-size:24px;"></i>-'.$ext;
						$html .= '<td class="compacttd">' . $btn . '</td>';
					}	
				}
				else
				{
					$html .= '<td class="' . $tdclass .'">' . $p->$name . '</td>';
				}

			}
							/**
				 * Zijn er kolommen die via een join opgezocht moeten worden?
				 * Op deze kolommen kan niet worden gesorteerd.
				 * c[0]=kolom in jointable to be displayed
				 * c[1]=header
				 * c[2]=type
				 * c[3]=jointable
				 * c[4]=joinfield current tabel
				 * c[5]=joinfield joined table
				 * e.g. ["gebouw","gebouw","string",$appartments,"house","huisnummer"]
				 */
				foreach ($this->joincolumns as $c)
				{
					$type = $c[2] ? $c[2] : "string";	// default type is string
					if($type == "int" || $type == "euro" || $type=="stringright") {$thclass = "compacttdright"; }	// getallen rechts aansluiten
					$name=$c[4];
					#$html .= '<td class="' . $tdclass .'">' . $p->$name . '</td>';
					$result=$dbio->ReadUniqueRecord(array("table"=>$c[3],"key"=>$c[5],"value"=>$p->$name));
					$name=$c[0];
					$value = empty($result) ? "" : $result->$name;;
					$html .= '<td class="' . $tdclass .'">' . $value . '</td>';
				}

			
			/**
			* view / modify / delete / copy buttons
			**/
			if (in_array("vw", $this->permissions)) 
			{
				#$html .= '<td><button type="submit" class="btn btn-link btn-xs showrecord" name="showrecord" value="' . $p->id . '"><i class="fa fa-eye"></i></button>';
				#$html .= '<td class="compacttd showrecord"><a class="btn btn-link btn-xs"><i class="fa fa-eye"></a></td>';
				$toggleid="toggle".$p->id;
				$html .= '<td class="compacttd " onclick="ToggleRow(\''.$toggleid.'\')"><i class="fa fa-eye" style="font-size:20px;color:blue;"></td>';
			}
			
			if (in_array("dl", $this->permissions)) 
			{ 
				$message=sprintf( prana_Ptext( 'deletesure','%s %d verwijderen , zeker weten?'),$this->single,$p->$primarykey);
				$html .= '<td class="compacttd"><button type="submit" name="deleterecord" class="btn btn-link btn-xs" onclick="return confirm(\'' . $message. '\');" value="' . $p->$primarykey . '"><i class="fa fa-trash"></i></button></td>';
			}
			if (in_array("md", $this->permissions)) 
			{ 
				$html .= '<td class="compacttd"><button type="submit" name="modifyrecord" class="btn btn-link btn-xs" value="' . $p->id . '"><i class="fa fa-edit"></i></button></td>';
			}
			if (in_array("cp", $this->permissions)) 
			{ 
				$html .= '<td class="compacttd"><button type="submit" name="copyrecord" class="btn btn-link btn-xs" value="' . $p->id . '"><i class="fa fa-copy"></i></button></td>'; 
			}

			$html .= '</tr>';
			$primarykey = $this->primarykey;
			$detail = $dbio->DisplayAllFields(array("table"=>$this->table,"key"=>$this->primarykey,"value"=>$p->$primarykey));
			/**
			* Row with record details will be displayed when clicked on view icon
			*/
			$cols = count($this->columns);	#number of columns
			$html .= '<tr id='.$toggleid.' style="display:none" >';
			$html .= '<td colspan="'.$cols.'">'.$detail.'</td>'; #span over all columns and show when onclick
			$html .= '</tr>';
		}
		$html .= '</table>';
		$html .= '<br>';
		#
		# buttons for next and previous page
		#
		$html .= sprintf(prana_Ptext("pageof","aantal records: %d pagina %d van %d"),$rows,$this->onpage,$pages);
		if($pages > 1) 
		{ 
			$html .= sprintf(prana_Ptext('bladeren',' bladeren: '));
			if($this->onpage > 1) { $html .= '<button type="submit" class="btn btn-link btn-sx" name="previouspage" value="' . $this->onpage . '"><i class="fa fa-caret-square-o-left" style="font-size:24px"></i></button>'; }
			if($this->onpage < $pages) { $html .= '<button type="submit" class="btn btn-link btn-sx" name="nextpage" value="' . $this->onpage . '"><i class="fa fa-caret-square-o-right" style="font-size:24px"></i></button>'; }
		}
		return($html);
	}
	public function CreateRecord()
    {
        $html = '';
        $dbio = new DBIO();
        $columns = $dbio->columns($this->table);
        foreach ($columns as $c)
        {
            if($c != $this->primarykey)
            {
                $this->fields[$c]='';
            }
        }
        $html = '';
		$html .=  sprintf('<h2>' . prana_Ptext('create','Nieuwe %s aanmaken') . '</h2>',$this->single);
        $html .= $this->FormTable("create");
		$html .= $this->SetPosts();
        return($html);
    }
	public function ModifyRecord()
    {
        $html = '';
        $dbio = new DBIO();
        $columns = $dbio->columns($this->table);
        $p = $dbio->ReadUniqueRecord(array("table"=>$this->table,"key"=>$this->primarykey,"value"=>$_POST['modifyrecord']));
        foreach ($columns as $c)
        {
            $this->fields[$c]=$p->$c;
        }
		$html .=  sprintf('<h2> %s wijzigen</h2>',$_POST['modifyrecord']);
		$html .= $this->FormTable("modify");
		$html .= $this->SetPosts();
        return($html);
    }
	public function CopyRecord()
    {
        $html = '';
        $dbio = new DBIO();
        $columns = $dbio->columns($this->table);
        $p = $dbio->ReadUniqueRecord(array("table"=>$this->table,"key"=>$this->primarykey,"value"=>$_POST['copyrecord']));
        foreach ($columns as $c)
        {
			if($c != $this->primarykey)
            {
				$this->fields[$c]=$p->$c;
            }
        }
        $html .=  sprintf('<h2>' . prana_Ptext('copy','%s kopieren') . '</h2>',$this->single);
        $html .= $this->FormTable("create");
		$html .= $this->SetPosts();
        return($html);
    }
	/*
		write a record to the database
		fields should be in POST parameters
		$_POST['crmod'] = 'create' or 'modify'
	*/
	public function WriteRecord()
	{
        $html = '';
        $dbio = new DBIO();
		if(!$this->CheckModify()) { $html .= "Record not written"; return($html);}	#check if input is valid
        $fields = array();
        $columns = $dbio->columns($this->table);
        foreach ($columns as $c)
        {
            if(isset($_POST[$c]))
            {
                $fields += [$c=>$_POST[$c]];
            }
        }
        if($_POST['crmod'] == "create")
        {
            $id=$dbio->CreateRecord(array("table"=>$this->table,"fields"=>$fields));
            $html .= sprintf(prana_Ptext('created','%s %d is aangemaakt'), $this->single, $id);
           
        }
        if($_POST['crmod'] == "modify")
        {
            $dbio->ModifyRecord(array("table"=>$this->table,"fields"=>$fields,"key"=>$this->primarykey,"value"=>$_POST["primarykey"]));
            $html .= sprintf(prana_Ptext('modified','record %d is gewijzigd'), $_POST['primarykey']);
        }
        return($html);
    }
	/**
	 * Delete a record from the table
	 */
	public function DeleteRecord()
	{
		$html = '';
		$dbio = new dbio();
		$html .= sprintf(prana_Ptext('deleted','%s %d is verwijderd'), $this->single, $_POST['deleterecord']);
		$html = $this->AfterDelete($_POST['deleterecord']);   #nog iets anders te doen als record wordt verwijderd?
		$dbio->DeleteRecord(array("table"=>$this->table,"key"=>$this->primarykey,"value"=>$_POST['deleterecord']));
		return($html);
	}
	/**
	 * Download a document
	 */
	/**
	 * Initial load of the database table
	 * Ask for a csv file with all columns of the table
	 */
	public function InitialLoad()
    {
		$html = '';
        $form = new Forms();
        $html .= $form->File(array("label"=>prana_Ptext("choosefile","Bestand kiezen"), "id"=>"bestand", "value"=>"iniload","accept"=>".csv"));
        $form->buttons = [
            ['id'=>'writeinitialload','value'=>prana_Ptext("iniload","Inital load inlezen")],
            ['id'=>'cancel','value'=>prana_Ptext("annuleren","annuleren"),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="writeinitialload" name="writeinitialload" type="hidden" />';
        $html .= $this->SetPosts();	// safe post values created bij tableform
        return($html);
    }
	public function WriteInitialLoad()
    {
		$dbio = new dbio();
		$html = '';
		$html .= prana_Ptext("loadiniload","initial load wordt geladen");
		$fp = fopen($_FILES['bestand']["tmp_name"],"rb");
		$initialload = array();
        if(($header = fgetcsv($fp, 0, ";")) !== FALSE)
        {
            //Loop through the CSV rows.
            while (($row = fgetcsv($fp, 0, ";")) !== FALSE) 
            {
                $rows[] = array_combine($header, $row);
            }
        }
        foreach ($rows as $row)
        {
            $dbio->CreateRecord(array("table"=>$this->table,"fields"=>$row));
		}
		$html .= $this->SetPosts();	// safe poast values created bij tableform
        return($html);
	}
	#
	# Export records to be used in Excell and now using javascript
	#
	public function ExportRecords()
	{
		global $wpdb;
		$dbio = new dbio();
		$this->allcolumns = $dbio->DescribeColumns(array("table"=>$this->table));	#get information about all columns
		$export = '';
		#$export .= '<div>';
		$export .= '<table style="display:none">';
		#$export .= '<table class="csvexport" style="display:none">';
		$export .= '<tr>';
		foreach ($this->allcolumns as $c)
		{
			$export .= '<th>' . $c->Field . '</th>';
		}
		$export .= '</tr>';
		$pb = $dbio->ReadRecords(array("table"=>$this->table,"columns"=>$this->columns,"filters"=>$this->filters));
		foreach ( $pb as $p )
		{
			$export .= '<tr>';
			foreach ($this->allcolumns as $c)
			{
				$name=$c->Field;
				$field = str_replace(["\r\n", "\n", "\r"], "", $p->$name); # remove crlf
				$field = str_replace(';',':',$field);				#replace ; by : cause ; is fieldseparator
				$export .= '<td>' . $field . '</td>';
			}
			$export .= '</tr>';
		}
		$export .= '</table>';
		$filename = $this->table . '.csv';	#add csv extension
		#$export .= '<p id="exportfilename" style="display:none">'.$filename.'</p>';
		#$export .= '<button id="exporttable" class="prana-btnhigh">export</button>';   #javascript export.js does the rest
		$export .= '<span style="display:none">'.$filename.'</span>';
		$export .= '<button class="prana-button col exporttable">export</button>';   #javascript exportcsv.js does the rest
		#$export .= '</div>';
		return($export);
	}
	public function SetPosts()
	{
		#
		# set post values
		#
		$html = '';
		$html .='<input id="onpage" name="onpage" type="hidden" value=' . $this->onpage .  ' />';	#current page
		$html .='<input id="onsort" name="onsort" type="hidden" value=' . $this->onsort .  ' />';	#current sort column
		$html .='<input id="sortorder" name="sortorder" type="hidden" value=' . $this->sortorder .  ' />';	#direction of sorting (ASC or DESC)
		if($this->rows_per_page) { $html .='<input id="rows_per_page" name="rows_per_page" type="hidden" value=' . $this->rows_per_page .  ' />'; }
		#
        # geef hprimarykeyige filters door een POST values om ze weer terug te kunnen krijgen bij volgende klik
        #
        if($this->filters) { $html .='<input id="filters" name="filters" type="hidden" value=' . urlencode(json_encode($this->filters)) .  ' />'; }
		return($html);
	}
	public function GetPosts()
	{
		# get the values of the previous run:
		if(isset($_POST['previouspage'])) {$this->previouspage = $_POST['previouspage']; }
		if(isset($_POST['nextpage'])) {$this->nextpage = $_POST['nextpage']; }
		if(isset($_POST['onpage'])) {$this->onpage = $_POST['onpage']; }
		if(isset($_POST['onsort'])) {$this->onsort = $_POST['onsort']; }
		if(isset($_POST['sortorder'])) {$this->sortorder = $_POST['sortorder']; }
		if(isset($_POST['rows_per_page'])) {$this->rows_per_page = $_POST['rows_per_page']; }
		if(isset($_POST['filters'])) { $this->filters=json_decode(urldecode($_POST['filters'])); } #zet filters terug
	}
}

?>