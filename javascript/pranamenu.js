// javascript for menu
// toglle submenu
function ToggleMenuRow(id) 
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