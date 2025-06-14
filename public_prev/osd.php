<?php header('Content-Type:application/opensearchdescription+xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<OpenSearchDescription  xmlns="http://a9.com/-/spec/opensearch/1.1/" >
<!--                        xmlns:time="http://a9.com/-/opensearch/extensions/time/1.0/"-->
<!--                        xmlns:geo="http://a9.com/-/opensearch/extensions/geo/1.0/">-->
    <ShortName>Vici.org</ShortName>
    <Description>Search the Archaeological Atlas of Antiquity</Description>
    <Tags>roman classical antiquity history places sites finds archaeology map open data creative commons</Tags>
    <Contact>rene@digitopia.nl</Contact>
    <Url type="text/html" template="https://vici.org/search.php?terms={searchTerms}&amp;page={startPage?}"/>
<!--    <Url type="text/html" template="http://vici.org/search.php?terms={searchTerms?}&amp;page={startPage?}&amp;from={time:start}"/>-->
<!--    <Url type="text/html" template="http://vici.org/search.php?terms={searchTerms?}&amp;page={startPage?}&amp;near={geo:lat},{geo:lon}&amp;radius={geo:radius?}"/>-->
    <Url type="application/json" template="https://vici.org/search.php?terms={searchTerms}&amp;format=json"/>
    <Url type="application/vnd.google-earth.kml+xml" template="https://vici.org/search.php?terms={searchTerms}&amp;format=kml"/>
<!--    <Url type="application/vnd.google-earth.kml+xml" template="http://vici.org/search.php?terms={searchTerms?}&amp;near={geo:lat},{geo:lon}&amp;radius={geo:radius?}&amp;format=kml"/>-->
    <Url type="application/opensearchdescription+xml" rel="self" template="https://vici.org/osd.php" />
    <Query role="example" searchTerms="hadrian" />
</OpenSearchDescription>