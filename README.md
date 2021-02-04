# JS-Calculators
This was my first calculator when I was learning HTML CSS and JavaScript. Here I also used CSS3 animation and this is looking very nice.
I made this calculator when I was in 7th semester of B-Tech(CS[2018])
If you want to access it, just clone it and open in your browser.

BEGIN 
DECLARE l_no INTEGER; 
SELECT max(CONVERT(substring(FILE_ID,3),UNSIGNED INTEGER)+1) into l_no FROM `file_master`; 
RETURN concat('F',lpad(ifnull(l_no,1),5,'000'));
END
