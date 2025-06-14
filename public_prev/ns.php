<?php header('Content-type: application/rdf+xml');
echo '<?xml version="1.0" encoding="utf-8" ?>'."\n"; ?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:owl="http://www.w3.org/2002/07/owl#"
         xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
         xmlns:dc="http://purl.org/dc/terms/"
         xmlns:mads="http://www.loc.gov/mads/rdf/v1#">

    <owl:Ontology rdf:about="http://vici.org/ns/2015/07#">
        <rdfs:label>Ontology aimed to describe and support the model behind or used by http://vici.org/.</rdfs:label>
        <dc:creator>René Voorburg</dc:creator>
    </owl:Ontology>

    <!-- core classes for the vici-website: -->

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Marker">
        <rdfs:subClassOf rdf:resource="http://xmlns.com/foaf/0.1/Document"/>
        <rdfs:comment>A marker represents a #Site on a map.</rdfs:comment>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Site">
        <rdfs:label>Site</rdfs:label>
        <rdfs:subClassOf rdf:resource="http://erlangen-crm.org/current/E27_Site"/>
        <rdfs:subClassOf rdf:resource="http://lawd.info/ontology/Place"/>
        <rdfs:comment>Core object type described by http://vici.org. </rdfs:comment>
    </rdfs:Class>


    <!-- first level subclasses for Site: -->

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Infrastructure">
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Site"/>
        <rdfs:label>Site of civic infrastructural objects or constructions</rdfs:label>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Military">
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Site"/>
        <rdfs:label>Site of military buildings or infrastructure</rdfs:label>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Settlement">
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Site"/>
        <rdfs:label>Site of civic or rural settlements. Primarily for non-military purposes or civic part of military settlement.</rdfs:label>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Construction">
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Site"/>
        <rdfs:label>(Site of) Buildings or other larger fixed man made constructions.</rdfs:label>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Artefact">
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Site"/>
        <rdfs:label>Artefacts in situ or smaller remains of constructions.</rdfs:label>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Intangible">
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Site"/>
        <rdfs:subClassOf rdf:resource="http://erlangen-crm.org/current/E2_Temporal_Entity"/>
        <rdfs:label>Site of an event in time</rdfs:label>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Touristic">
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Site"/>
        <rdfs:label>A current day location related to historic sites, events or objects.</rdfs:label>
    </rdfs:Class>

    <!-- more classes, all subClass of Site: -->

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#City">
        <rdfs:label>City</rdfs:label>
        <rdfs:label xml:lang="nl">Stad</rdfs:label>
        <rdfs:comment xml:lang="en">A city like a civitas or colonia, municipum.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Settlement"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300008389"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Vicus">
        <rdfs:label>Village</rdfs:label>
        <rdfs:label xml:lang="nl">Dorp</rdfs:label>
        <rdfs:comment xml:lang="en">A village like a canabae or a vicus.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Settlement"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300000754"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Rural">
        <rdfs:label>Farm</rdfs:label>
        <rdfs:label xml:lang="nl">Hoeve</rdfs:label>
        <rdfs:comment xml:lang="en">A rural settlement, a farm or a small group of farms.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Settlement"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300008420"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Fort">
        <rdfs:label>Castle</rdfs:label>
        <rdfs:label xml:lang="nl">Fort</rdfs:label>
        <rdfs:comment xml:lang="en">A castle, fort, mini-fort or naval base. Construction aimed at housing military personel.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Military"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300006888"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Watchtower">
        <rdfs:label>Watchtower</rdfs:label>
        <rdfs:label xml:lang="nl">Wachtpost</rdfs:label>
        <rdfs:comment xml:lang="en">A watchtower, signaltower or comparable smaller military construction.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Military"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300134522"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Camp">
        <rdfs:label>Camp</rdfs:label>
        <rdfs:label xml:lang="nl">Mars- of oefenkamp</rdfs:label>
        <rdfs:comment xml:lang="en">A temporary military camp like a marching camp or practicing camp.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Military"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300164117"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Aquaduct">
        <rdfs:label>Aqueduct</rdfs:label>
        <rdfs:label xml:lang="nl">Aquaduct</rdfs:label>
        <rdfs:comment xml:lang="en">An aqueduct, or a location where a part of an aqueduct is visible. Can be displayed on the map as a line.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Infrastructure"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300006165"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Road">
        <rdfs:label>Road</rdfs:label>
        <rdfs:label xml:lang="nl">Weg</rdfs:label>
        <rdfs:comment xml:lang="en">A paved or hardened road. Can be displayed on the map as a line.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Infrastructure"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300008217"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Bridge">
        <rdfs:label>Bridge</rdfs:label>
        <rdfs:label xml:lang="nl">Brug</rdfs:label>
        <rdfs:comment xml:lang="en">A bridge, usually as part of a road system.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Infrastructure"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300007836"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Villa">
        <rdfs:label>Villa rustica</rdfs:label>
        <rdfs:comment xml:lang="en">The central building or the complex of buildings of an agricultural estate.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Construction"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300005518"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Mansio">
        <rdfs:label>Mansio</rdfs:label>
        <rdfs:label xml:lang="nl">Herberg</rdfs:label>
        <rdfs:comment xml:lang="en">A resting place along a road, like a tavern or a small settlement related to it. Might be a mansio or mutatio.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Construction"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300120555"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Theater">
        <rdfs:label>Theatre</rdfs:label>
        <rdfs:comment xml:lang="en">A theatre, amphitheatre, stadium, circus or similar.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Construction"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300108449"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300007255"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300007271"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300007117"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Baths">
        <rdfs:label>Baths</rdfs:label>
        <rdfs:label xml:lang="nl">Thermen</rdfs:label>
        <rdfs:comment xml:lang="en">Public baths or a bath house that is part of a larger complex.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Construction"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300080054"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Temple">
        <rdfs:label>Temple</rdfs:label>
        <rdfs:label xml:lang="nl">Tempel</rdfs:label>
        <rdfs:comment xml:lang="en">A temple, sanctuary or early church.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Construction"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300120364"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Industry">
        <rdfs:label>Workshop</rdfs:label>
        <rdfs:label xml:lang="nl">Werkplaats</rdfs:label>
        <rdfs:comment xml:lang="en">A workshop or industry like a mine, port or pottery.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Construction"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300004531"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Graves">
        <rdfs:label>Grave</rdfs:label>
        <rdfs:label xml:lang="nl">Graf</rdfs:label>
        <rdfs:comment xml:lang="en">A group of graves, a burial field or a significant grave monument.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Construction"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300266755"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300005891"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300000372"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Building">
        <rdfs:label>Other building</rdfs:label>
        <rdfs:label xml:lang="nl">Overig gebouw</rdfs:label>
        <rdfs:comment xml:lang="en">Stone remains of a building, not matching any other class. Fallback for more specific categories.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Construction"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300004792"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Altar">
        <rdfs:label>Altar</rdfs:label>
        <rdfs:label xml:lang="nl">Altaar</rdfs:label>
        <rdfs:comment xml:lang="en">A relief, votiv stone, altar or similar monument. Excludes graves or burial monuments.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Artefact"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300003725"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300047090"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Milestone">
        <rdfs:label>Milestone</rdfs:label>
        <rdfs:label xml:lang="nl">Mijlsteen</rdfs:label>
        <rdfs:comment xml:lang="en">The original location of a milestone.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Artefact"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300006973"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Shipwreck">
        <rdfs:label>Shipwreck</rdfs:label>
        <rdfs:label xml:lang="nl">Scheepswrak</rdfs:label>
        <rdfs:comment xml:lang="en">Location where the remains of a ship were found.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Artefact"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300386973"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Object">
        <rdfs:label>Object or find</rdfs:label>
        <rdfs:label xml:lang="nl">Object of vondst</rdfs:label>
        <rdfs:comment xml:lang="en">The location of smaller archaeological finds, artefacts like pottery or coins.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Artefact"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300266151"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Observation">
        <rdfs:label>Observation</rdfs:label>
        <rdfs:label xml:lang="nl">Observatie</rdfs:label>
        <rdfs:comment xml:lang="en">Location of an archaeological observation, for example traces of a ditch of a road. Fallback for more specific categories.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Artefact"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300266151"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Event">
        <rdfs:label>Site of historical event</rdfs:label>
        <rdfs:label xml:lang="nl">Plaats van historische gebeurtenis</rdfs:label>
        <rdfs:comment xml:lang="en">The location of a significant historic event, for example a battlefield.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Intangible"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300069084"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Museum">
        <rdfs:label>Museum</rdfs:label>
        <rdfs:comment xml:lang="en">A museum about classical antiquity or that has artifacts from antiquity on display.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Touristic"/>
        <rdfs:subClassOf rdf:resource="http://wikidata.org/entity/Q33506"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300005768"/>
    </rdfs:Class>

    <rdfs:Class rdf:about="http://vici.org/ns/2015/07#Memorial">
        <rdfs:label>Memorial</rdfs:label>
        <rdfs:comment xml:lang="en">A contemporary monument that reminds about the history related to the specific location by artistic means or with a replica.</rdfs:comment>
        <rdfs:subClassOf rdf:resource="http://vici.org/ns/2015/07#Touristic"/>
        <rdfs:subClassOf rdf:resource="http://wikidata.org/wiki/Q4989906"/>
        <mads:isIdentifiedByAuthority rdf:resource="http://vocab.getty.edu/aat/300006956"/>
    </rdfs:Class>


    <!-- properties: -->

    <rdf:Property rdf:about="http://vici.org/ns/2015/07#smallZoom">
        <rdfs:label>Lowest zoomlevel for icons</rdfs:label>
        <rdfs:comment xml:lang="en">Lowest zoomlevel at which a small icon is shown on the map.</rdfs:comment>
        <rdfs:domain rdf:resource="http://vici.org/ns/2015/07#Marker"/>
        <rdfs:range rdf:resource="http://www.w3.org/2001/XMLSchema#int"/>
    </rdf:Property>

    <rdf:Property rdf:about="http://vici.org/ns/2015/07#bigZoom">
        <rdfs:label>Zoomlevel showing large icons.</rdfs:label>
        <rdfs:comment xml:lang="en">Lowest zoomlevel at which the big icon is shown on the map.</rdfs:comment>
        <rdfs:domain rdf:resource="http://vici.org/ns/2015/07#Marker"/>
        <rdfs:range rdf:resource="http://www.w3.org/2001/XMLSchema#int"/>
    </rdf:Property>

    <rdf:Property rdf:about="http://vici.org/ns/2015/07#isVisible">
        <rdfs:label>Visibility</rdfs:label>
        <rdfs:comment xml:lang="en">Value 1 for sites that are visible either onsite or on current satellite imagery, otherwise 0.</rdfs:comment>
        <rdfs:domain rdf:resource="http://vici.org/ns/2015/07#Site"/>
        <rdfs:range rdf:resource="http://www.w3.org/2001/XMLSchema#int"/>
    </rdf:Property>

    <rdf:Property rdf:about="http://vici.org/ns/2015/07#hasAccuracy">
        <rdfs:label>Accuracy</rdfs:label>
        <rdfs:comment xml:lang="en">
            Value for expected accuracy of the location.
            0 = better than 1 meter (point inside perimeter of site) ;
            1 = 1 to 5 meters ;
            2 = 5 to 25 meters ;
            3 = 25 to 100 meters ;
            4 = 100 to 500 meters ;
            5 = 500 meters or worse.
        </rdfs:comment>
        <rdfs:domain rdf:resource="http://www.w3.org/2003/01/geo/wgs84_pos#location"/>
        <rdfs:range rdf:resource="http://www.w3.org/2001/XMLSchema#int"/>
    </rdf:Property>

</rdf:RDF>