<?php
require_once dirname(__FILE__) . '/../src/Application.php';

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-06-20 at 20:20:27.
 */
class ApplicationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //Suppress sendHeaders        
        $this->object = $this->getMock("Application", array('sendHeaders'));
        $this->object->setControllerPath(__DIR__ . '/controllers');
        $this->object->expects($this->any())->method("sendHeaders")->will($this->returnValue(null));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Application::bootstrap
     * @covers Application::getBootstrap
     */
    public function testBootstrap()
    {
        $this->object->bootstrap("hello", function(){return "ciao";});
        $boot = $this->object->getBootstrap("hello");
        
        $this->assertEquals($boot, "ciao");
    }
    
    /**
     * Resources must bootstrap onetime
     * 
     * @covers Application::getBootstrap 
     */
    public function testGetMultipleTimes()
    {
        $this->object->bootstrap("hello", function(){
            return new View();
        });
        $boot = $this->object->getBootstrap("hello");
        $boot2 = $this->object->getBootstrap("hello");
        
        $this->assertSame($boot, $boot2);
    }
    
    public function testSetGetEventManager()
    {
        $mng = new EventManager();
        $this->object->setEventManager($mng);
        
        $this->assertSame($mng, $this->object->getEventManager());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testBootstrapNotCallable()
    {
        $this->object->bootstrap("up", "not-callable");
    }
    
    public function testMissingLayout()
    {
        $this->object->bootstrap("view", function(){
            $v = new View();
            $v->setViewPath(__DIR__ . '/views');
        });
        $this->object->setControllerPath(__DIR__ . '/controllers');
        
        ob_start();
        $this->object->run("/error/error");
        $content = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals("--> error action <--", $content);
        
    }
    
    public function testErrorPages()
    {
        ob_start();
        $this->object->run("/invalid/controller");
        $errorPage = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals("--> error action <--", $errorPage);
    }
    
    public function testInitAction()
    {
        ob_start();
        $this->object->run("init/index");
        $initOutput = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals("<-- init -->", $initOutput);
    }

    public function testPreDispatchHook()
    {
        $app = '';
        $this->object->getEventManager()->subscribe("pre.dispatch", function($r, $app){
            $r->setControllerName("admin");
            $r->setActionName("login");
        });
        
        ob_start();
        $this->object->run("/init/index");
        $adminOutput = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals("<-- admin login -->", $adminOutput);
    }
    
    public function testThenMethod()
    {
        ob_start();
        $this->object->run("/then/first");
        $thenOutput = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals("first-><-second", $thenOutput);
    }
    
    public function testMissingAction()
    {
        $this->setExpectedException("RuntimeException", "Page not found admin/missing-action", 404);
        
        $this->object->dispatch("/admin/missing-action");
    }
    
    public function testSafeBaseView()
    {
        $v = new View();
        $v->setViewPath(__DIR__ . '/views');
        
        $this->object->bootstrap("view", function() use ($v) {
            return $v;
        });
        
        $phpunit = $this;
        $this->object->getEventManager()->subscribe("post.dispatch", function($controller) use ($phpunit, $v) {
            $view = $controller->view;
            $phpunit->assertNotSame($v, $view);
        });
        
        $this->object->dispatch("/admin/login");
    }
    
    public function testMissingEventManager()
    {
        $app = new Application();
        $eventManager = $app->getEventManager();
        $this->assertInstanceOf("EventManager", $eventManager);
    }
    
    public function testLayout()
    {
        $this->object->bootstrap("layout", function(){
            $l = new Layout();
            $l->setScriptName("layout.phtml");
            $l->setViewPath(__DIR__ . '/layouts');
            return $l;
        });
        
        ob_start();
        $this->object->run("/init/index");
        $content = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals("<body><-- init --></body>", $content);
    }
    
    public function testLayoutViewHelpersPass()
    {
        $this->object->bootstrap('layout', function(){
            $l = new Layout();
            $l->setScriptName("title-helper.phtml");
            $l->setViewPath(__DIR__ . '/layouts');
            
            $l->addHelper("title", function($part = false){
                static $parts = array();
                static $delimiter = ' :: ';
            
                return ($part === false) ? implode($delimiter, $parts) : $parts[] = $part;
            });
            
            return $l;
        });
        
        $this->object->bootstrap('view', function(){
            $v = new View();
            $v->setViewPath(__DIR__ . '/views');
            
            return $v;
        });
        
        ob_start();
        $this->object->run("/general/title-helper");
        $content = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals("<title>the title helper :: second</title>", $content);
    }
    
    public function testEmptyPullDrivenRequest()
    {
        $this->object->bootstrap("view", function(){
            $v = new View();
            $v->setViewPath(__DIR__ . '/views');
            
            return $v;
        });
        
        ob_start();
        $this->object->run("/general/pull-driven");
        $content = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals("<p>Pull-driven experience</p>", $content);
    }
    
    public function testCompletelyMissingPullDrivenRequest()
    {
        $this->object->setControllerPath(null);
        $this->object->bootstrap("view", function(){
            $v = new View();
            $v->setViewPath(__DIR__ . '/views');
        
            return $v;
        });
        
        ob_start();
        $this->object->run("/pull/driven");
        $content = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals("<h1>Complete pull driven</h1>", $content);
    }
    
    public function testCompletelyMissingPullWithDataDrivenRequest()
    {
        $this->object->bootstrap("view", function(){
            $v = new View();
            $v->setViewPath(__DIR__ . '/views');
    
            return $v;
        });
    
        ob_start();
        $this->object->run("/pull/driven-data");
        $content = ob_get_contents();
        ob_end_clean();
    
        $this->assertEquals("<h2>Controller Data</h2>", $content);
    }
}
