SELECT * FROM ppl JOIN ppl2grp USING (ppl_id) LEFT JOIN (
SELECT ppl_id, MAX(timestamp) time FROM log JOIN ppl2grp USING (ppl_id) WHERE event = 'login_success' AND grp_id = 429 GROUP BY ppl_id) bla USING (ppl_id) WHERE grp_id = 429 ORDER BY time

