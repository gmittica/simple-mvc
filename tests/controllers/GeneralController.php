<?php 
class GeneralController extends Controller
{
    public function titleHelperAction()
    {
        
    }
    
    public function pullAction()
    {
        return array('title' => 'ok');
    }
    
    public function directAction()
    {
        
    }
    
    public function pullDataAction()
    {
        $clazz = new stdClass();
        
        $clazz->title = 'Controller Data';
        
        return $clazz;
    }
}