<?php declare(strict_types=1);

use ExtIds\Normalizers\Pleiades;
use PHPUnit\Framework\TestCase;

final class PleiadesTest extends TestCase
{
//    public function testIsValidUrl(): void
//    {
//        $object = new Pleiades();
//        $this->assertTrue($object->isValidURL('https://pleiades.stoa.org/places/98956'));
//        $this->assertTrue($object->isValidURL('http://pleiades.stoa.org/places/98956'));
//
//        $this->assertFalse($object->isValidURL('https://pleiades.stoa.org/places/aa/98956'));
//        $this->assertFalse($object->isValidURL('https://www.livius.org/museum/belginum'));
//    }
//
//    public function testIsValidTag(): void
//    {
//        $object = new Pleiades();
//        $this->assertTrue($object->isValidTag('pleiades:place=123'));
//        $this->assertFalse($object->isValidTag('pleiades:place=123x'));
//        $this->assertFalse($object->isValidTag('pleiades:places=123'));
//        $this->assertFalse($object->isValidTag('livius:museum=belginum/'));
//    }
//
//    public function testIsValidId(): void
//    {
//        $object = new Pleiades();
//        $this->assertTrue($object->isValidId('1'));
//        $this->assertTrue($object->isValidId('6373'));
//        $this->assertTrue($object->isValidId('74848748'));
//        $this->assertTrue($object->isValidId('0'));
//
//        $this->assertFalse($object->isValidId('-1'));
//        $this->assertFalse($object->isValidId('1+1'));
//        $this->assertFalse($object->isValidId('abc'));
//    }
//
//    public function testUrlToTag() : void
//    {
//        $object = new Pleiades();
//        $this->assertEquals(
//            'pleiades:place=98956',
//            $object->urlToTag('http://pleiades.stoa.org/places/98956')
//        );
//        $this->assertEquals(
//            'pleiades:place=98956',
//            $object->urlToTag('https://pleiades.stoa.org/places/98956')
//        );
//        $this->assertEquals(
//            '',
//            $object->urlToTag('https://pleiades.stoa.org/places/98956s')
//        );
//
//    }

    public function testGetUrlProvidingTag() : void
    {
        $object = new Pleiades();
        $object->setUrlByTagOrUrl('pleiades:place=123');
        $this->assertEquals('https://pleiades.stoa.org/places/123', $object->getUrl());

        $object->setUrlByTagOrUrl('pleiades:place=125x');
        $this->assertEquals('https://pleiades.stoa.org/places/123', $object->getUrl());
    }

    public function testGetUrlProvidingId() : void
    {
        $object = new Pleiades();
        $object->setUrlById('123');
        $this->assertEquals('https://pleiades.stoa.org/places/123', $object->getUrl());

        $object->setUrlById('0');
        $this->assertEquals('https://pleiades.stoa.org/places/123', $object->getUrl());
    }

    public function testGetUrlProvidingUrl() : void
    {
        $object = new Pleiades();
        $object->setUrlByTagOrUrl('https://pleiades.stoa.org/places/1234');
        $this->assertEquals('https://pleiades.stoa.org/places/1234', $object->getUrl());

        $object->setUrlByTagOrUrl('https://pleiades.stoa.org/places/1234');
        $this->assertEquals('https://pleiades.stoa.org/places/1234', $object->getUrl());

        $object->setUrlByTagOrUrl('https://wikidata.org/entity/Q98956');
        $this->assertEquals('https://pleiades.stoa.org/places/1234', $object->getUrl());

        $object->setUrlByTagOrUrl('https://pleiades.stoa.org/places/123');
        $this->assertEquals('https://pleiades.stoa.org/places/123', $object->getUrl());
    }

    public function testGetid() : void
    {
        $object = new Pleiades();
        $object->setUrlByTagOrUrl('https://pleiades.stoa.org/places/321');
        $this->assertEquals('321', $object->getId());
        $object->setUrlByTagOrUrl('livius=sirmium-sremska-mitrovica');
        $this->assertEquals('321', $object->getId());
    }
}
