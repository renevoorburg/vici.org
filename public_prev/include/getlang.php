<?php

/**
Copyright 2013-14, René Voorburg, rene@digitopia.nl

Rev: 20140202

This file is part of the Vici.org source as used on http://vici.org/

Vici.org source is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Vici.org  source is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Vici.org source.  If not, see <http://www.gnu.org/licenses/>.
*/

define("DEFAULTLANG", "en");

function isGoogle() 
{
	if (strpos($_SERVER['HTTP_USER_AGENT'], "Googlebot")) {
		return true;
	} else {
		return false;
	}
}

function getLangURL($selectedlang, $url = NULL) 
// returns the current URL / of given URL for the given $language
{
    global $lang_neg_reason, $browserlang;
    
    if (empty($url)) $url=$_SERVER['REQUEST_URI'];
    
    // strip any 'lang' parameter
    $url=preg_replace('/&lang=../', '', $url);
    $url=preg_replace('/\?lang=..&/', '?', $url);
    $url=preg_replace('/\?lang=../', '', $url);
    
   
    if ($browserlang!=$selectedlang) {
        // add new 'lang' parameter
        if (preg_match('/\?.*=/', $url)) {
            $url=$url."&lang=$selectedlang";
        } else {
            $url=$url."?lang=$selectedlang";
        };
    };    
    return ($url);
}


$availableLanguages=array("en", "de", "fr", "nl");

$langname['en']="In English";
$langname['de']="Auf Deutsch";
$langname['fr']="En Français";
$langname['nl']="In het Nederlands";

unset($chosenlang); 
unset($browserlang); 
$lang_neg_reason='';


// language specific url?
if (isset($_GET['lang'])) {
	$getlang = $_GET['lang'];
	if (in_array($getlang, $availableLanguages, true)) { 
		$chosenlang = $getlang;
		$lang_neg_reason = "language_url"; 
	};
}
if ($lang_neg_reason=='language_url') $url_suffix="?lang=".$chosenlang; else $url_suffix="";

// nothing yet? do the negotiation dance? (aber nicht für Google, wieso Deutsch??)
if (!isGoogle()) 
{
    # Get the list of acceptable languages
    # or use default
    unset($acceptlang);
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $acceptlang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        for ($i = 0; $i < count($acceptlang);  $i++) {
            $Lang = explode(';', $acceptlang[$i]);
            $acceptlang[$i] = trim($Lang[0]);
        }
    } else $acceptlang = $availableLanguages;
    for ($i = 0; $i < count($acceptlang);  $i++) {
        $Lang_split = explode('-', $acceptlang[$i]);
        $Lang_pre = trim($Lang_split[0]);
        if (in_array($Lang_pre, $availableLanguages)) {
            $browserlang = $Lang_pre;
            $i = count($acceptlang)+1;
        }
    }
};

if (! isset($chosenlang)) {
    if (isset($browserlang)) {
        $chosenlang = $browserlang;
        $lang_neg_reason = "http_nego";
    } else {
        $chosenlang = "en";
        $lang_neg_reason = "default";
    }
}

function babelfish($element, $lang = "none") 
// prints translated element
{
    global $chosenlang;
    if ($lang=="none") $lang=$chosenlang;
    echo sbabelfish($element, $lang);
}

