<?php header('Content-type: text/turtle'); ?>
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix dc: <http://purl.org/dc/terms/> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .
@prefix ns0: <http://www.loc.gov/mads/rdf/v1#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#> .

<http://vici.org/ns/2015/07#>
  a owl:Ontology ;
  rdfs:label "Ontology aimed to describe and support the model behind or used by http://vici.org/."@en ;
  dc:creator "René Voorburg" .

<http://vici.org/ns/2015/07#Marker>
  a rdfs:Class ;
  rdfs:subClassOf foaf:Document ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:comment "A marker represents a #Site on a map."@en .

<http://vici.org/ns/2015/07#Site>
  a rdfs:Class ;
  rdfs:label "Site" ;
  rdfs:subClassOf <http://erlangen-crm.org/current/E27_Site>, <http://lawd.info/ontology/Place> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:comment "Core object type described by http://vici.org. "@en .

<http://vici.org/ns/2015/07#Infrastructure>
  a rdfs:Class ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Site> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:label "Site of civic infrastructural objects or constructions"@en .

<http://vici.org/ns/2015/07#Military>
  a rdfs:Class ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Site> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:label "Site of military buildings or infrastructure"@en .

<http://vici.org/ns/2015/07#Settlement>
  a rdfs:Class ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Site> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:label "Site of civic or rural settlements. Primarily for non-military purposes or civic part of military settlement."@en .

<http://vici.org/ns/2015/07#Construction>
  a rdfs:Class ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Site> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:label "(Site of) Buildings or other larger fixed man made constructions."@en .

<http://vici.org/ns/2015/07#Artefact>
  a rdfs:Class ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Site> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:label "Artefacts in situ or smaller remains of constructions."@en .

<http://vici.org/ns/2015/07#Intangible>
  a rdfs:Class ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Site>, <http://erlangen-crm.org/current/E2_Temporal_Entity> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:label "Site of an event in time"@en .

<http://vici.org/ns/2015/07#Touristic>
  a rdfs:Class ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Site> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:label "A current day location related to historic sites, events or objects."@en .

<http://vici.org/ns/2015/07#City>
  a rdfs:Class ;
  rdfs:label "City"@en, "Stad"@nl ;
  rdfs:comment "A city like a civitas or colonia, municipum."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Settlement> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300008389> .

<http://vici.org/ns/2015/07#Vicus>
  a rdfs:Class ;
  rdfs:label "Village"@en, "Dorp"@nl ;
  rdfs:comment "A village like a canabae or a vicus."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Settlement> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300000754> .

<http://vici.org/ns/2015/07#Rural>
  a rdfs:Class ;
  rdfs:label "Farm"@en, "Hoeve"@nl ;
  rdfs:comment "A rural settlement, a farm or a small group of farms."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Settlement> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300008420> .

<http://vici.org/ns/2015/07#Fort>
  a rdfs:Class ;
  rdfs:label "Castle"@en, "Fort"@nl ;
  rdfs:comment "A castle, fort, mini-fort or naval base. Construction aimed at housing military personel."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Military> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300006888> .

<http://vici.org/ns/2015/07#Watchtower>
  a rdfs:Class ;
  rdfs:label "Watchtower"@en, "Wachtpost"@nl ;
  rdfs:comment "A watchtower, signaltower or comparable smaller military construction."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Military> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300134522> .

<http://vici.org/ns/2015/07#Camp>
  a rdfs:Class ;
  rdfs:label "Camp"@en, "Mars- of oefenkamp"@nl ;
  rdfs:comment "A temporary military camp like a marching camp or practicing camp."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Military> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300164117> .

<http://vici.org/ns/2015/07#Aquaduct>
  a rdfs:Class ;
  rdfs:label "Aqueduct"@en, "Aquaduct"@nl ;
  rdfs:comment "An aqueduct, or a location where a part of an aqueduct is visible. Can be displayed on the map as a line."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Infrastructure> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300006165> .

<http://vici.org/ns/2015/07#Road>
  a rdfs:Class ;
  rdfs:label "Road"@en, "Weg"@nl ;
  rdfs:comment "A paved or hardened road. Can be displayed on the map as a line."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Infrastructure> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300008217> .

<http://vici.org/ns/2015/07#Bridge>
  a rdfs:Class ;
  rdfs:label "Bridge"@en, "Brug"@nl ;
  rdfs:comment "A bridge, usually as part of a road system."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Infrastructure> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300007836> .

<http://vici.org/ns/2015/07#Villa>
  a rdfs:Class ;
  rdfs:label "Villa rustica"@en ;
  rdfs:comment "The central building or the complex of buildings of an agricultural estate."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Construction> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300005518> .

<http://vici.org/ns/2015/07#Mansio>
  a rdfs:Class ;
  rdfs:label "Mansio"@en, "Herberg"@nl ;
  rdfs:comment "A resting place along a road, like a tavern or a small settlement related to it. Might be a mansio or mutatio."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Construction> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300120555> .

<http://vici.org/ns/2015/07#Theater>
  a rdfs:Class ;
  rdfs:label "Theatre"@en ;
  rdfs:comment "A theatre, amphitheatre, stadium, circus or similar."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Construction> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300108449>, <http://vocab.getty.edu/aat/300007255>, <http://vocab.getty.edu/aat/300007271>, <http://vocab.getty.edu/aat/300007117> .

