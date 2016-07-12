# Tentative Readme for the GoatFramework Beta 0.1

This file is intended as a quick tutorial and information center for the Goat Framework Core.

It is in no way a full resource, only a quickstart to allow you to discover the system.
Use the code documentation for more detailed informations.

## Coding standards exceptions

This project uses the [PSR-1](http://www.php-fig.org/psr/psr-1/), 
[PSR-2](http://www.php-fig.org/psr/psr-2/)and [PSR-4]¸(http://www.php-fig.org/psr/psr-4/) 
coding standards with some exceptions. All these exceptions are tentative and subject to change.

- In PSR-2, the *Opening braces for control structures MUST go on the same line, 
and closing braces MUST go on the next line after the body* is ignored in favor of 
*Opening braces for control structures MUST go on the next line, and closing braces 
MUST go on the next line after the body* due to personal preferences.
- In PSR-1, the Vendor name for namespaces is ignored. This is temporary.
- PSR-3 is skipped, the core logger class may implement this interface in the future.

## Build a static page

The framework comes with two pages, the default index Hello world page 
and a minimalist error page.

For this example, we will create the Example/Example page. To add more pages, you must 
follow the routing standards of the framework.

By default, all accepted Request must either send the user to the default page or
follow this standard : www.example.com/Folder/Page/Action/Param1/Param2/.../ParamN

The first two elements of the URL, the Folder and page pair, is called the route.
This route is used by the system to locate the MVC trio for the page while the remaining
parts of the URL are used to send calls to the controller. 

In the Models and Views folder in the project root, create an Example folder and 
an Example class. Both class are named with a simple standard which is classnameModel
and classnameView.

In the model class, write this code :

```
namespace Models\Example;

class ExampleModel extends \Core\MVC\CoreModel
{
    protected $data = "<h1>Example</h1>\nWorking example";
}
```

The $data attribute is what will be shown on the screen, it can be anything, as 
long as it can be converted to a string by the view.

In the view, write this code :

```
namespace Views\Example;

class ExampleView extends \Core\MVC\CoreView
{
}
```

The view is empty, the core View does all the job needed to show the data on the screen.

Now run your site at your address/Example/Example and you should see the text shown on the screen.
You will also notice the route shown. The route is printed by the view in order for your
javascript code to be able to send calls to the right page. Hide this div with CSS.

## Make your page dynamic

To make our example a bit more dynamic, we now need to create a controller. A controller
can send signals and data from the page to the model, but it cannot send anything 
to the view, everything goes through the model. the controller only job is to receive, 
filter and manage data before sending it to the model.

For our example, we would like to be able to show a bit more text if the controller 
receives a signal. For example, if we tried to access www.example.com/Example/Example/Show/Hello,
we want the page to display the example page plus a big Hello.

To do this, remember the second part of the URL, after the route. Action/Param1/Param2/.../ParamN
where the action is the method called on the controller and the params its parameters.

In the Controllers folder, create an Example folder and an ExampleController like 
in the previous section. In this class, write this code :

```
namespace Controllers\Example;

class ExampleController extends \Core\MVC\CoreController
{
    public function Show(string $text)
    {
        $this->Model->setData($this->Model->getData . "<h2>$text</h2>");
    }
}
```

The controller has a reference to the page's model, we set the data as it's 
current data plus the text given in parameters. Run the page again and you should
see a big hello next to the example.

When making a dynamic page (or any page), you don't have to manage exceptions inside
your classes (except those you want to catch). All the core MVC classes's method have
an aspect appended to them that catch all exception when thrown and show the error page.
It also set the model's internal exception with the thrown exception.

You can use the aspects to add such functionalities to your page, see the 
Core\Aspect classes for more details.