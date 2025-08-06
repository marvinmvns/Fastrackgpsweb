//MÁSCARA DE VALORES
/*

Uso:
<input type="text" size="20" onkeypress="return txtBoxFormat(this, '99/99/9999', event);">

<input type="text" size="20" onkeypress="return txtBoxFormat(this, '999.999.999-9', event);">

*/

function txtBoxFormat(objeto, sMask, evtKeyPress) {
 var i, nCount, sValue, fldLen, mskLen,bolMask, sCod, nTecla;

 nTecla = (window.Event) ? evtKeyPress.which : evtKeyPress.keyCode;
 
 if(document.all) { // Internet Explorer
   nTecla = evtKeyPress.keyCode; }
 else if(document.layers) { // Nestcape
   nTecla = evtKeyPress.which;
 }
 
 if (nTecla != 8) {
	 sValue = objeto.value;

	 // Limpa todos os caracteres de formatação que
	 // já estiverem no campo.
	 sValue = sValue.toString().replace( "-", "" );
	 sValue = sValue.toString().replace( "-", "" );
	 sValue = sValue.toString().replace( ".", "" );
	 sValue = sValue.toString().replace( ".", "" );
	 sValue = sValue.toString().replace( "/", "" );
	 sValue = sValue.toString().replace( "/", "" );
	 sValue = sValue.toString().replace( ":", "" );
	 sValue = sValue.toString().replace( ":", "" );
	 sValue = sValue.toString().replace( "(", "" );
	 sValue = sValue.toString().replace( "(", "" );
	 sValue = sValue.toString().replace( ")", "" );
	 sValue = sValue.toString().replace( ")", "" );
	 sValue = sValue.toString().replace( " ", "" );
	 sValue = sValue.toString().replace( " ", "" );
	 fldLen = sValue.length;
	 mskLen = sMask.length;

	 i = 0;
	 nCount = 0;
	 sCod = "";
	 mskLen = fldLen;

	 while (i <= mskLen) {
	   bolMask = ((sMask.charAt(i) == "-") || (sMask.charAt(i) == ".") || (sMask.charAt(i) == "/") || (sMask.charAt(i) == ":"))
	   bolMask = bolMask || ((sMask.charAt(i) == "(") || (sMask.charAt(i) == ")") || (sMask.charAt(i) == " "))

	   if (bolMask) {
		 sCod += sMask.charAt(i);
		 mskLen++; }
	   else {
		 sCod += sValue.charAt(nCount);
		 nCount++;
	   }

	   i++;
	 }

	 objeto.value = sCod;

	 if (nTecla != 8 && typeof(nTecla) != "undefined") { // backspace
	   if (sMask.charAt(i-1) == "9") { // apenas números...
		 return ((nTecla > 47) && (nTecla < 58)); } // números de 0 a 9
	   else { // qualquer caracter...
		 return true;
	   } 
	 }
	 else {
	   return true;
	 }
 }
 
}