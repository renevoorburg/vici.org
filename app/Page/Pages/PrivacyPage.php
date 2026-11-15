<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;

class PrivacyPage extends PageRenderer
{
    private string $template = 'privacy.tpl';

    private const CONTENT_EN = '<p>Vici.org is a non-commercial website run by one private individual — no company or organisation is involved. This policy explains which personal data Vici.org processes and why.</p>

<h2>Data controller</h2>
<p>René Voorburg, <a href="mailto:rene@digitopia.nl">rene@digitopia.nl</a>. Hosting takes place within the European Union.</p>

<h2>What is stored when you register</h2>
<ul>
<li><strong>Account name</strong> and <strong>display name</strong>. The display name is shown publicly on the pages and images you contribute to; you choose it yourself — it does not have to be your real name and you can change it in your profile settings.</li>
<li><strong>E-mail address</strong>. Used only for account activation and password resets. It is never shown publicly, never shared with others, and never sold — Vici.org is not commercial and has no interest in your data.</li>
<li><strong>Password</strong>. Never stored as such; only a cryptographic hash is kept, so the site operator cannot read your password.</li>
</ul>

<h2>Contributions</h2>
<p>Texts, images and lines you contribute are published under the licenses stated on the site (written content CC BY-SA, metadata CC0) and are publicly attributed to your display name. Contributions are provided by users under their own responsibility; if you notice a problem with a contribution, please report it.</p>

<h2>Cookies and tracking</h2>
<p>Vici.org uses only functional cookies: a session cookie (PHPSESSID) needed for login and preferences. There are no advertising or third-party tracking cookies. Bot protection is done with a self-hosted proof-of-work check (ALTCHA); nothing is sent to an external service.</p>

<h2>Technical data</h2>
<p>Like any webserver, the site keeps technical logs that may contain IP addresses, and uses IP-based rate limiting to protect the service. These data are not linked to your account, are not shared and are retained only as long as needed for security and troubleshooting.</p>

<h2>Your rights</h2>
<p>Under the GDPR you may request access to, correction of, or deletion of your personal data, and you may object to processing. E-mail addresses are never published, so simply contact <a href="mailto:rene@digitopia.nl">rene@digitopia.nl</a>. Note that published contributions are meant to be a lasting part of the atlas (as on Wikipedia); deleting your account does not automatically remove them — choose a display name you are comfortable with.</p>';

    private const CONTENT_NL = '<p>Vici.org is een niet-commerciële website die wordt beheerd door één particulier — er is geen bedrijf of organisatie bij betrokken. Dit statement legt uit welke persoonsgegevens Vici.org verwerkt en waarom.</p>

<h2>Verwerkingsverantwoordelijke</h2>
<p>René Voorburg, <a href="mailto:rene@digitopia.nl">rene@digitopia.nl</a>. De hosting vindt plaats binnen de Europese Unie.</p>

<h2>Wat er bij registratie wordt opgeslagen</h2>
<ul>
<li><strong>Accountnaam</strong> en <strong>weergavenaam</strong>. De weergavenaam wordt publiek getoond bij de pagina’s en afbeeldingen waaraan je bijdraagt; je kiest die zelf — het hoeft niet je echte naam te zijn en je kunt hem aanpassen in je profielinstellingen.</li>
<li><strong>E-mailadres</strong>. Wordt alleen gebruikt voor accountactivatie en het herstellen van wachtwoorden. Het wordt nooit publiek getoond, nooit gedeeld met anderen en nooit verkocht — Vici.org is niet commercieel en heeft geen belang bij jouw gegevens.</li>
<li><strong>Wachtwoord</strong>. Wordt nooit als zodanig opgeslagen; er wordt alleen een cryptografische hash bewaard, zodat de beheerder je wachtwoord niet kan lezen.</li>
</ul>

<h2>Bijdragen</h2>
<p>Teksten, afbeeldingen en lijnen die je bijdraagt worden gepubliceerd onder de licenties die op de site vermeld staan (teksten CC BY-SA, metadata CC0) en worden publiek aan je weergavenaam toegeschreven. Bijdragen worden onder eigen verantwoordelijkheid door gebruikers geleverd; zie je een probleem met een bijdrage, meld het dan.</p>

<h2>Cookies en tracking</h2>
<p>Vici.org gebruikt alleen functionele cookies: een sessiecookie (PHPSESSID) dat nodig is voor inloggen en voorkeuren. Er zijn geen advertentie- of trackingcookies van derden. Bot-bescherming gebeurt met een zelf-gehoste proof-of-work-check (ALTCHA); er wordt niets naar een externe dienst gestuurd.</p>

<h2>Technische gegevens</h2>
<p>Zoals elke webserver houdt de site technische logs bij die IP-adressen kunnen bevatten, en gebruikt hij op IP gebaseerde rate limiting om de dienst te beschermen. Deze gegevens worden niet aan je account gekoppeld, niet gedeeld en alleen bewaard zolang dat voor beveiliging en probleemoplossing nodig is.</p>

<h2>Je rechten</h2>
<p>Op grond van de AVG kun je verzoeken om inzage in, correctie of verwijdering van je persoonsgegevens, en je kunt bezwaar maken tegen verwerking. Je e-mailadres wordt nooit gepubliceerd, dus mail simpelweg <a href="mailto:rene@digitopia.nl">rene@digitopia.nl</a>. Let op: gepubliceerde bijdragen zijn bedoeld als blijvend onderdeel van de atlas (zoals bij Wikipedia); verwijderen van je account verwijdert ze niet automatisch — kies dus een weergavenaam die je wilt gebruiken.</p>';

