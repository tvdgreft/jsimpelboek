//
//	Toomt/verbergt detalinformatie na een rij in een tabel.
//	Ieder rij moet dan worden gevolgd met detailinformatie van de rij. Deze rij moet class showdetail bv:
//
//			$detail = $dbio->DisplayAllFields(array("table"=>$this->table,"key"=>$this->primarykey,"value"=>$p->$primarykey));
//		$cols = count($this->columns);	#number of columns
//			$html .= '<tr>';
//			$html .= '<td colspan="'.$cols.'" class="showdetail">'.$detail.'</td>'; #span over all columns and show when onclick
//			$html .= '</tr>';
//
// display / hide filters
//
function ToggleRow(id) 
{
	var state = document.getElementById(id).style.display;
	if (state == 'table-row') 
	{
		document.getElementById(id).style.display = 'none';
	} 
	else 
	{
		document.getElementById(id).style.display = 'table-row';
	}
}
//
// display / hide filters
//
function ToggleFilters(id) 
{
	var state = document.getElementById(id).style.display;
	if (state == 'block') 
	{
		document.getElementById(id).style.display = 'none';
	} 
	else 
	{
		document.getElementById(id).style.display = 'block';
	}
}