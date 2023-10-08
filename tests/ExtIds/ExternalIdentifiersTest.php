<?php declare(strict_types=1);

use ExtIds\ExternalIdentifiers;
use PHPUnit\Framework\TestCase;


final class ExternalIdentifiersTest extends TestCase
{
    public function testIdsFromSpannedUris(): void
    {
        $object = ExternalIdentifiers::withDbParams('<span>https://www.romaq.org/the-project/aqueducts/1153-abellum.html</span><span>http://imperium.ahlfeldt.se/places/98956</span><span>https://wikidata.org/entity/Q98956</span><span>https://pleiades.stoa.org/places/98956</span><span>http://www.livius.org/articles/place/rome/rome-photos/via-appia</span>');

        $this->assertEquals('98956', $object->getId('pleiades'));
        $this->assertEquals('place=rome/rome-photos/via-appia', $object->getId('livius'));
        $this->assertEquals('Q98956', $object->getId('wikidata'));
        $this->assertEquals('98956', $object->getId('dare'));
        $this->assertEquals('1153', $object->getId('romaq'));
    }

    public function testIdsFromSpannedTags(): void
    {
        $object = ExternalIdentifiers::withDbParams(
            '<span>pleiades:place=98956</span><span>livius:place=rome/rome-photos/via-appia</span><span>romaq:aqueduct=1154</span><span>wikidata:entity=Q98956</span><span>dare:place=123</span>'
        );
        $this->assertEquals('98956', $object->getId('pleiades'));
        $this->assertEquals('place=rome/rome-photos/via-appia', $object->getId('livius'));
        $this->assertEquals('Q98956', $object->getId('wikidata'));
        $this->assertEquals('123', $object->getId('dare'));
        $this->assertEquals('1154', $object->getId('romaq'));
    }

    public function testIdsFromParamsWithSpannedTags(): void
    {
        $object = ExternalIdentifiers::withDbParams(
            '<span>pleiades:place=98956</span><span>livius:place=rome/rome-photos/via-appia</span><span>wikidata:entity=Q98956</span>',
            '98957',
            'place=rome'
        );
        $this->assertEquals('98957', $object->getId('pleiades'));
        $this->assertEquals('place=rome', $object->getId('livius'));
    }

    public function testIdsFromParamsWithSpannedUrls(): void
    {
        $object = ExternalIdentifiers::withDbParams(
            '<span>https://pleiades.stoa.org/places/98956</span><span>http://www.livius.org/articles/place/rome/rome-photos/via-appia</span><span>wikidata:entity=Q98956</span>',
            '98957',
            'place=rome'
        );
        $this->assertEquals('98957', $object->getId('pleiades'));
        $this->assertEquals('place=rome', $object->getId('livius'));
    }

    public function testIdsFromDuplicateSpannedTags(): void
    {
        $object = ExternalIdentifiers::withDbParams(
            '<span>dare:place=123</span><span>http://imperium.ahlfeldt.se/places/321</span><span>pleiades:place=98956</span><span>https://pleiades.stoa.org/places/98957</span><span>livius:place=rome</span><span>wikidata:entity=Q98956</span><span>http://www.livius.org/articles/place/rome/rome-photos/via-appia</span>'
        );
        $this->assertEquals('98957', $object->getId('pleiades'));
        $this->assertEquals('place=rome/rome-photos/via-appia', $object->getId('livius'));
        $this->assertEquals('Q98956', $object->getId('wikidata'));
        $this->assertEquals('321', $object->getId('dare'));
    }

    public function testSpannedUrls() : void
    {
        $object = ExternalIdentifiers::withDbParams(
            '<span>pleiades:place=98956</span><span>http://www.nu.nl/</span><span>http://www.nu.nl/</span><span>livius:place=rome/rome-photos/via-appia</span><span>romaq:aqueduct=1154</span><span>wikidata:entity=Q98956</span><span>dare:place=123</span><span>dare:place=321</span>'
        );
        $this->assertEquals(
            '<span>https://pleiades.stoa.org/places/98956</span><span>https://livius.org/articles/place/rome/rome-photos/via-appia</span><span>https://www.romaq.org/the-project/aqueducts/1154</span><span>http://imperium.ahlfeldt.se/places/321</span><span>https://wikidata.org/entity/Q98956</span><span>http://www.nu.nl/</span>' ,
            $object->getAllUrlsSpanned()
        );
    }

    public function testUrlsArraySpanned() : void
    {
        $urlsArr = ['https://pleiades.stoa.org/places/98956', 'https://livius.org/articles/place/rome/rome-photos/via-appia'];
        $this->assertEquals(
            '<span>https://pleiades.stoa.org/places/98956</span><span>https://livius.org/articles/place/rome/rome-photos/via-appia</span>',
            ExternalIdentifiers::urlsArraySpanned($urlsArr)
        );
    }


    public function testTagsSpanned() : void
    {
        $object = ExternalIdentifiers::withDbParams(
            '<span>https://livius.org/articles/place/roma</span><span>http://imperium.ahlfeldt.se/places/13</span><span>https://wikidata.org/entity/Q124</span><span>https://www.mithraeum.eu/monument/21</span>'
        );
        $this->assertEquals(
            'mithraeum:monument=21',
            $object->getTag('mithraeum')
        );
    }

}