    private const CONTENT_DE = '<p>Vici.org ist eine nicht-kommerzielle Website, die von einer einzelnen Privatperson betrieben wird — es ist weder ein Unternehmen noch eine Organisation beteiligt. Diese Erklärung beschreibt, welche personenbezogenen Daten Vici.org verarbeitet und warum.</p>

<h2>Verantwortliche Stelle</h2>
<p>René Voorburg, <a href="mailto:rene@digitopia.nl">rene@digitopia.nl</a>. Das Hosting findet innerhalb der Europäischen Union statt.</p>

<h2>Was bei der Registrierung gespeichert wird</h2>
<ul>
<li><strong>Kontoname</strong> und <strong>Anzeigename</strong>. Der Anzeigename wird öffentlich bei den Seiten und Bildern gezeigt, zu denen du beiträgst; du wählst ihn selbst — es muss nicht dein echter Name sein und du kannst ihn in deinen Profileinstellungen ändern.</li>
<li><strong>E-Mail-Adresse</strong>. Wird nur für die Kontobestätigung und das Zurücksetzen des Passworts verwendet. Sie wird nie öffentlich gezeigt, nie an Dritte weitergegeben und nie verkauft — Vici.org ist nicht kommerziell und hat kein Interesse an deinen Daten.</li>
<li><strong>Passwort</strong>. Wird nie im Klartext gespeichert; es wird nur ein kryptografischer Hash aufbewahrt, sodass der Betreiber dein Passwort nicht lesen kann.</li>
</ul>

<h2>Beiträge</h2>
<p>Texte, Bilder und Linien, die du beisteuerst, werden unter den auf der Website genannten Lizenzen veröffentlicht (Texte CC BY-SA, Metadaten CC0) und öffentlich deinem Anzeigenamen zugeschrieben. Beiträge werden von Nutzern in eigener Verantwortung bereitgestellt; sollte dir ein Problem mit einem Beitrag auffallen, melde es bitte.</p>

<h2>Cookies und Tracking</h2>
<p>Vici.org verwendet nur funktionale Cookies: ein Session-Cookie (PHPSESSID), das für Anmeldung und Einstellungen nötig ist. Es gibt keine Werbe- oder Tracking-Cookies von Dritten. Der Bot-Schutz erfolgt über eine selbst gehostete Proof-of-Work-Prüfung (ALTCHA); es werden keine Daten an externe Dienste gesendet.</p>

<h2>Technische Daten</h2>
<p>Wie jeder Webserver führt die Site technische Logs, die IP-Adressen enthalten können, und nutzt IP-basiertes Rate Limiting zum Schutz des Dienstes. Diese Daten werden nicht mit deinem Konto verknüpft, nicht weitergegeben und nur so lange aufbewahrt, wie es für Sicherheit und Fehlerbehebung nötig ist.</p>

<h2>Deine Rechte</h2>
<p>Nach der DSGVO kannst du Auskunft über, Berichtigung oder Löschung deiner personenbezogenen Daten verlangen und der Verarbeitung widersprechen. Deine E-Mail-Adresse wird nie veröffentlicht — schreib einfach an <a href="mailto:rene@digitopia.nl">rene@digitopia.nl</a>. Beachte: veröffentlichte Beiträge sind als dauerhafter Teil des Atlas gedacht (wie bei Wikipedia); das Löschen deines Kontos entfernt sie nicht automatisch — wähle daher einen Anzeigenamen, mit dem du dich wohlfühlst.</p>';

