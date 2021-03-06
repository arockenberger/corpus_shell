<?php

namespace tests\unit\ACDH\FCSSRU\mysqlonsru;

use ACDH\FCSSRU\mysqlonsru\FilteredGlossaryOnSRU;

$runner = true;

require_once __DIR__ . '/GlossaryTestBase.php';
require_once __DIR__ . '/../../../../../vendor/autoload.php';
// this is not autoload enabled yet. There are to many magic global constants that need to be set when loading.
require_once __DIR__ . '/../../../../../modules/mysqlonsru/FilteredGlossaryOnSRU.php';
/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-10-24 at 15:50:48.
 */
class FilteredGlossaryOnSRUTest extends GlossaryTestBase {

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        parent::setUp();
        $this->t = new FilteredGlossaryOnSRU($this->params);        
        
        $ref = new \ReflectionProperty('ACDH\FCSSRU\mysqlonsru\FilteredGlossaryOnSRU', 'db');
        $ref->setAccessible(true);
        $ref->setValue($this->t, $this->dbMock);
    }

    protected function tearDown() {
        
    }

    /**
     * @test
     */
    public function it_should_use_the_right_sql_for_restricted_scan() {
        $this->params->operation = 'scan';
        // default context is a restricted one.
        $this->setupDBMockForSqlScan($this->getBothFilters());
        
        $ret = $this->t->scan();
        
        $this->assertInstanceOf('ACDH\FCSSRU\SRUDiagnostics', $ret);
    }
    
    protected function getReleasedPrefilter() {
        return "(SELECT tab.id, tab.xpath, tab.txt FROM $this->context"."_ndx AS tab ".
                     "INNER JOIN ".
                        "(SELECT inner.id FROM $this->context"."_ndx AS `inner` ".
                        "WHERE inner.txt = 'released' ".
                        "AND inner.xpath LIKE '%-change-f-status-') AS prefid ".
                     "ON tab.id = prefid.id WHERE tab.txt != '-') ";
    }
    
    protected function getBothFilters() {
        return "(SELECT tab.id, tab.xpath, tab.txt FROM " . $this->getReleasedPrefilter() . "AS tab ".
                     "INNER JOIN ".
                        "(SELECT inner.id FROM $this->context"."_ndx AS `inner` ".
                        "WHERE CAST(inner.txt AS signed int) < 2 ".
                        "AND inner.xpath LIKE '%entry-xr-bibl-tunisCourse-') AS prefid ".
                     "ON tab.id = prefid.id WHERE tab.txt != '-') AS ndx ";        
    }
    
    /**
     * @test
     */
    public function it_should_use_the_right_sql_for_restricted_search() {
        $this->params->operation = 'searchRetrieve';
        $query = 'waer';
        $this->params->query = $query;
        // default context is a restricted one.
        $this->setupDBMockForSqlSearch($this->getBothFilters());
        
        $ret = $this->t->search();
        
        $this->assertInstanceOf('ACDH\FCSSRU\SRUDiagnostics', $ret);
    }

}
