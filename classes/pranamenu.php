<?php
/**
 * class menu
 * class to make the menu
 **/
namespace SIMPELBOEK;

 
class pranamenu
{
	/**
	 * table for the main menu. An array of menuitems
	* each menuitem is an array of 3 values:
	 *	id, name, link or dropdown (link is a link item ans dropdown is just a item for other subitems)
	*/
	public $mainmenu = array();
	/**
	 * table for the sub menus. An array of menuitems
	*	each menuitem is an array of 3 values:
	*	id, id hoofdmenu, name
	*/
	public $submenu = array();
	/*
		list of menuitems which should be disabled
	*/
	public $disabled = array();

	/**
	*	make the menu
	*	attention: first make the array mainmenu and submenu
	*/
		
	public function Menu()
	{
		$html = '';
		$html .= '<div class="mynav">';
		$html .= '<ul>';
		foreach ($this->mainmenu as $m)
		{
			if($m[2] == 'link') 
			{
				$html .= $this->item($m[0],$m[1]);
			}
			if($m[2] == 'dropdown')
			{
				$html .= $this->mainitem($m[0],$m[1]);
				
				foreach ($this->submenu as $s)
				{
					if($s[1] == $m[0])
					{
						$html .= $this->subitem($s[0],$s[2]);
					}
				}
				$html .= $this->CloseMainitem();
			}
		}
		$html .='</ul>';
		$html .= '</div>';
		return($html);
	}
	/**
	 * display button for an menu item with a link
	 */
	public function item($id,$value)
	{
		$html='';
		$html = '<li  class="liinline">';
		#$html .= '<button class="button_menu" id="menu_' . $id . '" name="' . $id . '" value="' . $id . '"';
		$html .= '<button class="button_menu" id="menu" name="menu" value="' . $id . '"';
		if(in_array($id,$this->disabled)) { $html .= ' disabled'; }
		$html .= '>' . $value . '</button>';
		$html .= '</li>';
		return($html);
	}
	/**
	* display a menu item  with submenu itemns
	 */
	public function mainitem($id,$value)
	{
		$html='';
		$html = '<li  class="liinline">';
		$html .= '<button type="button" class="button_menu" id="' . $id . '" name="' . $id . '" value="' . $id . '"';
		#$html .= '<button class="button_menu" id="mainmenu" name="mainmenu" value="' . $id . '"';
		$subid = "sub".$id;
		$html .= ' onclick="ToggleMenuRow(\''.$subid.'\')"';				#toggle submenu
		#$html .= ' disabled';
		$html .= '>' . $value . ' <i class="fa fa-caret-down" aria-hidden="true"></i></button>';
		$html .= '<ul id='.$subid.' style="display:none" >';
		return($html);
	}
	public function closeMainitem()
	{
		$html='';
		$html .= '</ul></li>';
		return($html);
	}
	/**
	*	display submenu items
	*/
	public function subitem($id,$value)
	{
		$html ='';
		$html .= '<li>';
		#$html .= '<button class="button_menu" id="menu_' . $id . '" name="' . $id . '" value="' . $id . '"';
		$html .= '<button class="button_menu" id="menu" name="menu" value="' . $id . '"';
		if(in_array($id,$this->disabled)) { $html .= ' disabled'; }
		$html .= '>' . $value . '</button>';
		$html .= '</li>';
		return($html);
	}
	/**
	 * Als het een submenu is geef dan het mainmenu terug
	 * anders kijk of het een mainmenu is en geeft dat dan terug
	 */
	public function MainMenu($menu)
    {
        foreach($this->submenu as $s)
        {
            if($menu == $s[0]) {return($s[1]);}
        }
		foreach($this->mainmenu as $s)
        {
            if($menu == $s[0]) {return($s[0]);}
        }
        return("");
    }
}