function sbabelfish($element, $lang = "none") 
// returns translated element
{
    global $chosenlang;   
    if ($lang=="none") $lang=$chosenlang;
    
    $strings["Roman history nearby"]["en"] = "Archaeological Atlas of Antiquity";
    $strings["Roman history nearby"]["de"] = 'Atlas zur Archäologie des Altertums';
    $strings["Roman history nearby"]["fr"] = "Atlas archéologique de l'Antiquité";
    $strings["Roman history nearby"]["nl"] = "Archeologische Atlas van de Oudheid";
    $strings["more"]["en"] = "more";
    $strings["more"]["de"] = "weiter";
    $strings["more"]["fr"] = "plus";
    $strings["more"]["nl"] = "meer";
    
    $strings["Selected"]["en"] = $element;
    $strings["Selected"]["de"] = "Selektion";
    $strings["Selected"]["fr"] = "Selection";
    $strings["Selected"]["nl"] = "Geselecteerd";
    
    $strings["Featured"]["en"] = $element;
    $strings["Featured"]["de"] = "Schaukasten";
    $strings["Featured"]["fr"] = "Présenté";
    $strings["Featured"]["nl"] = "Uitgelicht";
    
    $strings["show"]["en"] = $element;
    $strings["show"]["de"] = 'anzeigen';
    $strings["show"]["fr"] = 'exposer';
    $strings["show"]["nl"] = 'toon';
    
    $strings["Show"]["en"] = $element;
    $strings["Show"]["de"] = 'Zeige';
    $strings["Show"]["fr"] = 'Exposer';
    $strings["Show"]["nl"] = 'Toon';
    
    $strings["Background"]["en"] = $element;
    $strings["Background"]["de"] = 'Hintergrund';
    $strings["Background"]["fr"] = 'Fond';
    $strings["Background"]["nl"] = 'Achtergrond';
    
    $strings["Satellite"]["en"] = $element;
    $strings["Satellite"]["de"] = 'Satellit';
    $strings["Satellite"]["fr"] = $element;
    $strings["Satellite"]["nl"] = 'Satelliet';
    
    $strings["Satellite with labels"]["en"] = $element;
    $strings["Satellite with labels"]["de"] = 'Satellit mit Labels';
    $strings["Satellite with labels"]["fr"] = 'Satellite avec noms des lieux';
    $strings["Satellite with labels"]["nl"] = 'Satelliet met labels';
    
    $strings["back"]["en"] = "back";
    $strings["back"]["de"] = "zurück";
    $strings["back"]["fr"] = "retour";
    $strings["back"]["nl"] = "terug";
            
    $strings["all_button"]["en"] = "All";
    $strings["all_button"]["de"] = "Alles";
    $strings["all_button"]["fr"] = "Tout";
    $strings["all_button"]["nl"] = "Alles";
    
    $strings["visible_button"]["en"] = "Visible";
    $strings["visible_button"]["de"] = "Sichtbar";
    $strings["visible_button"]["fr"] = "Visible";
    $strings["visible_button"]["nl"] = "Zichtbaar";
    
    $strings["visible_text"]["en"] = "What can been seen";
    $strings["visible_text"]["de"] = "Was ist Sichtbar";
    $strings["visible_text"]["fr"] = "Que peut-on voir";
    $strings["visible_text"]["nl"] = "Wat valt er te zien";
    
    $strings["You need to log on"]["en"] = $element;
    $strings["You need to log on"]["de"] = $element;
    $strings["You need to log on"]["fr"] = $element;
    $strings["You need to log on"]["nl"] = "Meld u aan of registeer u om deze pagina te kunnen bewerken.";
    
    $strings["not logged on"]["en"] = $element;
    $strings["not logged on"]["de"] = "";
    $strings["not logged on"]["fr"] = "";
    $strings["not logged on"]["nl"] = "Niet aangemeld";
    
    $strings["login/register"]["en"] = $element;
    $strings["login/register"]["de"] = "Anmelden / Benutzerkonto erstellen";
    $strings["login/register"]["fr"] = "Créer un compte ou se connecter";
    $strings["login/register"]["nl"] = "Aanmelden / registreren";

    $strings["Login"]["en"] = "Login";
    $strings["Login"]["de"] = "Anmelden";
    $strings["Login"]["fr"] = "Connecter";
    $strings["Login"]["nl"] = "Aanmelden";

    $strings["logout"]["en"] = $element;
    $strings["logout"]["de"] = "Abmelden";
    $strings["logout"]["fr"] = "Déconnexion";
    $strings["logout"]["nl"] = "afmelden";
    
    $strings["Username"]["en"] = $element;
    $strings["Username"]["de"] = "Benutzername";
    $strings["Username"]["fr"] = "Nom d’utilisateur";
    $strings["Username"]["nl"] = "Gebruikersnaam";
    
    $strings["Password"]["en"] = $element;
    $strings["Password"]["de"] = "Passwort";
    $strings["Password"]["fr"] = "Mot de passe";
    $strings["Password"]["nl"] = "Wachtwoord";
    
    $strings["Don't have an account? Create one."]["en"] = "Don't have an account? <a href='/register.php'>Create one</a>.";
    $strings["Don't have an account? Create one."]["de"] = "Noch kein Benutzerkonto? <a href='/register.php'>Hier legst du ein Konto an</a>.";
    $strings["Don't have an account? Create one."]["fr"] = "Vous n'avez pas de compte? <a href='/register.php'>Créer un compte</a>.";
    $strings["Don't have an account? Create one."]["nl"] = "Nog geen gebruikersnaam? <a href='/register.php'>Maak een account aan</a>.";
    
    $strings["Unknown username or password."]["en"] = "Unknown username or password. Please try again or <a href='/lostpw.php'>reset your password</a>.";
    //$strings["Unknown username or password."]["de"] = $element;
    //$strings["Unknown username or password."]["fr"] = $element;
    $strings["Unknown username or password."]["nl"] = "Onbekende gebruikersnaam of wachtwoord. Probeer het opnieuw of <a href='/lostpw.php'>stel een nieuw wachtwoord in</a>.";
    
    $strings["Unknown email address."]["en"] = "Unknown email address. Please try again.";
    //$strings["Unknown email address."]["de"] = "Unknown email address. Please try again.";
    //$strings["Unknown email address."]["fr"] = "Unknown email address. Please try again.";
    $strings["Unknown email address."]["en"] = "Dit adres is niet bekend. Probeer het opnieuw.";
    
    $strings["The password is now active."]["en"] = "The new password is now active.";
    //$strings["The password is now active."]["de"] = "The new password is now active.";
    //$strings["The password is now active."]["fr"] = "The new password is now active.";
    $strings["The password is now active."]["nl"] = "U kunt het nieuwe wachtwoord nu gebruiken om u <a href='/login.php'>aan te melden</a>.";
    
    $strings["Create account"]["en"] = $element;
    $strings["Create account"]["de"] = "Benutzerkonto anlegen";
    $strings["Create account"]["fr"] = "Créer un compte";
    $strings["Create account"]["nl"] = "Maak account aan";
    
    $strings["Confirm password"]["en"] = $element;
    $strings["Confirm password"]["de"] = "Passwort wiederhole";
    $strings["Confirm password"]["fr"] = "Confirmez le mot de passe";
    $strings["Confirm password"]["nl"] = "Bevestig wachtwoord";

    $strings["Name"]["en"] = $element;
    $strings["Name"]["de"] = "Name";
    $strings["Name"]["fr"] = "Nom";
    $strings["Name"]["nl"] = "Naam";
    
    $strings["Email address"]["en"] = $element;
    $strings["Email address"]["de"] = "E-Mail-Adresse";
    $strings["Email address"]["fr"] = "Courriel";
    $strings["Email address"]["nl"] = "E-mailadres";
    
    $strings["reset password %s %s"]["en"] = "Dear %s,\n\nSomebody, likely you, has requested to reset the password for the Vici.org account associated with this email address. This can be done by following this link:\n\nhttp://vici.org/reset.php?code=%s\n\nYou may ignore this message if you don't want to reset the password.\n\nVici.org - Roman history nearby -\n";
    //$strings["reset password %s %s"]["de"] =
    //$strings["reset password %s %s"]["fr"] =
    $strings["reset password %s %s"]["nl"] = "Beste %s,\n\nIemand, waarschijnlijk uzelf, heeft op Vici.org verzocht het wachtwoord bij dit e-mailadres opnieuw in te stellen. Dit kan door onderstaande link te volgen.\n\nhttp://vici.org/reset.php?code=%s\n\nAls u uw wachtwoord niet wilt wijzigen dan kunt u dit bericht negeren.\n\nVici.org - Romeins verleden in de buurt -\n";

    $strings["Submit"]["en"] = $element;
    $strings["Submit"]["de"] = "Senden";
    $strings["Submit"]["fr"] = "Envoyer ";
    $strings["Submit"]["nl"] = "Verzenden";

    $strings["Reset"]["en"] = $element;
    $strings["Reset"]["de"] = "Wiederherstellen";
    $strings["Reset"]["fr"] = "Réinitialiser ";
    $strings["Reset"]["nl"] = "Opnieuw instellen";
    
    $strings["Reset password"]["en"] = $element;
    $strings["Reset password"]["de"] = "Passwort wiederherstellen";
    $strings["Reset password"]["fr"] = "Réinitialiser mot de passe";
    $strings["Reset password"]["nl"] = "Wachtwoord opnieuw instellen";
    
    $strings["An email with instructions has been sent."]["en"] = $element;
    //$strings["An email with instructions has been sent."]["de"] = $element;
    //$strings["An email with instructions has been sent."]["fr"] = $element;
    $strings["An email with instructions has been sent."]["nl"] = "Er is zojuist een e-mail aan u verzonden met instructies.";
    
    $strings["confirmation mail %s %s"]["en"] =  "Dear %s,\n\nYour account at Vici.org need to be activated. This is done by following the next link:\n\nhttp://vici.org/confirm.php?code=%s\n\nVici.org - Roman history nearby -\n";
    //$strings["confirmation mail %s %s"]["de"] = "E-Mail-Adresse";
    //$strings["confirmation mail %s %s"]["fr"] = "Courriel";
    $strings["confirmation mail %s %s"]["nl"] = "Beste %s,\n\nUw account op Vici.org is bijna klaar. Volg onderstaande link om het account te activeren.\n\nhttp://vici.org/confirm.php?code=%s\n\nVici.org - Romeins verleden in de buurt -\n";
    
    $strings["Activate your account at Vici.org"]["en"] = $element;
    //$strings["Activate your account at Vici.org"]["de"] = $element;
    //$strings["Activate your account at Vici.org"]["fr"] = $element;
    $strings["Activate your account at Vici.org"]["nl"] = "Activeer uw account op Vici.org";
    
    $strings["Account created, activation required %s"]["en"] = "Your account has been created but needs to be activate. Please activate the account by following the link that has been sent to %s.";
    //$strings["Account created, activation required %s"]["de"] = $element;
    //$strings["Account created, activation required %s"]["fr"] = $element;
    $strings["Account created, activation required %s"]["nl"] = "Uw account is aangemaakt maar moet nog geactiveerd worden. Activeer het account door de link te volgen die zojuist verstuurd is aan %s.";
    
    $strings["Username taken."]["en"] = 'Username already taken. Please select another username.';
    // $strings["Username taken."]["de"] =  $element;
    // $strings["Username taken."]["fr"] =  $element;
    $strings["Username taken."]["nl"] = 'Gebruikersnaam bestaat al. Kies aub een andere gebruikersnaam.';
    
    $strings["Email address taken."]["en"] = 'Email address already in use.';
    //$strings["Email address taken."]["de"] = 'Email address already in use.';
    //$strings["Email address taken."]["fr"] = 'Email address already in use.';
    $strings["Email address taken."]["nl"] = 'Het e-mailadres is al in gebruik.';
    
    $strings["error:username_too_short"]["en"] = "Username should be at least 4 characters.";
    //$strings["error:username_too_short"]["de"] = $element;
    //$strings["error:username_too_short"]["fr"] = $element;
    $strings["error:username_too_short"]["nl"] = "Gebruikersnaam moet minstens 4 tekens lang zijn.";
    
    $strings["error:passwords_dont_match"]["en"] = "Passwords do not match.";
    //$strings["error:passwords_dont_match"]["de"] = $element;
    //$strings["error:passwords_dont_match"]["fr"] = $element;
    $strings["error:passwords_dont_match"]["nl"] = "De opgegeven wachtwoorden verschillen.";
    
    $strings["error:password_too_short"]["en"] = "The password should be at least 7 characters.";
    //$strings["error:password_too_short"]["de"] = $element;
    //$strings["error:password_too_short"]["fr"] = $element;
    $strings["error:password_too_short"]["nl"] = "Het wachtwoord moet minstens 7 tekens bevatten.";
    
    $strings["error:password_needs_number"]["en"] = "The password must include at least one number.";
    //$strings["error:password_needs_number"]["de"] = $element;
    //$strings["error:password_needs_number"]["fr"] = $element;
    $strings["error:password_needs_number"]["nl"] = "Het wachtwoord moet minstens één cijfer bevatten.";
    
    $strings["error:password_needs_uppercase"]["en"] = "The password must include at least one uppercase letter.";
    //$strings["error:password_needs_uppercase"]["de"] = $element;
    //$strings["error:password_needs_uppercase"]["fr"] = $element;
    $strings["error:password_needs_uppercase"]["nl"] = "Het wachtwoord moet minstens één hoofdletter bevatten.";
    
    $strings["error:password_needs_lowercase"]["en"] = "The password must include one or more lowercase letters.";
    //$strings["error:password_needs_lowercase"]["de"] = $element;
    //$strings["error:password_needs_lowercase"]["fr"] = $element;
    $strings["error:password_needs_lowercase"]["nl"] = "Het wachtwoord moet minstens één niet-hoofdletter bevatten.";
    
    $strings["error:password_needs_special"]["en"] = "The password must include at least one special character - #,@,%,!";
    //$strings["error:password_needs_special"]["de"] = $element;
    //$strings["error:password_needs_special"]["fr"] = $element;
    $strings["error:password_needs_special"]["nl"] = "Het wachtwoord moet minstens één speciaal teken bevatten: #,@,%,!.";

    $strings["error:fullname_too_short"]["en"] = "Please supply your real name.";
    //$strings["error:fullname_too_short"]["de"] = $element;
    //$strings["error:fullname_too_short"]["fr"] = $element;
    $strings["error:fullname_too_short"]["nl"] = "Geef aub ook uw naam op.";
    
    $strings["error:wrong_email"]["en"] = "Incorrect e-mail address.";
    //$strings["error:wrong_email"]["de"] = $element;
    //$strings["error:wrong_email"]["fr"] = $element;
    $strings["error:wrong_email"]["nl"] = "Onjuist e-mailadres.";
    
    $strings["altar"]["en"] = 'Altar';
    //$strings["altar"]["de"] = $element;
    //$strings["altar"]["fr"] = $element;
    $strings["altar"]["nl"] = "Votiefsteen";
    
    $strings["aquaduct"]["en"] = 'Aqueduct';
    //$strings["aquaduct"]["de"] = $element;
    //$strings["aquaduct"]["fr"] = $element;
    $strings["aquaduct"]["nl"] = "Aquaduct";
    
    $strings["bridge"]["en"] = 'Bridge';
    //$strings["bridge"]["de"] = $element;
    //$strings["bridge"]["fr"] = $element;
    $strings["bridge"]["nl"] = "Brug";
    
    $strings["building"]["en"] = 'Building (other)';
    //$strings["building"]["de"] = $element;
    //$strings["building"]["fr"] = $element;
    $strings["building"]["nl"] = "Gebouw (overig)";
    
    $strings["city"]["en"] = 'City';
    //$strings["city"]["de"] = $element;
    //$strings["city"]["fr"] = $element;
    $strings["city"]["nl"] = "Stad";
    
    $strings["event"]["en"] = "Site of historic event";
    //$strings["event"]["de"] = $element;
    //$strings["event"]["fr"] = $element;
    $strings["event"]["nl"] = "Plaats historische gebeurtenis";
    
    $strings["fort"]["en"] = 'Castle';
    //$strings["fort"]["de"] = $element;
    //$strings["fort"]["fr"] = $element;
    $strings["fort"]["nl"] = "Fort";
    
    $strings["graves"]["en"] = 'Grave or burial field';
    //$strings["graves"]["de"] = $element;
    //$strings["graves"]["fr"] = $element;
    $strings["graves"]["nl"] = "Graf (-veld)";
    
    $strings["industry"]["en"] = 'Workshop or industry';
    //$strings["industry"]["de"] = $element;
    //$strings["industry"]["fr"] = $element;
    $strings["industry"]["nl"] = "Werkplaats / industrie";
        
    $strings["mansio"]["en"] = 'Tavern or relay';
    //$strings["mansio"]["de"] = $element;
    //$strings["mansio"]["fr"] = $element;
    $strings["mansio"]["nl"] = "Herberg of halteplaats";
    
    $strings["memorial"]["en"] = 'Memorial (contemporary)';
    //$strings["memorial"]["de"] = $element;
    //$strings["memorial"]["fr"] = $element;
    $strings["memorial"]["nl"] = "Gedenksteen (hedendaags)";
    
    $strings["milestone"]["en"] = 'Milestone';
    //$strings["milestone"]["de"] = $element;
    //$strings["milestone"]["fr"] = $element;
    $strings["milestone"]["nl"] = "Mijlsteen";
    
    $strings["museum"]["en"] = 'Museum';
    //$strings["museum"]["de"] = $element;
    //$strings["museum"]["fr"] = $element;
    $strings["museum"]["nl"] = "Museum (hedendaags)";

    $strings["object"]["en"] = 'Find';
    //$strings["object"]["de"] = $element;
    //$strings["object"]["fr"] = $element;
    $strings["object"]["nl"] = "Vondst(-en)";
    
    $strings["observation"]["en"] = 'Archeological observation';
    //$strings["observation"]["de"] = $element;
    //$strings["observation"]["fr"] = $element;
    $strings["observation"]["nl"] = "Archeologische waarneming"; 

    $strings["road"]["en"] = 'Road';
    $strings["road"]["de"] = "Römerstraße";
    $strings["road"]["fr"] = "Voie romaine";
    $strings["road"]["nl"] = "Weg";
        
    $strings["shipwreck"]["en"] = 'Shipwreck';
    //$strings["shipwreck"]["de"] = $element;
    //$strings["shipwreck"]["fr"] = $element;
    $strings["shipwreck"]["nl"] = "Scheepswrak";
    
    $strings["temple"]["en"] = 'Temple';
    //$strings["temple"]["de"] = $element;
    //$strings["temple"]["fr"] = $element;
    $strings["temple"]["nl"] = "Tempel";
    
    $strings["theater"]["en"] = 'Theatre or amphitheatre';
    //$strings["theater"]["de"] = $element;
    //$strings["theater"]["fr"] = $element;
    $strings["theater"]["nl"] = "Theater of amfitheater";
    
    $strings["vicus"]["en"] = 'Vicus or canabae';
    //$strings["vicus"]["de"] = $element;
    //$strings["vicus"]["fr"] = $element;
    $strings["vicus"]["nl"] = "Vicus of canabae";
    
    $strings["villa"]["en"] = "Villa rustica";
    $strings["villa"]["de"] = "Villa rustica";
    $strings["villa"]["fr"] = "Villa rustica";
    $strings["villa"]["nl"] = "Villa rustica";
    
    $strings["watchtower"]["en"] = 'Watchtower';
    //$strings["watchtower"]["de"] = $element;
    //$strings["watchtower"]["fr"] = $element;
    $strings["watchtower"]["nl"] = "Wachttoren";
    
    $strings["visible"]["en"] = $element;
    $strings["visible"]["de"] = $element;
    $strings["visible"]["fr"] = $element;
    $strings["visible"]["nl"] = "Zichtbaar";
    
    $strings["invisible"]["en"] = $element;
    $strings["invisible"]["de"] = $element;
    $strings["invisible"]["fr"] = $element;
    $strings["invisible"]["nl"] = "Niet zichtbaar";
    
    $strings["This object was added by %s on %s."]["en"] = $element;
    $strings["This object was added by %s on %s."]["de"] = $element;
    $strings["This object was added by %s on %s."]["fr"] = $element;
    $strings["This object was added by %s on %s."]["nl"] = "Dit object is toegevoegd door %s op %s. ";

    $strings["Last update by %s on %s."]["en"] = $element;
    $strings["Last update by %s on %s."]["de"] = $element;
    $strings["Last update by %s on %s."]["fr"] = $element;
    $strings["Last update by %s on %s."]["nl"] = "Laatst bewerkt door %s op %s. ";

    /* form validation messages: */
    $strings["no changes in form"]["en"] = $element;
    $strings["no changes in form"]["de"] = $element;
    $strings["no changes in form"]["fr"] = $element;
    $strings["no changes in form"]["nl"] = "Er zijn geen wijzigingen, de pagina is niet opgeslagen.";
    
    $strings["summary is required"]["en"] = $element;
    $strings["summary is required"]["de"] = $element;
    $strings["summary is required"]["fr"] = $element;
    $strings["summary is required"]["nl"] = "De samenvatting is een verplicht veld maar ontbreekt nu";
    
    $strings["requires a latitude and longitude"]["en"] = $element;
    $strings["requires a latitude and longitude"]["de"] = $element;
    $strings["requires a latitude and longitude"]["fr"] = $element;
    $strings["requires a latitude and longitude"]["nl"] = "De breedte- en lengtegraad ontbreken. Gebruik notatie met decimale punt, een komma als scheidingsteken.";
    
    $strings["title should be longer"]["en"] = $element;
    $strings["title should be longer"]["de"] = $element;
    $strings["title should be longer"]["fr"] = $element;
    $strings["title should be longer"]["nl"] = "De titel ontbreekt of is te kort.";
    
    $strings["do not delete content"]["en"] = $element;
    $strings["do not delete content"]["de"] = $element;
    $strings["do not delete content"]["fr"] = $element;
    $strings["do not delete content"]["nl"] = "Te veel pagina-inhoud verwijderd.";

    $strings["Main page"]["en"] = "Map";
    $strings["Main page"]["de"] = "Landkarte";
    $strings["Main page"]["fr"] = "Carte";
    $strings["Main page"]["nl"] = "Kaart";
    
    $strings["Add a new place"]["en"] = $element;
    $strings["Add a new place"]["de"] = "Neuen Ort einfügen";
    $strings["Add a new place"]["fr"] = "Ajouter nouveau lieu";
    $strings["Add a new place"]["nl"] = "Nieuwe plaats toevoegen";
    
    $strings["Recently added"]["en"] = $element;
    $strings["Recently added"]["de"] = "Zuletzt hinzugefügt";
    $strings["Recently added"]["fr"] = "Nouveaux lieux";
    $strings["Recently added"]["nl"] = "Onlangs toegevoegd";
    
    $strings["Recently changed"]["en"] = $element;
    $strings["Recently changed"]["de"] = "Letzte Änderungen";
    $strings["Recently changed"]["fr"] = "Modifications récentes";
    $strings["Recently changed"]["nl"] = "Onlangs gewijzigd";
    
    $strings["by"]["en"] = $element;
    $strings["by"]["de"] = "von";
    $strings["by"]["fr"] = "par";
    $strings["by"]["nl"] = "door";
    
    $strings["Top creators"]["en"] = $element;
    //$strings["Top creators"]["de"] = "";
    //$strings["Top creators"]["fr"] = "";
    $strings["Top creators"]["nl"] = "Top toevoegers";
    
    $strings["Top editors"]["en"] = $element;
    //$strings["Top creators"]["de"] = "";
    //$strings["Top creators"]["fr"] = "";
    $strings["Top editors"]["nl"] = "Top bewerkers";
    
    $strings["About Vici"]["en"] = $element;
    $strings["About Vici"]["de"] = "Über Vici.org";
    $strings["About Vici"]["fr"] = "À propos de Vici.org";
    $strings["About Vici"]["nl"] = "Over Vici.org";

    $strings["Linking to the map"]["en"] = $element;
    $strings["Linking to the map"]["de"] = "Karte verlinken";
    $strings["Linking to the map"]["fr"] = "Créer des liens";
    $strings["Linking to the map"]["nl"] = "Naar de kaart linken";  
    
    $strings["Data services"]["en"] = $element;
    $strings["Data services"]["de"] = "Datendienste";
    $strings["Data services"]["fr"] = "Services de données";
    $strings["Data services"]["nl"] = "Gegevensdiensten";    
        
    $strings["Mobile version"]["en"] = $element;
    $strings["Mobile version"]["de"] = "Mobile Ansicht";
    $strings["Mobile version"]["fr"] = 'Version mobile';
    $strings["Mobile version"]["nl"] = "Mobiele versie";
    
    $strings["Presentation"]["en"] = $element;
    $strings["Presentation"]["de"] = 'Präsentation';
    $strings["Presentation"]["fr"] = 'Présentation';
    $strings["Presentation"]["nl"] = 'Voorstelling';
    
    $strings["Languages"]["en"] = "In other languages";
    $strings["Languages"]["de"] = "In anderen Sprachen";
    $strings["Languages"]["fr"] = "Autres languages";
    $strings["Languages"]["nl"] = "In andere talen";
    
    $strings["edit"]["en"] = $element;
    $strings["edit"]["de"] = "Bearbeiten";
    $strings["edit"]["fr"] = "modifier";
    $strings["edit"]["nl"] = "bewerken";
    
    $strings["Save page"]["en"] = $element;
    $strings["Save page"]["de"] = "Seite speichern";
    $strings["Save page"]["fr"] = "Publier";
    $strings["Save page"]["nl"] = "Pagina opslaan";
    
    $strings["Cancel"]["en"] = $element;
    $strings["Cancel"]["de"] = "Abbrechen";
    $strings["Cancel"]["fr"] = "Annuler";
    $strings["Cancel"]["nl"] = "Annuleren";
    
    $strings["Unique name"]["en"] = "Unique name for the object";
    $strings["Unique name"]["de"] = $element;
    $strings["Unique name"]["fr"] = $element;
    $strings["Unique name"]["nl"] = "Unieke naam voor het object";
   
    $strings["Summary"]["en"] =  "summary (required)";
    $strings["Summary"]["de"] =  $element;
    $strings["Summary"]["fr"] =  $element;
    $strings["Summary"]["nl"] =  "samenvatting (verplicht)";
    
    $strings["subcategory"]["en"] = "subcategory (optional)";
    $strings["subcategory"]["de"] = $element;
    $strings["subcategory"]["fr"] = $element;
    $strings["subcategory"]["nl"] = "subcategorie (optioneel)";

    $strings["Coordinats"]["en"] =  $element;
    $strings["Coordinats"]["de"] =  "Koordinaten";
    $strings["Coordinats"]["fr"] =  "Coordonnées";
    $strings["Coordinats"]["nl"] =  "Coördinaten";
    
    $strings["Category"]["en"] =  $element;
    $strings["Category"]["de"] =  "Kategorie";
    $strings["Category"]["fr"] =  "Catégorie";
    $strings["Category"]["nl"] =  "Categorie";
    
    $strings["Estimated accuracy"]["en"] =  $element;
    $strings["Estimated accuracy"]["de"] =  "Geschätzte Genauigkeit";
    $strings["Estimated accuracy"]["fr"] =  "Précision estimée";
    $strings["Estimated accuracy"]["nl"] =  "Geschatte nauwkeurigheid";
    
    $strings["Identifiers"]["en"] =  $element;
    $strings["Identifiers"]["de"] =  $element;
    $strings["Identifiers"]["fr"] =  "Identificateurs";
    $strings["Identifiers"]["nl"] =  $element;
    
    $strings["coordinats"]["en"] = "latitude, longitude (decimal)";
    $strings["coordinats"]["de"] = "latitude, longitude (decimal)";
    $strings["coordinats"]["fr"] = "latitude, longitude (decimal)";
    $strings["coordinats"]["nl"] = "breedte-, lengtegraad (decimaal)";
    
    $strings["external URI"]["en"] = "external URI (optional)";
    $strings["external URI"]["de"] = $element;
    $strings["external URI"]["fr"] = $element;
    $strings["external URI"]["nl"] = "URI in andere dataset (optioneel)";
    
    $strings["References"]["en"] =  $element;
    $strings["References"]["de"] =  "Referenzen";
    $strings["References"]["fr"] =  "Références";
    $strings["References"]["nl"] =  "Bronverwijzingen";
    
    $strings["All objects"]["en"] = $element;
    $strings["All objects"]["de"] = 'Alles zeigen';
    $strings["All objects"]["fr"] = 'Tous les objets';
    $strings["All objects"]["nl"] = 'Alle objecten';
    
    $strings["Visible objects only"]["en"] = $element;
    $strings["Visible objects only"]["de"] = 'Nur sichtbare Objekte';
    $strings["Visible objects only"]["fr"] = 'Seul les objets visible';
    $strings["Visible objects only"]["nl"] = 'Alleen zichtbare objecten';
    
    $strings["Show labels"]["en"] = $element;
    $strings["Show labels"]["de"] = 'Labels anzeigen';
    $strings["Show labels"]["fr"] = 'Noms des lieux modernes';
    $strings["Show labels"]["nl"] = 'Toon labels';
    
    $strings["Available under CCASA"]["en"] = $element;
    $strings["Available under CCASA"]["de"] = $element;
    $strings["Available under CCASA"]["fr"] = $element;
    $strings["Available under CCASA"]["nl"] = 'Beschikbaar onder de <a href="http://creativecommons.org/licenses/by-sa/3.0/deed.nl">Creative Commons Naamsvermelding/Gelijk delen</a>-licentie.';
    
    $strings["New object"]["en"] = 'Add new object or location';
    $strings["New object"]["de"] = $element;
    $strings["New object"]["fr"] = $element;
    $strings["New object"]["nl"] = 'Nieuw object of plaats toevoegen';

    $strings["Settlements"]["en"] = 'Settlements';
    //$strings["Settlements"]["de"] = $element;
    ///$strings["Settlements"]["fr"] = $element;
    $strings["Settlements"]["nl"] = 'Steden of nederzettingen';
    
    $strings["category 2"] = $strings["Settlements"];

    $strings["Military buildings"]["en"] = "Military buildings or objects";
    //$strings["Military buildings"]["de"] = $element;
    //$strings["Military buildings"]["fr"] = $element;
    $strings["Military buildings"]["nl"] = 'Militaire gebouwen of objecten';

    $strings["category 4"] = $strings["Military buildings"];

    $strings["Infrastructural"]["en"] = 'Infrastructural';
    //$strings["Infrastructural"]["de"] = $element;
    //$strings["Infrastructural"]["fr"] = $element;
    $strings["Infrastructural"]["nl"] = 'Infrastructurele objecten';
    
    $strings["category 6"] = $strings["Infrastructural"];
    
    $strings["Roman buildings"]["en"] = 'Roman buildings';
    //$strings["Roman buildings"]["de"] = $element;
    //$strings["Roman buildings"]["fr"] = $element;
    $strings["Roman buildings"]["nl"] = 'Romeinse gebouwen';
    
    $strings["category 8"] = $strings["Roman buildings"];
    
    $strings["Smaller objects"]["en"] = 'Objects or observations';
    //$strings["Smaller objects"]["de"] = $element;
    //$strings["Smaller objects"]["fr"] = $element;
    $strings["Smaller objects"]["nl"] = 'Objecten of waarnemingen';
    
    $strings["category 10"] = $strings["Smaller objects"];
        
    $strings["Current day objects or locations"]["en"] = "Museums etcetera";
    //$strings["Current day objects or locations"]["de"] = $element;
    //$strings["Current day objects or locations"]["fr"] = $element;
    $strings["Current day objects or locations"]["nl"] = 'Musea e.d.';
    
    $strings["category 12"] = $strings["Current day objects or locations"];
       
    $strings["Event"]["en"] = "Location of a historic event";
    //$strings["event"]["de"] = $element;
    //$strings["event"]["fr"] = $element;
    $strings["Event"]["nl"] = "Plaats van een historische gebeurtenis";
    
    $strings["Event explained"]["en"] = "The location of a major historic event, for example a battlefield.";
    //$strings["event explained"]["de"] = $element;
    //$strings["event explained"]["fr"] = $element;
    $strings["Event explained"]["nl"] = "De plaats van een belangrijke historische gebeurtenis zoals bijvoorbeeld een veldslag.";
    
    $strings["Museum"]["en"] = $element;
    $strings["Museum"]["de"] = $element;
    $strings["Museum"]["fr"] = $element;
    $strings["Museum"]["nl"] = $element;
    
    $strings["Museum explained"]["en"] = 'A museum about Roman history or that has Roman artifacts on display.';
    //$strings["Museum"]["de"] = $element;
    //$strings["Museum"]["fr"] = $element;
    $strings["Museum explained"]["nl"] = 'Een museum over Romeinse geschiedenis of met een tentoonstelling van Romeinse objecten.';
    
    $strings["Monument"]["en"] = $element;
    $strings["Monument"]["de"] = $element;
    $strings["Monument"]["fr"] = $element;
    $strings["Monument"]["nl"] = $element;
    
    $strings["Monument explained"]["en"] = 'A monument that reminds about Roman history related to the specific location.';
    //$strings["Monument explained"]["de"] = $element;
    //$strings["Monument explained"]["fr"] = $element;
    $strings["Monument explained"]["nl"] = 'Een monument dat herinnert aan Romeinse geschiedenis op de betreffende locatie.';    
    
    $strings["Castle"]["en"] = $element;
    //$strings["Castle"]["de"] = $element;
    //$strings["Castle"]["fr"] = $element;
    $strings["Castle"]["nl"] = 'Fort';
    
    $strings["castle"]["en"] = $element;
    //$strings["castle"]["de"] = $element;
    //$strings["castle"]["fr"] = $element;
    $strings["castle"]["nl"] = 'Fort';
    
    $strings["Castle explained"]["en"] = 'A Roman fort, mini-fort or naval base.';
    //$strings["Castle explained"]["de"] = $element;
    //$strings["Castle explained"]["fr"] = $element;
    $strings["Castle explained"]["nl"] = 'Een militaire basis zoals een castrum, castellum, marinebasis of mini-fort.';    
    
    $strings["City"]["en"] = $element;
    //$strings["City"]["de"] = $element;
    //$strings["City"]["fr"] = $element;
    $strings["City"]["nl"] = 'Stad';
    
    $strings["City explained"]["en"] = 'A city built following the Roman model, like a civitas or colonia.';
    //$strings["City explained"]["de"] = $element;
    //$strings["City explained"]["fr"] = $element;
    $strings["City explained"]["nl"] = 'Een stad op Romeinse leest, bijvoorbeeld een civitas of colonia.';   
    
    $strings["Vicus explained"]["en"] = 'A village built following the Roman model, like a canabae or vicus.';
    //$strings["Vicus explained"]["de"] = $element;
    //$strings["Vicus explained"]["fr"] = $element;
    $strings["Vicus explained"]["nl"] = 'Een kleinere plaats op Romeinse leest, zoals een canabae of vicus.';   
    
    $strings["Settlement"]["en"] = $element;
    $strings["Settlement"]["de"] = $element;
    $strings["Settlement"]["fr"] = $element;
    $strings["Settlement"]["nl"] = 'Nederzetting';
    
    $strings["rural"]["en"] = 'Rural settlement';
    //$strings["rural"]["de"] = $element;
    //$strings["rural"]["fr"] = $element;
    $strings["rural"]["nl"] = 'Inheemse nederzetting';
    
    $strings["Settlement explained"]["en"] = 'A rural settlement, farm or group of farms.';
    //$strings["Settlement explained"]["de"] = $element;
    //$strings["Settlement explained"]["fr"] = $element;
    $strings["Settlement explained"]["nl"] = 'Een inheemse nederzetting of groep gebouwen.';  
    
    $strings["Road"]["en"] = $element;
    $strings["Road"]["de"] = "Römerstraße";
    $strings["Road"]["fr"] = "Voie romaine";
    $strings["Road"]["nl"] = "Weg";
    
    $strings["Road explained"]["en"] = "Roman road or a location where a part of a roman road is visible. Can be displayed as a line on the map";
    $strings["Road explained"]["de"] = "Römerstraße";
    $strings["Road explained"]["fr"] = "Voie romaine";
    $strings["Road explained"]["nl"] = "Romeinse weg, of een plek op de kaart waar een romeinse weg zichtbaar is. Kan als een lijn op de kaart getoond worden.";
    
    $strings["Watchtower"]["en"] = $element;
    //$strings["Watchtower"]["de"] = $element;
    //$strings["Watchtower"]["fr"] = $element;
    $strings["Watchtower"]["nl"] = 'Wachttoren';
    
    $strings["Watchtower explained"]["en"] = 'A Roman watchtower or comparable smaller military object.';
    //$strings["Watchtower explained"]["de"] = $element;
    //$strings["Watchtower explained"]["fr"] = $element;
    $strings["Watchtower explained"]["nl"] = 'Een kleiner militair object als een wacht- of signaaltoren.';  
    
    $strings["Camp"]["en"] = $element;
    //$strings["Camp"]["de"] = $element;
    //$strings["Camp"]["fr"] = $element;
    $strings["Camp"]["nl"] = 'Marskamp';
    
    $strings["camp"]["en"] = 'Temporary camp';
    //$strings["camp"]["de"] = $element;
    //$strings["camp"]["fr"] = $element;
    $strings["camp"]["nl"] = 'Marskamp';
    
    $strings["Camp explained"]["en"] = 'A temporary camp.';
    $strings["Camp explained"]["de"] = 'Ein Marschlager';
    //$strings["Camp explained"]["fr"] = $element;
    $strings["Camp explained"]["nl"] = 'Een tijdelijk kamp of marskamp.'; 
    
    $strings["Villa rustica explained"]["en"] = 'The central building or buildings of an agricultural estate.';
    //$strings["Villa rustica explained"]["de"] = $element;
    //$strings["Villa rustica explained"]["fr"] = $element;
    $strings["Villa rustica explained"]["nl"] = 'Een villa of villa-complex met agrarische functie.'; 
    
    $strings["Mansio explained"]["en"] = 'A tavern or small settlement around a resting place along a road.';
    //$strings["Mansio explained"]["de"] = $element;
    //$strings["Mansio explained"]["fr"] = $element;
    $strings["Mansio explained"]["nl"] = 'Een herberg of een halteplaats langs een weg.'; 
    
    $strings["Theatre"]["en"] = $element;
    $strings["Theatre"]["de"] = $element;
    $strings["Theatre"]["fr"] = $element;
    $strings["Theatre"]["nl"] = 'Theater';
    
    $strings["Theatre explained"]["en"] = 'A theatre, amphitheatre or circus.';
    //$strings["Theatre explained"]["de"] = $element;
    //$strings["Theatre explained"]["fr"] = $element;
    $strings["Theatre explained"]["nl"] = 'Een theater, amfitheater of circus.'; 
    
    $strings["Baths"]["en"] = $element;
    $strings["Baths"]["de"] = $element;
    $strings["Baths"]["fr"] = 'Thermae';
    $strings["Baths"]["nl"] = 'Thermen';
    
    $strings["baths"]["en"] = 'Baths';
    $strings["baths"]["de"] = 'Thermae';
    $strings["baths"]["fr"] = 'Thermae';
    $strings["baths"]["nl"] = 'Thermen';
    
    $strings["Baths explained"]["en"] = 'Public baths or a smaller bath house.';
    //$strings["Baths explained"]["de"] = $element;
    //$strings["Baths explained"]["fr"] = $element;
    $strings["Baths explained"]["nl"] = 'Thermen of een kleiner badhuis.'; 
    
    $strings["Temple"]["en"] = $element;
    $strings["Temple"]["de"] = $element;
    $strings["Temple"]["fr"] = $element;
    $strings["Temple"]["nl"] = 'Tempel';
    
    $strings["Temple explained"]["en"] = 'A Roman or indigenous Roman temple.';
    //$strings["Temple explained"]["de"] = $element;
    //$strings["Temple explained"]["fr"] = $element;
    $strings["Temple explained"]["nl"] = 'Romeinse of Romeins-inheemse tempel.'; 

    $strings["Aquaduct"]["en"] = 'Aqueduct';
    $strings["Aquaduct"]["de"] = 'Wasserleitung';
    $strings["Aquaduct"]["fr"] = 'Aqueduc';
    $strings["Aquaduct"]["nl"] = $element;
    
    $strings["Aquaduct explained"]["en"] = 'Aqueduct, or a location where a part of an aqueduct is visible. Can be displayed as a line on the map.';
    //$strings["Aquaduct explained"]["de"] = $element;
    //$strings["Aquaduct explained"]["fr"] = $element;
    $strings["Aquaduct explained"]["nl"] = 'Aquaduct, of plek waar een deel van een aquaduct zichtbaar is. Kan als een lijn op de kaart getoond worden.'; 
    
    $strings["Workshop"]["en"] = $element;
    $strings["Workshop"]["de"] = $element;
    $strings["Workshop"]["fr"] = $element;
    $strings["Workshop"]["nl"] = 'Werkplaats of industrie';
    
    $strings["Workshop explained"]["en"] = 'A workshop or industry like a mine or pottery.';
    //$strings["Workshop explained"]["de"] = $element;
    //$strings["Workshop explained"]["fr"] = $element;
    $strings["Workshop explained"]["nl"] = 'Industriële gebouwen of installaties zoals een mijn of tegelbakkerij.'; 
    
    $strings["Bridge"]["en"] = 'Bridge';
    //$strings["Bridge"]["de"] = $element;
    //$strings["Bridge"]["fr"] = $element;
    $strings["Bridge"]["nl"] = 'Brug';
    
    $strings["Bridge explained"]["en"] = 'A roman bridge.';
    //$strings["Bridge explained"]["de"] = $element;
    //$strings["Bridge explained"]["fr"] = $element;
    $strings["Bridge explained"]["nl"] = 'Een Romeinse brug.'; 
    
    
    $strings["Grave"]["en"] = 'Grave';
    $strings["Grave"]["de"] = $element;
    $strings["Grave"]["fr"] = $element;
    $strings["Grave"]["nl"] = 'Graf';
    
    $strings["Grave explained"]["en"] = 'Graves, burial field or grave monument.';
    //$strings["Grave explained"]["de"] = $element;
    //$strings["Grave explained"]["fr"] = $element;
    $strings["Grave explained"]["nl"] = 'Grafveld of significant grafmonument.'; 
    
    $strings["Building"]["en"] = $element;
    $strings["Building"]["de"] = $element;
    $strings["Building"]["fr"] = $element;
    $strings["Building"]["nl"] = 'Gebouw (overig)';
    
    $strings["Building explained"]["en"] = 'Stone remains of an (unclassified) building.';
    //$strings["Building explained"]["de"] = $element;
    //$strings["Building explained"]["fr"] = $element;
    $strings["Building explained"]["nl"] = 'Overige gebouwen of niet-determineerbare stenen gebouwresten.'; 
    
    $strings["Altar"]["en"] = $element;
    $strings["Altar"]["de"] = $element;
    $strings["Altar"]["fr"] = $element;
    $strings["Altar"]["nl"] = 'Votiefsteen';
    
    $strings["Altar explained"]["en"] = 'An altar or votive stone.';
    //$strings["Altar explained"]["de"] = $element;
    //$strings["Altar explained"]["fr"] = $element;
    $strings["Altar explained"]["nl"] = 'Een altaar of een votiefsteen.'; 
    
    $strings["Milestone"]["en"] = $element;
    $strings["Milestone"]["de"] = $element;
    $strings["Milestone"]["fr"] = $element;
    $strings["Milestone"]["nl"] = 'Mijlsteen';
    
    $strings["Milestone explained"]["en"] = 'The original location of a Roman milestone.';
    //$strings["Milestone explained"]["de"] = $element;
    //$strings["Milestone explained"]["fr"] = $element;
    $strings["Milestone explained"]["nl"] = 'De originele locatie van een Romeinse mijlsteen.'; 
    
    $strings["Shipwreck"]["en"] = $element;
    $strings["Shipwreck"]["de"] = $element;
    $strings["Shipwreck"]["fr"] = $element;
    $strings["Shipwreck"]["nl"] = 'Scheepswrak';
    
    $strings["Shipwreck explained"]["en"] = 'Location where the remains of a ship were found.';
    //$strings["Shipwreck explained"]["de"] = $element;
    //$strings["Shipwreck explained"]["fr"] = $element;
    $strings["Shipwreck explained"]["nl"] = 'Vindplaats van het restant van een schip.'; 
    
    $strings["Find"]["en"] = $element;
    $strings["Find"]["de"] = $element;
    $strings["Find"]["fr"] = $element;
    $strings["Find"]["nl"] = 'Vondst';
    
    $strings["Find explained"]["en"] = 'The location of smaller archeological finds, like ceramics or coins.';
    //$strings["Find explained"]["de"] = $element;
    //$strings["Find explained"]["fr"] = $element;
    $strings["Find explained"]["nl"] = 'Een kleinere archeologische vondst zoals resten keramiek of munten.'; 
    
    $strings["Observation"]["en"] = 'Archeological observation';
    //$strings["Observation"]["de"] = $element;
    //$strings["Observation"]["fr"] = $element;
    $strings["Observation"]["nl"] = 'Archeologische waarneming';
    
    $strings["Observation explained"]["en"] = 'An archeological observation, like the traces of a ditch as part of a road.';
    //$strings["Observation explained"]["de"] = $element;
    //$strings["Observation explained"]["fr"] = $element;
    $strings["Observation explained"]["nl"] = 'Een archeologische waarneming, bijvoorbeeld resten van een greppel als aanwijzing van de ligging van een weg.'; 
    
    $strings["Vici.org explained"]["en"] = '';
    //$strings["Vici.org explained"]["de"] = $element;
    //$strings["Vici.org explained"]["fr"] = $element;
    $strings["Vici.org explained"]["nl"] = 'Vici.org maakt het Romeinse verleden weer zichtbaar.'; 
    
    $strings["Changelog"]["en"] = '<h2>Recent changes</h2>
    <ul>
    
    </ul>';
    //$strings["Changelog explained"]["de"] = $element;
    //$strings["Changelog explained"]["fr"] = $element;
    //$strings["Changelog explained"]["nl"] = ''; 
    
    $strings["Plans explained"]["en"] = '<h2>Future changes</h2>
    <p>The following lists gives the some of the improvements that are envisioned for future versions of Vici.org.</p>
    <ul>
    <li>Enable registration functionality <strong>-- high priority</strong>.</li>
    <li>Add or improve current translations.</li>
    <li>Create a viewer for older versions of locations. Currently older versions are stored but there is no way for users to view them.</li>
    <li>Improve the usability of the functionality for specifying geolocations.</li>
    <li>Enable users to add lines like roads or aquaducts.</li>
    <li>Enable users to upload images.</li>
    <li>Create a native app for the mobile interface.</li>
    </ul>';
    //$strings["Plans explained"]["de"] = $element;
    //$strings["Plans explained"]["fr"] = $element;
    //$strings["Plans explained"]["nl"] = ''; 
    
    $strings["Account activation"]["en"] = $element;
    //$strings["Account activation"]["de"] = $element;
    //$strings["Account activation"]["fr"] = $element;
    $strings["Account activation"]["nl"] = 'Activering van uw account';
    
    $strings["No confirmation code supplied."]["en"] = $element;
    //$strings["No confirmation code supplied."]["de"] = $element;
    //$strings["No confirmation code supplied."]["fr"] = $element;
    $strings["No confirmation code supplied."]["nl"] = 'Geen bevestigingscode opgegeven.';
  
    $strings["Confirmation code not found"]["en"] = 'Unknown confirmation code';
    //$strings["Confirmation code not found"]["de"] = $element;
    //$strings["Confirmation code not found"]["fr"] = $element;
    $strings["Confirmation code not found"]["nl"] = 'Onbekende bevestigingscode'; 
  
    $strings["Your account has been activated."]["en"] = $element;
    //$strings["Your account has been activated."]["de"] = $element;
    //$strings["Your account has been activated."]["fr"] = $element;
    $strings["Your account has been activated."]["nl"] = 'Uw account is geactiveerd.'; 
  
    $strings["Login to continue."]["en"] = "<a href='/login.php'>Login</a> to continue.";
    //$strings["Login to continue."]["de"] = $element;
    //$strings["Login to continue."]["fr"] = $element;
    $strings["Login to continue."]["nl"] = "<a href='/login.php'>Meld u aan</a> om door te gaan";
    
    $strings["metadescription"]["en"] =  "Vici.org, Roman history nearby. The wiki that puts all roman history, sites and finds on the map.";
    //$strings["metadescription"]["de"] =  "Vici.org, Roman history nearby. The wiki that puts all roman sites and finds on the map.";
    //$strings["metadescription"]["fr"] =  "Vici.org, Roman history nearby. The wiki that puts all roman sites and finds on the map.";
    $strings["metadescription"]["nl"] =  "Vici.org, Romeins verleden in de buurt. De wiki die Romeinse geschiedenis, vondsten en monumenten op de kaart zet.";

    $strings["Search results"]["en"] = $element;
    $strings["Search results"]["de"] = "Suchergebnisse";
    $strings["Search results"]["fr"] = "Résultats de la recherche";
    $strings["Search results"]["nl"] = "Zoekresultaten";
  
    $strings["Page"]["en"] = $element;
    $strings["Page"]["de"] = "Seite";
    $strings["Page"]["fr"] = "Pagina";
    $strings["Page"]["nl"] = "Pagina";
    
    $strings["Download as KML"]["en"] = $element;
    $strings["Download as KML"]["de"] = "KML Datei runterladen";
    $strings["Download as KML"]["fr"] = "Télécharger des fichiers KML";
    $strings["Download as KML"]["nl"] = "Bewaren als KML";
  
    $strings["Nothing found"]["en"] = "There were no results matching the query.";
    $strings["Nothing found"]["de"] = "Für die Suchanfrage wurden keine Ergebnisse gefunden.";
    $strings["Nothing found"]["fr"] = "Il n’y a aucun résultat correspondant à la requête.";
    $strings["Nothing found"]["nl"] = "De zoekvraag leverde geen resultaten.";

    $strings["lostpw_intro"]["en"] = "To reset your password, please identify yourself by supplying the email adressed you registered at Vici.";
    //$strings["lostpw_intro"]["de"] = "";
    //$strings["lostpw_intro"]["fr"] = "Télécharger des fichiers KML";
    $strings["lostpw_intro"]["nl"] = "Om een nieuw wachtwoord in te kunnen stellen moet u zich eerst identificeren met het e-mailadres waarmee u zich bij Vici geregistreerd heeft.";
  
  
    $strings["No annotation yet"]["en"] =  "There is not yet an English annotation for this place.";
    $strings["No annotation yet"]["de"] =  "Es gibt noch keine Anmerkungen auf Deutsch.";
    $strings["No annotation yet"]["fr"] =  "Il n'y a pas une annotation en français.";
    $strings["No annotation yet"]["nl"] =  "Er is nog geen Nederlandstalige annotatie voor deze plaats.";
  
    $strings["Feel invited to add an annotation"]["en"] =  "You are invited to improve Vici by adding one.";
    $strings["Feel invited to add an annotation"]["de"] =  " ";
    $strings["Feel invited to add an annotation"]["fr"] =  " ";
    $strings["Feel invited to add an annotation"]["en"] =  "U kunt Vici helpen verbeteren door een annotatie toe te voegen.";
  
    $strings["There are annotations in other languages"]["en"] = $element;
    $strings["There are annotations in other languages"]["de"] = "Text in anderen Sprachen";
    $strings["There are annotations in other languages"]["fr"] = "Il ya du texte dans d'autres langues";
    $strings["There are annotations in other languages"]["nl"] = "Er zijn wel annotaties in andere talen";
  
    $strings["Available under the Creative Commons Attribution-ShareAlike License."]["en"] = $element;
    $strings["Available under the Creative Commons Attribution-ShareAlike License."]["de"] = "Verfügbar unter der „Creative Commons Attribution/Share Alike“ Lizenz.";
    $strings["Available under the Creative Commons Attribution-ShareAlike License."]["fr"] = "Disponible sous licence Creative Commons paternité partage à l’identique.";
    $strings["Available under the Creative Commons Attribution-ShareAlike License."]["nl"] = "Beschikbaar onder de Creative Commons Naamsvermelding/Gelijk delen-licentie.";

    $strings["Images"]["en"] = $element;
    $strings["Images"]["de"] = "Bilder";
    $strings["Images"]["fr"] = "Des images";
    $strings["Images"]["nl"] = "Afbeeldingen";
  
    $strings["Panoramio images"]["en"] = "Pictures from the area (Panoramio)";
    $strings["Panoramio images"]["de"] = "Bilder aus der Gegend (Panoramio)";
    $strings["Panoramio images"]["fr"] = "Photos de la région (Panoramio)";
    $strings["Panoramio images"]["nl"] = "Foto's uit de omgeving (Panoramio)";
    
    $strings["about this image"]["en"] = $element;
    $strings["about this image"]["de"] = "Beschreibung und Lizenz";
    $strings["about this image"]["fr"] = "description et licence";
    $strings["about this image"]["nl"] = "beschrijving en licentie";
    
    $strings["upload images"]["en"] = $element;
    $strings["upload images"]["de"] = "Bilder Hochladen";
    $strings["upload images"]["fr"] = "télécharger des photos";
    $strings["upload images"]["nl"] = "foto upload";
    
    $strings["upload kml"]["en"] = 'KML file upload';
    $strings["upload kml"]["de"] = "KML Hochladen";
    $strings["upload kml"]["fr"] = "télécharger de fichier KML";
    $strings["upload kml"]["nl"] = "KML upload";
    
    $strings["Recently uploaded"]["en"] = $element;
    $strings["Recently uploaded"]["de"] = "Neueste Bilder";
    $strings["Recently uploaded"]["fr"] = "Dernières images";
    $strings["Recently uploaded"]["nl"] = "Nieuwste afbeeldingen";
    
  
    // return asked string, for DEFAULTLANG if needed, otherwise the bare $element
    if (isset($strings[$element][$lang])) {
        return($strings[$element][$lang]);
    } elseif (isset($strings[$element][DEFAULTLANG])) {
                return($strings[$element][DEFAULTLANG]);
    } else return $element;
    
}


?>
