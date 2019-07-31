function addition(){
	a=Number(document.calculator.value1.value);
	b=Number(document.calculator.value2.value);
	c=a+b;
	document.calculator.result.value=c;
}
function substraction(){
	x=Number(document.calculator.value1.value);
	y=Number(document.calculator.value2.value);
	z=x-y;
	document.calculator.result.value=z;
}
function multiplication(){
	p=Number(document.calculator.value1.value);
	q=Number(document.calculator.value2.value);
	r=p*q;
	document.calculator.result.value=r;
}
function division(){
	l=Number(document.calculator.value1.value);
	m=Number(document.calculator.value2.value);
	if(m==0)
	{
		alert('Denominator Value Can Not Be Equal ZERO!!!!. So Please Use Integer Numbers.');
	}
	else
	{
	n=l/m;
	document.calculator.result.value=n;
	}
}
function greatest(){
	var1=Number(document.calculator.value1.value);
	var2=Number(document.calculator.value2.value);
	rst=(var1>var2)?var1:var2;
	document.calculator.result.value=rst;
}
function least(){
	v1=Number(document.calculator.value1.value);
	v2=Number(document.calculator.value2.value);
	vr=(v1<v2)?v1:v2;
	document.calculator.result.value=vr;
}
function modulous(){
	x1=Number(document.calculator.value1.value);
	y1=Number(document.calculator.value2.value);
	if(y1==0)
	{
		alert("Denominator Can't Be Equal ZERO.!!!");
	}
	else{
	z1=x1%y1;
	document.calculator.result.value=z1;
	}
}
function square(){
	g=Number(document.calculator.value3.value);
	f=g*g;
	document.calculator.result.value=f;
}
function cube(){
	x2=Number(document.calculator.value3.value);
	y2=x2*x2*x2;
	document.calculator.result.value=y2;
}
function sqroot(){
	g=Number(document.calculator.value3.value);
	if(g<0)
	{
		alert("Negative Number Is Not Allowed.!!!");
	}
	else{
	f=Math.sqrt(g);
	document.calculator.result.value=f.toPrecision(5);;
	}
}
function cuberoot(){
	x2=Number(document.calculator.value3.value);
	if(x2<0)
	{
		alert("Negative Number Is Not Allowed.!!!");
	}
	else{
		y2=Math.cbrt(x2);
		document.calculator.result.value=y2.toPrecision(5);
	}
}
function sinfunction(){
	x2=Number(document.calculator.value3.value);
	y2=Math.sin(x2);
	document.calculator.result.value=y2.toPrecision(5);
}
function cosfunction(){
	x2=Number(document.calculator.value3.value);
	y2=Math.cos(x2);
	document.calculator.result.value=y2.toPrecision(5);
}
function tanfunction(){
	g=Number(document.calculator.value3.value);
	f=Math.tan(g);
	document.calculator.result.value=f.toPrecision(5);
}
function exponential(){
	g=Number(document.calculator.value3.value);
	f=Math.exp(g);
	document.calculator.result.value=f.toPrecision(5);
}
function power(){
	v1=Number(document.calculator.value1.value);
	v2=Number(document.calculator.value2.value);
	vr=Math.pow(v1,v2);
	document.calculator.result.value=vr;
}
function logbase10(){
	g=Number(document.calculator.value3.value);
	if(g<0)
	{
		alert("Log Does Not Take Negative Number.");
	}
	else if(g==0)
	{
		alert("INFINITE.!!");
	}
	else
	{	
	f=Math.log10(g);
	document.calculator.result.value=f.toPrecision(5);
	}
}
function logbase2(){
	g=Number(document.calculator.value3.value);
	if(g<0)
	{
		alert("Log Does Not Take Negative Number.");
	}
	else if(g==0)
	{
		alert("INFINITE.!!");
	}
	else
	{	
	f=Math.log2(g);
	document.calculator.result.value=f.toPrecision(5);
	}
}
function logbaseE(){
	g=Number(document.calculator.value3.value);
	if(g<0)
	{
		alert("Log Does Not Take Negative Number.");
	}
	else if(g==0)
	{
		alert("INFINITE.!!");
	}
	else
	{	
	f=Math.log(g);
	document.calculator.result.value=f.toPrecision(4);
	}
}