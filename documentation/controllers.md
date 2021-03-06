# Controllers

The controller section

## Init hook

Before any action dispatch the framework executes the `init()` method.

```php
<?php
class IndexController extends Controller
{
    public function init()
    {
        // The init hook
    }
}
```

Using the object inheritance could be a good choice for this hook.

```php
<?php
abstract class BaseController extends Controller
{
    public function init()
    {
        // Reusable code
    }
}
```

## Next action

The `next` action goes forward to the next action appending
the next view.

```php
<?php 
class IndexController extends Controller
{
    public function indexAction()
    {
        $this->view->hello = "hello";
        
        
        $this->then("/index/next");
    }
    
    public function nextAction()
    {
        $this->view->cose = "ciao";
    }
}
```

The result is the first view (`index.phtml`) concatenated to the
second view (`next.phtml`).

## Redirects 

You can handle redirects using the `redirect()` method

```php
<?php 
class IndexController extends Controller
{
    public function indexAction()
    {
        // Send as moved temporarily
        $this->redirect("/contr/act", 302);
    } 
}
```

## Interact with layout and views

You can disable the layout system at any time using the `disableLayout()`
method.

```php
<?php 
class IndexController extends Controller
{
    public function indexAction()
    {
        // Remove layout
        $this->disableLayout();
    } 
}
```

You can disable the view attached to a controller using the `setNoRender()`
method

```php
<?php 
class IndexController extends Controller
{
    public function indexAction()
    {
        // No this view
        $this->setNoRender();
    } 
}
```

## Change the layout on the fly

If you want to change your layout during an action or a plugin interaction
you can use the resources manager

```php
<?php
class IndexController extends Controller
{
    public function fullWithAction()
    {
        $this->getResource("layout")->setScriptName("full-width.phtml");
    } 
}
```

Obviously you must use the layout manager.

## Using headers

You can send different headers using `addHeader()` method

```php
<?php 
class IndexController extends Controller
{
    public function indexAction()
    {
        $this->addHeader("Content-Type", "text/plain");
    } 
}
```

The end.