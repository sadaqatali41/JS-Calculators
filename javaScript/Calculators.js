function Calculator(){
	var a= confirm("Choose Your Calculator?\n\n (1) Calculator With Three Input Boxes --->OK\n\n (2) Calculator With One Input Box --->Cancel");
	if(a==true){
		open("calculator.html");
	}
	else{
		open("calc.html");
	}
}