    private const CONTENT_FR = '<p>Vici.org est un site non commercial géré par un seul particulier — aucune entreprise ni organisation n’y est impliquée. Cette déclaration explique quelles données personnelles Vici.org traite et pourquoi.</p>

<h2>Responsable du traitement</h2>
<p>René Voorburg, <a href="mailto:rene@digitopia.nl">rene@digitopia.nl</a>. L’hébergement a lieu au sein de l’Union européenne.</p>

<h2>Ce qui est enregistré lors de l’inscription</h2>
<ul>
<li><strong>Nom de compte</strong> et <strong>nom d’affichage</strong>. Le nom d’affichage est montré publiquement sur les pages et les images auxquelles vous contribuez ; vous le choisissez vous-même — ce n’est pas nécessairement votre vrai nom et vous pouvez le modifier dans vos paramètres.</li>
<li><strong>Adresse e-mail</strong>. Utilisée uniquement pour l’activation du compte et la réinitialisation du mot de passe. Elle n’est jamais affichée publiquement, jamais partagée et jamais vendue — Vici.org n’est pas commercial et n’a aucun intérêt dans vos données.</li>
<li><strong>Mot de passe</strong>. Jamais stocké en clair ; seul un hachage cryptographique est conservé, de sorte que l’opérateur ne peut pas lire votre mot de passe.</li>
</ul>

<h2>Contributions</h2>
<p>Les textes, images et lignes que vous contribuez sont publiés sous les licences indiquées sur le site (textes CC BY-SA, métadonnées CC0) et sont attribués publiquement à votre nom d’affichage. Les contributions sont fournies par les utilisateurs sous leur propre responsabilité ; si vous constatez un problème dans une contribution, merci de le signaler.</p>

<h2>Cookies et pistage</h2>
<p>Vici.org n’utilise que des cookies fonctionnels : un cookie de session (PHPSESSID) nécessaire à la connexion et aux préférences. Il n’y a aucun cookie publicitaire ni de pistage par des tiers. La protection contre les robots se fait par une vérification proof-of-work auto-hébergée (ALTCHA) ; rien n’est envoyé à un service externe.</p>

<h2>Données techniques</h2>
<p>Comme tout serveur web, le site conserve des journaux techniques pouvant contenir des adresses IP, et utilise une limitation de débit basée sur l’IP pour protéger le service. Ces données ne sont pas liées à votre compte, ne sont pas partagées et ne sont conservées que le temps nécessaire à la sécurité et au dépannage.</p>

<h2>Vos droits</h2>
<p>En vertu du RGPD, vous pouvez demander l’accès à vos données personnelles, leur rectification ou leur suppression, et vous pouvez vous opposer au traitement. Votre adresse e-mail n’est jamais publiée — écrivez simplement à <a href="mailto:rene@digitopia.nl">rene@digitopia.nl</a>. Attention : les contributions publiées sont conçues comme une partie durable de l’atlas (comme sur Wikipédia) ; supprimer votre compte ne les retire pas automatiquement — choisissez donc un nom d’affichage qui vous convient.</p>';

    public function __construct(Session $session)
    {
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);

        $content = [
            'nl' => self::CONTENT_NL,
            'de' => self::CONTENT_DE,
            'fr' => self::CONTENT_FR,
        ][$session->getLanguage()] ?? self::CONTENT_EN;

        $this->assign('privacy_content', $content);
        $this->assign('title', $session->translator->get('Privacy'));
    }
}
