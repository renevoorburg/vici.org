<?php declare(strict_types=1);

use ExtIds\Normalizers\Romaq;
use PHPUnit\Framework\TestCase;

class RomaqTest extends TestCase
{
//    public function testIsValidUrl(): void
//    {
//        $object = new Romaq();
//        $this->assertTrue($object->isValidURL('https://www.romaq.org/the-project/aqueducts/article/685'));
//        $this->assertTrue($object->isValidURL('https://www.romaq.org/the-project/aqueducts/1153'));
//        $this->assertTrue($object->isValidURL('https://www.romaq.org/the-project/aqueducts/1153-abellum.html'));
//        $this->assertTrue($object->isValidURL('http://romaq.org/the-project/aqueducts/1153-abellum.html'));
//    }
//
//    public function testIsValidTag(): void
//    {
//        $object = new Romaq();
//        $this->assertTrue($object->isValidTag('romaq:aqueduct=685'));
//        $this->assertTrue($object->isValidTag('romaq:aqueduct=  685'));
//        $this->assertTrue($object->isValidTag('romaq=  685'));
//        $this->assertFalse($object->isValidTag('romaq:aqueduct='));
//    }
//
//    public function testIsValidId(): void
//    {
//        $object = new Romaq();
//        $this->assertTrue($object->isValidId('98956'));
//        $this->assertFalse($object->isValidId('98956x'));
//        $this->assertFalse($object->isValidId(''));
//    }
//
//    public function testUrlToTag() : void
//    {
//        $object = new Romaq();
//        $this->assertEquals(
//            'romaq:aqueduct=685',
//            $object->urlToTag('https://www.romaq.org/the-project/aqueducts/article/685')
//        );
//        $this->assertEquals(
//            'romaq:aqueduct=1153',
//            $object->urlToTag('https://www.romaq.org/the-project/aqueducts/1153')
//        );
//        $this->assertEquals(
//            'romaq:aqueduct=1153',
//            $object->urlToTag('https://www.romaq.org/the-project/aqueducts/1153-abellum.html')
//        );
//    }

    public function testGetUrlProvidingTag() : void
    {
        $object = new Romaq();
        $object->setUrlByTagOrUrl('romaq:aqueduct=1153');
        $this->assertEquals('https://www.romaq.org/the-project/aqueducts/1153', $object->getUrl());

        $object->setUrlByTagOrUrl('dare:id=98956');
        $this->assertEquals('https://www.romaq.org/the-project/aqueducts/1153', $object->getUrl());
    }

    public function testGetUrlProvidingId() : void
    {
        $object = new Romaq();
        $object->setUrlById('98956');
        $this->assertEquals('https://www.romaq.org/the-project/aqueducts/98956', $object->getUrl());

        $object->setUrlById('jhsdjhsd');
        $this->assertEquals('https://www.romaq.org/the-project/aqueducts/98956', $object->getUrl());
    }

    public function testGetUrlProvidingUrl() : void
    {
        $object = new Romaq();
        $object->setUrlByTagOrUrl('https://www.romaq.org/the-project/aqueducts/1153');
        $this->assertEquals('https://www.romaq.org/the-project/aqueducts/1153', $object->getUrl());

        $object->setUrlByTagOrUrl('https://www.romaq.org/the-project/aqueducts/1154');
        $this->assertEquals('https://www.romaq.org/the-project/aqueducts/1154', $object->getUrl());
    }

    public function testGetid() : void
    {
        $object = new Romaq();
        $object->setUrlByTagOrUrl('https://www.romaq.org/the-project/aqueducts/1154');
        $this->assertEquals('1154', $object->getId());
    }

}