<http://vici.org/ns/2015/07#Baths>
  a rdfs:Class ;
  rdfs:label "Baths"@en, "Thermen"@nl ;
  rdfs:comment "Public baths or a bath house that is part of a larger complex."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Construction> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300080054> .

<http://vici.org/ns/2015/07#Temple>
  a rdfs:Class ;
  rdfs:label "Temple"@en, "Tempel"@nl ;
  rdfs:comment "A temple, sanctuary or early church."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Construction> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300120364> .

<http://vici.org/ns/2015/07#Industry>
  a rdfs:Class ;
  rdfs:label "Workshop"@en, "Werkplaats"@nl ;
  rdfs:comment "A workshop or industry like a mine, port or pottery."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Construction> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300004531> .

<http://vici.org/ns/2015/07#Graves>
  a rdfs:Class ;
  rdfs:label "Grave"@en, "Graf"@nl ;
  rdfs:comment "A group of graves, a burial field or a significant grave monument."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Construction> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300266755>, <http://vocab.getty.edu/aat/300005891>, <http://vocab.getty.edu/aat/300000372> .

<http://vici.org/ns/2015/07#Building>
  a rdfs:Class ;
  rdfs:label "Other building"@en, "Overig gebouw"@nl ;
  rdfs:comment "Stone remains of a building, not matching any other class. Fallback for more specific categories."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Construction> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300004792> .

<http://vici.org/ns/2015/07#Altar>
  a rdfs:Class ;
  rdfs:label "Altar"@en, "Altaar"@nl ;
  rdfs:comment "A relief, votiv stone, altar or similar monument. Excludes graves or burial monuments."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Artefact> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300003725>, <http://vocab.getty.edu/aat/300047090> .

<http://vici.org/ns/2015/07#Milestone>
  a rdfs:Class ;
  rdfs:label "Milestone"@en, "Mijlsteen"@nl ;
  rdfs:comment "The original location of a milestone."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Artefact> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300006973> .

<http://vici.org/ns/2015/07#Shipwreck>
  a rdfs:Class ;
  rdfs:label "Shipwreck"@en, "Scheepswrak"@nl ;
  rdfs:comment "Location where the remains of a ship were found."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Artefact> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300386973> .

<http://vici.org/ns/2015/07#Object>
  a rdfs:Class ;
  rdfs:label "Object or find"@en, "Object of vondst"@nl ;
  rdfs:comment "The location of smaller archaeological finds, artefacts like pottery or coins."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Artefact> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300266151> .

<http://vici.org/ns/2015/07#Observation>
  a rdfs:Class ;
  rdfs:label "Observation"@en, "Observatie"@nl ;
  rdfs:comment "Location of an archaeological observation, for example traces of a ditch of a road. Fallback for more specific categories."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Artefact> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300266151> .

<http://vici.org/ns/2015/07#Event>
  a rdfs:Class ;
  rdfs:label "Site of historical event"@en, "Plaats van historische gebeurtenis"@nl ;
  rdfs:comment "The location of a significant historic event, for example a battlefield."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Intangible> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300069084> .

<http://vici.org/ns/2015/07#Museum>
  a rdfs:Class ;
  rdfs:label "Museum"@en ;
  rdfs:comment "A museum about classical antiquity or that has artifacts from antiquity on display."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Touristic>, <http://wikidata.org/entity/Q33506> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300005768> .

<http://vici.org/ns/2015/07#Memorial>
  a rdfs:Class ;
  rdfs:label "Memorial"@en ;
  rdfs:comment "A contemporary monument that reminds about the history related to the specific location by artistic means or with a replica."@en ;
  rdfs:subClassOf <http://vici.org/ns/2015/07#Touristic>, <http://wikidata.org/wiki/Q4989906> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  ns0:isIdentifiedByAuthority <http://vocab.getty.edu/aat/300006956> .

<http://vici.org/ns/2015/07#smallZoom>
  a rdf:Property ;
  rdfs:label "Lowest zoomlevel for icons"@en ;
  rdfs:comment "Lowest zoomlevel at which a small icon is shown on the map."@en ;
  rdfs:domain <http://vici.org/ns/2015/07#Marker> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:range xsd:int .

<http://vici.org/ns/2015/07#bigZoom>
  a rdf:Property ;
  rdfs:label "Zoomlevel showing large icons."@en ;
  rdfs:comment "Lowest zoomlevel at which the big icon is shown on the map."@en ;
  rdfs:domain <http://vici.org/ns/2015/07#Marker> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:range xsd:int .

<http://vici.org/ns/2015/07#isVisible>
  a rdf:Property ;
  rdfs:label "Visibility"@en ;
  rdfs:comment "Value 1 for sites that are visible either onsite or on current satellite imagery, otherwise 0."@en ;
  rdfs:domain <http://vici.org/ns/2015/07#Site> ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:range xsd:int .

<http://vici.org/ns/2015/07#hasAccuracy>
  a rdf:Property ;
  rdfs:label "Accuracy"@en ;
  rdfs:comment """
            Value for expected accuracy of the location.
            0 = better than 1 meter (point inside perimeter of site) ;
            1 = 1 to 5 meters ;
            2 = 5 to 25 meters ;
            3 = 25 to 100 meters ;
            4 = 100 to 500 meters ;
            5 = 500 meters or worse.
        """@en ;
  rdfs:domain geo:location ;
  rdfs:isDefinedBy <http://vici.org/ns/2015/07#> ;
  rdfs:range xsd:int .