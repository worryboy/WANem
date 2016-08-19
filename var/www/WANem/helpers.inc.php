<?
/****************************************************************************/
/*                              COPYRIGHT				    */
/****************************************************************************/
/*                                                                          */
/*                                                                          */
/*          Please have a look at CopyrightInformation.txt        	    */
/*                                                                          */
/*                                                                          */
/****************************************************************************/

//********************************************************************************
//Function to change the default selected value in a selectbox if it doesn't match
//$value
//(Currently not in use)
//********************************************************************************
function default_select_option($selectHTML, $value) {

	//Check the current selected value for a match against $value
	//Set $n to the position of the first character of the default selected value
	//This is 9 characters after the start of the word, "selected"
	$n=(strpos($selectHTML, "selected") + 9);
	$tmpStr="";

	//While $n is not a '<' character
	while(ord(substr($selectHTML, $n, 1))!=60):
		//Add character to $tempStr
		$tmpStr=$tmpStr . substr($selectHTML, $n, 1);
		//Increment $n
		$n = ++$n;
	endwhile;

	//If $tmpStr doesn't match $value then:
	//Replace "selected" in the $selectHTML with ""
	//Find the position of the correct matching value and set $n to one space before it
	//Insert " selected" one space before the matching value
	if ($tmpStr!=$value) {
		$selectHTML=str_replace("selected", "", $selectHTML);
		$n=(strpos($selectHTML, $value)-1);
		$selectHTML=substr_replace($selectHTML, " selected ", $n, 0);
	}
}

