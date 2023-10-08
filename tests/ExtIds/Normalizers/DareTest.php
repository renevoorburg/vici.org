<?php declare(strict_types=1);

use ExtIds\Normalizers\Dare;
use PHPUnit\Framework\TestCase;

final class DareTest extends TestCase
{
//    public function testIsValidUrl(): void
//    {
//        $object = new Dare();
//        $this->assertTrue($object->isValidURL('http://dare.ht.lu.se/places/27576.html'));
//        $this->assertTrue($object->isValidURL('https://dare.ht.lu.se/places/27576.html'));
//        $this->assertTrue($object->isValidURL('http://francia.ahlfeldt.se/page/places/38'));
//        $this->assertTrue($object->isValidURL('https://imperium.ahlfeldt.se/places/45914'));
//        $this->assertTrue($object->isValidURL('https://dh.gu.se/dare/places/45914'));
//        $this->assertTrue($object->isValidURL('https://dh.gu.se/dare/places/45914.html'));
//        $this->assertFalse($object->isValidURL('https://dh.gu.sex/dare/places/45914.html'));
//    }
//
//    public function testIsValidTag(): void
//    {
//        $object = new Dare();
//        $this->assertTrue($object->isValidTag('dare:place=123'));
//        $this->assertTrue($object->isValidTag('dare:id=123'));
//        $this->assertTrue($object->isValidTag('dare id=123'));
//        $this->assertTrue($object->isValidTag('dare id= 123'));
//        $this->assertTrue($object->isValidTag('dareid=123'));
//        $this->assertTrue($object->isValidTag('dare place=123'));
//        $this->assertTrue($object->isValidTag('dare=  123'));
//
//        $this->assertFalse($object->isValidTag('dare:place='));
//    }
//
//    public function testIsValidId(): void
//    {
//        $object = new Dare();
//        $this->assertTrue($object->isValidId('98956'));
//        $this->assertFalse($object->isValidId('98956x'));
//        $this->assertFalse($object->isValidId(''));
//    }
//
//    public function testUrlToTag() : void
//    {
//        $object = new Dare();
//        $this->assertEquals(
//            'dare:place=27576',
//            $object->urlToTag('http://dare.ht.lu.se/places/27576.html')
//        );
//        $this->assertEquals(
//            'dare:place=27576',
//            $object->urlToTag('https://dare.ht.lu.se/places/27576.html')
//        );
//        $this->assertEquals(
//            'dare:place=38',
//            $object->urlToTag('http://francia.ahlfeldt.se/page/places/38')
//        );
//        $this->assertEquals(
//            'dare:place=45914',
//             $object->urlToTag('https://imperium.ahlfeldt.se/places/45914')
//        );
//        $this->assertEquals(
//            'dare:place=45914',
//            $object->urlToTag('https://dh.gu.se/dare/places/45914')
//        );
//        $this->assertEquals(
//            'dare:place=45914',
//            $object->urlToTag('https://dh.gu.se/dare/places/45914.html')
//        );
//    }

    public function testGetUrlProvidingTag() : void
    {
        $object = new Dare();
        $object->setUrlByTagOrUrl('dare:place=98956');
        $this->assertEquals('http://imperium.ahlfeldt.se/places/98956', $object->getUrl());

        $object->setUrlByTagOrUrl('dare id=98956');
        $this->assertEquals('http://imperium.ahlfeldt.se/places/98956', $object->getUrl());

        $object->setUrlByTagOrUrl('dare:id= 98956');
        $this->assertEquals('http://imperium.ahlfeldt.se/places/98956', $object->getUrl());
    }

    public function testGetUrlProvidingId() : void
    {
        $object = new Dare();
        $object->setUrlById('98956');
        $this->assertEquals('http://imperium.ahlfeldt.se/places/98956', $object->getUrl());

        $object->setUrlById('0');
        $this->assertEquals('http://imperium.ahlfeldt.se/places/98956', $object->getUrl());
    }

    public function testGetUrlProvidingUrl() : void
    {
        $object = new Dare();
        $object->setUrlByTagOrUrl('http://dare.ht.lu.se/places/27576.html');
        $this->assertEquals('http://imperium.ahlfeldt.se/places/27576', $object->getUrl());
        $object->setUrlByTagOrUrl('http://imperium.ahlfeldt.se/places/777');
        $this->assertEquals('http://imperium.ahlfeldt.se/places/777', $object->getUrl());
    }

    public function testGetid() : void
    {
        $object = new Dare();
        $object->setUrlByTagOrUrl('http://imperium.ahlfeldt.se/places/27576');
        $this->assertEquals('27576', $object->getId());
    }

}