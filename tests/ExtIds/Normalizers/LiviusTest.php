<?php declare(strict_types=1);

use ExtIds\Normalizers\Livius;
use PHPUnit\Framework\TestCase;

final class LiviusTest extends TestCase
{
//    public function testIsValidUrl(): void
//    {
//        $object = new Livius();
//        $this->assertTrue($object->isValidURL('https://www.livius.org/articles/place/rome/rome-photos/via-appia/'));
//        $this->assertTrue($object->isValidURL('http://livius.org/articles/place/rome/rome-photos/via-appia/'));
//        $this->assertTrue($object->isValidURL('https://www.livius.org/museum/belginum/'));
//        $this->assertTrue($object->isValidURL('https://www.livius.org/museum/belginum'));
//
//        $this->assertFalse($object->isValidURL('http://livdius.org/nu.nl/'));
//    }
//
//    public function testIsValidTag(): void
//    {
//        $object = new Livius();
//        $this->assertTrue($object->isValidTag('livius:place=sempeter/sempeter-mausoleum-of-ennius'));
//        $this->assertTrue($object->isValidTag('livius:place=sirmium-sremska-mitrovica'));
//        $this->assertTrue($object->isValidTag('livius:museum=belginum'));
//        $this->assertTrue($object->isValidTag('livius:museum=belginum/'));
//
//        $this->assertFalse($object->isValidTag('livius:livius=livius'));
//        $this->assertFalse($object->isValidTag('wikidata:entity=q98956'));
//    }
//
//    public function testIsValidId(): void
//    {
//        $object = new Livius();
//        $this->assertTrue($object->isValidId('place=bagacum-bavay'));
//        $this->assertTrue($object->isValidId('source-content=the-siege-of-flevum-28-ce'));
//        $this->assertTrue($object->isValidId('museum=kalkriese-museum-und-park'));
//        $this->assertTrue($object->isValidId('battle=teutoburg-forest-9-ce/kalkriese!'));
//
//        $this->assertFalse($object->isValidId('livius=kalkriese-museum-und-park'));
//        $this->assertFalse($object->isValidId('story=teutoburg-forest-9-ce/kalkriese'));
//    }
//
//    public function testUrlToTag() : void
//    {
//        $object = new Livius();
//        $this->assertEquals(
//            'livius:museum=belginum',
//            $object->urlToTag('https://www.livius.org/museum/belginum')
//        );
//        $this->assertEquals(
//            'livius:place=sirmium-sremska-mitrovica',
//            $object->urlToTag('https://www.livius.org/articles/place/sirmium-sremska-mitrovica')
//        );
//        $this->assertEquals(
//            'livius:place=rome/rome-photos/via-appia',
//            $object->urlToTag('https://www.livius.org/articles/place/rome/rome-photos/via-appia/')
//        );
//        $this->assertEquals(
//            'livius:place=rome/rome-photos/via-appia',
//            $object->urlToTag('http://livius.org/articles/place/rome/rome-photos/via-appia')
//        );
//        $this->assertEquals(
//            '',
//            $object->urlToTag('https://www.liviusx.org/museum/belginum')
//        );
//    }

    public function testGetUrlProvidingTag() : void
    {
        $livius = new Livius();
        $livius->setUrlByTagOrUrl('livius:museum=belginum');
        $this->assertEquals('https://livius.org/museum/belginum', $livius->getUrl());

        $livius->setUrlByTagOrUrl('livius:museum=belginum/');
        $this->assertEquals('https://livius.org/museum/belginum', $livius->getUrl());

        $livius->setUrlByTagOrUrl('livius:place=sirmium-sremska-mitrovica');
        $this->assertEquals('https://livius.org/articles/place/sirmium-sremska-mitrovica', $livius->getUrl());

        $livius->setUrlByTagOrUrl('livius:battle=sirmium-sremska-mitrovica/bla');
        $this->assertEquals('https://livius.org/articles/battle/sirmium-sremska-mitrovica/bla', $livius->getUrl());

        $livius->setUrlByTagOrUrl('livius:battle=sirmium-sremska-mitrovica/bla/blaat');
        $this->assertEquals('https://livius.org/articles/battle/sirmium-sremska-mitrovica/bla/blaat', $livius->getUrl());

        $livius->setUrlByTagOrUrl('http://www.wikidata.org/wiki/Q98956x');
        $this->assertEquals('https://livius.org/articles/battle/sirmium-sremska-mitrovica/bla/blaat', $livius->getUrl());
    }

    public function testGetUrlProvidingId() : void
    {
        $livius = new Livius();
        $livius->setUrlById('place=sirmium-sremska-mitrovica');
        $this->assertEquals('https://livius.org/articles/place/sirmium-sremska-mitrovica', $livius->getUrl());

        $livius->setUrlById('place=sirmium-sremska-mitrovica/');
        $this->assertEquals('https://livius.org/articles/place/sirmium-sremska-mitrovica', $livius->getUrl());

        $livius->setUrlById('place=sirmium-sremska-mitrovica/bloe');
        $this->assertEquals('https://livius.org/articles/place/sirmium-sremska-mitrovica/bloe', $livius->getUrl());

        $livius->setUrlById('place=sirmium-sremska-mitrovica/bloe/');
        $this->assertEquals('https://livius.org/articles/place/sirmium-sremska-mitrovica/bloe', $livius->getUrl());

        $livius->setUrlById('place=sirmium-sremska-mitrovica/bloe/bla');
        $this->assertEquals('https://livius.org/articles/place/sirmium-sremska-mitrovica/bloe/bla', $livius->getUrl());

        $livius->setUrlById('place=sirmium-sremska-mitrovica/bloe/bla/');
        $this->assertEquals('https://livius.org/articles/place/sirmium-sremska-mitrovica/bloe/bla', $livius->getUrl());

        $livius->setUrlById('museum=belginum');
        $this->assertEquals('https://livius.org/museum/belginum', $livius->getUrl());

        $livius->setUrlById('nosuch=belginuma');
        $this->assertEquals('https://livius.org/museum/belginum', $livius->getUrl());
    }

    public function testGetUrlProvidingUrl() : void
    {
        $livius = new Livius();
        $livius->setUrlByTagOrUrl('http://www.livius.org/museum/belginum/');
        $this->assertEquals('https://livius.org/museum/belginum', $livius->getUrl());

        $livius->setUrlByTagOrUrl('https://wikidata.org/entity/Q1234');
        $this->assertEquals('https://livius.org/museum/belginum', $livius->getUrl());

        $livius->setUrlByTagOrUrl('https://wikidata.org/entity/Q98956');
        $this->assertEquals('https://livius.org/museum/belginum', $livius->getUrl());

        $livius->setUrlByTagOrUrl('http://www.livius.org/museum/belginum/');
        $this->assertEquals('https://livius.org/museum/belginum', $livius->getUrl());
    }

    public function testGetid() : void
    {
        $livius = new Livius();
        $livius->setUrlByTagOrUrl('https://livius.org/museum/belginum');
        $this->assertEquals('museum=belginum', $livius->getId());
        $livius->setUrlByTagOrUrl('livius=sirmium-sremska-mitrovica');
        $this->assertEquals('museum=belginum', $livius->getId());
    }

}
