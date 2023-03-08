<?php
#
# toon alle helpbestanden
#
namespace SIMPELBOEK;

class Help
{
    public function Start()
    {
        $html = '';
        $manuals = ["boekhoudingen","beginbalans","begroting","beheer","overzichten","bankimport","jaarafsluiting"];
        $manual=SIMPELBOEK_DOC_DIR . 'manual.html';
        $html .= file_get_contents($manual);
        foreach ($manuals as $manual)
        {
            $manual=SIMPELBOEK_DOC_DIR . 'manual_' . $manual.'.html';
            $html .= file_get_contents($manual);
        }
        return($html);
    }
}