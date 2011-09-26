SELECT DISTINCT grp_id, naam FROM grp JOIN ppl2grp USING (grp_id) WHERE ppl_id = ANY ( SELECT ppl_id FROM ppl2grp WHERE grp_id = 687 ) AND schooljaar = '0910' AND grp_type_id = ( SELECT grp_type_id FROM grp_types WHERE grp_type_naam = 'lesgroep' )
ORDER BY naam

FAST:

SELECT DISTINCT grp.naam, grp2.naam FROM grp JOIN ppl2grp USING (grp_id) JOIN ppl2grp AS ppl2equiv ON ppl2equiv.ppl_id = ppl2grp.ppl_id JOIN grp AS grp2 ON ppl2equiv.grp_id = grp2.grp_id WHERE grp.grp_id != grp2.grp_id AND grp.grp_id = 683 AND grp.schooljaar = grp2.schooljaar AND grp2.grp_type_id =  ( SELECT grp_type_id FROM grp_types WHERE grp_type_naam = 'lesgroep' )
