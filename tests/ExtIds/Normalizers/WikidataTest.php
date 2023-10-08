<?php declare(strict_types=1);

use ExtIds\Normalizers\Wikidata;
use PHPUnit\Framework\TestCase;

final class WikidataTest extends TestCase
{
//    public function testIsValidUrl(): void
//    {
//        $object = new Wikidata();
//        $this->assertTrue($object->isValidURL('https://wikidata.org/entity/Q98956'));
//        $this->assertTrue($object->isValidURL('https://wikidata.org/entity/q98956'));
//        $this->assertTrue($object->isValidURL('http://www.wikidata.org/entity/q9895'));
//        $this->assertTrue($object->isValidURL('http://www.wikidata.org/wiki/q989566'));
//
//        $this->assertFalse($object->isValidURL('http://www.wikidata.org/wikidata/q98956'));
//        $this->assertFalse($object->isValidURL('https://wikidata.org/entity/Q98956x'));
//    }
//
//    public function testIsValidTag(): void
//    {
//        $object = new Wikidata();
//        $this->assertTrue($object->isValidTag('wikidata=Q98956'));
//        $this->assertTrue($object->isValidTag('wikidata:entity=Q98956'));
//        $this->assertTrue($object->isValidTag('wikidata:entity=q98956'));
//
//        $this->assertFalse($object->isValidTag('wikidata:page=q98956'));
//        $this->assertFalse($object->isValidTag('wikidata=q98956x'));
//        $this->assertFalse($object->isValidTag('wikidata:entity=q98956x'));
//    }
//
//    public function testIsValidId(): void
//    {
//        $object = new Wikidata();
//        $this->assertTrue($object->isValidId('Q98956'));
//        $this->assertTrue($object->isValidId('q98956'));
//
//        $this->assertFalse($object->isValidId('p98956'));
//        $this->assertFalse($object->isValidId('q98956!'));
//    }
//
//    public function testUrlToTag() : void
//    {
//        $object = new Wikidata();
//        $this->assertEquals(
//            'wikidata:entity=Q98956',
//            $object->urlToTag('https://wikidata.org/entity/Q98956')
//        );
//        $this->assertEquals(
//            'wikidata:entity=Q98956',
//            $object->urlToTag('https://wikidata.org/entity/q98956')
//        );
//        $this->assertEquals(
//            'wikidata:entity=Q98956',
//            $object->urlToTag('https://wikidata.org/wiki/Q98956')
//        );
//
//        $this->assertNotEquals(
//            'wikidata:entity=Q98956',
//            $object->urlToTag('https://wikidata.org/page/Q98956')
//        );
//    }

    public function testGetUrlProvidingTag() : void
    {
        $wiki = new Wikidata();
        $wiki->setUrlByTagOrUrl('wikidata:entity=Q98956');
        $this->assertEquals('https://wikidata.org/entity/Q98956', $wiki->getUrl());

        $wiki->setUrlByTagOrUrl('wikidata:wiki=Q98956');
        $this->assertEquals('https://wikidata.org/entity/Q98956', $wiki->getUrl());

        $wiki->setUrlByTagOrUrl('http://www.wikidata.org/wiki/Q98956x');
        $this->assertEquals('https://wikidata.org/entity/Q98956', $wiki->getUrl());

        $wiki->setUrlByTagOrUrl('wikidata=Q2586986');
        $this->assertEquals('https://wikidata.org/entity/Q2586986', $wiki->getUrl());
    }

    public function testGetUrlProvidingId() : void
    {
        $wiki = new Wikidata();
        $wiki->setUrlById('Q98956');
        $this->assertEquals('https://wikidata.org/entity/Q98956', $wiki->getUrl());

        $wiki = new Wikidata();
        $this->assertEquals('', $wiki->setUrlById('Q98956x'));
    }

    public function testGetUrlProvidingUrl() : void
    {
        $wiki = new Wikidata();
        $wiki->setUrlByTagOrUrl('https://wikidata.org/entity/Q98956');
        $this->assertEquals('https://wikidata.org/entity/Q98956', $wiki->getUrl());

        $wiki->setUrlByTagOrUrl('https://wikidata.org/entity/Q1234');
        $this->assertEquals('https://wikidata.org/entity/Q1234', $wiki->getUrl());

        $wiki->setUrlByTagOrUrl('https://wikidata.org/entity/Q98956');
        $this->assertEquals('https://wikidata.org/entity/Q98956', $wiki->getUrl());
    }

    public function testGetId() : void
    {
        $wiki = new Wikidata();
        $wiki->setUrlByTagOrUrl('https://wikidata.org/entity/Q98956');
        $this->assertEquals('Q98956', $wiki->getId());
    }

}
