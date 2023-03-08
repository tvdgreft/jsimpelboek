<?php
#
# database tables
#
namespace SIMPELBOEK;

class Dbtables
{
	const boekhoudingen = ["name"=>"simpelboek_boekhoudingen", "columns"=>"
	    `id` int(10) NOT NULL AUTO_INCREMENT,
		`code` varchar(10) NOT NULL,
		`naam` varchar(50) NOT NULL,
		`boekjaar` int(4) NOT NULL,
		`kapitaalrekening` varchar(4),
		`winstrekening` varchar(4),
		`verliesrekening` varchar(4),
		UNIQUE KEY (`code`),
		PRIMARY KEY (`id`)"];

    const rekeningen = ["name"=>"simpelboek_rekeningen", "columns"=>"
        `id` int(10) NOT NULL AUTO_INCREMENT, 
		`naam` varchar(50) NOT NULL, 
		`bankrekening` varchar(20),
		`rekeningnummer` varchar(4),
		`soort` varchar(1) NOT NULL,
		`type` varchar(1) NOT NULL,
        `btwpercentage` varchar(5),		#btwpercentage bv 19,50 of 21 (controleren na submit form)
	    UNIQUE KEY (`rekeningnummer`),
		PRIMARY KEY (`id`)"];

	const balans = ["name"=>"simpelboek_balans", "columns"=>"
        `id` int(10) NOT NULL AUTO_INCREMENT, 
        `rekeningnummer` varchar(4) NOT NULL,
       `boekjaar` varchar(4) NOT NULL,
       `bedrag` varchar(10) NOT NULL,
        PRIMARY KEY (`id`)"];

	const begroting = ["name"=>"simpelboek_begroting", "columns"=>"
        `id` int(10) NOT NULL AUTO_INCREMENT, 
	    `rekeningnummer` varchar(4) NOT NULL,
		`boekjaar` varchar(4) NOT NULL,
		`bedrag` varchar(10) NOT NULL,
		PRIMARY KEY (`id`)"];

	const boekingen = ["name"=>"simpelboek_boekingen", "columns"=>"
        `id` int(10) NOT NULL AUTO_INCREMENT, 
		`datum`date NOT NULL,
		`bedrag` varchar(10) NOT NULL,
		`btw` varchar(10),
		`type` varchar(1) NOT NULL,
		`rekening` varchar(4) NOT NULL,
		`tegenrekening` varchar(4),
		`referentie` varchar(255),
		`bankrekening` varchar(20),
		`bankrekeninghouder` varchar(255),
		`omschrijving` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)"];
}
?>